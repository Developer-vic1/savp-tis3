import math
from statistics import fmean


def recall_at_k(retrieved_ids: list[str], relevant_ids: set[str], k: int) -> float:
    if not relevant_ids:
        return 0.0
    retrieved = set(retrieved_ids[:k])
    return len(retrieved & relevant_ids) / len(relevant_ids)


def reciprocal_rank(retrieved_ids: list[str], relevant_ids: set[str]) -> float:
    for rank, item_id in enumerate(retrieved_ids, start=1):
        if item_id in relevant_ids:
            return 1 / rank
    return 0.0


def ndcg_at_k(retrieved_ids: list[str], relevant_ids: set[str], k: int) -> float:
    if not relevant_ids:
        return 0.0
    seen: set[str] = set()
    dcg = 0.0
    for rank, item_id in enumerate(retrieved_ids[:k], start=1):
        if item_id in relevant_ids and item_id not in seen:
            dcg += 1 / math.log2(rank + 1)
            seen.add(item_id)
    ideal_hits = min(k, len(relevant_ids))
    idcg = sum(1 / math.log2(rank + 1) for rank in range(1, ideal_hits + 1))
    return dcg / idcg if idcg else 0.0


def aggregate_metrics(
    rankings: list[list[str]],
    relevant: list[set[str]],
) -> dict[str, float]:
    if not rankings or len(rankings) != len(relevant):
        raise ValueError("rankings y relevant deben tener la misma longitud no vacía")
    pairs = list(zip(rankings, relevant, strict=True))
    return {
        "recall_at_1": round(
            fmean(recall_at_k(items, expected, 1) for items, expected in pairs),
            4,
        ),
        "recall_at_3": round(
            fmean(recall_at_k(items, expected, 3) for items, expected in pairs),
            4,
        ),
        "recall_at_5": round(
            fmean(recall_at_k(items, expected, 5) for items, expected in pairs),
            4,
        ),
        "mrr": round(
            fmean(reciprocal_rank(items, expected) for items, expected in pairs),
            4,
        ),
        "ndcg_at_5": round(
            fmean(ndcg_at_k(items, expected, 5) for items, expected in pairs),
            4,
        ),
    }
