from typing import Any, Literal, Protocol

from pydantic import BaseModel, ConfigDict, Field

from app.contracts.requests import TutorConversationTurn
from app.contracts.responses import KnowledgeEvidence
from app.prompts.guards import detect_prompt_injection
from app.tutor.consulta_documental import (
    is_subject_question,
    requested_institutions,
    semester,
)
from app.tutor.consulta_documental import normalized as normalize_text
from app.tutor.conversation import conversation_entry, social_conversation_entry
from app.tutor.datos_documentados import ProgramFact, VerifiedSemester
from app.tutor.datos_institucionales import (
    InstitutionReview,
    NationalRegistrySummary,
    PublishedOffer,
)
from app.tutor.needs import SystemProgramNeed

STRUCTURED_PROVIDER_VERSION = "structured-answer-v3.2.0"


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
    clarification: str | None = None
    listed_careers: list[ProgramFact] = Field(default_factory=list)
    published_offer: PublishedOffer | None = None
    institutions_under_review: list[InstitutionReview] | None = None
    national_registry: NationalRegistrySummary | None = None
    national_registry_request: str | None = None
    subject_program: ProgramFact | None = None
    subject_semester_unverified: bool = False


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


def _semester_subjects_text(item: VerifiedSemester) -> str:
    parts = [f"Semestre {item.semester} (página {item.page}):"]
    if item.subjects:
        if item.mention_subjects:
            parts.append("Materias comunes:")
        parts.extend(f"- {name}" for name in item.subjects)
    if item.mention_subjects:
        parts.append("Materias según la mención que elijas:")
        for mention, names in item.mention_subjects.items():
            parts.append(f"{mention}:")
            parts.extend(f"- {name}" for name in names)
    return "\n".join(parts)


