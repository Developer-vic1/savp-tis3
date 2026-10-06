"""Lectura local limitada. No ejecuta contenido ni certifica autenticidad documental."""
import json
import sys
from pathlib import Path
from pypdf import PdfReader

try:
    ruta = Path(sys.argv[1])
    if ruta.stat().st_size > 8 * 1024 * 1024:
        raise ValueError("El documento supera 8 MB.")
    lector = PdfReader(str(ruta))
    if lector.is_encrypted:
        raise ValueError("Necesitamos un PDF sin contraseña para leer su contenido.")
    if len(lector.pages) > 60:
        raise ValueError("Adjunta la autorización concreta de hasta 60 páginas.")
    texto = "\n".join((pagina.extract_text() or "")[:25000] for pagina in lector.pages)
    print(json.dumps({"texto": texto[:180000], "paginas": len(lector.pages)}, ensure_ascii=True))
except Exception:
    print(json.dumps({"error": "No pudimos leer este PDF. Usa un documento legible, sin contraseña y de hasta 60 páginas."}))
    sys.exit(1)
