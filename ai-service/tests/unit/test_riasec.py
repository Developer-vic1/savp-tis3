import pytest

from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import RiasecResponse, VocationalData
from app.riasec.scoring import score_riasec


def vocational(values: list[int]) -> VocationalData:
    return VocationalData(
        instrument_id="onet-mini-ip-2.0-es",
        instrument_version="2.0-es-2025",
        responses=[
            RiasecResponse(item_id=index, value=value)
            for index, value in enumerate(values, start=1)
        ],
    )


@pytest.mark.parametrize("value,expected", [(0, 0), (4, 20)])
def test_all_scale_extremes(value: int, expected: int) -> None:
    result = score_riasec(vocational([value] * 30))
    assert result.scores == {code: expected for code in "RIASEC"}
    assert result.ordered_codes == list("RIASEC")
    assert result.top_tie is True
    assert result.normalized_scores == {code: expected * 5 for code in "RIASEC"}
    assert result.answered_items == 30
    assert result.expected_items == 30
    assert result.coverage_ratio == 1


def test_tie_order_is_reproducible() -> None:
    values = [0] * 30
    for item_id in (2, 4, 8, 10, 14, 16, 20, 22, 26, 28):
        values[item_id - 1] = 4
    first = score_riasec(vocational(values))
    second = score_riasec(vocational(values))
    assert first.scores == second.scores
    assert first.top_codes == ["I", "S"]
    assert first.holland_code == "ISR"


def test_duplicate_and_missing_items_are_rejected() -> None:
    data = vocational([1] * 30)
    data.responses[-1] = RiasecResponse(item_id=1, value=1)
    with pytest.raises(DomainError) as caught:
        score_riasec(data)
    assert caught.value.code is ErrorCode.INCOMPLETE_INSTRUMENT
