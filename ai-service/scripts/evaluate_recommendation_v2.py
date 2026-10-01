import argparse
import json
from pathlib import Path
from typing import cast

from app.contracts.requests import AnalysisRequest
from app.contracts.v2 import AnalysisV2Request
from app.domain.student_profile import analyze_student
from app.domain.student_profile_v2 import analyze_student_v2

SERVICE_ROOT = Path(__file__).resolve().parents[1]


def _fixture_payload(path: Path) -> dict[str, object]:
    payload = cast(dict[str, object], json.loads(path.read_text(encoding="utf-8")))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Compara la base V1 experimental con perfiles V2 multidimensionales."
    )
    parser.add_argument(
        "--fixture",
        type=Path,
        default=SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json",
    )
    args = parser.parse_args()
    payload = _fixture_payload(args.fixture)
    v1_request = AnalysisRequest.model_validate(payload)
    v2_payload = {**payload, "schema_version": "2.0"}
    v2_request = AnalysisV2Request.model_validate(v2_payload)
    v1 = analyze_student(v1_request, "evaluation-v1")
    v2 = analyze_student_v2(v2_request, "evaluation-v2")

    serialized_v2 = json.dumps(v2.model_dump(mode="json"), ensure_ascii=False)
    forbidden_fields = {
        "affinity_score",
        "preparation_score",
        "compatibility_score",
        "compatibility_weights",
        "probability_of_success",
    }
    present_forbidden = sorted(
        field for field in forbidden_fields if f'"{field}"' in serialized_v2
    )
    if present_forbidden:
        raise RuntimeError(f"V2 contiene campos prohibidos: {present_forbidden}")

    output = {
        "fixture": args.fixture.name,
        "fixture_classification": "SYNTHETIC_TEST_FIXTURE",
        "comparison_scope": (
            "Comparación descriptiva de trazabilidad e invariantes; no mide predicción."
        ),
        "v1": {
            "classification": "LEGACY_EXPERIMENTAL_BASELINE",
            "criteria_version": v1.criteria_version,
            "ranking_status": v1.ranking_status,
            "ranked_careers": [
                {
                    "career_id": item.career_id,
                    "compatibility_score": item.compatibility_score,
                }
                for item in v1.career_ranking
            ],
            "claims_present": [
                "aggregate_experimental_score",
                "global_ranking",
                "experimental_numeric_gaps",
            ],
        },
        "v2": {
            "criteria_version": v2.criteria_version,
            "aggregation_policy": "NO_COMPOSITE_SCORE",
            "ranking_policy": "NO_GLOBAL_RANKING",
            "bridge_source_mode": v2.traceability.bridge_source_mode,
            "career_profiles": [
                {
                    "career_id": profile.career_id,
                    "academic_relations": profile.preparation.related_relations,
                    "relations_with_evidence": (
                        profile.preparation.relations_with_observed_academic_evidence
                    ),
                    "technical_status": profile.technical_relation.status,
                    "declared_interest_status": profile.declared_interest_relation.status,
                    "vocational_relation_status": (
                        profile.vocational_interest_relation.status
                    ),
                    "numeric_gap_count": len(profile.preparation.numeric_gaps),
                }
                for profile in v2.career_evidence_profiles
            ],
            "claims_removed": [
                "global_winner",
                "compatibility_percentage",
                "unsourced_numeric_gap",
                "riasec_as_aptitude",
            ],
            "information_preserved": [
                "observed_academic_records",
                "riasec_interest_profile",
                "technical_evidence",
                "declared_interest",
                "source_traceability",
            ],
        },
        "invariants": {
            "no_composite_score": True,
            "missing_is_not_zero": True,
            "preparation_excludes_evidence_quality": True,
            "riasec_uses_documented_occupational_crosswalk_only": True,
            "no_llm_dependency": True,
        },
    }
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
