"""Check local source snapshots and the corpus provenance chain."""

import argparse
import hashlib
import json
from pathlib import Path
from typing import Any, cast

ROOT = Path(__file__).resolve().parents[1]
WORKSPACE = ROOT.parent.resolve()


def read_json(path: Path) -> dict[str, Any]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        raise ValueError(f"MISMATCH {path}: JSON object expected")
    return cast(dict[str, Any], data)


def local_file(relative: str) -> Path:
    path = (ROOT / relative).resolve()
    if not path.is_relative_to(WORKSPACE):
        raise ValueError(f"MISMATCH path outside workspace: {relative}")
    return path


def digest(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def verify(*, upstream_only: bool = False) -> list[str]:
    findings: list[str] = []
    sources = read_json(ROOT / "data/sources/sources.json")["sources"]
    source_manifest = read_json(ROOT / "data/sources/sources.json")
    references = read_json(ROOT / "data/sources/references.json")["references"]
    external_entries = read_json(ROOT / "data/sources/external_information.json")["entries"]
    source_ids: set[str] = set()
    reference_ids: set[str] = set()
    external_ids: set[str] = set()
    for source in sources:
        source_id = source["source_id"]
        if source_id in source_ids:
            findings.append(f"MISMATCH duplicate source ID: {source_id}")
        source_ids.add(source_id)
        if source["source_type"] == "OFFICIAL_CAREER_HTML" and not all(
            (source.get("snapshot_timestamp"), source.get("final_url"), source.get("content_type"))
        ):
            findings.append(f"MISSING {source_id}: HTML snapshot provenance")
        path = local_file(source["local_path"])
        if not path.is_file():
            findings.append(f"MISSING {source_id}: {source['local_path']}")
            continue
        actual = digest(path)
        expected = source["document_hash"].removeprefix("sha256:")
        if actual != expected or source.get("sha256") != actual:
            findings.append(f"MISMATCH {source_id}: expected {expected}, actual {actual}")
        elif source.get("verification_status") != "VALID":
            findings.append(f"STALE {source_id}: verification_status")
        else:
            findings.append(f"VALID {source_id}: sha256:{actual}")

    for reference in references:
        reference_id = reference["reference_id"]
        if reference_id in reference_ids or reference_id in source_ids:
            findings.append(f"MISMATCH duplicate reference ID: {reference_id}")
        reference_ids.add(reference_id)
        if reference["reference_kind"] == "INTERNAL_ARTIFACT":
            relative = reference.get("local_path")
            if not relative or not local_file(relative).is_file():
                findings.append(f"MISSING {reference_id}: {relative}")
            else:
                findings.append(f"VALID {reference_id}: local artifact")
        elif not reference.get("url"):
            findings.append(f"MISSING {reference_id}: URL")
        else:
            findings.append(f"VALID {reference_id}: external reference metadata")

    for entry in external_entries:
        external_id = entry["external_id"]
        if external_id in external_ids or external_id in source_ids or external_id in reference_ids:
            findings.append(f"MISMATCH duplicate external ID: {external_id}")
        external_ids.add(external_id)
        if entry.get("recommendation_eligible") is not False:
            findings.append(f"MISMATCH {external_id}: recommendation_eligible")
        elif entry.get("evidence_layer") == "FUENTE_OFICIAL_EXTERNA" and not entry.get("official"):
            findings.append(f"MISMATCH {external_id}: official source flag")
        elif not str(entry.get("url", "")).startswith("https://"):
            findings.append(f"MISSING {external_id}: official HTTPS URL")
        elif entry.get("snapshot_status") == "VALID_LOCAL_SNAPSHOT":
            findings.append(f"MISMATCH {external_id}: validated snapshots belong in sources.json")
        else:
            findings.append(
                f"VALID {external_id}: informational layer, excluded from recommendation"
            )

    corpus_file = ROOT / "data/processed/corpus.jsonl"
    corpus_manifest = read_json(ROOT / "data/processed/corpus_manifest.json")
    if not corpus_file.is_file():
        findings.append("MISSING corpus.jsonl")
        return findings
    corpus_rows = [
        json.loads(line) for line in corpus_file.read_text(encoding="utf-8").splitlines() if line
    ]
    corpus_ids = [row["chunk_id"] for row in corpus_rows]
    source_by_id = {source["source_id"]: source for source in sources}
    orphan_sources = sorted({row["source_id"] for row in corpus_rows} - source_ids)
    leaked_external_sources = sorted({row["source_id"] for row in corpus_rows} & external_ids)
    if orphan_sources or len(corpus_ids) != len(set(corpus_ids)):
        findings.append(f"MISMATCH corpus IDs: orphan_sources={orphan_sources}")
    if leaked_external_sources:
        findings.append(
            f"MISMATCH corpus contains external informational sources: {leaked_external_sources}"
        )
    if any(not str(row.get("text", "")).strip() for row in corpus_rows):
        findings.append("MISMATCH corpus: empty chunk text")
    if corpus_manifest["chunk_count"] != len(corpus_rows):
        findings.append("STALE corpus manifest: chunk count")
    if corpus_manifest["source_manifest_version"] != source_manifest["manifest_version"]:
        findings.append("STALE corpus manifest: source manifest version")
    for row in corpus_rows:
        source = source_by_id.get(row["source_id"])
        if source and (
            row["document_hash"] != source["document_hash"] or row["version"] != source["version"]
        ):
            findings.append(f"STALE corpus chunk {row['chunk_id']}: source version/hash")
            break
    corpus_sha = digest(corpus_file)
    upstream_stale = any(
        line.startswith(("MISMATCH", "MISSING", "STALE corpus")) for line in findings
    )
    if not upstream_stale:
        findings.append(f"VALID corpus: {len(corpus_rows)} chunks, sha256:{corpus_sha}")
    if upstream_only:
        return findings
    return findings


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--upstream-only", action="store_true")
    args = parser.parse_args()
    try:
        findings = verify(upstream_only=args.upstream_only)
    except (OSError, ValueError, KeyError, TypeError, json.JSONDecodeError) as exc:
        print(f"MISMATCH registry verification: {exc}")
        print("FAIL")
        return 1
    for finding in findings:
        print(finding)
    passed = not any(line.startswith(("MISMATCH", "MISSING", "STALE")) for line in findings)
    print("PASS" if passed else "FAIL")
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
