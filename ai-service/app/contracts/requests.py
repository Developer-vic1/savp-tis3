from typing import Annotated, Any

from pydantic import BaseModel, ConfigDict, Field, model_validator

Score = Annotated[float, Field(ge=-10000, le=10000, allow_inf_nan=False)]
RiasecValue = Annotated[int, Field(ge=0, le=4, strict=True)]


class ContractModel(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class AcademicRecord(ContractModel):
    subject: str = Field(min_length=1, max_length=120)
    area: str | None = Field(default=None, min_length=1, max_length=120)
    score: Score
    scale_min: Score = 0
    scale_max: Score = 100
    period: str | None = Field(default=None, max_length=80)
    period_order: int | None = Field(default=None, ge=0, le=100)

    @model_validator(mode="after")
    def validate_scale(self) -> "AcademicRecord":
        if self.scale_max <= self.scale_min:
            raise ValueError("scale_max debe ser mayor que scale_min")
        if not self.scale_min <= self.score <= self.scale_max:
            raise ValueError("score debe encontrarse dentro de su escala declarada")
        return self


class AcademicData(ContractModel):
    records: list[AcademicRecord] = Field(min_length=1, max_length=500)


class AttendanceData(ContractModel):
    attended_classes: int = Field(ge=0, le=10000)
    total_classes: int = Field(gt=0, le=10000)

    @model_validator(mode="after")
    def validate_counts(self) -> "AttendanceData":
        if self.attended_classes > self.total_classes:
            raise ValueError("attended_classes no puede superar total_classes")
        return self


class RiasecResponse(ContractModel):
    item_id: int = Field(ge=1, le=30)
    value: RiasecValue


class VocationalData(ContractModel):
    instrument_id: str = Field(min_length=1, max_length=80)
    instrument_version: str = Field(min_length=1, max_length=80)
    responses: list[RiasecResponse] = Field(min_length=1, max_length=30)


class TechnicalCompetency(ContractModel):
    name: str = Field(min_length=1, max_length=160)
    evidence: str | None = Field(default=None, max_length=500)


class TechnicalData(ContractModel):
    specialty: str | None = Field(default=None, max_length=160)
    competencies: list[TechnicalCompetency] = Field(default_factory=list, max_length=100)


class LearningActivityData(ContractModel):
    assigned: int | None = Field(default=None, ge=0, le=10000)
    delivered: int | None = Field(default=None, ge=0, le=10000)
    late: int | None = Field(default=None, ge=0, le=10000)
    tasks: list["LearningTask"] = Field(default_factory=list, max_length=10000)

    @model_validator(mode="after")
    def validate_aggregates(self) -> "LearningActivityData":
        if self.delivered is not None and self.assigned is None:
            raise ValueError("assigned es obligatorio cuando se informa delivered")
        if (
            self.assigned is not None
            and self.delivered is not None
            and self.delivered > self.assigned
        ):
            raise ValueError("delivered no puede superar assigned")
        if self.late is not None and self.delivered is None:
            raise ValueError("delivered es obligatorio cuando se informa late")
        if self.late is not None and self.delivered is not None and self.late > self.delivered:
            raise ValueError("late no puede superar delivered")
        return self


class LearningTask(ContractModel):
    task_id: str = Field(min_length=1, max_length=120)
    delivered: bool
    late: bool = False
    score: Score | None = None
    scale_min: Score = 0
    scale_max: Score = 100
    period: str | None = Field(default=None, max_length=80)
    period_order: int | None = Field(default=None, ge=0, le=100)

    @model_validator(mode="after")
    def validate_task(self) -> "LearningTask":
        if self.late and not self.delivered:
            raise ValueError("una tarea no entregada no puede marcarse como atrasada")
        if self.score is not None and not self.delivered:
            raise ValueError("una tarea no entregada no puede tener calificación")
        if self.scale_max <= self.scale_min:
            raise ValueError("scale_max debe ser mayor que scale_min")
        if self.score is not None and not self.scale_min <= self.score <= self.scale_max:
            raise ValueError("score debe encontrarse dentro de su escala declarada")
        return self


class AnalysisRequest(ContractModel):
    schema_version: str
    student_id: str = Field(min_length=1, max_length=100, pattern=r"^[A-Za-z0-9._:-]+$")
    academic_period: str | None = Field(default=None, max_length=80)
    course: str | None = Field(default=None, max_length=120)
    academic: AcademicData | None = None
    attendance: AttendanceData | None = None
    vocational: VocationalData | None = None
    technical: TechnicalData | None = None
    declared_interests: list[str] | None = Field(default=None, max_length=50)
    history: list[dict[str, Any]] | None = Field(default=None, max_length=100)
    learning_activity: LearningActivityData | None = None


class KnowledgeSearchRequest(ContractModel):
    schema_version: str
    query: str = Field(min_length=2, max_length=1000)
    top_k: int = Field(default=5, ge=1, le=20)
    institution: str | None = Field(default=None, max_length=200)
    source_type: str | None = Field(default=None, max_length=100)
    official_only: bool = True


class TutorQueryRequest(ContractModel):
    schema_version: str
    question: str = Field(min_length=2, max_length=2000)
    subject: str | None = Field(default=None, max_length=120)
    level: str | None = Field(default=None, max_length=80)
    academic_context: dict[str, Any] | None = None
    student_context: dict[str, Any] | None = None
