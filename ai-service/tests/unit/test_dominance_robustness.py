import pytest

from app.recommendation.dominance import dominates, non_dominated_alternatives
from app.recommendation.robustness import (
    evaluate_rank_robustness,
    kendall_tau,
    pairwise_reversal_rate,
    rank_alternatives,
    spearman_rho,
    top_k_jaccard,
)


def test_dominance_requires_every_declared_dimension() -> None:
    assert dominates({"a": 80, "b": 70}, {"a": 70, "b": 70}, ["a", "b"])
    assert not dominates({"a": 80, "b": 60}, {"a": 70, "b": 70}, ["a", "b"])
    assert not dominates({"a": 80, "b": None}, {"a": 70, "b": 70}, ["a", "b"])


def test_pareto_front_preserves_tradeoffs() -> None:
    alternatives = {
        "balanced": {"interest": 70.0, "preparation": 70.0},
        "interest": {"interest": 90.0, "preparation": 60.0},
        "dominated": {"interest": 60.0, "preparation": 60.0},
    }
    assert non_dominated_alternatives(
        alternatives, ["interest", "preparation"]
    ) == ["balanced", "interest"]


def test_rank_correlation_formulas_on_known_reversal() -> None:
    reference = ["A", "B", "C"]
    reverse = ["C", "B", "A"]
    assert spearman_rho(reference, reference) == 1
    assert spearman_rho(reference, reverse) == -1
    assert kendall_tau(reference, reference) == 1
    assert kendall_tau(reference, reverse) == -1
    assert pairwise_reversal_rate(reference, reverse) == 1
    assert top_k_jaccard(reference, reverse, 2) == pytest.approx(1 / 3)


def test_weight_space_report_is_robustness_not_probability() -> None:
    alternatives = {
        "A": {"x": 90.0, "y": 60.0},
        "B": {"x": 60.0, "y": 90.0},
        "C": {"x": 50.0, "y": 50.0},
    }
    rankings = [
        rank_alternatives(alternatives, {"x": 0.8, "y": 0.2}),
        rank_alternatives(alternatives, {"x": 0.5, "y": 0.5}),
        rank_alternatives(alternatives, {"x": 0.2, "y": 0.8}),
    ]
    report = evaluate_rank_robustness(rankings, top_k=2)
    assert report.context == "ROBUSTNESS_OVER_WEIGHT_SPACE"
    assert report.configuration_count == 3
    assert report.top_1_stability == pytest.approx(2 / 3)
    assert report.top_k_frequency["C"] == 0
    assert "no son probabilidades" in report.interpretation
