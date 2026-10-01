from typing import Any, Protocol

from pydantic import BaseModel, ConfigDict, Field

from app.contracts.responses import KnowledgeEvidence
from app.prompts.guards import detect_prompt_injection

STRUCTURED_PROVIDER_VERSION = "structured-answer-v1.0.0"


class TutorMaterial(BaseModel):
    model_config = ConfigDict(extra="forbid")

    question: str
    subject: str | None
    level: str | None
    allowed_academic_context: dict[str, Any]
    allowed_student_context: dict[str, Any]
    evidence: list[KnowledgeEvidence]
    insufficient_evidence: bool


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
                    "datos o citar fuentes inexistentes. SAVP opera exclusivamente sobre hechos "
                    "extraídos del corpus documental oficial y no toma decisiones vocacionales por "
                    "el usuario."
                ),
                suggested_topics=topics,
                warnings=["Solicitud adversaria/inyección bloqueada por política de seguridad."],
            )

        if material.insufficient_evidence or not safe_evidence:
            return ProviderAnswer(
                answer=(
                    "No encontré evidencia oficial suficiente en el corpus para responder con "
                    "seguridad. Reformula la pregunta indicando carrera, institución, materia o "
                    "gestión; no asumiré requisitos que las fuentes no muestran."
                ),
                suggested_topics=topics,
                warnings=warnings
                + ["Respuesta abstencionista: evidencia recuperada insuficiente."],
            )

        paragraphs: list[str] = []
        if _asks_for_deterministic_career_decision(material.question):
            paragraphs.append(
                "No puedo elegir una carrera por la persona. "
                "Los intereses y la preparación académica observada son constructos distintos; "
                "la evidencia disponible debe interpretarse por separado."
            )
            warnings.append(
                "El tutor explicó evidencia, pero no sustituyó la orientación humana."
            )
        elif _asks_about_intelligence(material.question):
            paragraphs.append(
                "Aclaración psicométrica: El instrumento RIASEC evalúa intereses vocacionales "
                "y preferencias ocupacionales; no evalúa inteligencia, capacidad cognitiva general "
                "ni aptitud académica."
            )
            warnings.append("Aclaración de límite psicométrico RIASEC generada.")
        elif _asks_about_success_probability(material.question):
            paragraphs.append(
                "Aclaración metodológica: La afinidad mide coincidencia entre intereses del "
                "estudiante y ofertas académicas; no representa probabilidad de éxito "
                "universitario ni garantiza la aprobación de asignaturas."
            )
            warnings.append("Aclaración sobre no-equivalencia de afinidad a probabilidad generada.")
        else:
            paragraphs.append("Respuesta extractiva basada en evidencia oficial recuperada:")

        for position, item in enumerate(safe_evidence[:3], start=1):
            locator = f"p. {item.page}" if item.page else item.section or "sección web"
            paragraphs.append(
                f"{position}. {item.summary} ({item.institution}, {locator}, "
                f"fuente {item.source_id})."
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
