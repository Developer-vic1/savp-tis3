import json

from app.prompts.guards import (
    detect_prompt_injection,
    quarantine_injected_evidence,
    sanitize_student_context,
    wrap_untrusted_evidence,
)
from app.prompts.registry import (
    PROMPT_REGISTRY,
    SCHEMA_INSTRUCTION_V1,
    SYSTEM_POLICY_V1,
    TASK_INSTRUCTION_V1,
)
from app.prompts.schemas import PromptMetadata
from app.tutor.providers import TutorMaterial


def render_tutor_prompt(
    material: TutorMaterial,
    *,
    system_policy: str = SYSTEM_POLICY_V1,
    task_instruction: str = TASK_INSTRUCTION_V1,
    include_schema: bool = True,
) -> tuple[list[dict[str, str]], PromptMetadata, list[str]]:
    warnings: list[str] = []

    # 1. Detect prompt injection in user question
    is_injection, injection_reason = detect_prompt_injection(material.question)
    if is_injection and injection_reason:
        warnings.append(f"Alerta de seguridad: {injection_reason}")

    # 2. Build system message
    system_text_parts = [system_policy, task_instruction]
    if include_schema:
        system_text_parts.append(SCHEMA_INSTRUCTION_V1)
    system_content = "\n\n".join(system_text_parts)

    # 3. Sanitize student context
    safe_academic = sanitize_student_context(material.allowed_academic_context)
    safe_student = sanitize_student_context(material.allowed_student_context)

    # 4. Format evidence with untrusted wrappers
    evidence_blocks: list[str] = []
    for item in material.evidence:
        locator = f"página {item.page}" if item.page else item.section or "documento general"
        doc_header = f"DOCUMENTO [{item.source_id}] — {item.institution} ({locator}):"
        safe_summary, quarantined = quarantine_injected_evidence(item.summary)
        if quarantined:
            warnings.append(
                f"Evidencia {item.chunk_id} omitida: contiene instrucciones no confiables."
            )
        doc_body = wrap_untrusted_evidence(safe_summary)
        evidence_blocks.append(f"{doc_header}\n{doc_body}")

    evidence_text = (
        "\n\n".join(evidence_blocks)
        if evidence_blocks
        else "No hay fragmentos documentales disponibles para esta consulta."
    )

    # 5. Build user content
    user_payload = {
        "pregunta_estudiante": material.question,
        "materia": material.subject,
        "nivel": material.level,
        "contexto_academico_sanitizado": safe_academic,
        "contexto_estudiante_sanitizado": safe_student,
        "evidencia_insuficiente": material.insufficient_evidence,
        "evidencia_recuperada": evidence_text,
    }

    user_content = (
        "CONSULTA DEL ESTUDIANTE Y EVIDENCIA RECUPERADA:\n"
        + json.dumps(user_payload, ensure_ascii=False, indent=2)
    )

    messages = [
        {"role": "system", "content": system_content},
        {"role": "user", "content": user_content},
    ]

    metadata = PROMPT_REGISTRY["system_policy_v1"]
    return messages, metadata, warnings
