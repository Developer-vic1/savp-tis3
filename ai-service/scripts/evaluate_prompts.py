import json
from pathlib import Path
from typing import cast

from app.prompts.evaluator import run_prompt_evaluation
from app.prompts.schemas import PromptEvaluationScenario

SERVICE_ROOT = Path(__file__).resolve().parents[1]
SCENARIOS_PATH = SERVICE_ROOT / "data" / "evaluation" / "prompt_scenarios.json"
RESULTS_PATH = SERVICE_ROOT / "data" / "evaluation" / "prompt_results.json"


def main() -> None:
    document = cast(
        dict[str, object], json.loads(SCENARIOS_PATH.read_text(encoding="utf-8"))
    )
    raw_scenarios = cast(list[object], document["scenarios"])
    scenarios = [PromptEvaluationScenario.model_validate(item) for item in raw_scenarios]
    report = run_prompt_evaluation(scenarios)
    output = {
        "evaluation_id": "peter3-prompt-evaluation-2026-09-29",
        "scenario_dataset": str(SCENARIOS_PATH.relative_to(SERVICE_ROOT)).replace("\\", "/"),
        "provider": "structured-answer-v1.0.0",
        "result": report.model_dump(mode="json"),
    }
    RESULTS_PATH.write_text(
        json.dumps(output, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
