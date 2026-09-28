def linear_slope(points: list[tuple[int, float]]) -> float | None:
    unique_x = {x for x, _ in points}
    if len(points) < 2 or len(unique_x) < 2:
        return None
    mean_x = sum(x for x, _ in points) / len(points)
    mean_y = sum(y for _, y in points) / len(points)
    denominator = sum((x - mean_x) ** 2 for x, _ in points)
    if denominator == 0:
        return None
    numerator = sum((x - mean_x) * (y - mean_y) for x, y in points)
    return round(numerator / denominator, 4)


def trend_label(slope: float | None) -> str | None:
    if slope is None:
        return None
    if slope > 1:
        return "ASCENDING"
    if slope < -1:
        return "DESCENDING"
    return "STABLE"

