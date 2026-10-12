from app.knowledge.sedes_universitarias import leer_oferta_sedes


def html(indicators, title="Arquitectura"):
    tags = "".join(f'<span class="{classes}">{code}</span>' for code, classes in indicators)
    return (
        '<nav>La Paz Santa Cruz Cochabamba El Alto</nav><main class="career-page">'
        f'<div class="bg-primary"><h1>{title}</h1><div><span>Disponible en:</span>'
        f"<div>{tags}</div></div></div><footer>La Paz</footer></main>"
    ).encode()


def read(content):
    return leer_oferta_sedes(
        content,
        "Arquitectura",
        "https://unifranz.edu.bo/carreras/arquitectura/",
        "sha256:" + "a" * 64,
        "2026-10-09T20:00:00+00:00",
    )


def test_architecture_is_only_offered_in_santa_cruz_even_when_other_names_are_present():
    result = read(
        html(
            [
                ("CBBA", "opacity-50"),
                ("EA", "opacity-50"),
                ("LPZ", "opacity-50"),
                ("SCZ", "opacity-100 border-b-2"),
            ]
        )
    )
    assert result["campuses"] == ["Santa Cruz"]
    assert result["campus_evidence"]["status"] == "OFERTA_IDENTIFICADA"
    assert (
        next(item for item in result["campus_availability"] if item["campus"] == "La Paz")["status"]
        == "NO_INDICADA"
    )


def test_unknown_or_conflicting_markers_and_wrong_identity_never_confirm_a_campus():
    for classes in ["", "opacity-100", "opacity-50 opacity-100 border-b-2"]:
        result = read(html([("LPZ", classes)]))
        assert result["campuses"] == []
        assert result["campus_evidence"]["status"] == "PENDIENTE"
    assert read(html([("SCZ", "opacity-100 border-b-2")], title="Medicina"))["campuses"] == []


def test_missing_availability_label_and_global_menu_are_not_evidence():
    result = read(
        b'<main class="career-page"><h1>Arquitectura</h1><nav>Disponible en: La Paz</nav>'
        b"<footer>Santa Cruz</footer></main>"
    )
    assert result["campuses"] == []
    assert result["campus_availability"] == []


def test_duplicate_codes_remain_pending_instead_of_choosing_one_indicator():
    result = read(html([("LPZ", "opacity-100 border-b-2"), ("LPZ", "opacity-50")]))
    assert result["campuses"] == []
    assert result["campus_availability"][0]["status"] == "PENDIENTE"
