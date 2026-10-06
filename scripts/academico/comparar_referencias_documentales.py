"""Comparación visual de referencias; no certifica identidad ni firma electrónica."""
import io
import json
import sys
from PIL import Image, ImageChops, ImageOps

Image.MAX_IMAGE_PIXELS = 16000000


def normalizar(imagen):
    imagen = imagen.convert('RGBA')
    fondo = Image.new('RGBA', imagen.size, 'white')
    fondo.alpha_composite(imagen)
    tinta = fondo.convert('L').point(lambda valor: 255 if valor < 180 else 0)
    caja = tinta.getbbox()
    if not caja or sum(tinta.histogram()[200:]) < 50:
        raise ValueError('La imagen está vacía o no tiene trazos legibles.')
    tinta = tinta.crop(caja)
    return ImageOps.pad(tinta, (96, 96), color=0), tinta.width / tinta.height


def comparar(pdf, firma, sello):
    import pymupdf as fitz
    referencias = [normalizar(Image.open(ruta)) for ruta in [firma, sello]]
    mejores = [0.0, 0.0]
    documento = fitz.open(pdf)
    if documento.is_encrypted or len(documento) > 60:
        raise ValueError('Usa un PDF sin contraseña, de hasta 60 páginas.')
    examinadas = set()
    for pagina in documento:
        for datos in pagina.get_images(full=True):
            xref = datos[0]
            if xref in examinadas:
                continue
            examinadas.add(xref)
            if len(examinadas) > 100:
                raise ValueError('El documento contiene demasiadas imágenes para revisar.')
            try:
                imagen = documento.extract_image(xref)
                candidata, proporcion = normalizar(Image.open(io.BytesIO(imagen['image'])))
            except (ValueError, OSError):
                continue
            for indice, (referencia, aspecto) in enumerate(referencias):
                if abs(proporcion / aspecto - 1) > 0.12:
                    continue
                diferencia = sum(i * n for i, n in enumerate(ImageChops.difference(candidata, referencia).histogram()))
                puntuacion = max(0.0, 1 - diferencia / max(sum(referencia.getdata()), 1))
                mejores[indice] = max(mejores[indice], puntuacion)
    coinciden = all(valor >= 0.94 for valor in mejores)
    return {'coinciden': coinciden, 'firma': round(mejores[0], 4), 'sello': round(mejores[1], 4),
            'metodo': 'Imágenes incrustadas comparadas con referencias vigentes; requiere comprobación humana',
            'error': '' if coinciden else 'No se identificaron ambas referencias. Usa un PDF con firma y sello legibles como imágenes separadas; una hoja escaneada completa requiere revisión adicional.'}


if __name__ == '__main__':
    try:
        if sys.argv[1] == 'validar':
            normalizar(Image.open(sys.argv[2]))
            resultado = {'valida': True}
        else:
            resultado = comparar(*sys.argv[2:5])
        print(json.dumps(resultado, ensure_ascii=False))
    except Exception as error:
        print(json.dumps({'valida': False, 'coinciden': False, 'error': str(error)}, ensure_ascii=False))
