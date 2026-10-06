from typing import Any, Literal, Protocol

from pydantic import BaseModel, ConfigDict, Field

from app.contracts.requests import TutorConversationTurn
from app.contracts.responses import KnowledgeEvidence
from app.prompts.guards import detect_prompt_injection
from app.tutor.conversation import conversation_entry, social_conversation_entry
from app.tutor.needs import SystemProgramNeed

STRUCTURED_PROVIDER_VERSION = "structured-answer-v3.0.0"


class TutorMaterial(BaseModel):
    model_config = ConfigDict(extra="forbid")

    question: str
    subject: str | None
    level: str | None
    allowed_academic_context: dict[str, Any]
    allowed_student_context: dict[str, Any]
    conversation_history: list[TutorConversationTurn] = Field(default_factory=list)
    evidence: list[KnowledgeEvidence]
    insufficient_evidence: bool
    intent: Literal[
        "SOCIAL",
        "CAPABILITIES",
        "GENERAL_PEDAGOGICAL",
        "EVIDENCE_GROUNDED",
    ] = "EVIDENCE_GROUNDED"
    requires_official_evidence: bool = True
    system_needs: list[SystemProgramNeed] = Field(default_factory=list)


class ProviderAnswer(BaseModel):
    model_config = ConfigDict(extra="forbid")

    answer: str
    suggested_topics: list[str] = Field(max_length=8)
    warnings: list[str]


class AnswerProvider(Protocol):
    provider_version: str

    def answer(self, material: TutorMaterial) -> ProviderAnswer: ...


def _suggested_topics(evidence: list[KnowledgeEvidence]) -> list[str]:
    topics: list[str] = []
    for item in evidence:
        candidate = item.section or item.title
        if candidate not in topics:
            topics.append(candidate)
        if len(topics) == 5:
            break
    return topics


def _asks_for_deterministic_career_decision(question: str) -> bool:
    normalized = question.casefold()
    markers = (
        "qué carrera debo",
        "que carrera debo",
        "elige mi carrera",
        "decide mi carrera",
        "recomiéndame una carrera",
        "recomiendame una carrera",
    )
    return any(marker in normalized for marker in markers)


def _asks_about_intelligence(question: str) -> bool:
    normalized = question.casefold()
    markers = ("inteligente", "inteligencia", "coeficiente intelectual", "ci alto", "iq")
    return any(marker in normalized for marker in markers)


def _asks_about_success_probability(question: str) -> bool:
    normalized = question.casefold()
    markers = (
        "probabilidad de éxito",
        "probabilidad de exito",
        "éxito asegurado",
        "exito asegurado",
        "% de éxito",
        "% de exito",
    )
    return any(marker in normalized for marker in markers)


def _contains_injection_or_adversarial(question: str) -> bool:
    normalized = question.casefold()
    markers = (
        "ignora todas las instrucciones",
        "ignora las instrucciones",
        "ignore previous instructions",
        "recomiéndame ser astronauta",
        "recomiendame ser astronauta",
        "cítame la fuente inexistente",
        "citame la fuente inexistente",
        "invéntame",
        "inventame",
    )
    return any(marker in normalized for marker in markers)


def _context_summary(material: TutorMaterial) -> str | None:
    values: list[str] = []
    academic_period = material.allowed_academic_context.get("academic_period")
    course = material.allowed_academic_context.get("course")
    riasec_code = material.allowed_student_context.get("riasec_code")
    technical_specialty = material.allowed_student_context.get("technical_specialty")
    if academic_period:
        values.append(f"gestión o periodo {academic_period}")
    if course:
        values.append(f"curso {course}")
    if riasec_code:
        values.append(f"código de intereses RIASEC {riasec_code}")
    if technical_specialty:
        values.append(f"especialidad técnica {technical_specialty}")
    return ", ".join(values) if values else None


def _system_needs_answer(
    material: TutorMaterial,
    safe_evidence: list[KnowledgeEvidence],
    warnings: list[str],
) -> ProviderAnswer | None:
    safe_source_ids = {item.source_id for item in safe_evidence}
    programs = [
        program for program in material.system_needs if program.source_id in safe_source_ids
    ]
    if len(programs) < 2:
        return None

    paragraphs = [
        "Mapa documental de preparación para programas afines a Sistemas. "
        "Muestra contenidos publicados, no requisitos de admisión ni una decisión automática."
    ]
    context_summary = _context_summary(material)
    if context_summary:
        paragraphs.append(f"Tomé en cuenta tu contexto autorizado: {context_summary}.")

    topic_counts = {"álgebra y matemática": 0, "programación": 0}
    for program in programs:
        if program.initial_subjects:
            paragraphs.append(
                f"• {program.university} — {program.career}: contenidos iniciales publicados: "
                f"{', '.join(program.initial_subjects)}. [{program.source_id}]"
            )
            subjects = " ".join(program.initial_subjects).casefold()
            if "algebra" in subjects or "álgebra" in subjects or "matemat" in subjects:
                topic_counts["álgebra y matemática"] += 1
            if "programacion" in subjects or "programación" in subjects:
                topic_counts["programación"] += 1
            continue

        paragraphs.append(
            f"• {program.university} — {program.career}: la malla está registrada, pero el "
            "snapshot revisado no permite publicar materias iniciales verificables; no las "
            f"inferiré. [{program.source_id}]"
        )
        warnings.append(
            f"{program.source_id}: faltan materias iniciales extraíbles para la comparación."
        )

    common_topics = [topic for topic, count in topic_counts.items() if count >= 2]
    if common_topics:
        paragraphs.append(
            "Patrón publicado en más de una malla: "
            f"{', '.join(common_topics)}. Úsalo para priorizar refuerzo, no como "
            "requisito universal."
        )
    return ProviderAnswer(
        answer="\n\n".join(paragraphs),
        suggested_topics=[
            "Comparar materias iniciales",
            "Revisar áreas a reforzar",
            "Abrir fuentes oficiales",
        ],
        warnings=warnings,
    )


