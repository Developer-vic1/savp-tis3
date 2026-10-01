from collections import Counter
from collections.abc import Mapping, Sequence
from itertools import combinations
from statistics import fmean

from pydantic import BaseModel, ConfigDict, Field


class RobustnessModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class RobustnessReport(RobustnessModel):
    context: str
    configuration_count: int = Field(ge=1)
    top_1_stability: float = Field(ge=0, le=1)
    top_1_frequency: dict[str, float]
    top_k: int = Field(ge=1)
    top_k_frequency: dict[str, float]
    mean_top_k_jaccard: float = Field(ge=0, le=1)
    mean_spearman_rho: float = Field(ge=-1, le=1)
    mean_kendall_tau: float = Field(ge=-1, le=1)
    rank_reversal_rate: float = Field(ge=0, le=1)
    dominance_stability: float | None = Field(default=None, ge=0, le=1)
    interpretation: str


def _assert_same_alternatives(left: Sequence[str], right: Sequence[str]) -> None:
    if len(left) != len(set(left)) or len(right) != len(set(right)):
        raise ValueError("Los rankings no pueden contener alternativas duplicadas")
    if set(left) != set(right):
        raise ValueError("Los rankings deben contener las mismas alternativas")


def top_k_jaccard(left: Sequence[str], right: Sequence[str], k: int) -> float:
    if k < 1:
        raise ValueError("k debe ser al menos 1")
    left_set = set(left[:k])
    right_set = set(right[:k])
    union = left_set | right_set
    return 1.0 if not union else len(left_set & right_set) / len(union)


def spearman_rho(left: Sequence[str], right: Sequence[str]) -> float:
    _assert_same_alternatives(left, right)
    count = len(left)
    if count < 2:
        return 1.0
    right_rank = {alternative: index for index, alternative in enumerate(right, start=1)}
    squared_differences = sum(
        (index - right_rank[alternative]) ** 2
        for index, alternative in enumerate(left, start=1)
    )
    return 1 - (6 * squared_differences) / (count * (count**2 - 1))


def kendall_tau(left: Sequence[str], right: Sequence[str]) -> float:
    _assert_same_alternatives(left, right)
    count = len(left)
    if count < 2:
        return 1.0
    left_rank = {alternative: index for index, alternative in enumerate(left)}
    right_rank = {alternative: index for index, alternative in enumerate(right)}
    concordant = 0
    discordant = 0
    for first, second in combinations(left, 2):
        left_order = left_rank[first] - left_rank[second]
        right_order = right_rank[first] - right_rank[second]
        if left_order * right_order > 0:
            concordant += 1
        else:
            discordant += 1
    return (concordant - discordant) / (concordant + discordant)


def pairwise_reversal_rate(reference: Sequence[str], ranking: Sequence[str]) -> float:
    return (1 - kendall_tau(reference, ranking)) / 2


def rank_alternatives(
    alternatives: Mapping[str, Mapping[str, float]],
    weights: Mapping[str, float],
) -> list[str]:
    if not alternatives:
        return []
    if not weights or any(weight < 0 for weight in weights.values()):
        raise ValueError("Los pesos deben ser no negativos y contener dimensiones")
    if abs(sum(weights.values()) - 1.0) > 1e-9:
        raise ValueError("Los pesos deben sumar 1")
    dimensions = set(weights)
    for alternative_id, values in alternatives.items():
        if set(values) != dimensions:
            raise ValueError(f"Dimensiones incompletas para {alternative_id}")
    scores = {
        alternative_id: sum(values[name] * weights[name] for name in weights)
        for alternative_id, values in alternatives.items()
    }
    return sorted(scores, key=lambda alternative_id: (-scores[alternative_id], alternative_id))


def evaluate_rank_robustness(
    rankings: Sequence[Sequence[str]],
    *,
    top_k: int = 3,
    dominance_fronts: Sequence[Sequence[str]] | None = None,
    context: str = "ROBUSTNESS_OVER_WEIGHT_SPACE",
) -> RobustnessReport:
    if not rankings:
        raise ValueError("Se requiere al menos un ranking")
    reference = list(rankings[0])
    if not reference:
        raise ValueError("Los rankings no pueden estar vacíos")
    normalized = [list(ranking) for ranking in rankings]
    for ranking in normalized[1:]:
        _assert_same_alternatives(reference, ranking)
    effective_k = min(top_k, len(reference))
    top_1_counts = Counter(ranking[0] for ranking in normalized)
    top_k_counts = Counter(
        alternative for ranking in normalized for alternative in ranking[:effective_k]
    )
    configuration_count = len(normalized)
    pairwise_jaccards = [
        top_k_jaccard(left, right, effective_k)
        for left, right in combinations(normalized, 2)
    ]
    rhos = [spearman_rho(reference, ranking) for ranking in normalized]
    taus = [kendall_tau(reference, ranking) for ranking in normalized]
    reversals = [pairwise_reversal_rate(reference, ranking) for ranking in normalized]

    dominance_stability: float | None = None
    if dominance_fronts is not None:
        if len(dominance_fronts) != configuration_count:
            raise ValueError("Debe existir un frente de dominancia por configuración")
        reference_front = set(dominance_fronts[0])
        dominance_stability = fmean(
            (
                len(reference_front & set(front)) / len(reference_front | set(front))
                if reference_front | set(front)
                else 1.0
            )
            for front in dominance_fronts
        )

    return RobustnessReport(
        context=context,
        configuration_count=configuration_count,
        top_1_stability=max(top_1_counts.values()) / configuration_count,
        top_1_frequency={
            alternative: count / configuration_count
            for alternative, count in sorted(top_1_counts.items())
        },
        top_k=effective_k,
        top_k_frequency={
            alternative: top_k_counts[alternative] / configuration_count
            for alternative in sorted(reference)
        },
        mean_top_k_jaccard=fmean(pairwise_jaccards) if pairwise_jaccards else 1.0,
        mean_spearman_rho=fmean(rhos),
        mean_kendall_tau=fmean(taus),
        rank_reversal_rate=fmean(reversals),
        dominance_stability=dominance_stability,
        interpretation=(
            "Las frecuencias describen estabilidad sobre las configuraciones exploradas; "
            "no son probabilidades de que una carrera sea correcta."
        ),
    )
