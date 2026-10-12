const COLLECTION_STATUSES = ['SIN_INICIAR', 'EN_PROCESO', 'COMPLETADA', 'PARCIAL', 'NO_DISPONIBLE'];

export default function coleccionUniversitaria(config) {
    return {
        collection: null,
        loading: false,
        starting: false,
        analyzing: false,
        selectedSemesters: {},
        error: '',
        search: '',
        campusFilter: 'TODAS',
        downloadNotice: '',
        timer: null,
        controller: null,
        destroyed: false,
        retryAt: 0,
        mustCheckStatus: false,
        init() { this.refresh(); },
        destroy() {
            this.destroyed = true;
            clearTimeout(this.timer);
            this.controller?.abort();
        },
        get running() { return this.collection?.status === 'EN_PROCESO'; },
        get busy() { return this.loading || this.starting || this.analyzing || this.running; },
        get phaseLabel() {
            if (this.running && this.collection?.processing_stage) {
                return ({RECONOCIMIENTO:'Reconociendo el contenido de las materias', ANALISIS:'Analizando los bloques académicos',
                    ORGANIZACION:'Ordenando las materias por semestre'})[this.collection.processing_stage] || 'Procesando las mallas guardadas';
            }
            return this.analyzing ? 'Buscando y organizando las materias' : (this.loading ? 'Recuperando tu colección guardada' : 'Preparando la lectura de las mallas');
        },
        get canAnalyze() {
            return !!config.canCollect && !!config.analysisUrl && !!this.collection?.careers?.length && !this.busy
                && !this.mustCheckStatus && Date.now() >= this.retryAt;
        },
        get pendingDownloads() {
            const pending = ['PENDIENTE', 'NO_DISPONIBLE'];
            return (this.collection?.careers || []).some(career => pending.includes(career.status)
                || (career.curricula || []).some(curriculum => pending.includes(curriculum.status)));
        },
        get canStart() {
            return !!config.canCollect && !this.busy && this.collection?.status !== 'COMPLETADA'
                && !this.mustCheckStatus && Date.now() >= this.retryAt;
        },
        get filteredCareers() {
            const term = this.search.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLocaleLowerCase('es').trim();
            return (this.collection?.careers || []).filter(career => this.matchesCampus(career)).filter(career =>
                `${career.name} ${(career.campuses || []).join(' ')}`.normalize('NFD')
                    .replace(/\p{Diacritic}/gu, '').toLocaleLowerCase('es').includes(term));
        },
        confirmedCampuses(career) {
            const available = (career.campus_availability || []).filter(item => item.status === 'OFERTADA' && this.safeUrl(item.source_url));
            if (Array.isArray(career.campus_availability)) return [...new Set(available.map(item => item.campus).filter(Boolean))];
            return career.campus_evidence?.status === 'OFERTA_IDENTIFICADA' && this.safeUrl(career.campus_evidence.source_url)
                ? [...new Set(career.campuses || [])] : [];
        },
        campusPending(career) {
            return career.campus_evidence?.status === 'PENDIENTE'
                || (career.campus_availability || []).some(item => item.status === 'PENDIENTE')
                || (!Array.isArray(career.campus_availability) && !this.confirmedCampuses(career).length);
        },
        matchesCampus(career, filter = this.campusFilter) {
            if (filter === 'TODAS') return true;
            if (filter === 'PENDIENTES') return this.campusPending(career);
            return this.confirmedCampuses(career).includes(filter);
        },
        get campusFilters() {
            const careers = this.collection?.careers || [];
            const campuses = [...new Set(careers.flatMap(career => [
                ...this.confirmedCampuses(career), ...(career.campus_availability || []).map(item => item.campus),
            ]).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'es'));
            return [{ value: 'TODAS', label: 'Todas las carreras', count: careers.length },
                ...campuses.map(campus => ({ value: campus, label: campus, count: careers.filter(career => this.matchesCampus(career, campus)).length })),
                { value: 'PENDIENTES', label: 'Sede pendiente de verificación', count: careers.filter(career => this.campusPending(career)).length }];
        },
        campusAvailabilityLabel(item) {
            return `${item.campus} · ${item.status === 'OFERTADA' ? 'Oferta publicada' : (item.status === 'NO_INDICADA' ? 'No indicada en la fuente' : 'Pendiente de verificar')}`;
        },
        campusEvidenceExplanation(item) {
            return item.status === 'OFERTADA' ? 'Esta sede aparece activa en la disponibilidad de la carrera.'
                : item.status === 'NO_INDICADA' ? 'Esta sede aparece desactivada en la disponibilidad de la carrera.'
                    : 'El indicador de esta sede necesita comprobarse en la ficha oficial.';
        },
        curriculumCampusLabel(curriculum) {
            return curriculum.campuses?.length ? `Sedes indicadas en esta malla: ${curriculum.campuses.join(' · ')}`
                : 'Sede de esta malla pendiente de confirmación. La oferta de la carrera se consulta por separado.';
        },
        get actionLabel() {
            if (this.starting) return this.collection?.status === 'PARCIAL' && !this.pendingDownloads ? 'Solicitando lectura…' : 'Solicitando extracción…';
            if (this.running) return 'Extracción en curso';
            if (this.collection?.status === 'PARCIAL') return this.pendingDownloads ? 'Reanudar descargas pendientes' : 'Preparar lectura';
            if (this.collection?.status === 'COMPLETADA') return 'Colección guardada';
            return 'Extraer carreras y mallas';
        },
        statusLabel(status) {
            return ({ SIN_INICIAR: 'Lista para iniciar', EN_PROCESO: 'Extracción en curso', COMPLETADA: 'Extracción completada',
                PARCIAL: 'Extracción parcial', NO_DISPONIBLE: 'Lectura no disponible', EXTRAIDA: 'Contenido extraído',
                EXTRAIDO: 'Contenido extraído', EXTRACTED: 'Contenido extraído', GUARDADA: 'Guardada para revisión',
                GUARDADO: 'Guardado para revisión', PENDIENTE: 'Pendiente de extracción', ERROR: 'Requiere revisión',
                NO_EXTRAIBLE: 'Lectura no disponible', EXTRAIDA_PENDIENTE_REVISION: 'Extraída · pendiente de revisión',
                SIN_TEXTO: 'Sin texto digital extraíble', SIN_TEXTO_DIGITAL: 'Requiere lectura visual' })[status] || String(status || 'Estado no informado').replaceAll('_', ' ');
        },
        badgeClass(status) {
            if (['COMPLETADA', 'EXTRAIDA', 'EXTRAIDO', 'EXTRACTED', 'GUARDADA', 'GUARDADO'].includes(status)) return 'ui-badge-success';
            if (status === 'EXTRAIDA_PENDIENTE_REVISION') return 'ui-badge-info';
            if (status === 'EN_PROCESO') return 'ui-badge-info';
            if (['ERROR', 'PARCIAL', 'NO_DISPONIBLE', 'NO_EXTRAIBLE', 'SIN_TEXTO', 'SIN_TEXTO_DIGITAL'].includes(status)) return 'ui-badge-warning';
            return 'ui-badge-muted';
        },
        safeUrl(value) {
            try {
                const url = new URL(value);
                const officialHost = url.hostname === 'unifranz.edu.bo' || url.hostname.endsWith('.unifranz.edu.bo');
                return url.protocol === 'https:' && officialHost && !url.username && !url.password && !url.port ? url.href : null;
            } catch { return null; }
        },
        reviewUrl(value) {
            return this.safeUrl(value) ? `/conocimiento/fuentes?url=${encodeURIComponent(value)}` : null;
        },
        documentTitle(career) {
            return `Malla curricular · ${career.name || 'Carrera universitaria'}`;
        },
        documentKey(curriculum) { return curriculum.document_hash || curriculum.url; },
        semesters(curriculum) {
            const declared = curriculum.curricular_structure?.semesters;
            const groups = new Map();
            const append = (number, subjects) => {
                const semester = Number(number);
                if (!Number.isInteger(semester) || semester < 1 || semester > 20) return;
                const existing = groups.get(semester) || { number: semester, subjects: [] };
                for (const item of subjects || []) {
                    const name = typeof item === 'string' ? item.trim() : String(item?.name || '').trim();
                    if (!name || existing.subjects.some(subject => subject.name === name)) continue;
                    existing.subjects.push(typeof item === 'string' ? { name } : { ...item, name });
                }
                groups.set(semester, existing);
            };
            if (Array.isArray(declared) && declared.length) {
                for (const semester of declared) append(semester.number, semester.subjects);
            } else {
                for (const subject of curriculum.subjects_candidates || []) append(subject.semester, [subject]);
            }
            return [...groups.values()].sort((a, b) => a.number - b.number);
        },
        selectedSemester(curriculum) {
            const groups = this.semesters(curriculum);
            return groups.find(group => group.number === this.selectedSemesters[this.documentKey(curriculum)]) || groups[0] || null;
        },
        selectSemester(curriculum, number) {
            this.selectedSemesters = { ...this.selectedSemesters, [this.documentKey(curriculum)]: number };
        },
        subjectCount(curriculum) { return this.semesters(curriculum).reduce((total, semester) => total + semester.subjects.length, 0); },
        subjectCountLabel(count) { return `${count} materia${count === 1 ? '' : 's'} identificada${count === 1 ? '' : 's'}`; },
        structureSummary(curriculum) {
            const groups = this.semesters(curriculum);
            if (!groups.length && !curriculum.document_hash) return 'Todavía no hay una copia guardada de esta malla para analizar. Consulta el documento oficial o reanuda su descarga pendiente.';
            if (!groups.length) return 'Todavía no hay materias organizadas por semestre. Analiza la malla guardada para preparar su revisión.';
            const declared = Number(curriculum.curricular_structure?.semester_count);
            const coverage = Number.isInteger(declared) && declared > 0 && declared !== groups.length
                ? `${groups.length} de ${declared} semestres con materias identificadas` : `${groups.length} semestre${groups.length === 1 ? '' : 's'} identificado${groups.length === 1 ? '' : 's'}`;
            const count = this.subjectCount(curriculum);
            return `${coverage} · ${count} materia${count === 1 ? '' : 's'} para revisar con la malla oficial.`;
        },
        downloadUrl(curriculum) {
            const hash = String(curriculum.document_hash || '').replace(/^sha256:/i, '');
            return /^[a-f0-9]{64}$/i.test(hash)
                ? `/conocimiento/colecciones/unifranz/documento/${hash.toLowerCase()}/descargar` : null;
        },
        humanText(value) {
            return String(value || '').replace(/\bOCR\b/gi, 'reconocimiento de texto')
                .replace(/\bSHA-?256\b/gi, 'identificación interna');
        },
        readingSummary(curriculum) {
            if (curriculum.status === 'EN_PROCESO') return 'Se está preparando el texto del documento para su revisión.';
            const pages = Number(curriculum.pages);
            const digitalPages = Number(curriculum.digital_pages);
            const recognizedPages = Number(curriculum.recognized_pages ?? 0);
            const readablePages = Number(curriculum.readable_pages ?? (digitalPages + recognizedPages));
            if (curriculum.pages != null && curriculum.digital_pages != null && Number.isFinite(pages) && Number.isFinite(digitalPages)) {
                if (recognizedPages > 0 && Number.isFinite(readablePages)) {
                    const coverage = `Hay texto disponible en ${readablePages} de ${pages} páginas: ${digitalPages} con texto del documento y ${recognizedPages} con texto reconocido de imágenes.`;
                    const pending = readablePages < pages ? ' Quedan páginas sin texto disponible para revisar visualmente.' : '';
                    const review = curriculum.status === 'PARCIAL' ? ' La lectura parcial requiere revisión visual.' : '';
                    return `${coverage}${pending}${review} Confirma las materias del texto reconocido con el documento original.`;
                }
                if (digitalPages === 0 && pages > 0) return `${pages} página${pages === 1 ? '' : 's'} guardada${pages === 1 ? '' : 's'}. El documento requiere reconocer el texto de sus imágenes antes de revisar las materias.`;
                if (digitalPages < pages) return `Hay texto digital en ${digitalPages} de ${pages} páginas. Revisa el texto disponible y las páginas que todavía necesitan lectura.`;
                if (pages > 0) return `Hay texto digital en las ${pages} página${pages === 1 ? '' : 's'}. Revisa las materias y su organización en el documento original.`;
            }
            return curriculum.excerpt ? 'Hay texto disponible para revisar las materias. Confirma su orden y contenido con el documento oficial.'
                : 'Todavía no se confirmó texto disponible para revisar las materias.';
        },
        pageReadingLabel(page) {
            return `Página ${page.page} · ${page.method === 'DIGITAL' ? 'Texto del documento' : 'Texto reconocido · Revisar'}`;
        },
        formatDate(value) {
            const date = value ? new Date(value) : null;
            return date && !Number.isNaN(date.getTime()) ? date.toLocaleString('es-BO', { timeZone: 'America/La_Paz' }) : 'Sin actualización registrada';
        },
        async refresh() { return this.request('GET'); },
        async analyze() {
            if (!this.canAnalyze) return;
            return this.request('POST', config.analysisUrl, true);
        },
        async start() {
            if (!this.canStart) return;
            return this.request('POST');
        },
        async request(method, url = config.url, analysis = false) {
            if (this.destroyed || this.loading || this.starting || this.analyzing) return;
            clearTimeout(this.timer);
            this.timer = null;
            this.loading = method === 'GET';
            this.starting = method === 'POST' && !analysis;
            this.analyzing = analysis;
            this.error = '';
            this.controller = new AbortController();
            try {
                const response = await fetch(url, {
                    method, credentials: 'same-origin', signal: this.controller.signal,
                    headers: { Accept: 'application/json', ...(method === 'POST' ? { 'X-CSRF-TOKEN': config.csrf } : {}) },
                });
                if (!response.ok) {
                    if (response.status === 419) throw new Error('La sesión venció. Recarga la página e ingresa nuevamente antes de continuar.');
                    if (response.status === 429) {
                        const seconds = Number(response.headers?.get('Retry-After'));
                        const wait = Number.isFinite(seconds) && seconds > 0 ? Math.ceil(seconds) : 30;
                        this.retryAt = Date.now() + wait * 1000;
                        throw new Error(`El servicio recibió demasiadas solicitudes. Espera ${wait} segundos y consulta el estado antes de reintentar.`);
                    }
                    if (response.status === 403) throw new Error('Tu cuenta no tiene permiso para realizar esta extracción. Solicita acceso a revisión del conocimiento.');
                    throw new Error('No pudimos consultar el Aporte Ingenieril. Comprueba la conexión y vuelve a consultar el estado.');
                }
                const payload = await response.json();
                if (payload.schema_version !== '1.0' || payload.institution !== 'UNIFRANZ'
                    || !COLLECTION_STATUSES.includes(payload.status) || !Array.isArray(payload.careers)) {
                    throw new Error('La respuesta no confirma una colección válida. Consulta el estado antes de iniciar otra extracción.');
                }
                if (this.destroyed) return;
                this.collection = payload;
                this.mustCheckStatus = false;
                this.retryAt = 0;
            } catch (error) {
                if (error.name === 'AbortError' || this.destroyed) return;
                this.error = error instanceof TypeError || error instanceof SyntaxError
                    ? 'No se recibió una respuesta válida del servicio. Comprueba la conexión y consulta el estado para recuperar el resultado.'
                    : error.message || 'No se recibió confirmación del servicio. Consulta el estado para recuperar el resultado.';
                this.mustCheckStatus = true;
            } finally {
                this.loading = false;
                this.starting = false;
                this.analyzing = false;
                this.controller = null;
                if (!this.destroyed && !this.error && this.running) {
                    this.timer = setTimeout(() => this.refresh(), 4000);
                }
            }
        },
    };
}
