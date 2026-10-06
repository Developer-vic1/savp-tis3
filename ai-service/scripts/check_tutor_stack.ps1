param(
    [string] $BaseUrl = "http://127.0.0.1:8001",
    [string] $ApiKey = ""
)

$ErrorActionPreference = "Stop"
$headers = @{}
if ($ApiKey) {
    $headers["X-SAVP-AI-Key"] = $ApiKey
}

$health = Invoke-RestMethod -Uri "$BaseUrl/health" -Headers $headers
$greetingPayload = @{
    schema_version = "1.0"
    question = "Hola, ¿cómo puedes ayudarme?"
} | ConvertTo-Json
$greeting = Invoke-RestMethod -Method Post -Uri "$BaseUrl/api/v1/tutor/query" -Headers $headers -ContentType "application/json; charset=utf-8" -Body $greetingPayload
$knowledgePayload = @{
    schema_version = "1.0"
    question = "¿Qué materias tiene el primer semestre de Sistemas UCB?"
} | ConvertTo-Json
$knowledge = Invoke-RestMethod -Method Post -Uri "$BaseUrl/api/v1/tutor/query" -Headers $headers -ContentType "application/json; charset=utf-8" -Body $knowledgePayload

[pscustomobject]@{
    health = $health.status
    greeting_mode = $greeting.answer_mode
    greeting = $greeting.answer
    knowledge_mode = $knowledge.answer_mode
    knowledge_sources = @($knowledge.sources).Count
    knowledge_answer = $knowledge.answer
}
