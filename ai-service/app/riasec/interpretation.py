RIASEC_ORDER = ("R", "I", "A", "S", "E", "C")
RIASEC_NAMES = {
    "R": "Realista",
    "I": "Investigador",
    "A": "Artístico",
    "S": "Social",
    "E": "Emprendedor",
    "C": "Convencional",
}


def ordered_codes(scores: dict[str, int]) -> list[str]:
    return sorted(RIASEC_ORDER, key=lambda code: (-scores[code], RIASEC_ORDER.index(code)))


def safe_interpretation(top_codes: list[str]) -> str:
    names = [RIASEC_NAMES[code] for code in top_codes]
    focus = names[0] if len(names) == 1 else ", ".join(names[:-1]) + f" y {names[-1]}"
    return (
        f"Tus respuestas muestran mayor interés relativo por actividades de tipo {focus}. "
        "Este resultado es exploratorio y no determina tu personalidad, capacidad ni carrera."
    )
