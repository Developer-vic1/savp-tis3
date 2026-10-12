# Preparación documental de UNIFRANZ - 9 de octubre de 2026

## 1. Qué se hizo

Se preparó un índice local BM25 a partir de evidencia verificada de UNIFRANZ. Este paso calcula estadísticas de términos para recuperar documentos; no ajusta pesos de un modelo generativo ni entrena el cuestionario RIASEC. El lector de imágenes usa el modelo español ya entrenado de Tesseract mediante PyMuPDF. No se enviaron documentos a un proveedor externo de IA.

## 2. Datos y tiempos medidos

- Carreras con ficha: 17.
- Documentos utilizados: 28 (11 mallas PDF y 17 fichas HTML).
- Fragmentos: 635; reconocidos desde imágenes: 22.
- Verificar y preparar datos: 1.1416 s.
- Construir el índice: 0.0624 s.
- Evaluar consultas: 0.0216 s.
- Total incluyendo escritura del índice: 1.2461 s.

Estos tiempos corresponden al índice documental local. No incluyen la recolección por red ni la lectura previa de imágenes.

## 3. Evaluación y límites

Las 6 consultas de control encontraron la carrera esperada entre los tres primeros fragmentos; MRR@3 = 1.00. Son controles pequeños de recuperación, varios nombran la carrera; no demuestran exactitud general, compatibilidad vocacional ni validez curricular.

| Consulta | Carrera esperada | Posición |
|---|---|---|
| Materias de Psicología y neuropsicología clínica | Psicología | 1 |
| Malla de Medicina y anatomía | Medicina | 1 |
| Materias de Enfermería y cuidados de salud | Enfermería | 1 |
| Ingeniería de Sistemas e Innovación Digital: programación y bases de datos | Ingeniería de Sistemas e Innovación Digital | 1 |
| Preparación de alimentos y cocina profesional | Gastronomía y Artes Culinarias | 1 |
| Derecho constitucional y legislación | Derecho | 1 |

Los nombres de materias, semestres y requisitos requieren revisión humana contra el PDF. Los bloques de microcredenciales se excluyen de la extracción de materias semestrales. No se ha publicado esta preparación al tutor ni al catálogo estudiantil.

## 4. Fuentes utilizadas

