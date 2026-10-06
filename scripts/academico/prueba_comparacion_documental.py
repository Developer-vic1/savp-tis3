"""Pruebas locales: imágenes incrustadas, ausencia de marcas, imagen vacía."""
import tempfile
from pathlib import Path
import pymupdf as fitz
from PIL import Image
from comparar_referencias_documentales import comparar, normalizar

with tempfile.TemporaryDirectory() as directorio:
    rutas = [Path(directorio) / 'firma.png', Path(directorio) / 'sello.png']
    for ruta, texto in zip(rutas, ['Firma de prueba', 'Sello de prueba institucional']):
        from PIL import ImageDraw
        imagen = Image.new('RGB', (220, 100), 'white')
        dibujo = ImageDraw.Draw(imagen)
        dibujo.text((20, 20), texto, fill='black')
        if 'Sello' in texto:
            dibujo.ellipse((10, 5, 200, 95), outline='black', width=3)
        imagen.save(ruta)
    pdf = Path(directorio) / 'prueba.pdf'
    documento = fitz.open()
    pagina = documento.new_page()
    pagina.insert_image(fitz.Rect(20, 20, 240, 120), filename=str(rutas[0]))
    pagina.insert_image(fitz.Rect(20, 150, 240, 250), filename=str(rutas[1]))
    documento.save(pdf)
    assert comparar(str(pdf), *map(str, rutas))['coinciden']
    documento = fitz.open()
    documento.new_page().insert_text((20, 20), 'Nombre del Director, firma y sello')
    vacio = Path(directorio) / 'vacio.pdf'
    documento.save(vacio)
    assert not comparar(str(vacio), *map(str, rutas))['coinciden']
    try:
        normalizar(Image.new('RGB', (200, 100), 'white'))
    except ValueError:
        pass
    else:
        raise AssertionError('Se aceptó una imagen vacía')
    print('3 comprobaciones documentales correctas.')
