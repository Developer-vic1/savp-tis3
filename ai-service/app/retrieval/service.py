import json
from functools import lru_cache

from app.contracts.responses import KnowledgeEvidence
from app.knowledge.registry import SERVICE_ROOT
from app.retrieval.hybrid import HYBRID_VERSION, HybridHit, HybridRetriever, tokenize
from app.retrieval.index import SELECTED_INDEX, SemanticIndex


@lru_cache(maxsize=1)
def get_retriever() -> HybridRetriever:
    return HybridRetriever(SemanticIndex.load_selected())


def selected_retrieval_metadata() -> dict[str, str]:
    selected = json.loads(SELECTED_INDEX.read_text(encoding="utf-8"))
    corpus_manifest_path = SERVICE_ROOT / "data" / "processed" / "corpus_manifest.json"
    corpus_manifest = json.loads(corpus_manifest_path.read_text(encoding="utf-8"))
    return {
        "corpus_version": str(corpus_manifest["corpus_version"]),
        "embedding_model": str(selected["model_id"]),
        "retrieval_version": HYBRID_VERSION,
    }


def relevant_excerpt(text: str, query: str, max_chars: int = 650) -> str:
    normalized_text = text.casefold()
    positions = [
        normalized_text.find(term.casefold())
        for term in tokenize(query)
        if normalized_text.find(term.casefold()) >= 0
    ]
    anchor = min(positions) if positions else 0
    start = max(0, anchor - 100)
    end = min(len(text), start + max_chars)
    excerpt = text[start:end].strip()
    if start:
        excerpt = "…" + excerpt
    if end < len(text):
        excerpt += "…"
    return excerpt


def hit_to_evidence(hit: HybridHit, query: str) -> KnowledgeEvidence:
    chunk = hit.chunk
    reference = chunk.url
    if chunk.page:
        reference = f"{reference}#page={chunk.page}"
    excerpt = relevant_excerpt(chunk.text, query)
    return KnowledgeEvidence(
        source_id=chunk.source_id,
        chunk_id=chunk.chunk_id,
        title=chunk.title,
        institution=chunk.institution,
        source_type=chunk.source_type,
        publication_date=chunk.publication_date,
        page=chunk.page,
        section=chunk.section,
        summary=excerpt,
        relevant_text=excerpt,
        relevance=round(hit.score, 6),
        reference=reference,
        official=chunk.official,
        retrieval_method=HYBRID_VERSION,
    )


def evidence_is_insufficient(query: str, hits: list[HybridHit]) -> bool:
    if not hits:
        return True
    query_terms = set(tokenize(query))
    if not query_terms:
        return True
    top = hits[0]
    document_terms = set(
        tokenize(" ".join((top.chunk.title, top.chunk.section or "", top.chunk.text)))
    )
    overlap = len(query_terms & document_terms) / len(query_terms)
    return top.score < 0.72 or top.lexical_rank is None or overlap < 0.2
