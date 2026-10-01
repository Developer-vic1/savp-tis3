# Checkpoint WIP de fase 2.1 — registro histórico

El checkpoint transferido y sincronizado para cerrar la fase fue
`388890c2e004116e16374582da810406f4125508` en
`work/peter3-mejoras-fase2`. Conservó los cambios de Fase 2.1 antes
de reconstruir FAISS. `b02a3926a5a5487a1c0dec70069e335c37eb2615`
es únicamente el commit histórico anterior; no es punto de partida
operativo para repetir esta fase.

En ese checkpoint el corpus ya tenía 773 chunks y SHA-256
`ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`,
pero los índices E5 y MiniLM conservaban vectores del corpus anterior.
Los dos fallos de pytest se clasificaron `EXPECTED_STALE_INDEX`.
El bloqueo de Windows Code Integrity para Torch 2.14.0 y scikit-learn
1.9.1 impedía inicialmente generar embeddings reales.

El 2026-10-01 se encontró y comprobó en VicDev una combinación de
wheels oficiales CPU: Torch 2.13.0+cpu, torchvision 0.28.0+cpu y
scikit-learn 1.8.0. Los dos modelos cargaron y codificaron textos
reales antes de actualizar el lock. Después se regeneraron ambos
índices, se ejecutó DEV, se congeló E5 y se ejecutó TEST. Los resultados
vigentes están en `FINAL_STATUS.md`, `CORPUS_STATE.md` y
`RETRIEVAL_EVALUATION.md`.

Este archivo explica la procedencia del checkpoint. No es una
instrucción para volver a `b02a392` ni para reutilizar los índices
anteriores. Cualquier nueva sincronización debe partir del HEAD actual
de la rama y verificar el SHA del corpus y los manifests antes de usar
retrieval.
