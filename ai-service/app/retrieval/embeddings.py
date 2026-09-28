from functools import lru_cache
from typing import Any

import numpy as np

from app.knowledge.registry import SERVICE_ROOT

MODEL_SPECS: dict[str, dict[str, str | bool]] = {
    "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2": {
        "query_prefix": "",
        "passage_prefix": "",
        "license": "apache-2.0",
    },
    "intfloat/multilingual-e5-small": {
        "query_prefix": "query: ",
        "passage_prefix": "passage: ",
        "license": "mit",
    },
}


@lru_cache(maxsize=2)
def _load_model(model_id: str) -> Any:
    from sentence_transformers import SentenceTransformer

    if model_id not in MODEL_SPECS:
        raise ValueError(f"Modelo de embeddings no registrado: {model_id}")
    cache = SERVICE_ROOT / ".cache" / "huggingface"
    cache.mkdir(parents=True, exist_ok=True)
    return SentenceTransformer(model_id, device="cpu", cache_folder=str(cache))


class SentenceEmbeddingBackend:
    def __init__(self, model_id: str) -> None:
        if model_id not in MODEL_SPECS:
            raise ValueError(f"Modelo de embeddings no registrado: {model_id}")
        self.model_id = model_id
        self.spec = MODEL_SPECS[model_id]

    @property
    def model(self) -> Any:
        return _load_model(self.model_id)

    def _encode(self, texts: list[str], prefix_key: str) -> np.ndarray:
        prefix = str(self.spec[prefix_key])
        prepared = [prefix + text for text in texts]
        vectors: np.ndarray = self.model.encode(
            prepared,
            batch_size=32,
            show_progress_bar=False,
            convert_to_numpy=True,
            normalize_embeddings=True,
        )
        return np.asarray(vectors, dtype=np.float32)

    def encode_documents(self, texts: list[str]) -> np.ndarray:
        return self._encode(texts, "passage_prefix")

    def encode_queries(self, texts: list[str]) -> np.ndarray:
        return self._encode(texts, "query_prefix")


def model_slug(model_id: str) -> str:
    return model_id.replace("/", "__").replace("-", "_").casefold()
