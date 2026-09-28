# Pipeline de ingesta y OCR

## Problema

El corpus mezcla PDF con texto, PDF compuesto solo por imágenes, HTML y TXT/MD. Tratar una
página escaneada como texto vacío perdería evidencia; aplicar OCR a todo degradaría texto
digital válido y aumentaría costo.

## Evidencia y decisión

PyMuPDF extrae y renderiza PDF dentro del entorno aislado. Una página se considera digital
cuando contiene al menos 80 caracteres alfanuméricos extraídos. En caso contrario, si posee
contenido visual, se renderiza a 200 DPI y se envía a EasyOCR con español e inglés. El umbral
es una **DECISIÓN DE DISEÑO** versionada, no una medida universal de calidad.

Poppler y Tesseract no están instalados en el host. Por eso el pipeline no depende de ellos:
usa PyMuPDF y EasyOCR, ambos fijados por `uv.lock`. Si el modelo OCR no puede cargarse, el
proceso falla con error explícito; no etiqueta una página como procesada.

## Componentes

- `app/ingestion/extractors.py`: detección y extracción PDF/HTML/TXT/MD.
- `app/ingestion/ocr.py`: OCR real en español y confianza agregada.
- `app/ingestion/chunking.py`: fragmentos deterministas con solapamiento.
- `scripts/build_corpus.py`: ingesta reproducible del manifiesto oficial.
- `scripts/render_pdf_pages.py`: renderizado para control visual.

## Contrato

Cada segmento guarda texto, página o sección, método (`DIGITAL`, `OCR`, `HTML`, `TEXT`) y
confianza cuando existe. Cada chunk hereda todos los metadatos de fuente exigidos, además de
`chunk_id`, método y ordinal. Texto vacío no genera chunks.

## Pruebas

- PDF digital generado y extraído sin OCR;
- PDF compuesto por imagen detectado y enviado a OCR;
- inferencia OCR real en español sobre un escaneo sintético;
- HTML sin scripts/estilos y TXT/MD con secciones;
- chunking estable, solapamiento y metadatos completos;
- renderizado e inspección visual de páginas ministerial, universitaria y escaneada.

## Riesgos

OCR puede confundir acentos, columnas y tipografía pequeña. El texto OCR conserva método y
confianza para poder excluirlo o revisarlo. La calidad visual no sustituye validación humana
del contenido oficial.

## Evidencia ejecutada 2026-09-28

- Corpus: 11 fuentes, 818 chunks y 818 IDs únicos.
- PDF: 389 páginas digitales y 22 páginas procesadas por OCR; 11 páginas visuales sin texto
  recuperable quedaron como advertencia, no como éxito.
- Resultado: 35 chunks OCR, todos con confianza; mínimo 0.4638 y media 0.7656.
- Escaneo sintético: texto español recuperado con confianza 0.8691; prueba real aprobada en
  89.28 s en la primera carga del modelo.
- Reconstrucción completa: 315.3 s sobre CPU.
- Control visual: se inspeccionaron el anexo BTH ministerial, la malla 2026 de Ingeniería de
  Sistemas UCB y el PDF imagen-only sintético; los tres renders fueron legibles y completos.
- Suite ordinaria: 40 pruebas aprobadas, una prueba OCR real omitida por defecto y ejecutada
  explícitamente con `RUN_REAL_OCR=1`.
