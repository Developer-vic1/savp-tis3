"""Single versioned boundary for the official public 1–5 response scale."""

from pydantic import BaseModel, ConfigDict, Field

from app.contracts.requests import RiasecResponse, VocationalData
from app.riasec.instrument import load_instrument
from app.riasec.scoring import official_web_value_to_internal, score_riasec


class PublicResponse(BaseModel):
    model_config = ConfigDict(extra="forbid")
    item_id: int = Field(strict=True, ge=1, le=30)
    value: int = Field(strict=True, ge=1, le=5)


class PublicRiasecData(BaseModel):
    model_config = ConfigDict(extra="forbid")
    instrument_version: str = Field(min_length=1, max_length=80)
    responses: list[PublicResponse] = Field(min_length=30, max_length=30)

    def to_vocational(self) -> VocationalData:
        data = VocationalData(
            instrument_id=load_instrument()["instrument_id"],
            instrument_version=self.instrument_version,
            responses=[
                RiasecResponse(
                    item_id=item.item_id, value=official_web_value_to_internal(item.value)
                )
                for item in self.responses
            ],
        )
        score_riasec(data)  # Reject duplicate IDs and unsupported versions before analysis.
        return data
