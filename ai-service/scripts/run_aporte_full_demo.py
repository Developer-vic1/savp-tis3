import json
import sys
from datetime import UTC, datetime
from pathlib import Path
from typing import Any, cast

from app.contracts.requests import TutorQueryRequest
from app.contracts.v2 import AnalysisV2Request
from app.domain.student_profile_v2 import analyze_student_v2
from app.retrieval.service import (
    evidence_is_insufficient,
    get_retriever,
    hit_to_evidence,
    selected_retrieval_metadata,
)
from app.tutor.service import answer_structured

SERVICE_ROOT = Path(__file__).resolve().parents[1]


def _fixture() -> dict[str, Any]:
    raw = json.loads(
        (SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json").read_text(
            encoding="utf-8"
        )
    )
    payload = cast(dict[str, Any], raw)
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    payload["schema_version"] = "2.0"
    return payload


def main() -> None:
    if hasattr(sys.stdout, "reconfigure"):
        sys.stdout.reconfigure(encoding="utf-8")
    payload = _fixture()
    request = AnalysisV2Request.model_validate(payload)
    analysis = analyze_student_v2(
        request,
        "demo-aporte-ingenieril-full",
        generated_at=datetime(2026, 9, 29, tzinfo=UTC),
    )
    retriever = get_retriever()
    question = (
        "materias primer semestre Ingeniería de Sistemas UCB Álgebra Lineal "
        "Matemáticas Discretas"
    )
    hits = retriever.search(question, 5, official_only=True)
    evidence = [hit_to_evidence(hit, question) for hit in hits]
    tutor_payload = TutorQueryRequest(
        schema_version="1.0",
        question=question,
        subject="Ingeniería de Sistemas",
        level="primer semestre",
        academic_context={
            "course": request.course,
            "areas_to_reinforce": ["Matemática"],
        },
    )
    tutor_answer, tutor_material = answer_structured(tutor_payload, retriever)
    snapshot = analysis.student_snapshot
    metadata = selected_retrieval_metadata()
    output = {
        "01_fixture": {
            "classification": "SYNTHETIC_TEST_FIXTURE",
            "path": "data/fixtures/complete_profile.json",
            "student_id": request.student_id,
        },
        "02_riasec": snapshot.vocational_interest_evidence.model_dump(mode="json"),
        "03_academic": snapshot.academic_evidence.model_dump(mode="json"),
        "04_technical_bth": snapshot.technical_evidence.model_dump(mode="json"),
        "05_evidence_quality": snapshot.evidence_quality.model_dump(mode="json"),
        "06_career_evidence_profiles": [
            item.model_dump(mode="json") for item in analysis.career_evidence_profiles
        ],
        "07_reinforcement": {
            item.career_id: [
                area.model_dump(mode="json")
                for area in item.preparation.reinforcement_areas
            ]
            for item in analysis.career_evidence_profiles
        },
        "08_limitations": [item.model_dump(mode="json") for item in analysis.limitations],
        "09_sources": [item.model_dump(mode="json") for item in analysis.sources_used],
        "10_knowledge_query": {
            "question": question,
            "insufficient_evidence": evidence_is_insufficient(question, hits),
            "results": [item.model_dump(mode="json") for item in evidence],
        },
        "11_tutor_answer": tutor_answer.model_dump(mode="json"),
        "12_citations": [
            {
                "source_id": item.source_id,
                "chunk_id": item.chunk_id,
                "reference": item.reference,
            }
            for item in tutor_material.evidence
        ],
        "13_trace_id": analysis.trace_id,
        "14_versions": {
            "engine": analysis.engine_version,
            "criteria": analysis.criteria_version,
            "bridge": analysis.traceability.bridge_version,
            "catalog": analysis.traceability.catalog_version,
            "crosswalk": analysis.traceability.crosswalk_version,
            **metadata,
        },
    }
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
