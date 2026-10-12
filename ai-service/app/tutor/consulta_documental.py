"""Ámbito de consultas documentales y entidades nombradas por el estudiante."""

import re
import unicodedata


def normalized(text: str) -> str:
    folded = unicodedata.normalize("NFKD", text.casefold())
    return " ".join(
        re.findall(
            r"[a-z0-9]+",
            "".join(character for character in folded if not unicodedata.combining(character)),
        )
    )


INSTITUTIONS = {
    "ucb": ("universidad catolica boliviana", "ucb"),
    "universidad catolica": ("universidad catolica boliviana", "ucb"),
    "umsa": ("universidad mayor de san andres", "umsa"),
    "universidad mayor de san andres": ("universidad mayor de san andres", "umsa"),
    "unifranz": ("unifranz", "universidad franz tamayo"),
    "franz tamayo": ("unifranz", "universidad franz tamayo"),
    "universidad privada franz tamayo": ("unifranz", "universidad franz tamayo"),
    "universidad franz tamayo": ("unifranz", "universidad franz tamayo"),
    "upb": ("universidad privada boliviana", "upb"),
    "universidad privada boliviana": ("universidad privada boliviana", "upb"),
    "emi": ("escuela militar de ingenieria", "emi"),
}
CAREERS = (
    "sistemas",
    "psicologia",
    "civil",
    "ambiental",
    "medicina",
    "enfermeria",
    "odontologia",
    "derecho",
    "arquitectura",
    "comercial",
    "gastronomia",
    "turismo",
    "bioquimica",
    "farmacia",
    "publicidad",
    "marketing",
    "auditoria",
    "transmedia",
    "comunicacion",
    "periodismo",
    "negocios",
    "gestion empresarial",
    "economica",
)
ORDINALS = {
    "primer": 1,
    "primero": 1,
    "segundo": 2,
    "tercer": 3,
    "tercero": 3,
    "cuarto": 4,
    "quinto": 5,
    "sexto": 6,
    "septimo": 7,
    "octavo": 8,
    "noveno": 9,
    "decimo": 10,
    "undecimo": 11,
    "duodecimo": 12,
}


def semester(text: str) -> int | None:
    value = normalized(text)
    for ordinal, number in ORDINALS.items():
        if re.search(r"\b" + ordinal + r"\s+semestre\b", value):
            return number
    match = re.search(
        r"\b(?:semestre\s+(\d{1,2})|(\d{1,2})(?:ro|do|er|to|mo|vo)?\s+semestre)\b", value
    )
    numeric = int(next(group for group in match.groups() if group)) if match else None
    return numeric if numeric and 1 <= numeric <= 12 else None


def is_subject_question(text: str) -> bool:
    value = normalized(text)
    return bool(re.search(r"\b(?:materias|asignaturas|semestre)\b", value))


def is_career_list_question(text: str) -> bool:
    value = normalized(text)
    return bool(
        re.search(r"\b(?:carreras|programas)\b", value)
        and not re.search(r"\b(?:malla|materias|asignaturas)\b", value)
        and (
            re.search(r"\b(?:que|cuales|lista|listado|listar|dime|contiene|ofrece|tiene)\b", value)
            or re.search(r"\b(?:carreras|programas)\s+(?:de|en)\b", value)
        )
    )


def is_institution_review_question(text: str) -> bool:
    value = normalized(text)
    return bool(
        re.search(r"\b(?:universidades|instituciones)\b", value)
        and re.search(r"\b(?:revision|revisando|pendientes|verificacion|verificando)\b", value)
        and not re.search(r"\b(?:carrera|carreras|malla|materias)\b", value)
    )


def institution_registry_request(text: str) -> str | None:
    """Preguntas sobre nuestro directorio, separadas de la vigencia legal externa."""
    value = normalized(text)
    if is_institution_review_question(text) or re.search(
        r"\b(?:carrera|carreras|malla|materias)\b", value
    ):
        return None
    if not re.search(r"\b(?:universidades|instituciones)\b", value):
        return None
    if not re.search(r"\b(?:registrad[ao]s|registro|directorio|tenemos|incorporad[ao]s)\b", value):
        return None
    if re.search(r"\b(?:cuantas|cuantos|numero|total)\b", value):
        return "COUNT"
    if re.search(r"\b(?:cuales|que|lista|listado|muestra)\b", value):
        return "LIST"
    return None


def requested_institutions(text: str) -> list[tuple[str, ...]]:
    value = normalized(text)
    return list(
        dict.fromkeys(
            aliases
            for marker, aliases in INSTITUTIONS.items()
            if re.search(r"\b" + re.escape(marker) + r"\b", value)
        )
    )


def requested_careers(text: str) -> list[str]:
    value = normalized(text)
    return [career for career in CAREERS if re.search(r"\b" + career + r"\b", value)]


def missing_scope(text: str) -> list[str]:
    if not is_subject_question(text):
        return []
    return [
        label
        for label, present in (
            ("universidad", bool(requested_institutions(text))),
            ("carrera", bool(requested_careers(text))),
        )
        if not present
    ]


def semester_text(section: str | None, text: str, target: int) -> str | None:
    """Sólo evidencia de sección o encabezado semestral; una lista inicial no basta."""
    section_number = semester(section or "")
    if section_number is not None:
        return text if section_number == target else None
    lines = text.splitlines()
    headings = [
        (index, semester(line))
        for index, line in enumerate(lines)
        if len(line.strip()) <= 55 and semester(line) is not None
    ]
    for position, (index, number) in enumerate(headings):
        if number == target:
            end = headings[position + 1][0] if position + 1 < len(headings) else len(lines)
            return "\n".join(lines[index:end]).strip()
    # Algunas mallas representan cada semestre con un número, seguido por códigos
    # de asignatura/UVE. El corpus aplana sus saltos. Exige el rótulo publicado
    # «Semestres», dos bloques consecutivos y la gramática de asignatura explícita;
    # no usa los códigos o el catálogo para inferir el semestre.
    if re.search(r"\bSemestres\b", text, re.I):
        numbered = list(
            re.finditer(r"(?<!\S)([1-9]|1[0-2])\s+(?=[A-Z]{2,5}-\d{3}\s*/\s*UVE\s*\d+)", text)
        )
        for position, heading in enumerate(numbered[:-1]):
            if int(heading.group(1)) != target:
                continue
            following = numbered[position + 1]
            if int(following.group(1)) != target + 1:
                return None
            block = text[heading.end() : following.start()].strip()
            subjects = re.findall(
                r"([A-Z]{2,5}-\d{3})\s*/\s*UVE\s*\d+\s+(.+?)\s*[▪•]\s*Req:", block
            )
            if len(subjects) >= 2:
                return f"Semestre {target}\n" + "\n".join(
                    f"• {name.strip()} ({code})" for code, name in subjects
                )
    return None