| Carrera | Tipo | Fuente oficial |
|---|---|---|
| Derecho | Ficha HTML | [Derecho](https://unifranz.edu.bo/carreras/derecho/) |
| Derecho | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/derecho.pdf) |
| Psicología | Ficha HTML | [Psicología](https://unifranz.edu.bo/carreras/psicologia/) |
| Psicología | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/psicologia.pdf) |
| Comunicación y Periodismo Digital | Ficha HTML | [Comunicación y Periodismo Digital](https://unifranz.edu.bo/carreras/comunicacion-y-periodismo-digital/) |
| Negocios y Gestión Empresarial | Ficha HTML | [Negocios y Gestión Empresarial](https://unifranz.edu.bo/carreras/negocios-y-gestion-empresarial/) |
| Negocios y Gestión Empresarial | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/negocios-y-gestion-empresarial.pdf) |
| Turismo y Hotelería | Ficha HTML | [Turismo y Hotelería](https://unifranz.edu.bo/carreras/turismo-y-hoteleria/) |
| Turismo y Hotelería | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/turismo-y-hoteleria.pdf) |
| Gastronomía y Artes Culinarias | Ficha HTML | [Gastronomía y Artes Culinarias](https://unifranz.edu.bo/carreras/gastronomia-y-artes-culinarias/) |
| Gastronomía y Artes Culinarias | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/gastronomia-y-artes-culinarias.pdf) |
| Auditoría Financiera y Control de Gestión | Ficha HTML | [Auditoría Financiera y Control de Gestión](https://unifranz.edu.bo/carreras/auditoria-financiera-y-control-de-gestion/) |
| Ingeniería Comercial | Ficha HTML | [Ingeniería Comercial](https://unifranz.edu.bo/carreras/ingenieria-comercial/) |
| Ingeniería Económica y Financiera | Ficha HTML | [Ingeniería Económica y Financiera](https://unifranz.edu.bo/carreras/ingenieria-economica-y-financiera/) |
| Arquitectura | Ficha HTML | [Arquitectura](https://unifranz.edu.bo/carreras/arquitectura/) |
| Diseño Digital y Producción Transmedia | Ficha HTML | [Diseño Digital y Producción Transmedia](https://unifranz.edu.bo/carreras/diseno-digital-y-produccion-transmedia/) |
| Diseño Digital y Producción Transmedia | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/diseno-digital-y-produccion-transmedia.pdf) |
| Publicidad y Marketing | Ficha HTML | [Publicidad y Marketing](https://unifranz.edu.bo/carreras/publicidad-y-marketing/) |
| Publicidad y Marketing | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/publicidad-y-marketing.pdf) |
| Bioquímica y Farmacia | Ficha HTML | [Bioquímica y Farmacia](https://unifranz.edu.bo/carreras/bioquimica-y-farmacia/) |
| Bioquímica y Farmacia | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/bioquimica-y-farmacia.pdf) |
| Enfermería | Ficha HTML | [Enfermería](https://unifranz.edu.bo/carreras/enfermeria/) |
| Enfermería | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/enfermeria.pdf) |
| Medicina | Ficha HTML | [Medicina](https://unifranz.edu.bo/carreras/medicina/) |
| Odontología | Ficha HTML | [Odontología](https://unifranz.edu.bo/carreras/odontologia/) |
| Odontología | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/odontologia.pdf) |
| Ingeniería de Sistemas e Innovación Digital | Ficha HTML | [Ingeniería de Sistemas e Innovación Digital](https://unifranz.edu.bo/carreras/ingenieria-de-sistemas-e-innovacion-digital/) |
| Ingeniería de Sistemas e Innovación Digital | Malla PDF | [Descargar la Malla Curricular](https://unifranz.edu.bo/wp-content/uploads/2026/07/ingenieria-de-sistemas-e-innovacion-digital.pdf) |

## 5. Descargas pendientes

Se identificaron 17 enlaces de mallas, pero seis no respondieron dentro del tiempo limitado. No se presentan como descargados:

- Comunicación y Periodismo Digital.
- Auditoría Financiera y Control de Gestión.
- Ingeniería Comercial.
- Ingeniería Económica y Financiera.
- Arquitectura.
- Medicina.

## 6. Reproducción y artefactos

Desde `ai-service`:

```powershell
.\.venv\Scripts\python.exe scripts/extraer_unifranz.py
.\.venv\Scripts\python.exe scripts/revisar_materias_unifranz.py
.\.venv\Scripts\python.exe scripts/preparar_conocimiento_unifranz.py
```

La colección debe terminar antes de los dos últimos pasos. El índice y el informe completo de fuentes, ubicaciones y resultados están en `data/knowledge/colecciones/unifranz/preparacion/`. Las instantáneas y fragmentos se conservan fuera de Git.

Modelo local de español: [tesseract-ocr/tessdata_fast](https://github.com/tesseract-ocr/tessdata_fast/blob/main/spa.traineddata). Implementación de lectura: [PyMuPDF](https://pymupdf.readthedocs.io/en/latest/page.html#Page.get_textpage_ocr).

## 7. Verificación de interfaz

Se comprobó sesión Administrador, búsqueda, lectura por páginas, materias propuestas y descarga real de Psicología a Descargas. El adjunto recibió nombre humano. Se revisó pantalla estrecha con el menú móvil cerrado, sin desbordamiento horizontal, y ambos temas. La revisión de materias conserva el encabezado y su desplazamiento propio. Los resultados visuales se conservan en `output/playwright/`.

Pasaron 105 pruebas Python, 8 Laravel (42 aserciones), 43 de frontend y la compilación Vite/Blade. Incluyen reconocimiento real de una imagen en español y una prueba que evita atribuir microcredenciales a un semestre.
