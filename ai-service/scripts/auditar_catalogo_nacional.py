"""Auditoría local reproducible de candidatos; no cambia ni promueve evidencia."""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import sys
from datetime import UTC, datetime
from pathlib import Path
from typing import Any
from urllib.parse import urlsplit

SERVICE_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(SERVICE_ROOT))

from app.knowledge.ingesta_nacional import canonical_url  # noqa: E402

KINDS = {
    "CARRERA",
    "MALLA_O_PLAN",
    "DURACION",
    "COSTO_PUBLICADO",
    "BECA_PUBLICADA",
    "PERFIL_PROFESIONAL",
    "SEDE",
}
CONTAMINATION = re.compile(
    r"casino|jackpot|roulette|slot machine|slot game|"
    r"pari sportif|jeux de hasard|online gambling",
    re.I,
)


def normalized(value: Any) -> str:
    return re.sub(r"\s+", " ", str(value or "")).strip().casefold()


def evidence_texts(chunks: list[dict[str, Any]]) -> list[str]:
    """Reconstituye solo solapamientos literales de chunks adyacentes del mismo ámbito."""
    texts: list[str] = []
    previous: tuple[Any, Any] | None = None
    chain = ""
    for chunk in chunks:
        text = str(chunk.get("text") or "")
        location = (chunk.get("page"), chunk.get("section"))
        overlap = 0
        if location == previous:
            # La ingesta nacional usa 120 caracteres y ajusta al límite de palabra.
            for size in range(min(120, len(chain), len(text)), 19, -1):
                if chain.endswith(text[:size]):
                    overlap = size
                    break
        if overlap:
            chain += text[overlap:]
        else:
            if chain:
                texts.append(normalized(chain))
            chain = text
        previous = location
        texts.append(normalized(chunk.get("section")))
    if chain:
        texts.append(normalized(chain))
    return texts


