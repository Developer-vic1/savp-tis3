from collections import defaultdict
from statistics import fmean, pstdev

from app.contracts.requests import LearningActivityData, LearningTask
from app.contracts.responses import EvidenceStatus, LearningActivityProfile


def _rounded(value: float) -> float:
    return round(value, 2)


def _normalized_task_score(task: LearningTask) -> float:
    if task.score is None:
        raise ValueError("La tarea no contiene calificación")
    return _rounded(
        100 * (float(task.score) - float(task.scale_min)) / (task.scale_max - task.scale_min)
    )


def _regularity(tasks: list[LearningTask]) -> float | None:
    by_period: dict[int, list[LearningTask]] = defaultdict(list)
    for task in tasks:
        if task.period_order is not None:
            by_period[task.period_order].append(task)
    if len(by_period) < 2:
        return None
    completion_by_period = [
        sum(task.delivered for task in period_tasks) / len(period_tasks)
        for _, period_tasks in sorted(by_period.items())
    ]
    return _rounded(max(0.0, 1 - (pstdev(completion_by_period) / 0.5)))


def build_learning_activity_profile(data: LearningActivityData) -> LearningActivityProfile:
    warnings: list[str] = []
    assigned: int | None
    delivered: int | None
    late: int | None
    if data.tasks:
        assigned = len(data.tasks)
        delivered = sum(task.delivered for task in data.tasks)
        late = sum(task.late for task in data.tasks)
        evidence_status = EvidenceStatus.SUFFICIENT
        if data.assigned is not None and data.assigned != assigned:
            warnings.append(
                "Los totales agregados difieren del detalle; las métricas usan las "
                "tareas detalladas."
            )
    else:
        assigned = data.assigned
        delivered = data.delivered
        late = data.late
        evidence_status = (
            EvidenceStatus.PARTIAL
            if assigned is not None and delivered is not None
            else EvidenceStatus.INSUFFICIENT
        )

    missing = assigned - delivered if assigned is not None and delivered is not None else None
    completion = delivered / assigned if assigned and delivered is not None else None
    late_ratio = late / delivered if delivered and late is not None else None
    on_time = (
        (delivered - late) / assigned
        if assigned and delivered is not None and late is not None
        else None
    )

    graded = [task for task in data.tasks if task.score is not None]
    grades = [_normalized_task_score(task) for task in graded]
    if data.tasks and not graded:
        warnings.append(
            "No hay tareas calificadas; el promedio de actividad permanece desconocido."
        )
    regularity = _regularity(data.tasks)
    if data.tasks and regularity is None:
        warnings.append(
            "No hay tareas en al menos dos períodos ordenados; la regularidad permanece "
            "desconocida."
        )
    if evidence_status is EvidenceStatus.INSUFFICIENT:
        warnings.append(
            "No hay totales ni tareas suficientes para calcular actividad de aprendizaje."
        )

    return LearningActivityProfile(
        evidence_status=evidence_status,
        assigned=assigned,
        delivered=delivered,
        missing=missing,
        late=late,
        completion_ratio=_rounded(completion) if completion is not None else None,
        on_time_ratio=_rounded(on_time) if on_time is not None else None,
        late_ratio=_rounded(late_ratio) if late_ratio is not None else None,
        graded_count=len(grades),
        mean_grade=_rounded(fmean(grades)) if grades else None,
        grade_standard_deviation=_rounded(pstdev(grades)) if len(grades) > 1 else None,
        regularity_ratio=regularity,
        warnings=warnings,
    )
