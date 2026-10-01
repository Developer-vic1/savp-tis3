# Auditoría npm de Fusion_Sistema

Fecha: 2026-10-01. Antes: 11 paquetes afectados (1 low, 2 moderate, 6 high, 2 critical). Después: 0. Los conteos npm son paquetes afectados, no cantidad de avisos independientes.

Se actualizó axios de forma dirigida dentro de major 1 y dependencias resolubles dentro de los rangos existentes. Vite permanece en major 8. No se usó `npm audit fix --force`. `npm ci` y build pasaron. La etiqueta devDependencies no determina exposición: axios se importa en el bundle del navegador.

| Paquete | Severidad inicial | Directa | Versión corregida instalada | Alcance |
|---|---|---|---|---|
| axios | high | sí | 1.20.0 | Browser runtime: importado por resources/js/bootstrap.js; los avisos de adaptador Node no prueban exposición en navegador. |
| baseline-browser-mapping | moderate | no | 2.11.26 | Build: datos de compatibilidad para browserslist. |
| browserslist | high | no | 4.29.3 | Build: consultas de compatibilidad/estadísticas; no lógica de usuario de Laravel. |
| concurrently | critical | sí | 9.2.4 | Dev-only: coordinación de procesos locales; no se entrega al navegador. |
| follow-redirects | moderate | no | 1.16.1 | Dependencia de axios: adaptador HTTP Node; no integrado en el bundle browser. |
| form-data | high | no | 4.0.6 | Dependencia de axios: multipart Node; no integrado en el bundle browser. |
| nanoid | high | no | 3.3.19 | Build: dependencia de PostCSS; no uso productivo directo encontrado. |
| postcss | high | sí | 8.5.28 | Build/dev: procesa CSS y mapas de fuentes; no servidor PHP de producción. |
| postcss-selector-parser | low | no | 6.1.4 | Build: Tailwind/PostCSS nested; parser de selectores. |
| shell-quote | critical | no | 1.9.0 | Dev-only: dependencia de concurrently; interpretación de comandos. |
| vite | high | sí | 8.3.2 | Build/dev: servidor de desarrollo y apertura de editor; exposición condicionada a servidor dev/configuración y entradas no confiables. |

## Dependencias y avisos concretos

Las rutas siguientes proceden del audit inicial; el JSON adjunto conserva rangos afectados, fixAvailable y chains `via`/`effects`. Las relaciones son evidencia del grafo de dependencias, no demostración de explotación.

### axios

Rutas: `node_modules/axios`.

