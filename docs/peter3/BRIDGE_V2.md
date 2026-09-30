# Bridge secundaria/BTH–universidad V2

`ai-service/data/bridge/secondary_university_v2.json` contiene 17 relaciones granulares:

- 15 `DOCUMENT_SUPPORTED_INFERENCE`;
- 2 `HYPOTHESIS`;
- 0 `DIRECTLY_DOCUMENTED`;
- 0 equivalencias y 0 pesos o puntajes.

Los documentos de secundaria y universidad prueban los contenidos de cada extremo, pero no
afirman explícitamente la correspondencia entre ambos. Por eso incluso las relaciones BTH de
programación, construcción, contabilidad y administración son inferencias apoyadas por
documentos, no relaciones directamente documentadas.

Cada relación declara `relation_id`, contenido y competencia secundaria, conocimiento y materia
universitaria, carreras alcanzadas, fuentes de ambos extremos, estado, justificación y
limitaciones. `HYPOTHESIS` queda reservado para asociaciones plausibles que requieren validación
experta o empírica.

Reglas de interpretación:

- afinidad no equivale a preparación;
- similitud temática no equivale a equivalencia curricular;
- una relación no predice aprobación ni éxito universitario;
- la ausencia de evidencia se conserva como desconocido, no como cero;
- ninguna relación genera un ranking global o una recomendación determinista.

La integridad referencial y la taxonomía se prueban en `test_bridge_v2.py` y
`test_referential_integrity.py`.
