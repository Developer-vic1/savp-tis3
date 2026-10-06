"""Reorganización puntual: conserva los métodos del módulo y sustituye sus lecturas activas."""
from pathlib import Path
import re
import subprocess

raiz = Path(__file__).resolve().parents[2]
ruta = raiz / 'app/Livewire/Admin/GestionCurso.php'
nuevo = ruta.read_text(encoding='utf-8')
anterior = subprocess.check_output(['git','show','HEAD:app/Livewire/Admin/GestionCurso.php'],cwd=raiz).decode('utf-8')

def funciones(texto):
    salida = {}
    for m in re.finditer(r'^    (?:public|private|protected) function (\w+)\(', texto, re.M):
        i = texto.find('{',m.end()); nivel = 1; i += 1; estado = None
        while nivel and i < len(texto):
            c = texto[i]; siguiente = texto[i:i+2]
            if estado in ("'", '"'):
                if c == '\\': i += 2; continue
                if c == estado: estado = None
            elif estado == '//':
                if c == '\n': estado = None
            elif estado == '/*':
                if siguiente == '*/': estado = None; i += 2; continue
            elif siguiente in ('//','/*'): estado = siguiente; i += 2; continue
            elif c in ("'", '"'): estado = c
            elif c == '{': nivel += 1
            elif c == '}': nivel -= 1
            i += 1
        if nivel: raise RuntimeError(m.group(1))
        salida[m.group(1)] = (m.start(), i, texto[m.start():i])
    return salida

antes = funciones(anterior); despues = funciones(nuevo)
for nombre, (ini, fin, _) in sorted(antes.items(), key=lambda e:e[1][0], reverse=True):
    if nombre in despues: anterior = anterior[:ini] + despues[nombre][2] + anterior[fin:]
agregadas = '\n\n'.join(v[2] for k,v in despues.items() if k not in antes)
final = anterior.rfind('}')
anterior = anterior[:final] + agregadas + '\n' + anterior[final:]
imports = []
for linea in re.findall(r'^use .*;$',nuevo,re.M):
    if linea not in anterior: imports.append(linea)
anterior = anterior.replace('class GestionCurso', '\n'.join(imports)+'\n\nclass GestionCurso',1)
anterior = anterior.replace('use WithPagination;','use WithPagination, WithFileUploads;',1)
propiedades = []
for linea in nuevo.splitlines():
    m = re.match(r'    (?:#\[Locked\] )?public (?:\??[\w]+ )?\$(\w+)\s*=',linea)
    if not m: continue
    nombre = m.group(1)
    existente = re.search(r'^    public (?!function)[^\n]*\$'+re.escape(nombre)+r'\s*=.*$',anterior,re.M)
    if existente:
        if '#[Locked]' in linea: anterior = anterior[:existente.start()] + linea + anterior[existente.end():]
    else: propiedades.append(linea)
anterior = anterior.replace('    protected $paginationTheme', '\n'.join(propiedades)+'\n\n    protected $paginationTheme',1)
ruta.write_text(anterior,encoding='utf-8')
print('Flujos conservados:',len(antes),'· métodos actualizados:',len(set(antes)&set(despues)))
