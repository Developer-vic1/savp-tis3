# Gobernanza de parámetros

`ai-service/data/parameters/registry.json` contiene 29 parámetros: 11 `DOCUMENTED`, 1 `DERIVED`,
1 `EMPIRICALLY_EVALUATED`, 14 `EXPERIMENTAL` y 2 `PROHIBITED`.

Las clases significan:

- `DOCUMENTED`: valor transcrito de una fuente normativa o técnica.
- `DERIVED`: transformación matemática reproducible de valores documentados.
- `EMPIRICALLY_EVALUATED`: alternativa comparada en un benchmark versionado.
- `EXPERIMENTAL`: decisión de ingeniería aún no calibrada ni validada externamente.
- `PROHIBITED`: magnitud que no debe producirse, como probabilidad de éxito o inteligencia
  inferida desde RIASEC.

Valores operativos principales:

| Parámetro | Valor | Clase |
|---|---:|---|
| Mini-IP por ítem | 0–4; UI española equivalente 1–5 | `DOCUMENTED` |
| Total escolar / promoción | 100 / 51 | `DOCUMENTED` (RM 0190/2024) |
| Chunk / overlap | 1.200 / 180 caracteres | `EXPERIMENTAL` |
| RRF `k` | 60 | `EXPERIMENTAL` |
| BM25 `k1`, `b` | 1.5, 0.75 | `EXPERIMENTAL` |
| Top-k knowledge / candidatos tutor / contexto tutor | 5 / 8 / 5 | `EXPERIMENTAL` |
| Umbral de evidencia / overlap léxico | 0.72 / 0.20 | `EXPERIMENTAL` |
| Extracto máximo | 650 caracteres | `EXPERIMENTAL` |
| Modelo de embeddings seleccionado | `intfloat/multilingual-e5-small` | `EMPIRICALLY_EVALUATED` |
| LLM temperatura / max tokens / seed / timeout | 0 / 384 / 20260928 / 45 s | `EXPERIMENTAL` |

RRF y BM25 tienen fundamento bibliográfico, pero esos valores concretos no fueron ablacionados.
Las pruebas comparan el registro con las constantes efectivas para impedir deriva silenciosa.
