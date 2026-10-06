"""Consulta determinista de documentos oficiales. No usa modelos ni datos personales."""
import argparse
import hashlib
import io
import json
import re
import ssl
import unicodedata
from datetime import date
from html.parser import HTMLParser
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode, urljoin, urlsplit
from urllib.request import HTTPRedirectHandler, Request, build_opener

DOMINIOS = {"minedu.gob.bo", "www.minedu.gob.bo"}
MESES = {m: i + 1 for i, m in enumerate(("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"))}
FUENTES = {2026: "https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/1_RM_0001_EDUCACIN_REGULAR.pdf"}


def normalizar(texto):
    return " ".join("".join(c for c in unicodedata.normalize("NFKD", texto) if not unicodedata.combining(c)).lower().split())


def oficial(url):
    p = urlsplit(url)
    return p.scheme == "https" and p.hostname in DOMINIOS and p.port in (None, 443) and not p.username and not p.password


class RedireccionOficial(HTTPRedirectHandler):
    max_redirections = 3

    def redirect_request(self, req, fp, code, msg, headers, newurl):
        if not oficial(newurl):
            raise ValueError("Redirección fuera de la fuente oficial")
        return super().redirect_request(req, fp, code, msg, headers, newurl)


def descargar(url):
    if not oficial(url):
        raise ValueError("Fuente fuera del dominio permitido")
    req = Request(url, headers={"User-Agent": "SAVP-ConsultaCalendario/1.0", "Accept": "text/html,application/pdf"})
    with build_opener(RedireccionOficial()).open(req, timeout=7) as respuesta:
        datos = respuesta.read(10 * 1024 * 1024 + 1)
        if len(datos) > 10 * 1024 * 1024:
            raise ValueError("Documento demasiado grande para la revisión automática")
        return datos, respuesta.geturl()


class Enlaces(HTMLParser):
    def __init__(self):
        super().__init__()
        self.items, self.actual = [], None

    def handle_starttag(self, tag, attrs):
        if tag == "a":
            self.actual = [dict(attrs).get("href", ""), ""]

    def handle_data(self, data):
        if self.actual is not None:
            self.actual[1] += data

    def handle_endtag(self, tag):
        if tag == "a" and self.actual is not None:
            self.items.append(tuple(self.actual))
            self.actual = None


def enlaces(datos, base):
    parser = Enlaces()
    parser.feed(datos.decode("utf-8", errors="replace"))
    return [(urljoin(base, url), titulo) for url, titulo in parser.items if oficial(urljoin(base, url))]


def extraer_pdf(datos, anio, url):
    from pypdf import PdfReader
    lector = PdfReader(io.BytesIO(datos))
    if lector.is_encrypted or len(lector.pages) > 120:
        return None
    cabecera = normalizar(" ".join(p.extract_text() or "" for p in lector.pages[:4]))
    if not re.search(rf"000?1\s*/\s*{anio}\b", cabecera) or "subsistema de educacion regular" not in cabecera:
        return None
    encontrados = {"inicio": [], "cierre": []}
    for numero, pagina in enumerate(lector.pages[:35], start=1):
        texto = normalizar(pagina.extract_text() or "")
        for campo in encontrados:
            patron = rf"\b{campo}(?: de clases)?:\s*(?:hasta el\s*)?(\d{{1,2}})\s+de\s+([a-z]+)\s+de\s+{anio}\b"
            for coincidencia in re.finditer(patron, texto):
                mes = MESES.get(coincidencia[2])
                if mes:
                    try:
                        encontrados[campo].append((date(anio, mes, int(coincidencia[1])).isoformat(), numero))
                    except ValueError:
                        pass
    if any(len({f for f, _ in valores}) != 1 for valores in encontrados.values()):
        return None
    inicio, pagina_inicio = encontrados["inicio"][0]
    cierre, pagina_cierre = encontrados["cierre"][0]
    if inicio >= cierre:
        return None
    return {"inicio": inicio, "cierre": cierre, "url": url, "documento": f"RM 0001/{anio} · Educación Regular",
            "paginas": sorted({pagina_inicio, pagina_cierre}), "sha256": hashlib.sha256(datos).hexdigest()}


