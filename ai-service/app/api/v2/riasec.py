from typing import Literal

from fastapi import APIRouter, Depends, Request
from pydantic import BaseModel, ConfigDict

from app.api.dependencies import verify_api_key
from app.riasec.instrument import load_instrument
from app.riasec.public_contract import PublicRiasecData
from app.riasec.scoring import score_riasec

router = APIRouter(
    prefix="/api/v2/riasec", tags=["riasec-v2"], dependencies=[Depends(verify_api_key)]
)


class ApiModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class ScoreRequest(PublicRiasecData):
    pass


class InstrumentItem(ApiModel):
    item_id: int
    text: str


class ResponseOption(ApiModel):
    value: int
    label: str


class InstrumentResponse(ApiModel):
    schema_version: Literal["2.0"] = "2.0"
    instrument_id: str
    instrument_version: str
    title: str
    items: list[InstrumentItem]
    response_scale: list[ResponseOption]
    source_attribution: str
    source_url: str
    license: str
    license_url: str
    limitations: list[str]


class ScoreResponse(ApiModel):
    schema_version: Literal["2.0"] = "2.0"
    instrument_version: str
    scores: dict[str, int]
    top_codes: list[str]
    holland_code: str
    limitations: list[str]
    trace_id: str


LIMITATIONS = [
    "RIASEC describe intereses; no mide aptitud, inteligencia ni capacidad.",
    "El resultado no predice éxito ni decide una carrera.",
    "La equivalencia psicométrica para estudiantes bolivianos no se ha validado.",
]


@router.get("/instrument", response_model=InstrumentResponse)
def instrument() -> InstrumentResponse:
    data = load_instrument()
    return InstrumentResponse(
        instrument_id=data["instrument_id"],
        instrument_version=data["version"],
        title=data["title"],
        items=[
            InstrumentItem(item_id=item["item_id"], text=item["text"])
            for item in data["items"]
        ],
        response_scale=[
            ResponseOption(value=value, label=label)
            for value, label in enumerate(
                [
                    "Me disgusta mucho",
                    "Me disgusta",
                    "No estoy seguro",
                    "Me gusta",
                    "Me gusta mucho",
                ],
                start=1,
            )
        ],
        source_attribution=data["attribution"],
        source_url=data["source_url"],
        license=data["license"],
        license_url=data["license_url"],
        limitations=LIMITATIONS,
    )


@router.post("/score", response_model=ScoreResponse)
def score(payload: ScoreRequest, request: Request) -> ScoreResponse:
    result = score_riasec(payload.to_vocational())
    return ScoreResponse(
        instrument_version=result.instrument_version,
        scores=result.scores,
        top_codes=result.top_codes,
        holland_code=result.holland_code,
        limitations=LIMITATIONS,
        trace_id=request.state.trace_id,
    )
