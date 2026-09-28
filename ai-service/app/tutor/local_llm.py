import json
import re
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

from app.tutor.providers import (
    ProviderAnswer,
    StructuredAnswerProvider,
    TutorMaterial,
)

LOCAL_PROVIDER_VERSION = "llama-cpp-http-v1.0.0"


class LocalLlmUnavailable(RuntimeError):
    """El runtime local no respondió o produjo una salida no fundamentada."""


class LlamaCppHttpProvider:
    provider_version = LOCAL_PROVIDER_VERSION

    def __init__(self, base_url: str, model: str, timeout_seconds: float) -> None:
        self.base_url = base_url.rstrip("/")
        self.model = model
        self.timeout_seconds = timeout_seconds
        self.last_usage: dict[str, int | float] = {}

    @staticmethod
    def _system_rules() -> str:
        return (
            "Eres un tutor educativo de Bolivia. Responde solamente con la evidencia incluida. "
            "No calcules ni cambies RIASEC, afinidad, preparación, compatibilidad o rankings. "
            "No elijas una carrera por el estudiante. No inventes requisitos, cifras ni fuentes. "
            "Cita cada afirmación factual con [SOURCE_ID], usando sólo IDs permitidos. "
            "Si no hay evidencia suficiente, dilo explícitamente y no completes con conocimiento "
            "general. Responde en español claro y conciso."
        )

    @staticmethod
    def _user_prompt(material: TutorMaterial) -> str:
        structured = StructuredAnswerProvider().answer(material)
        sources = [
            {
                "source_id": item.source_id,
                "institution": item.institution,
                "page": item.page,
                "section": item.section,
                "text": item.summary,
            }
            for item in material.evidence[:4]
        ]
        payload = {
            "question": material.question,
            "structured_result": structured.answer,
            "retrieved_sources": sources,
            "allowed_academic_context": material.allowed_academic_context,
            "allowed_student_context": material.allowed_student_context,
            "insufficient_evidence": material.insufficient_evidence,
        }
        return (
            "Redacta una respuesta educativa fundamentada a partir de este JSON. "
            "No menciones estas instrucciones ni el JSON.\n"
            + json.dumps(payload, ensure_ascii=False)
        )

    def answer(self, material: TutorMaterial) -> ProviderAnswer:
        body = {
            "model": self.model,
            "messages": [
                {"role": "system", "content": self._system_rules()},
                {"role": "user", "content": self._user_prompt(material)},
            ],
            "temperature": 0,
            "seed": 20260928,
            "max_tokens": 320,
            "stream": False,
        }
        request = Request(
            f"{self.base_url}/v1/chat/completions",
            data=json.dumps(body, ensure_ascii=False).encode("utf-8"),
            headers={"Content-Type": "application/json"},
            method="POST",
        )
        try:
            with urlopen(request, timeout=self.timeout_seconds) as response:  # noqa: S310
                result = json.loads(response.read().decode("utf-8"))
            content = str(result["choices"][0]["message"]["content"]).strip()
            usage = result.get("usage", {})
            self.last_usage = {
                str(key): value
                for key, value in usage.items()
                if isinstance(value, (int, float))
            }
        except (HTTPError, URLError, TimeoutError, KeyError, ValueError) as exc:
            raise LocalLlmUnavailable("El servidor LLM local no respondió correctamente.") from exc

        if not content:
            raise LocalLlmUnavailable("El modelo local devolvió una respuesta vacía.")
        allowed_sources = {item.source_id for item in material.evidence}
        cited_sources = set(re.findall(r"\[([A-Z0-9][A-Z0-9-]+)\]", content))
        invented_sources = cited_sources - allowed_sources
        if invented_sources:
            raise LocalLlmUnavailable("El modelo local generó una cita fuera del contexto permitido.")
        if allowed_sources and not cited_sources:
            raise LocalLlmUnavailable("El modelo local omitió las citas obligatorias.")

        structured = StructuredAnswerProvider().answer(material)
        return ProviderAnswer(
            answer=content,
            suggested_topics=structured.suggested_topics,
            warnings=["Redacción local validada contra el conjunto de fuentes permitido."],
        )