- [Axios has a NO_PROXY Hostname Normalization Bypass that Leads to SSRF](https://github.com/advisories/GHSA-3p68-rc4w-qgx5) — moderate; rango `>=1.0.0 <1.15.0`.
- [Axios: Authentication Bypass via Prototype Pollution Gadget in `validateStatus` Merge Strategy](https://github.com/advisories/GHSA-w9j2-pvgh-6h63) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios: Incomplete Fix for CVE-2025-62718 — NO_PROXY Protection Bypassed via RFC 1122 Loopback Subnet (127.0.0.0/8) in Axios 1.15.0](https://github.com/advisories/GHSA-pmwg-cvhr-8vh7) — high; rango `>=1.0.0 <1.15.1`.
- [Axios: Invisible JSON Response Tampering via Prototype Pollution Gadget in `parseReviver`](https://github.com/advisories/GHSA-3w6x-2g7m-8v23) — moderate; rango `>=1.0.0 <1.15.2`.
- [Axios: Null Byte Injection via Reverse-Encoding in AxiosURLSearchParams](https://github.com/advisories/GHSA-xhjh-pmcv-23jw) — low; rango `>=1.0.0 <1.15.1`.
- [Axios: CRLF Injection in multipart/form-data body via unsanitized blob.type in formDataToStream](https://github.com/advisories/GHSA-445q-vr5w-6q77) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios: no_proxy bypass via IP alias allows SSRF](https://github.com/advisories/GHSA-m7pr-hjqh-92cm) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios' HTTP adapter-streamed uploads bypass maxBodyLength when maxRedirects: 0](https://github.com/advisories/GHSA-5c9x-8gcm-mpgx) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios: HTTP adapter streamed responses bypass maxContentLength](https://github.com/advisories/GHSA-vf2m-468p-8v99) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios: Prototype Pollution Gadgets - Response Tampering, Data Exfiltration, and Request Hijacking](https://github.com/advisories/GHSA-pf86-5x62-jrwf) — high; rango `>=1.0.0 <1.15.1`.
- [Axios: Header Injection via Prototype Pollution](https://github.com/advisories/GHSA-6chq-wfr3-2hj9) — high; rango `>=1.0.0 <1.15.1`.
- [Axios: XSRF Token Cross-Origin Leakage via Prototype Pollution Gadget in `withXSRFToken` Boolean Coercion](https://github.com/advisories/GHSA-xx6v-rp6x-q39c) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios has prototype pollution read-side gadgets in HTTP adapter that allow credential injection and request hijacking](https://github.com/advisories/GHSA-q8qp-cvcw-x6jj) — high; rango `>=1.0.0 <1.15.2`.
- [Axios has Unrestricted Cloud Metadata Exfiltration via Header Injection Chain](https://github.com/advisories/GHSA-fvcv-3m26-pcqx) — moderate; rango `>=1.0.0 <1.15.0`.
- [Axios: unbounded recursion in toFormData causes DoS via deeply nested request data](https://github.com/advisories/GHSA-62hf-57xw-28j9) — moderate; rango `>=1.0.0 <1.15.1`.
- [Axios: Regular Expression Denial of Service (ReDoS) via Cookie Name Injection](https://github.com/advisories/GHSA-hfxv-24rg-xrqf) — high; rango `>=1.0.0 <1.16.0`.
- [Allocation of Resources Without Limits or Throttling in Axios](https://github.com/advisories/GHSA-777c-7fjr-54vf) — high; rango `>=1.7.0 <1.16.0`.
- [Axios: Proxy-Authorization Credential Leak to Origin Server Across HTTP-to-HTTPS Redirect in Axios Node.js HTTP Adapter](https://github.com/advisories/GHSA-p92q-9vqr-4j8v) — high; rango `>=1.0.0 <1.16.0`.
- [Axios: Proxy-Authorization header leaks to redirect target when proxy is re-evaluated to direct connection](https://github.com/advisories/GHSA-j5f8-grm9-p9fc) — high; rango `>=1.0.0 <1.16.0`.
- [axios Vulnerable to Credential Theft and Response Hijacking via Prototype Pollution Gadget in Config Merge](https://github.com/advisories/GHSA-3g43-6gmg-66jw) — high; rango `>=1.0.0 <1.15.2`.
- [axios Vulnerable to Full Man-in-the-Middle via Prototype Pollution Gadget in `config.proxy`](https://github.com/advisories/GHSA-35jp-ww65-95wh) — high; rango `>=1.0.0 <1.16.0`.
- [axios has DoS & Header Injection via Prototype Pollution Read-Side Gadgets in axios merge functions](https://github.com/advisories/GHSA-898c-q2cr-xwhg) — moderate; rango `>=1.0.0 <1.16.0`.
- [Axios: Prototype pollution gadgets can alter axios request construction](https://github.com/advisories/GHSA-mmx7-hfxf-jppx) — moderate; rango `>=1.0.0 <1.18.0`.
- [Axios: Deep formToJSON Key Recursion Can Cause Denial of Service](https://github.com/advisories/GHSA-pmv8-rq9r-6j72) — moderate; rango `>=1.0.0 <1.18.0`.
- [Axios: HTTP/2 streamed uploads bypass `maxBodyLength`](https://github.com/advisories/GHSA-mwf2-3pr3-8698) — moderate; rango `>=1.13.0 <1.18.0`.
- [Axios: Nested axios option objects can consume polluted prototype values](https://github.com/advisories/GHSA-7q8q-rj6j-mhjq) — moderate; rango `>=1.0.0 <1.18.0`.
- [Axios: Fetch adapter `ReadableStream` uploads bypass `maxBodyLength`](https://github.com/advisories/GHSA-jqh4-m9w3-8hp9) — moderate; rango `>=1.7.0 <1.18.0`.
- [Axios: Excessive recursion in formDataToJSON can cause denial of service](https://github.com/advisories/GHSA-42h9-826w-cgv3) — moderate; rango `>=1.0.0 <1.18.0`.
- [Axios: Prototype pollution gadget in fetch adapter can alter outbound requests](https://github.com/advisories/GHSA-vh66-26gq-q6x8) — moderate; rango `>=1.7.0 <1.20.0`.
- [Axios: Prototype-Pollution Gadget in the Default Instance Allows Inherited Object.prototype.method to Override HTTP Method](https://github.com/advisories/GHSA-9fr6-4gfg-395g) — moderate; rango `>=1.0.0 <1.20.0`.
- [Axios: HTTP/2 adapter bypasses configured DNS lookup and proxy controls](https://github.com/advisories/GHSA-3pq3-5fj3-cg6v) — high; rango `>=1.13.0 <1.20.0`.
- [Axios: Denial of Service via Unhandled 'error' Event in HTTP/2 ClientHttp2Session Initialization](https://github.com/advisories/GHSA-542g-h47m-68v8) — high; rango `>=1.13.0 <1.20.0`.
- [Axios: Header Injection via Inherited headers After Minimal Interceptor](https://github.com/advisories/GHSA-j8rh-479h-cp32) — moderate; rango `>=1.0.0 <1.20.0`.
- [Axios: Fetch Adapter Header Injection via Inherited FormData getHeaders](https://github.com/advisories/GHSA-4hqw-qxg8-jxx2) — moderate; rango `>=1.12.0 <1.20.0`.

### baseline-browser-mapping

Rutas: `node_modules/baseline-browser-mapping`.

- [baseline-browser-mapping process termination on invalid input causes denial of service](https://github.com/advisories/GHSA-w5vr-8v7q-w6rv) — moderate; rango `>=2.0.0 <2.11.0`.

### browserslist

Rutas: `node_modules/browserslist`.

- [Browserslist: Unbounded memory growth (no cache eviction) via distinct query results, leading to eventual OOM](https://github.com/advisories/GHSA-c83g-rgw3-j3cx) — high; rango `<=4.28.6`.
- [Browserslist: Uncaught crash / prototype write via untrusted browserslist-stats.json custom stats (normalizeStats)](https://github.com/advisories/GHSA-73wf-gq98-2v4g) — high; rango `<=4.28.6`.

### concurrently

Rutas: `node_modules/concurrently`.

- Aviso heredado a través de `shell-quote`.

### follow-redirects

Rutas: `node_modules/follow-redirects`.

- [follow-redirects leaks Custom Authentication Headers to Cross-Domain Redirect Targets](https://github.com/advisories/GHSA-r4q5-vmmm-2653) — moderate; rango `<=1.15.11`.

### form-data

Rutas: `node_modules/form-data`.

- [form-data: CRLF injection in form-data via unescaped multipart field names and filenames](https://github.com/advisories/GHSA-hmw2-7cc7-3qxx) — high; rango `>=4.0.0 <4.0.6`.

### nanoid

Rutas: `node_modules/nanoid`.

- [nanoid: non-secure generators can loop indefinitely with negative size](https://github.com/advisories/GHSA-28wg-ghj8-5hjv) — high; rango `<3.3.16`.
- [nanoid: custom generators can loop indefinitely when size is zero](https://github.com/advisories/GHSA-2v37-7h3g-55p8) — high; rango `<3.3.18`.
- [nanoid: Integer Overflow or Wraparound](https://github.com/advisories/GHSA-xwg4-73v4-xw9w) — high; rango `<3.3.12`.

### postcss

Rutas: `node_modules/postcss`.

- [PostCSS has XSS via Unescaped </style> in its CSS Stringify Output](https://github.com/advisories/GHSA-qx2v-qp2m-jg93) — moderate; rango `<8.5.10`.
- [PostCSS: Arbitrary file read and information disclosure via attacker-controlled sourceMappingURL in CSS comments](https://github.com/advisories/GHSA-6g55-p6wh-862q) — high; rango `<=8.5.11`.
- [PostCSS: incomplete fix of GHSA-6g55-p6wh-862q — attacker-controlled sourceMappingURL reads arbitrary .map files when `from` is unset](https://github.com/advisories/GHSA-fxqj-rqcc-2cmp) — moderate; rango `<=8.5.22`.
- [PostCSS: Path Traversal in Previous Source Map Auto-Loading (sourceMappingURL) leads to Arbitrary .map File Disclosure](https://github.com/advisories/GHSA-r28c-9q8g-f849) — high; rango `<=8.5.17`.

### postcss-selector-parser

Rutas: `node_modules/postcss-nested/node_modules/postcss-selector-parser`, `node_modules/tailwindcss/node_modules/postcss-selector-parser`.

- [postcss-selector-parser allows denial of service through uncontrolled AST recursion](https://github.com/advisories/GHSA-w9m9-85wc-3x92) — low; rango `>=6.1.0 <6.1.3`.

### shell-quote

Rutas: `node_modules/shell-quote`.

- [shell-quote quote() does not escape newlines in object .op values](https://github.com/advisories/GHSA-w7jw-789q-3m8p) — critical; rango `>=1.1.0 <=1.8.3`.
- [shell-quote: Quadratic-complexity Denial of Service in `parse()` (CWE-407)](https://github.com/advisories/GHSA-395f-4hp3-45gv) — high; rango `<=1.8.4`.

### vite

Rutas: `node_modules/vite`.

- [launch-editor: NTLMv2 hash disclosure via UNC path handling on Windows](https://github.com/advisories/GHSA-v6wh-96g9-6wx3) — moderate; rango `>=8.0.0 <=8.0.15`.
- [vite: `server.fs.deny` bypass on Windows alternate paths](https://github.com/advisories/GHSA-fx2h-pf6j-xcff) — high; rango `>=8.0.0 <=8.0.15`.

## Riesgo restante

No quedan avisos conocidos en `npm audit` de esta ejecución. Esto no certifica ausencia de vulnerabilidades futuras ni sustituye revisión del código. Vite de pruebas se vinculó a 127.0.0.1 y sus procesos temporales se cierran al terminar. No se modificaron resources/css/app.css ni resources/js/app.js.