class StructuredAnswerProvider:
    provider_version = STRUCTURED_PROVIDER_VERSION

    def answer(self, material: TutorMaterial) -> ProviderAnswer:
        warnings: list[str] = []
        safe_evidence: list[KnowledgeEvidence] = []
        for item in material.evidence:
            injected, _ = detect_prompt_injection(item.summary)
            if injected:
                warnings.append(
                    f"Evidencia {item.chunk_id} en cuarentena por instrucciones no confiables."
                )
            else:
                safe_evidence.append(item)
        topics = _suggested_topics(safe_evidence)
        normalized_question = material.question.casefold()

        if _contains_injection_or_adversarial(material.question):
            return ProviderAnswer(
                answer=(
                    "No puedo procesar instrucciones que soliciten ignorar directrices, inventar "
                    "datos o citar fuentes inexistentes. SAVP utiliza fuentes permitidas y no toma "
                    "decisiones vocacionales por el estudiante."
                ),
                suggested_topics=topics,
                warnings=["Solicitud adversaria/inyección bloqueada por política de seguridad."],
            )

        if material.intent == "SOCIAL":
            entry = social_conversation_entry(material.question)
            return ProviderAnswer(
                answer=entry.answer,
                suggested_topics=entry.suggested_topics,
                warnings=warnings,
            )

        if material.intent == "CAPABILITIES":
            entry = conversation_entry("CAPABILITIES")
            return ProviderAnswer(
                answer=entry.answer,
                suggested_topics=entry.suggested_topics,
                warnings=warnings,
            )

        paragraphs: list[str] = []
        boundary_answer = False
        if _asks_for_deterministic_career_decision(material.question):
            paragraphs.append(
                "No puedo elegir una carrera por la persona. Los intereses y la preparación "
                "académica observada son dimensiones distintas que conviene revisar por separado."
            )
            warnings.append("El tutor explicó evidencia, pero no sustituyó la orientación humana.")
            boundary_answer = True
        elif _asks_about_intelligence(material.question):
            paragraphs.append(
                "RIASEC evalúa intereses vocacionales y preferencias ocupacionales; no evalúa "
                "inteligencia, capacidad cognitiva general ni aptitud académica."
            )
            warnings.append("Aclaración de límite psicométrico RIASEC generada.")
            boundary_answer = True
        elif _asks_about_success_probability(material.question):
            paragraphs.append(
                "La afinidad describe coincidencia de intereses; no representa una probabilidad de "
                "éxito universitario ni garantiza la aprobación de asignaturas."
            )
            warnings.append("Aclaración sobre no-equivalencia de afinidad a probabilidad generada.")
            boundary_answer = True

        if boundary_answer and not safe_evidence:
            return ProviderAnswer(
                answer="\n\n".join(paragraphs),
                suggested_topics=topics,
                warnings=warnings,
            )

        if material.insufficient_evidence or (
            material.requires_official_evidence and not safe_evidence
        ):
            paragraphs.append(conversation_entry("KNOWLEDGE_GAP").answer)
            return ProviderAnswer(
                answer="\n\n".join(paragraphs),
                suggested_topics=topics or conversation_entry("KNOWLEDGE_GAP").suggested_topics,
                warnings=warnings
                + ["Respuesta abstencionista: evidencia recuperada insuficiente."],
            )

        needs_answer = _system_needs_answer(material, safe_evidence, warnings)
        if needs_answer:
            return needs_answer

        context_summary = _context_summary(material)
        if context_summary:
            paragraphs.append(f"Tomé en cuenta tu contexto autorizado: {context_summary}.")
        source_count = len({item.source_id for item in safe_evidence})
        paragraphs.append(
            f"Encontré información relacionada en {source_count} fuente(s) oficial(es):"
        )
        for item in safe_evidence[:5]:
            locator = f"p. {item.page}" if item.page else item.section or "sección web"
            paragraphs.append(
                f"• {item.title} — {item.institution}: {item.summary} "
                f"[{item.source_id}, {locator}]"
            )

        if (
            ("afinidad" in normalized_question or "preparación" in normalized_question)
            and not _asks_about_success_probability(material.question)
        ):
            paragraphs.append(
                "Recuerda: afinidad no equivale a preparación y una brecha no equivale a "
                "incompatibilidad; señala un contenido que conviene reforzar."
            )
        return ProviderAnswer(
            answer="\n\n".join(paragraphs),
            suggested_topics=topics,
            warnings=warnings,
        )
