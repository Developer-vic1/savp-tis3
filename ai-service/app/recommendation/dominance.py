from collections.abc import Mapping, Sequence


def dominates(
    left: Mapping[str, float | None],
    right: Mapping[str, float | None],
    dimensions: Sequence[str],
) -> bool:
    if not dimensions:
        return False
    pairs: list[tuple[float, float]] = []
    for dimension in dimensions:
        left_value = left.get(dimension)
        right_value = right.get(dimension)
        if left_value is None or right_value is None:
            return False
        pairs.append((left_value, right_value))
    return all(a >= b for a, b in pairs) and any(a > b for a, b in pairs)


def non_dominated_alternatives(
    alternatives: Mapping[str, Mapping[str, float | None]],
    dimensions: Sequence[str],
) -> list[str]:
    result = [
        alternative_id
        for alternative_id, values in alternatives.items()
        if not any(
            other_id != alternative_id and dominates(other_values, values, dimensions)
            for other_id, other_values in alternatives.items()
        )
    ]
    return sorted(result)
