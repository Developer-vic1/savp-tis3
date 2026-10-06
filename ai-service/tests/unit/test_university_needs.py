from app.tutor.needs import system_needs_for_question


def test_system_needs_map_links_validated_university_programs() -> None:
    programs = system_needs_for_question(
        "Compara Sistemas UCB UPB UNIFRANZ materias primer semestre"
    )

    assert [program.source_id for program in programs] == [
        "BO-UCB-LP-SIS-MALLA-2026",
        "BO-UPB-LP-SISC-MALLA-2026",
        "BO-UNIFRANZ-LP-SISID-MALLA-2026",
    ]
    assert "Álgebra Lineal" in programs[0].initial_subjects
    assert "Programacion I" in programs[1].initial_subjects
    assert programs[2].initial_subjects == []


def test_system_needs_map_requires_multiple_universities() -> None:
    assert system_needs_for_question("Materias de Sistemas UCB") == []