def audit_catalog(output: Path, catalog: dict[str, Any]) -> dict[str, Any]:
    findings: list[dict[str, Any]] = []
    documents = []
    inventory = []
    candidate_counts = dict.fromkeys(sorted(KINDS), 0)
    institutions = catalog.get("institutions", [])

    def finding(
        code: str, severity: str, path: Path, detail: str, ordinal: int | None = None
    ) -> None:
        findings.append(
            {
                "code": code,
                "severity": severity,
                "manifest": str(path.relative_to(output)),
                "ordinal": ordinal,
                "detail": detail,
            }
        )

    for path in sorted(output.rglob("manifest.json")):
        try:
            manifest_bytes = path.read_bytes()
            manifest = json.loads(manifest_bytes)
            if not isinstance(manifest, dict):
                raise ValueError("El manifiesto no es un objeto.")
            final_url = manifest.get("final_url", "")
            host = (urlsplit(final_url).hostname or "").lower()
            matches = [
                institution
                for institution in institutions
                if institution.get("institution_id") == manifest.get("institution_id")
                or any(
                    host == domain or host.endswith("." + domain)
                    for domain in institution.get("official_domains", [])
                )
            ]
            institution = next(
                (
                    item
                    for item in matches
                    if any(
                        host == domain or host.endswith("." + domain)
                        for domain in item.get("official_domains", [])
                    )
                ),
                matches[0] if matches else {},
            )
            domains = institution.get("official_domains", [])
            if not canonical_url(final_url, domains):
                finding(
                    "SOURCE_DOMAIN_UNCONFIRMED",
                    "CRITICAL",
                    path,
                    "URL final sin dominio confirmado o ruta pública segura en el censo actual.",
                )
            if institution.get("ingestion_status") == "QUARANTINE_SITE_REVIEW_REQUIRED":
                finding(
                    "INSTITUTION_QUARANTINED",
                    "CRITICAL",
                    path,
                    "Fuente institucional en cuarentena; requiere inspección independiente.",
                )
            snapshot_name = manifest.get("snapshot", "")
            snapshot = (path.parent / snapshot_name).resolve()
            if not snapshot_name or not snapshot.is_relative_to(path.parent.resolve()):
                finding(
                    "SNAPSHOT_PATH_UNSAFE",
                    "CRITICAL",
                    path,
                    "La instantánea no tiene una referencia local segura.",
                )
                continue
            content = snapshot.read_bytes()
            digest = hashlib.sha256(content).hexdigest()
            if manifest.get("document_hash") != "sha256:" + digest:
                finding(
                    "SNAPSHOT_HASH_MISMATCH",
                    "CRITICAL",
                    path,
                    "La instantánea no coincide con el hash del manifiesto.",
                )
            chunks_bytes = (path.parent / "chunks.jsonl").read_bytes()
            chunks = [
                json.loads(line)
                for line in chunks_bytes.decode("utf-8").splitlines()
                if line.strip()
            ]
            facts_bytes = (path.parent / "candidatos.json").read_bytes()
            facts = json.loads(facts_bytes)
            if not isinstance(facts, list) or not all(isinstance(item, dict) for item in chunks):
                raise ValueError("Candidatos o fragmentos con estructura inválida.")
            inventory.append(
                {
                    "manifest": str(path.relative_to(output)),
                    "manifest_hash": hashlib.sha256(manifest_bytes).hexdigest(),
                    "snapshot_hash": digest,
                    "chunks_hash": hashlib.sha256(chunks_bytes).hexdigest(),
                    "candidates_hash": hashlib.sha256(facts_bytes).hexdigest(),
                }
            )
            if manifest.get("chunks") != len(chunks) or manifest.get("facts") != len(facts):
                finding(
                    "MANIFEST_COUNTS_MISMATCH",
                    "CRITICAL",
                    path,
                    "Los conteos del manifiesto no corresponden a los archivos.",
                )
            if not chunks:
                finding(
                    "NO_DIGITAL_TEXT",
                    "WARNING",
                    path,
                    "No existe texto digital; no admite entrenamiento ni extracción verificable.",
                )
            if manifest.get("content_type") == "application/pdf" and not content.startswith(
                b"%PDF-"
            ):
                finding("PDF_SIGNATURE_INVALID", "CRITICAL", path, "Firma PDF inválida.")
            texts = []
            for ordinal, chunk in enumerate(chunks):
                if chunk.get("source_url") != final_url or chunk.get("document_hash") != (
                    manifest.get("document_hash")
                ):
                    finding(
                        "CHUNK_REFERENCE_MISMATCH",
                        "CRITICAL",
                        path,
                        "Fragmento de otra URL o documento.",
                        ordinal,
                    )
                identity = (
                    f"{digest}|{chunk.get('page')}|{chunk.get('section')}|"
                    f"{ordinal}|{chunk.get('text')}"
                )
                expected_id = hashlib.sha256(identity.encode()).hexdigest()[:24]
                if chunk.get("chunk_id") != expected_id:
                    finding(
                        "CHUNK_ID_MISMATCH",
                        "CRITICAL",
                        path,
                        "El identificador no coincide con texto, página y sección.",
                        ordinal,
                    )
                page = chunk.get("page")
                if page is not None and (not isinstance(page, int) or page < 1):
                    finding("CHUNK_PAGE_INVALID", "CRITICAL", path, "Página inválida.", ordinal)
                texts.append(normalized(chunk.get("text")))
                texts.append(normalized(chunk.get("section")))
            joined = " ".join(texts)
            evidence_locations = evidence_texts(chunks)
            if CONTAMINATION.search(joined):
                finding(
                    "NON_ACADEMIC_CONTAMINATION",
                    "WARNING",
                    path,
                    "Se detectan términos de apuestas/casino; comprobar contexto "
                    "o sitio comprometido.",
                )
            for ordinal, fact in enumerate(facts):
                if not isinstance(fact, dict):
                    finding(
                        "CANDIDATE_STRUCTURE_INVALID",
                        "CRITICAL",
                        path,
                        "El candidato no es un objeto.",
                        ordinal,
                    )
                    continue
                kind = fact.get("tipo")
                if kind in candidate_counts:
                    candidate_counts[kind] += 1
                else:
                    finding(
                        "CANDIDATE_KIND_UNKNOWN",
                        "WARNING",
                        path,
                        "Tipo de candidato no reconocido.",
                        ordinal,
                    )
                if fact.get("source_url") != final_url:
                    finding(
                        "CANDIDATE_REFERENCE_MISMATCH",
                        "CRITICAL",
                        path,
                        "El candidato refiere otra URL.",
                        ordinal,
                    )
                evidence = normalized(fact.get("evidencia"))
                if not evidence or not any(evidence in text for text in evidence_locations):
                    finding(
                        "EVIDENCE_NOT_IN_CHUNKS",
                        "CRITICAL",
                        path,
                        "La evidencia literal no aparece en los fragmentos guardados.",
                        ordinal,
                    )
                if not fact.get("page") and not fact.get("section"):
                    finding(
                        "EVIDENCE_LOCATION_MISSING",
                        "WARNING",
                        path,
                        "La evidencia carece de página y sección identificable.",
                        ordinal,
                    )
                if kind in {"DURACION", "MALLA_O_PLAN", "COSTO_PUBLICADO"} and not fact.get(
                    "carrera"
                ):
                    finding(
                        "CAREER_CONTEXT_MISSING",
                        "WARNING",
                        path,
                        "No relacionar este dato automáticamente con una carrera concreta.",
                        ordinal,
                    )
                if kind == "COSTO_PUBLICADO":
                    if not re.search(r"\b20\d{2}\b", evidence):
                        finding(
                            "FEE_YEAR_MISSING",
                            "WARNING",
                            path,
                            "El costo carece de gestión literal; revisar vigencia.",
                            ordinal,
                        )
                    if not fact.get("sede"):
                        finding(
                            "FEE_CAMPUS_MISSING",
                            "WARNING",
                            path,
                            "El costo no identifica sede; revisar ámbito y conceptos.",
                            ordinal,
                        )
                if (
                    kind == "BECA_PUBLICADA"
                    and re.search(
                        r"direcci[oó]n|personal de|comit[eé]|formulario|carta dirigida|"
                        r"requisitos generales|requisitos espec[ií]ficos",
                        evidence,
                    )
                    and not re.search(r"\d+\s*%|beneficio|descuento|cobertura", evidence)
                ):
                    finding(
                        "SCHOLARSHIP_CONTEXT_ONLY",
                        "WARNING",
                        path,
                        "Mención administrativa o requisitos; no acredita beneficio, monto "
                        "ni una beca distinta.",
                        ordinal,
                    )
                if fact.get("estado") != "CANDIDATO_PENDIENTE_REVISION":
                    finding(
                        "CANDIDATE_REVIEW_STATUS_INVALID",
                        "CRITICAL",
                        path,
                        "La auditoría no reconoce promoción ni aprobación de estos candidatos.",
                        ordinal,
                    )
            documents.append(
                {
                    "manifest": str(path.relative_to(output)),
                    "source_url": final_url,
                    "institution_id": institution.get("institution_id"),
                    "chunks": len(chunks),
                    "candidates": len(facts),
                    "training_eligible": False,
                    "review_status": manifest.get("status"),
                }
            )
        except (OSError, ValueError, KeyError, TypeError) as error:
            finding("ARTIFACT_UNREADABLE", "CRITICAL", path, str(error)[:400])
    counts: dict[str, int] = {}
    for item in findings:
        counts[item["code"]] = counts.get(item["code"], 0) + 1
    inventory_hash = hashlib.sha256(
        json.dumps(inventory, sort_keys=True, separators=(",", ":")).encode()
    ).hexdigest()
    return {
        "schema_version": "1.0",
        "generated_at": datetime.now(UTC).isoformat(),
        "inventory_hash": "sha256:" + inventory_hash,
        "summary": {
            "documents": len(documents),
            "chunks": sum(item["chunks"] for item in documents),
            "candidates": sum(item["candidates"] for item in documents),
            "candidate_counts": candidate_counts,
            "critical": sum(item["severity"] == "CRITICAL" for item in findings),
            "warnings": sum(item["severity"] == "WARNING" for item in findings),
            "finding_counts": counts,
            "training_eligible_documents": 0,
        },
        "documents": documents,
        "findings": findings,
        "limitations": [
            "La integridad técnica no confirma vigencia, oferta ni exactitud curricular.",
            "Los patrones detectan indicios; requieren revisión humana contextual.",
        ],
        "before_training": [
            "Confirmar institución, documento, versión y vigencia actuales.",
            "Revisar manualmente carrera, sede, malla, materias y duración.",
            "Confirmar costos con moneda, concepto, periodo, gestión y sede.",
            "Excluir sitios en cuarentena, contenido ajeno y duplicados.",
            "Evaluar derechos de uso y resolver integridad y evidencia incompleta.",
            "Aprobar corpus, versionar división de evaluación y medir citas/abstención.",
            "Decidir si RAG basta antes de autorizar entrenamiento especializado.",
        ],
        "promotion": "NINGUNA; ningún candidato nacional está habilitado para entrenamiento.",
    }


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--directorio", type=Path, default=SERVICE_ROOT / "data/knowledge/nacional")
    parser.add_argument(
        "--catalogo", type=Path, default=SERVICE_ROOT / "data/catalog/universidades_bolivia.json"
    )
    parser.add_argument("--informe", type=Path)
    parser.add_argument("--estricto", action="store_true")
    args = parser.parse_args()
    catalog = json.loads(args.catalogo.read_text(encoding="utf-8"))
    report = audit_catalog(args.directorio, catalog)
    target = args.informe or args.directorio / "auditoria.json"
    target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(report["summary"], ensure_ascii=False, indent=2))
    print(f"Informe: {target.resolve()}")
    if args.estricto and report["summary"]["critical"]:
        raise SystemExit(1)


if __name__ == "__main__":
    main()