def _institution_with_article(name: str) -> str:
    return f"la {name}" if name.startswith(("Universidad", "Escuela")) else name


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
            normalized = material.question.casefold()
            if any(
                marker in normalized
                for marker in ("memoria", "recuerd", "guard", "historial", "conversa")
            ):
                return ProviderAnswer(
                    answer=(
                        "Uso sólo el historial reciente enviado por esta ventana. "
                        "No tengo memoria persistente de conversaciones anteriores. "
                        "Los documentos guardados se conservan por separado y requieren revisión."
                    ),
                    suggested_topics=["Consultar documentos", "Revisar fuentes guardadas"],
                    warnings=warnings,
                )
            entry = conversation_entry("CAPABILITIES")
            return ProviderAnswer(
                answer="Soy el asistente documental de SAVP. " + entry.answer,
                suggested_topics=entry.suggested_topics,
                warnings=warnings,
            )

        if material.published_offer:
            offer = material.published_offer
            names = "\n".join(f"- {career.name}" for career in offer.careers)
            return ProviderAnswer(
                answer=(
                    f"El índice oficial de UNIFRANZ publica estas "
                    f"{len(offer.careers)} carreras:\n\n"
                    f"{names}\n\nLa oferta por sede y sus mallas se verifican por separado."
                ),
                suggested_topics=["Revisar una carrera", "Ver mallas documentadas"],
                warnings=warnings,
            )

        if material.national_registry is not None:
            registry = material.national_registry
            departments = ", ".join(
                f"{department} ({count})" for department, count in registry.by_department.items()
            )
            answer = (
                f"En el directorio nacional de SAVP tenemos {len(registry.institutions)} "
                "instituciones registradas a partir de la guía ministerial de 2016. "
                "Es un registro histórico; no confirma cuántas están habilitadas actualmente. "
                f"Por departamento figuran: {departments}. Una misma universidad puede aparecer "
                "en varios departamentos."
            )
            institutional_count = material.allowed_academic_context.get(
                "institutional_university_count"
            )
            if isinstance(institutional_count, int) and institutional_count >= 0:
                answer += (
                    f" En la base académica de esta instalación hay {institutional_count} "
                    "universidades activas con sede registrada; ese conjunto es distinto "
                    "del directorio histórico."
                )
            if material.national_registry_request == "LIST":
                lines = [
                    f"- {item.name} — "
                    f"{', '.join(item.departments) or 'departamento no documentado'}"
                    for item in registry.institutions
                ]
                answer += "\n\n" + "\n".join(lines)
            return ProviderAnswer(
                answer=answer,
                suggested_topics=["Filtrar por departamento", "Ver instituciones en revisión"],
                warnings=warnings,
            )

        if material.institutions_under_review is not None:

            def review_label(item: InstitutionReview) -> str:
                return (
                    f"{item.name} — {', '.join(item.departments) or 'departamento no documentado'}"
                )

            pending = [
                review_label(item)
                for item in material.institutions_under_review
                if item.website_verification_status == "PENDING_CURRENT_VERIFICATION"
            ]
            unrelated = [
                review_label(item)
                for item in material.institutions_under_review
                if item.website_verification_status
                == "HISTORICAL_DOMAIN_WITH_CURRENT_UNRELATED_CONTENT"
            ]
            parts = [
                "En el directorio nacional seguimos comprobando el sitio web de "
                f"{len(material.institutions_under_review)} instituciones del registro histórico."
            ]
            if pending:
                parts.append(
                    f"En {len(pending)} aún debemos confirmar el dominio oficial:\n"
                    + "\n".join(f"- {name}" for name in pending)
                )
            if unrelated:
                parts.append(
                    "En esta otra encontramos contenido actual no relacionado en un dominio "
                    "histórico, así que no lo usamos como fuente:\n"
                    + "\n".join(f"- {name}" for name in unrelated)
                )
            parts.append(
                "La revisión del sitio no confirma si una institución está habilitada hoy. "
                "Las propuestas documentales del panel se revisan por separado."
            )
            return ProviderAnswer(
                answer="\n\n".join(parts),
                suggested_topics=["Ver directorio nacional", "Revisar una institución"],
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

        if material.clarification and not boundary_answer:
            return ProviderAnswer(
                answer=material.clarification,
                suggested_topics=["Precisar universidad y carrera"],
                warnings=["Consulta documental incompleta; no se atribuyeron materias."],
            )

        if material.listed_careers:
            institution = _institution_with_article(material.listed_careers[0].university_name)
            names = "\n".join(f"- {program.name}" for program in material.listed_careers)
            return ProviderAnswer(
                answer=(
                    f"De {institution}, tengo documentadas estas carreras:\n\n{names}\n\n"
                    "Esta lista refleja las publicaciones oficiales revisadas en SAVP; "
                    "puede faltar parte de la oferta actual."
                ),
                suggested_topics=["Ver fuentes de estas carreras", "Consultar una carrera"],
                warnings=warnings,
            )

        if material.subject_program and not boundary_answer:
            program = material.subject_program
            institution = _institution_with_article(program.university_name)
            requested_semester = semester(material.question)
            verified = next(
                (
                    item
                    for item in program.verified_semesters
                    if item.semester == requested_semester
                ),
                None,
            )
            if verified:
                mention_note = (
                    "\n\nLa malla indica elegir una de las menciones; las materias de ambas "
                    "ramas no son todas obligatorias."
                    if verified.mention_subjects
                    else ""
                )
                return ProviderAnswer(
                    answer=(
                        f"En la malla oficial de {program.name} en "
                        f"{institution}, encontré:\n\n"
                        f"{_semester_subjects_text(verified)}{mention_note}"
                    ),
                    suggested_topics=["Ver malla oficial", "Consultar otro semestre"],
                    warnings=warnings,
                )
            if material.subject_semester_unverified:
                if not program.has_curriculum:
                    return ProviderAnswer(
                        answer=(
                            f"Tengo una publicación oficial sobre {program.name} en "
                            f"{institution}, pero aún no una malla revisada "
                            "que permita confirmar las materias de ese semestre."
                        ),
                        suggested_topics=["Ver publicación oficial", "Consultar otra carrera"],
                        warnings=warnings + ["Malla pendiente de verificación."],
                    )
                return ProviderAnswer(
                    answer=(
                        f"Encontré la malla oficial de {program.name} en "
                        f"{institution}, pero no tengo una transcripción verificada "
                        f"de las materias del semestre {requested_semester}. "
                        "Puedes abrir el documento oficial; no quiero atribuirte materias "
                        "incorrectas."
                    ),
                    suggested_topics=["Abrir la malla oficial", "Consultar otra carrera"],
                    warnings=warnings + ["Materias del semestre pendientes de verificación."],
                )
            if requested_semester is None:
                if not program.has_curriculum:
                    return ProviderAnswer(
                        answer=(
                            f"Tengo una publicación oficial sobre {program.name} en "
                            f"{institution}, pero todavía no una malla revisada "
                            "para enumerar sus materias."
                        ),
                        suggested_topics=["Ver publicación oficial", "Consultar otra carrera"],
                        warnings=warnings + ["Malla pendiente de verificación."],
                    )
                if program.verified_semesters:
                    blocks = [
                        _semester_subjects_text(item)
                        for item in sorted(
                            program.verified_semesters, key=lambda item: item.semester
                        )
                    ]
                    has_mentions = any(item.mention_subjects for item in program.verified_semesters)
                    mention_note = (
                        " La malla indica elegir una de las menciones: las materias "
                        "de ambas ramas no son todas obligatorias."
                        if has_mentions
                        else ""
                    )
                    return ProviderAnswer(
                        answer=(
                            f"En la malla oficial de {program.name} en "
                            f"{institution}, tengo estas materias revisadas:\n\n"
                            + "\n\n".join(blocks)
                            + "\n\nLa información corresponde a los semestres transcritos "
                            "de la publicación enlazada." + mention_note
                        ),
                        suggested_topics=["Ver malla oficial", "Consultar un semestre"],
                        warnings=warnings,
                    )
                if program.initial_subjects:
                    subjects = "\n".join(f"- {subject}" for subject in program.initial_subjects)
                    return ProviderAnswer(
                        answer=(
                            f"De {program.name} en {institution}, "
                            f"tengo verificadas estas materias iniciales:\n\n{subjects}\n\n"
                            "Son materias iniciales, no la malla completa. "
                            "Si te interesa otro semestre, dime cuál y revisaré si está "
                            "documentado."
                        ),
                        suggested_topics=["Ver malla oficial", "Materias por semestre"],
                        warnings=warnings,
                    )
                pages = (
                    f" (páginas {', '.join(str(page) for page in program.curriculum_pages)})"
                    if program.curriculum_pages
                    else ""
                )
                return ProviderAnswer(
                    answer=(
                        f"Encontré la malla oficial de {program.name} en "
                        f"{institution}{pages}. "
                        "Las materias todavía no están transcritas con suficiente fiabilidad "
                        "para enumerarlas aquí. Puedes abrir la malla oficial; si me dices "
                        "qué semestre te interesa, comprobaré si ese tramo ya está verificado."
                    ),
                    suggested_topics=["Abrir la malla oficial", "Consultar un semestre"],
                    warnings=warnings + ["Transcripción de materias pendiente de verificación."],
                )

        if material.insufficient_evidence or (
            material.requires_official_evidence and not safe_evidence
        ):
            if is_subject_question(material.question) and semester(material.question):
                paragraphs.append(
                    "No encontré materias de ese semestre respaldadas "
                    "por las publicaciones revisadas. "
                    "Revisa la universidad, carrera y semestre de tu consulta; podemos comprobar "
                    "una malla oficial antes de atribuirle materias."
                )
            else:
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
            if semester(material.question):
                paragraphs.append(f"{item.institution}\n{item.title}")
                paragraphs.append(item.summary)
                paragraphs.append(f"Fuente: [{item.source_id}, {locator}]")
            else:
                paragraphs.append(
                    f"• {item.title} — {item.institution}: {item.summary} "
                    f"[{item.source_id}, {locator}]"
                )
        if semester(material.question):
            missing = [
                aliases[0]
                for aliases in requested_institutions(material.question)
                if not any(
                    any(alias in normalize_text(item.institution) for alias in aliases)
                    for item in safe_evidence
                )
            ]
            if missing:
                paragraphs.append(
                    "La revisión es parcial: no pude confirmar materias de ese semestre para "
                    + ", ".join(missing)
                    + ". Revisa sus mallas antes de compararlas."
                )

        if (
            "afinidad" in normalized_question or "preparación" in normalized_question
        ) and not _asks_about_success_probability(material.question):
            paragraphs.append(
                "Recuerda: afinidad no equivale a preparación y una brecha no equivale a "
                "incompatibilidad; señala un contenido que conviene reforzar."
            )
        return ProviderAnswer(
            answer="\n\n".join(paragraphs),
            suggested_topics=topics,
            warnings=warnings,
        )
