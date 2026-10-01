# Investigación RIASEC

Fecha de verificación: 2026-09-28.

Reverificación fase 2: 2026-09-30. La aplicación oficial en español publicó el asset
`https://onetinterestprofiler.org/es/assets/index-CD-U6n4j.js` (SHA-256 de los bytes descargados:
`c2492eb96d2422dec7912861d6c5091b374b49ac3305100917cdbac3208d841b`). Se extrajeron
los 30 triples `index`, `area`, `text` del propio asset oficial y se compararon uno por uno con
`onet_mini_ip_v2_es.json`: 30/30 coincidieron, sin discrepancias de orden, dimensión ni texto.
El fingerprint SHA-256 del arreglo canónico del instrumento es
`309e17a1101401a8559a81dd6f11f08924a1de407d984d0a9722313c12f1cdb4` y está fijado
en una prueba de contrato. La página oficial muestra las opciones 1–5; internamente se convierte
`valor - 1` para sumar 0–20 por dimensión. La licencia oficial vigente permite redistribución
literal bajo CC BY-ND 4.0 con atribución, enlace a licencia y sin versiones modificadas; una
adaptación exige la licencia de desarrollador y validación del producto.

## Fuentes primarias consultadas

1. National Center for O*NET Development, [Interest Profiler](https://www.onetcenter.org/IP.html).
2. National Center for O*NET Development, [O*NET Interest Profiler Manual, versión 1.0 (marzo de 2021)](https://www.onetcenter.org/dl_files/IP_Manual.pdf).
3. National Center for O*NET Development, [Development of an O*NET Mini Interest Profiler](https://www.onetcenter.org/dl_files/Mini-IP.pdf).
4. O*NET Web Services, [anuncio Mini‑IP Version 2.0, 7 de octubre de 2025](https://services.onetcenter.org/whatsnew).
5. O*NET Resource Center, [Career Exploration Tools Content License](https://www.onetcenter.org/license_tools.html).
6. Aplicación oficial en español, [Perfil de intereses O*NET](https://onetinterestprofiler.org/es/).

## Comparación

| Instrumento | Reactivos | Español oficial | Scoring | Licencia/uso | Decisión |
|---|---:|---|---|---|---|
| O*NET® Mini‑IP 2.0 | 30, 5 por dimensión | Sí, web oficial | 0–4; suma 0–20 por dimensión | reproducción literal CC BY‑ND 4.0; adaptación exige licencia de desarrollador y validación | seleccionado |
| O*NET® IP Short Form | 60, 10 por dimensión | Sí mediante web/API | 0–4; suma 0–40 | mismas opciones de licencia | no seleccionado por mayor carga |
| 18REST | 18 | original en portugués | descrito en publicación | no ofrece una traducción española oficial equivalente en las fuentes revisadas | descartado para este hito |

## Evidencia documentada

- El Mini‑IP fue construido con teoría de respuesta al ítem, fidelidad estructural RIASEC, cobertura y balance de género.
- El manual reporta alfa por dimensión de `.74` a `.81` en la muestra de validación (`N=575`) y correlaciones convergentes `.95–.96` con el Short Form.
- El manual describe una población de desarrollo/validación principalmente estadounidense; no constituye evidencia boliviana.
- La web oficial vigente usa 30 preguntas y ofrece español desde la versión 2.0 anunciada en 2025.

## Límites

**HECHO DOCUMENTADO.** No se encontró mención de Bolivia en el manual ni en el informe de desarrollo consultados.

**PENDIENTE DE VALIDACIÓN.** Comprensión lingüística, equivalencia cultural, confiabilidad y validez para estudiantes de la U.E. Franz Tamayo N.º 3.

**DECISIÓN DE DISEÑO.** El resultado se expresará como patrón de intereses exploratorios, nunca como personalidad, aptitud, diagnóstico o probabilidad de éxito.

## Licencia y atribución

Los reactivos se reproducen sin cambios bajo CC BY‑ND 4.0. Debe mantenerse esta atribución: “Incluye información de O*NET® Career Exploration Tools del U.S. Department of Labor, Employment and Training Administration (USDOL/ETA). Usada bajo CC BY‑ND 4.0.” O*NET® es marca de USDOL/ETA. USDOL/ETA no ha aprobado ni respaldado SAVP.
