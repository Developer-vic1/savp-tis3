import re
import unicodedata
from functools import lru_cache
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field

from app.knowledge.registry import SERVICE_ROOT

ConversationIntent = Literal[
    "SOCIAL", "THANKS", "FAREWELL", "WELLBEING", "CAPABILITIES", "KNOWLEDGE_GAP"
]
CONVERSATION_CATALOG_PATH = SERVICE_ROOT / "data" / "tutor" / "conversation_catalog.json"


class ConversationEntry(BaseModel):
    model_config = ConfigDict(extra="forbid")

    answer: str = Field(min_length=1, max_length=4000)
    suggested_topics: list[str] = Field(min_length=1, max_length=8)


class ConversationCatalog(BaseModel):
    model_config = ConfigDict(extra="forbid")

    version: str = Field(min_length=1, max_length=100)
    entries: dict[ConversationIntent, ConversationEntry]


@lru_cache(maxsize=1)
def load_conversation_catalog() -> ConversationCatalog:
    return ConversationCatalog.model_validate_json(
        CONVERSATION_CATALOG_PATH.read_text(encoding="utf-8")
    )


def conversation_entry(intent: ConversationIntent) -> ConversationEntry:
    if intent == "KNOWLEDGE_GAP":
        return ConversationEntry(
            answer="No encontré evidencia oficial suficiente en las publicaciones revisadas. "
            "Indica la universidad y carrera; podemos revisar una publicación oficial "
            "para comprobar el dato que buscas.",
            suggested_topics=["Precisar universidad y carrera", "Revisar publicación oficial"],
        )
    return load_conversation_catalog().entries[intent]


def social_conversation_entry(question: str) -> ConversationEntry:
    folded = unicodedata.normalize("NFKD", question.casefold())
    folded = "".join(character for character in folded if not unicodedata.combining(character))
    normalized = " ".join(re.findall(r"[a-z0-9]+", folded))
    if "gracias" in normalized:
        return conversation_entry("THANKS")
    if any(marker in normalized for marker in ("adios", "hasta luego", "nos vemos")):
        return conversation_entry("FAREWELL")
    if any(marker in normalized for marker in ("como estas", "que tal")):
        return conversation_entry("WELLBEING")
    return conversation_entry("SOCIAL").model_copy(
        update={
            "answer": "¡Hola! Soy el asistente documental de SAVP. "
            "Puedo revisar las fuentes validadas sobre carreras, universidades y materias. "
            "Para una consulta de materias, indica universidad y carrera; "
            "puedes precisar el semestre si buscas una parte de la malla.",
        }
    )
