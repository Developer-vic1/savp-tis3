from typing import Any, Protocol

from pydantic import BaseModel, ConfigDict, Field

from app.contracts.responses import KnowledgeEvidence

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


class StructuredAnswerProvider:
    provider_version = STRUCTURED_PROVIDER_VERSION

    def answer(self, material: TutorMaterial) -> ProviderAnswer:
        warnings: list[str] = []
        topics = _suggested_topics(material.evidence)
        if material.insufficient_evidence or not material.evidence:
            return ProviderAnswer(
                answer=(
                    "No encontré evidencia oficial suficiente en el corpus para responder con "
                    "seguridad. Reformula la pregunta indicando carrera, institución, materia o "
                    "gestión; no asumiré requisitos que las fuentes no muestran."
                ),
                suggested_topics=topics,
                warnings=["Respuesta abstencionista: evidencia recuperada insuficiente."],
            )

        paragraphs: list[str] = []
        if _asks_for_deterministic_career_decision(material.question):
            paragraphs.append(
                "No puedo elegir una carrera por la persona ni modificar el ranking determinista. "
                "La afinidad describe coincidencia de intereses; la preparación estima evidencia "
                "académica y técnica disponible. Deben interpretarse por separado."
            )
            warnings.append(
                "El tutor explicó evidencia, pero no sustituyó el análisis vocacional determinista."
            )
        else:
            paragraphs.append("Respuesta extractiva basada en evidencia oficial recuperada:")

        for position, item in enumerate(material.evidence[:3], start=1):
            locator = f"p. {item.page}" if item.page else item.section or "sección web"
            paragraphs.append(
                f"{position}. {item.summary} ({item.institution}, {locator}, "
                f"fuente {item.source_id})."
            )

        normalized_question = material.question.casefold()
        if "afinidad" in normalized_question or "preparación" in normalized_question:
            paragraphs.append(
                "Recuerda: afinidad no equivale a preparación y una brecha no equivale a "
                "incompatibilidad; señala un contenido que conviene reforzar."
            )
        return ProviderAnswer(
            answer="\n\n".join(paragraphs),
            suggested_topics=topics,
            warnings=warnings,
        )