def estudiar(anio):
    candidatos = [FUENTES[anio]] if anio in FUENTES else []
    pendientes_revision = False
    # Descubre la publicación del año solicitado; no extrapola fechas de otro año.
    if not candidatos:
        busqueda = "https://www.minedu.gob.bo/index.php?" + urlencode({"option": "com_finder", "q": f"0001/{anio}"})
        datos, base = descargar(busqueda)
        articulos = [(u, t) for u, t in enlaces(datos, base) if str(anio) in normalizar(t) and "0001" in normalizar(t) and ("resolucion" in normalizar(t) or "regular" in normalizar(t))]
        for url, titulo in articulos[:4]:
            contenido, final = descargar(url)
            if contenido.startswith(b"%PDF"):
                candidatos.append(final)
            else:
                candidatos.extend(u for u, t in enlaces(contenido, final) if urlsplit(u).path.lower().endswith(".pdf") and "regular" in normalizar(u + " " + t))
    for url in list(dict.fromkeys(candidatos))[:3]:
        datos, final = descargar(url)
        if not datos.startswith(b"%PDF"):
            continue
        resultado = extraer_pdf(datos, anio, final)
        if resultado:
            return {"estado": "RESULTADO", "mensaje": "Encontramos el inicio y cierre curricular en una publicación oficial. Revisa la fuente antes de aplicar las fechas.", "resultado": resultado}
        pendientes_revision = True
    return {"estado": "REVISION_REQUERIDA" if pendientes_revision else "SIN_PUBLICACION",
            "mensaje": "Encontramos documentos, pero sus fechas no se pudieron identificar con suficiente certeza. Revisa la fuente." if pendientes_revision else f"No encontramos fechas confirmables de Educación Regular para {anio} en las fuentes consultadas. Conservamos el pendiente y volveremos a revisar."}


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--anio", type=int, required=True)
    args = parser.parse_args()
    if not 2020 <= args.anio <= 2100:
        parser.error("Año fuera del rango permitido")
    try:
        salida = estudiar(args.anio)
    except ModuleNotFoundError:
        salida = {"estado": "NO_DISPONIBLE", "mensaje": "El lector de documentos no está disponible. El estudio queda pendiente de revisión técnica."}
    except HTTPError as error:
        salida = {"estado": "FUENTE_NO_DISPONIBLE", "mensaje": f"El sitio oficial no permitió completar la consulta (HTTP {error.code}). Conservamos el pendiente para reintentarlo."}
    except URLError as error:
        certificado = isinstance(error.reason, ssl.SSLError)
        salida = {"estado": "FUENTE_NO_DISPONIBLE" if certificado else "SIN_CONEXION", "mensaje": "No pudimos verificar la conexión segura con el Ministerio." if certificado else "No logramos conectar con el Ministerio. El estudio se reintentará cuando la conexión esté disponible."}
    except (TimeoutError, ConnectionError):
        salida = {"estado": "SIN_CONEXION", "mensaje": "La conexión no respondió a tiempo. Conservamos el pendiente para volver a consultar."}
    except (ValueError, OSError):
        salida = {"estado": "REVISION_REQUERIDA", "mensaje": "La fuente no pudo leerse con suficiente certeza. Conservamos el pendiente; las fechas del formulario no se modificaron."}
    except Exception:
        salida = {"estado": "REVISION_REQUERIDA", "mensaje": "No pudimos completar la lectura del documento. Las fechas del formulario se conservan para revisión."}
    print(json.dumps({"anio": args.anio, **salida}, ensure_ascii=False))


if __name__ == "__main__":
    main()
