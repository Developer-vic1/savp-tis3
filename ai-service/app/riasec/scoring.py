from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import VocationalData
from app.contracts.responses import RiasecProfile
from app.riasec.instrument import load_instrument
from app.riasec.interpretation import RIASEC_ORDER, ordered_codes, safe_interpretation


def score_riasec(data: VocationalData) -> RiasecProfile:
    instrument = load_instrument()
    unsupported_instrument = data.instrument_id != instrument["instrument_id"]
    unsupported_version = data.instrument_version != instrument["version"]
    if unsupported_instrument or unsupported_version:
        raise DomainError(
            ErrorCode.INVALID_RIASEC_RESPONSE,
            "El instrumento o su versión no están soportados.",
            details=[{
                "expected_instrument_id": instrument["instrument_id"],
                "expected_instrument_version": instrument["version"],
            }],
        )

    expected = {item["item_id"] for item in instrument["items"]}
    received = [response.item_id for response in data.responses]
    duplicates = sorted({item_id for item_id in received if received.count(item_id) > 1})
    missing = sorted(expected - set(received))
    unexpected = sorted(set(received) - expected)
    if missing or duplicates or unexpected or len(received) != len(expected):
        raise DomainError(
            ErrorCode.INCOMPLETE_INSTRUMENT,
            "El instrumento RIASEC debe contener exactamente una respuesta para cada reactivo.",
            details=[{
                "missing_item_ids": missing,
                "duplicate_item_ids": duplicates,
                "unexpected_item_ids": unexpected,
            }],
        )

    area_by_item = {item["item_id"]: item["code"] for item in instrument["items"]}
    scores = {code: 0 for code in RIASEC_ORDER}
    for response in data.responses:
        scores[area_by_item[response.item_id]] += response.value

    ordered = ordered_codes(scores)
    top_score = scores[ordered[0]]
    top_codes = [code for code in ordered if scores[code] == top_score]
    return RiasecProfile(
        instrument_id=instrument["instrument_id"],
        instrument_version=instrument["version"],
        scores=scores,
        normalized_scores={code: round(score * 5.0, 2) for code, score in scores.items()},
        answered_items=len(received),
        expected_items=len(expected),
        coverage_ratio=round(len(received) / len(expected), 2),
        ordered_codes=ordered,
        holland_code="".join(ordered[:3]),
        top_codes=top_codes,
        top_tie=len(top_codes) > 1,
        interpretation=safe_interpretation(top_codes),
    )
