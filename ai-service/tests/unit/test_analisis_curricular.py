import hashlib
import json

import pymupdf
import pytest

from app.knowledge import analisis_curricular as analysis
from app.knowledge.clasificador_bloques import caracteristicas, entrenar, probabilidad
from app.knowledge.coleccion_universitaria import COLLECTION_ROOT, _normal_name


def test_classifier_learns_labels_and_requires_both_classes():
    records = [{"features": caracteristicas("Derecho Civil", True), "label": 1} for _ in range(12)]
    records += [{"features": caracteristicas("CONOCE MAS", False), "label": 0} for _ in range(12)]
    weights = entrenar(records)
    assert probabilidad(caracteristicas("Derecho Civil", True), weights) > 0.8
    assert probabilidad(caracteristicas("CONOCE MAS", False), weights) < 0.2
    with pytest.raises(ValueError):
        entrenar(records[:12])


def test_organization_uses_heading_columns_not_page_number(tmp_path, monkeypatch):
    path = tmp_path / "layout.pdf"
    with pymupdf.open() as document:
        page = document.new_page(width=500, height=800)
        page.insert_text((80, 40), "Septimo")
        page.insert_text((80, 58), "Semestre")
        page.insert_text((50, 100), "Derecho Civil")
        page.insert_text((310, 40), "Octavo")
        page.insert_text((310, 58), "Semestre")
        page.insert_text((280, 100), "Proyecto Final")
        page.insert_text((40, 350), "PROFESIONALES - MICROCREDENCIALES")
        page.insert_text((50, 400), "Texto promocional que no es una materia")
        document.save(path)
    monkeypatch.setattr(analysis, "cargar_modelo", lambda: None)
    result = analysis.analizar_documento(
        path, "sha256:" + "a" * 64, "https://unifranz.edu.bo/malla.pdf"
    )
    assert [item["number"] for item in result["curricular_structure"]["semesters"]] == [7, 8]
    assert [item["name"] for item in result["subjects_candidates"]] == [
        "Derecho Civil",
        "Proyecto Final",
    ]
    assert "promocional" not in result["excerpt"]


def test_ambiguous_region_is_not_converted_into_subject(tmp_path, monkeypatch):
    path = tmp_path / "bad.pdf"
    with pymupdf.open() as document:
        page = document.new_page()
        page.insert_text((80, 40), "Primer")
        page.insert_text((80, 58), "Semestre")
        page.insert_text((50, 100), "!!!??? abc")
        document.save(path)
    monkeypatch.setattr(analysis, "cargar_modelo", lambda: None)

    def failure(*args):
        raise RuntimeError("Recognition unavailable")

    monkeypatch.setattr(analysis, "reconocer_region", failure)
    result = analysis.analizar_documento(
        path, "sha256:" + "a" * 64, "https://unifranz.edu.bo/malla.pdf"
    )
    assert result["subjects_candidates"] == []
    assert result["curricular_structure"]["pending_regions"] == 1
    assert result["curricular_structure"]["status"] == "REQUIERE_REVISION"


def test_lock_is_released_if_initial_read_fails(tmp_path, monkeypatch):
    released = []
    monkeypatch.setattr(
        analysis, "read_collection", lambda *args: (_ for _ in ()).throw(OSError("read"))
    )
    with pytest.raises(OSError):
        analysis.procesar_coleccion(tmp_path, lambda: released.append(True))
    assert released == [True]


def test_semester_headings_in_one_merged_block_are_separated_by_words():
    class Page:
        class Rect:
            height = 800

        rect = Rect()

        def get_text(self, kind):
            if kind == "blocks":
                return [
                    (100, 250, 440, 290, "Segundo\nCuarto\n"),
                    (100, 280, 440, 315, "Semestre\nSemestre\n"),
                ]
            return [
                (100, 250, 155, 270, "Segundo"),
                (370, 250, 425, 270, "Cuarto"),
                (100, 280, 155, 300, "Semestre"),
                (370, 280, 425, 300, "Semestre"),
            ]

    _, headings = analysis.regiones(Page())
    assert [heading["number"] for heading in headings] == [2, 4]
    assert analysis.semestre_del_bloque((70, 330, 180, 350, "Materia"), headings)["number"] == 2
    assert analysis.semestre_del_bloque((340, 330, 450, 350, "Materia"), headings)["number"] == 4


@pytest.mark.ocr
def test_real_psychology_has_54_subjects_in_eight_semesters_with_no_cover_noise():
    gold = json.loads(
        (COLLECTION_ROOT.parents[2] / "evaluation/psicologia_malla_etiquetada.json").read_text(
            encoding="utf-8"
        )
    )
    path = COLLECTION_ROOT / "instantaneas" / (gold["document_hash"][7:] + ".pdf")
    if not path.is_file() or not analysis._recognition_runtime():
        pytest.skip("PDF real o modelo español no disponible")
    assert hashlib.sha256(path.read_bytes()).hexdigest() == gold["document_hash"][7:]
    result = analysis.analizar_documento(path, gold["document_hash"], gold["source_url"])
    expected = {(item["semester"], _normal_name(item["name"])) for item in gold["subjects"]}
    actual = {
        (item["semester"], _normal_name(item["name"])) for item in result["subjects_candidates"]
    }
    assert actual == expected
    assert result["curricular_structure"]["semester_count"] == 8
    assert result["curricular_structure"]["subject_count"] == 54
    assert result["analysis_metrics"]["recognized_regions"] == 2
    assert result["curricular_structure"]["pending_regions"] == 0
    assert "CONOCE" not in result["excerpt"]
