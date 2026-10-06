import re
import unicodedata
from typing import Any

ALLOWED_STUDENT_CONTEXT_KEYS = {
    "academic_period",
    "affinity_label",
    "areas_to_reinforce",
    "course",
    "preparation_label",
    "preparation_route",
    "riasec_code",
    "strengths",
    "technical_specialty",
}

DISALLOWED_PII_PATTERNS = [
    r"\b[0-9]{7,8}(?:-[0-9][A-Z])?\b",  # Bolivian CI pattern
    r"\b[67][0-9]{7}\b",  # Bolivian cell phone
    r"\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b",  # Email
]

INJECTION_PATTERNS = [
    r"ignora\s+(?:todas\s+)?(?:las\s+)?instrucciones",
    r"ignore\s+(?:all\s+)?(?:previous\s+)?instructions",
    r"olvida\s+(?:todas\s+)?(?:las\s+)?reglas",
    r"disregard\s+(?:all\s+)?rules",
    r"system\s*override",
    r"modo\s*desarrollador",
    r"developer\s*mode",
    r"you\s*are\s*now\s*dan",
    r"eres\s*ahora\s*dan",
    r"jailbreak",
    r"reveal\s+(?:the\s+)?system\s+prompt",
    r"muestra\s+(?:el\s+)?prompt\s+del\s+sistema",
    r"follow\s+(?:these|my)\s+instructions",
    r"obedece\s+(?:estas|mis)\s+instrucciones",
    r"act\s+as\s+(?:an?\s+)?(?:admin|system|developer)",
    r"output\s+(?:only\s+)?(?:the\s+)?(?:secret|password|token)",
    r"recomi[eé]ndame\s+ser\s+[a-z]+",
    r"dime\s+qu[eé]\s+carrera\s+debo\s+elegir",
    r"elige\s+mi\s+carrera",
    r"decide\s+mi\s+carrera",
]

UNTRUSTED_DELIMITER_START = "--- BEGIN UNTRUSTED EVIDENCE ---"
UNTRUSTED_DELIMITER_END = "--- END UNTRUSTED EVIDENCE ---"


def _normalize_text(text: str) -> str:
    folded = unicodedata.normalize("NFKD", text.casefold())
    return "".join(c for c in folded if not unicodedata.combining(c))


def sanitize_student_context(context: dict[str, Any] | None) -> dict[str, Any]:
    """Filtra y sanitiza el contexto del estudiante, removiendo PII y limitando
    a campos pedagógicos autorizados.
    """
    if not context:
        return {}
    sanitized: dict[str, Any] = {}
    for key, value in context.items():
        if key not in ALLOWED_STUDENT_CONTEXT_KEYS:
            continue
        if isinstance(value, str):
            cleaned_value = value
            for pattern in DISALLOWED_PII_PATTERNS:
                cleaned_value = re.sub(pattern, "[DATO_PROTEGIDO]", cleaned_value)
            sanitized[key] = cleaned_value
        elif isinstance(value, list):
            cleaned_list: list[Any] = []
            for item in value:
                if isinstance(item, str):
                    cleaned_item = item
                    for pattern in DISALLOWED_PII_PATTERNS:
                        cleaned_item = re.sub(pattern, "[DATO_PROTEGIDO]", cleaned_item)
                    cleaned_list.append(cleaned_item)
                else:
                    cleaned_list.append(item)
            sanitized[key] = cleaned_list
        else:
            sanitized[key] = value
    return sanitized


def detect_prompt_injection(text: str) -> tuple[bool, str | None]:
    """Detecta intentos de inyección de prompt o manipulación de instrucciones."""
    normalized = _normalize_text(text)
    for pattern in INJECTION_PATTERNS:
        match = re.search(pattern, normalized)
        if match:
            return True, f"Patrón de inyección/manipulación detectado: '{match.group(0)}'"
    return False, None


def wrap_untrusted_evidence(text: str) -> str:
    """Enclava texto de evidencia recuperada dentro de delimitadores estrictos."""
    escaped = text.replace(UNTRUSTED_DELIMITER_START, "[DELIMITADOR_ESCAPADO]")
    escaped = escaped.replace(UNTRUSTED_DELIMITER_END, "[DELIMITADOR_ESCAPADO]")
    return f"{UNTRUSTED_DELIMITER_START}\n{escaped}\n{UNTRUSTED_DELIMITER_END}"


def quarantine_injected_evidence(text: str) -> tuple[str, bool]:
    """Replace instruction-like retrieved text instead of presenting it to a model."""
    detected, _ = detect_prompt_injection(text)
    if detected:
        return "[EVIDENCIA OMITIDA POR CONTENER INSTRUCCIONES NO CONFIABLES]", True
    return text, False


def validate_citations(
    answer: str,
    allowed_source_ids: set[str],
) -> tuple[bool, list[str], list[str]]:
    """Valida que todas las citas citadas en la respuesta pertenezcan a allowed_source_ids."""
    cited_brackets = set(re.findall(r"\[([A-Z0-9][A-Z0-9-]+)\]", answer))
    cited_prefixed = set(re.findall(r"\b(BO-[A-Z0-9-]+)\b", answer))
    all_cited = sorted(cited_brackets | cited_prefixed)

    valid_citations = [s for s in all_cited if s in allowed_source_ids]
    invalid_citations = [s for s in all_cited if s not in allowed_source_ids]

    is_valid = len(invalid_citations) == 0
    return is_valid, valid_citations, invalid_citations
