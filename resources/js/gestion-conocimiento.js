export default function knowledgeGovernance(config) {
    return {
        ...config,
        analysis: null,
        analysisToken: '',
        analysisOpen: false,
        analysisInvalidated: false,
        analyzing: false,
        submitting: false,
        detail: null,
        detailKind: null,
        detailOpen: false,
        extracting: false,
        extraction: null,
        extractionError: '',
        showAllFacts: false,
        helpOpen: false,
        examplesOpen: false,
        sourceSearch: '',
        sourceGroup: 'UNIVERSITARIAS',
        directorySummary: null,
        directoryLoading: false,
        directoryError: '',
        directoryController: null,
        directoryExpanded: false,
        languageMode: 'CLARO',
        updates: null,
        updatesLoading: false,
        updatingSource: '',
        updatesError: '',
        updatesNotice: '',
        updatesNeedsCheck: false,
        cadenceChoices: {},
        connectionController: null,
        updatesController: null,
        tutorController: null,
        serverMessage: '',
        serverErrors: {},
        touched: {},
        values: {},
        camposDetectados: {},
        expiresAt: 0,
        clockNow: Date.now(),
        clockTimer: null,
        requestController: null,
        requestVersion: 0,
        preview: null,
        previewError: '',
        previewChecking: false,
        previewTimer: null,
        previewDueAt: 0,
        previewController: null,
        previewVersion: 0,
        previewAttempts: [],
        previewRetryAt: 0,
        fichaPreviewOpen: false,
        fichaManual: false,
        fichaExpanded: false,
        publicacionOpen: false,
        publicacionLectura: false,
        entradaTipo: 'pagina',
        suggestionSelections: {},
        suggestionMessage: '',
        modalTrigger: null,
        savedBodyOverflow: null,
        connectionOpen: false,
        checkingConnection: false,
        connection: null,
        connectionError: '',
        tutorOpen: false,
        askingTutor: false,
        tutorQuestion: '',
        tutorAnswer: null,
        tutorError: '',
        tutorHistory: [],
        init() {
            this.$nextTick(() => {
                const entrada = this.initialSource || {};
                const campos = {url: entrada.url, title: entrada.carrera, declared_institution: entrada.institucion, source_type: entrada.carrera ? 'OFFICIAL_CAREER_HTML' : null};
                for (const [nombre, valor] of Object.entries(campos)) {
                    const campo = this.$refs.sourceForm?.elements.namedItem(nombre);
                    if (campo && valor && !campo.value) campo.value = valor;
                }
                this.refreshValues();
                this.fichaManual = Object.entries(this.values).some(([name, value]) => name !== 'url' && !!value);
                this.fichaExpanded = this.fichaManual;
                if (this.values.url && this.canPropose) this.schedulePreview();
            });
            this.checkConnection(false);
            if (this.directorySummaryUrl) this.loadDirectorySummary();
            if (this.canReview && this.updatesUrl) this.loadUpdates();
            this.clockTimer = setInterval(() => {
                this.clockNow = Date.now();
                if (this.analysisToken && this.clockNow >= this.expiresAt) {
                    this.analysisToken = '';
                    this.analysisInvalidated = true;
                    this.serverMessage = 'El análisis venció. Analice nuevamente para enviar la fuente.';
                }
            }, 1000);
            for (const name of ['analysisOpen', 'detailOpen', 'helpOpen', 'examplesOpen', 'connectionOpen', 'tutorOpen', 'fichaPreviewOpen', 'publicacionOpen']) {
                this.$watch(name, open => {
                    if (open) {
                        if (this.savedBodyOverflow === null) this.savedBodyOverflow = document.body.style.overflow;
                        document.body.style.overflow = 'hidden';
                        this.modalTrigger = document.activeElement;
                        this.$nextTick(() => {
                            const dialog = [...document.querySelectorAll('[data-dialogo-conocimiento][role="dialog"]')].find(element => element.getClientRects().length);
                            dialog?.querySelector('button')?.focus();
                        });
                    } else {
                        this.modalTrigger?.focus();
                        if (!['analysisOpen', 'detailOpen', 'helpOpen', 'examplesOpen', 'connectionOpen', 'tutorOpen', 'fichaPreviewOpen', 'publicacionOpen'].some(key => this[key]) && this.savedBodyOverflow !== null) {
                            document.body.style.overflow = this.savedBodyOverflow;
                            this.savedBodyOverflow = null;
                        }
                    }
                });
            }
        },
        destroy() {
            clearInterval(this.clockTimer);
            clearTimeout(this.previewTimer);
            this.previewController?.abort();
            this.requestController?.abort();
            this.connectionController?.abort();
            this.updatesController?.abort();
            this.tutorController?.abort();
            this.directoryController?.abort();
            if (this.savedBodyOverflow !== null) document.body.style.overflow = this.savedBodyOverflow;
        },
        refreshValues() {
            const form = this.$refs.sourceForm;
            if (!form) return;
            this.values = Object.fromEntries([...form.elements]
                .filter(element => element.name && !['analysis_token', '_token'].includes(element.name))
                .map(element => [element.name, element.value.trim()]));
        },
        touch(name) { this.touched[name] = true; this.refreshValues(); },
        normalize(value) {
            return (value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
        },
        get parsedUrl() {
            try { return new URL(this.values.url || ''); } catch { return null; }
        },
        get publicacionUrl() {
            if (this.urlIssue || !this.matchedUniversity) return null;
            const candidate = this.preview?.requested_url === this.values.url && this.preview?.can_use ? this.preview.final_url : this.values.url;
            try {
                const url = new URL(candidate);
                const host = url.hostname.toLowerCase().replace(/\.$/, '');
                if (url.protocol !== 'https:' || url.username || url.password || (url.port && url.port !== '443')) return null;
                return host === this.matchedUniversity.domain || host.endsWith(`.${this.matchedUniversity.domain}`) ? url.href : null;
            } catch { return null; }
        },
        get publicacionComprobada() {
            return this.preview?.requested_url === this.values.url ? this.preview : null;
        },
        get lecturaPublicacion() {
            const preview = this.publicacionComprobada;
            // Sólo texto de la respuesta vigente, nunca HTML externo ni una ficha manual.
            return preview?.can_use === true && /^sha256:[a-f0-9]{64}$/i.test(preview.document_hash || '') && typeof preview.excerpt === 'string' && preview.excerpt.trim() ? preview : null;
        },
        get muestraLecturaPublicacion() {
            const preview = this.publicacionComprobada;
            return this.publicacionLectura || preview?.embedding?.allowed === false || ['STUDENT_PORTAL', 'AUTHENTICATION'].includes(preview?.document_kind);
        },
        get motivoLecturaPublicacion() {
            const preview = this.publicacionComprobada;
            if (['STUDENT_PORTAL', 'AUTHENTICATION'].includes(preview?.document_kind)) return preview.message;
            if (preview?.embedding?.allowed === false) return preview.embedding.reason || 'La universidad no permite mostrar esta publicación dentro de otra página. Puede leer el extracto comprobado o abrir el enlace oficial.';
            if (!this.lecturaPublicacion) return 'No se obtuvo un extracto verificable. Puede abrir la publicación oficial o volver a comprobar su enlace.';
            return 'Esta lectura contiene el texto extraído del documento comprobado. Para consultar la publicación completa, abra el enlace oficial.';
        },
        abrirPublicacion() {
            this.publicacionLectura = false;
            this.publicacionOpen = true;
        },
        cambiarEntradaTipo() {
            const types = { pagina: 'OFFICIAL_UNIVERSITY_PAGE', malla: 'OFFICIAL_CURRICULUM_PDF', reglamento: 'OFFICIAL_REGULATION_PDF' };
            const type = types[this.entradaTipo];
            if (!type) return;
            this.$refs.sourceForm.elements.source_type.value = type;
            this.invalidateAnalysis();
            this.fichaManual = true;
            this.fichaExpanded = true;
        },
        elegirDocumento(documento) {
            if (this.canPropose === false || !documento?.url) return;
            try {
                const url = new URL(documento.url);
                const host = url.hostname.toLowerCase().replace(/\.$/, '');
                const institution = this.matchedUniversity?.institution;
                if (url.protocol !== 'https:' || url.username || url.password || (url.port && url.port !== '443') || !url.pathname.toLowerCase().endsWith('.pdf')) return;
                if (!this.universities.some(item => item.institution === institution && (host === item.domain || host.endsWith(`.${item.domain}`)))) return;
                this.entradaTipo = documento.kind === 'reglamento' ? 'reglamento' : 'malla';
                delete this.camposDetectados.source_type;
                this.$refs.sourceForm.elements.url.value = url.href;
                this.cambiarEntradaTipo();
                this.schedulePreview();
            } catch { /* La selección inválida conserva el formulario actual. */ }
        },
        elegirPaginaAcademica(pagina) {
            if (this.canPropose === false || this.previewChecking || !pagina?.url || this.publicacionComprobada?.can_use !== true) return;
            const ofrecida = this.publicacionComprobada.academic_pages?.find(item => item.url === pagina.url && item.kind === pagina.kind);
            if (!ofrecida) return;
            try {
                const url = new URL(ofrecida.url);
                const host = url.hostname.toLowerCase().replace(/\.$/, '');
                const domain = this.matchedUniversity?.domain;
                if (!domain || url.protocol !== 'https:' || url.username || url.password || (url.port && url.port !== '443')) return;
                if (host !== domain && !host.endsWith(`.${domain}`)) return;
                if (/\.pdf$/i.test(url.pathname) || /\/(?:login|signin|auth|solicitud|solicitudes|deudas|notas|datos_personales)(?:\/|$)/i.test(decodeURIComponent(url.pathname))) return;
                this.entradaTipo = 'pagina';
                delete this.camposDetectados.source_type;
                this.$refs.sourceForm.elements.url.value = url.href;
                this.$refs.sourceForm.elements.source_type.value = ofrecida.kind === 'carrera' ? 'OFFICIAL_CAREER_HTML' : 'OFFICIAL_UNIVERSITY_PAGE';
                this.invalidateAnalysis();
                this.schedulePreview();
            } catch { /* La ficha se conserva si un enlace no cumple el contrato. */ }
        },
        get urlIssue() {
            const value = this.values.url || '';
            if (!value) return 'Ingrese la dirección oficial de la fuente.';
            if (value.length > 2000) return 'Use como máximo 2000 caracteres en la URL.';
            const parsed = this.parsedUrl;
            if (!parsed || /[\s\\\p{Cc}\p{Cf}]|%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)|%(?![0-9a-f]{2})/iu.test(value)) return 'Use una dirección completa, sin espacios ni caracteres inválidos.';
            if (parsed.protocol !== 'https:') return 'Cambie HTTP por HTTPS; se requiere una conexión segura.';
            if (parsed.username || parsed.password) return 'Quite las credenciales de la URL. Use el enlace público oficial.';
            if (parsed.port && parsed.port !== '443') return 'Use el puerto HTTPS estándar, sin puertos personalizados.';
            const host = parsed.hostname.toLowerCase().replace(/\.$/, '');
            if (host === 'localhost' || /^[\d.]+$/.test(host) || host.includes(':') || host.startsWith('[')) return 'Una dirección IP o local no es una fuente universitaria.';
            if (!/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i.test(host) || host.length > 253) return 'Revise el dominio: contiene etiquetas o caracteres inválidos.';
            if (/\.(?:localhost|local|internal|test|invalid)$/i.test(host)) return 'Use un dominio público; las direcciones locales o de prueba no son fuentes universitarias.';
            if (!this.trustedDomains.some(domain => host === domain || host.endsWith(`.${domain}`)) && !host.endsWith('.bo')) return 'Este dominio no está registrado como universidad boliviana. Una fuente extranjera quedará bloqueada.';
            return '';
        },
        get matchedUniversity() {
            const host = this.parsedUrl?.hostname.toLowerCase().replace(/\.$/, '') || '';
            return this.universities.find(item => host === item.domain || host.endsWith(`.${item.domain}`));
        },
        get institutionIssue() {
            const value = this.values.declared_institution;
            if (!value) return 'Seleccione o escriba el nombre oficial de la universidad.';
            const university = this.matchedUniversity;
            if (university && ![university.institution, ...(university.aliases || [])].some(name => this.normalize(name) === this.normalize(value))) {
                return `El dominio corresponde a ${university.institution}. Corrija la universidad declarada.`;
            }
            return '';
        },
        get dateIssue() {
            const value = this.values.publication_date || '';
            if (!value) return '';
            if (!/^\d{4}(-\d{2}(-\d{2})?)?$/.test(value)) return 'Use 2026, 2026-03 o 2026-03-15.';
            const parts = value.split('-').map(Number);
            const year = parts[0];
            const month = parts[1] || 1;
            const day = parts[2] || 1;
            const date = new Date(0);
            date.setUTCFullYear(year, month - 1, day);
            if (!year || (parts.length > 1 && !parts[1]) || (parts.length > 2 && !parts[2]) || date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) return 'La fecha no existe. Revise el mes y el día.';
            return '';
        },
        get urlMessage() {
            if (!this.touched.url && !this.values.url) return 'Ejemplo: https://www.upb.edu/programas/pregrado. No use IP ni credenciales.';
            if (this.urlIssue) return this.urlIssue;
            return this.matchedUniversity ? `Dominio reconocido: ${this.matchedUniversity.institution}.` : 'Dominio .bo pendiente de verificación humana; todavía no podrá aprobarse.';
        },
        get institutionMessage() {
            return this.institutionIssue && (this.touched.declared_institution || this.values.declared_institution)
                ? this.institutionIssue : 'Use el nombre oficial o una sigla registrada. La sede se indica aparte.';
        },
        get dateMessage() { return this.dateIssue || 'Opcional. Año, año-mes o fecha completa; no invente una fecha.'; },
        fieldError(name) {
            if (this.serverErrors[name]?.length) return this.serverErrors[name][0];
            const field = this.$refs.sourceForm?.elements[name];
            const value = this.values[name] || '';
            const limits = this.fieldConstraints?.[name];
            const min = limits?.min ?? field?.minLength;
            const max = limits?.max ?? field?.maxLength;
            if ((limits?.obligatorio ?? field?.required) && !value) return 'Complete este campo para continuar.';
            const readable = value.replace(/\s+/gu, ' ').trim();
            if (value && min > 0 && Array.from(readable).length < min) return `Escriba al menos ${min} caracteres legibles.`;
            if (max > 0 && Array.from(value).length > max) return `Use como máximo ${max} caracteres.`;
            if (name !== 'url' && /[\x00-\x09\x0b\x0c\x0e-\x1f\x7f]|\p{Cf}/u.test(value)) return 'Quite los caracteres de control u ocultos del texto.';
            if (value && name !== 'url' && name !== 'publication_date' && !/[\p{L}\p{N}]/u.test(readable)) return 'Use texto legible; los signos aislados no describen la fuente.';
            if (name !== 'url' && (limits?.multilinea === false || ['title', 'declared_institution', 'campus', 'city', 'version'].includes(name)) && /[\r\n\u0085\u2028\u2029]/u.test(value)) return 'Use una sola línea en este campo.';
            if (name === 'url') return this.urlIssue || (this.preview && !this.preview.can_use ? this.preview.message : '');
            if (name === 'title' && this.preview?.can_use && this.normalizeTitle(value) !== this.normalizeTitle(this.preview.title)) return 'Use el título detectado en la publicación oficial.';
            if (name === 'declared_institution') return this.institutionIssue;
            if (name === 'publication_date') return this.dateIssue;
            if (name === 'source_type' && value && !['OFFICIAL_CURRICULUM_PDF', 'OFFICIAL_CAREER_HTML', 'OFFICIAL_REGULATION_PDF', 'OFFICIAL_UNIVERSITY_PAGE'].includes(value)) return 'Seleccione uno de los tipos documentales disponibles.';
            if (name === 'source_type' && this.preview?.can_use && ((this.preview.content_type === 'application/pdf') !== value.endsWith('_PDF'))) return 'El tipo no coincide con el documento detectado. Revise la vista previa.';
            if (name === 'limitations_text') {
                const lines = value.split(/\r\n|[\n\r\v\f\u0085\u2028\u2029]/).map(line => line.trim()).filter(Boolean);
                if (lines.length > 10) return 'Use como máximo 10 limitaciones.';
                if (lines.some(line => line.length < 3 || line.length > 500)) return 'Cada limitación debe tener entre 3 y 500 caracteres.';
            }
            return '';
        },
        fieldClass(name) {
            if (!this.touched[name] && !this.values[name]) return '';
            return this.fieldError(name) ? 'kg-input-error' : 'kg-input-valid';
        },
        fieldHelpClass(name) { return (this.touched[name] || this.values[name]) && this.fieldError(name) ? 'kg-text-error' : ''; },
        fieldMessage(name, fallback) { return (this.touched[name] || this.values[name]) && this.fieldError(name) ? this.fieldError(name) : fallback; },
        characterCount(name, maximum) { return `${Array.from(this.values[name] || '').length} / ${maximum}`; },
        get passedChecks() { return this.frontendChecks.filter(item => item.ok).length; },
        get validationScore() { return Math.round(this.passedChecks / Math.max(1, this.frontendChecks.length) * 100); },
        get nextPreparationAction() {
            if (!this.values.url || this.urlIssue) return { label: 'Pegue una URL oficial de la publicación', help: this.urlIssue || 'Use el enlace público de una carrera, malla o reglamento boliviano.', button: 'Ir a la URL', field: 'url' };
            if (!this.preview?.can_use) return { label: 'Compruebe la publicación', help: this.preview?.message || 'Detectaremos título, institución y posibles duplicados antes de completar la ficha.', button: 'Comprobar URL', mode: 'preview' };
            const labels = { title: 'Confirme el título publicado', declared_institution: 'Confirme la universidad', source_type: 'Elija el tipo de documento', scope: 'Describa el alcance académico', campus: 'Indique la sede', city: 'Indique la ciudad', version: 'Indique la versión o gestión', justification: 'Explique qué conocimiento aporta', publication_date: 'Revise la fecha indicada', limitations_text: 'Revise las limitaciones' };
            const required = ['title', 'declared_institution', 'source_type', 'scope', 'campus', 'city', 'version', 'justification'];
            const field = [...required, 'publication_date', 'limitations_text'].find(name => (required.includes(name) && !this.values[name]) || this.fieldError(name));
            if (field) return { label: labels[field], help: this.fieldError(field) || 'Tome este dato de la publicación y corrija el campo señalado.', button: 'Ir al campo', field };
            if (!this.analysisToken) return { label: 'Analice la ficha completa', help: 'Revise el resultado antes de enviarla a revisión administrativa.', button: 'Analizar fuente', mode: 'analyze' };
            return { label: 'Ficha analizada', help: 'Puede enviar la propuesta a revisión. La incorporación requiere aprobación.', button: 'Ver envío', mode: 'submit' };
        },
        goToPreparationAction() {
            const action = this.nextPreparationAction;
            if (action.mode === 'preview') return this.inspectUrl();
            if (action.mode === 'analyze') return this.analyze();
            if (action.field && action.field !== 'url') { this.fichaManual = true; this.fichaExpanded = true; }
            this.$nextTick(() => {
                const target = action.mode === 'submit' ? this.$refs.sourceForm?.querySelector('button[type="submit"]') : this.$refs.sourceForm?.elements.namedItem(action.field);
                target?.scrollIntoView?.({ block: 'center', behavior: 'smooth' });
                target?.focus?.();
            });
        },
        get workflowSteps() {
            return [
                { number: 1, title: 'Pegar la publicación', help: 'URL oficial de una carrera, malla o documento.', state: this.values.url && !this.urlIssue ? 'kg-step-complete' : 'kg-step-current' },
                { number: 2, title: 'Detectar y comprobar', help: this.previewChecking ? 'Comprobando la fuente ahora.' : 'Revisar institución y duplicados reales.', state: this.preview?.can_use ? 'kg-step-complete' : this.values.url && !this.urlIssue ? 'kg-step-current' : '' },
                { number: 3, title: 'Revisar la vista previa', help: 'Confirmar los datos detectados y su aporte.', state: this.analysisToken ? 'kg-step-complete' : this.preview?.can_use ? 'kg-step-current' : '' },
                { number: 4, title: 'Incorporación supervisada', help: 'Enviar, aprobar y verificar antes de activar.', state: this.analysisToken ? 'kg-step-current' : '' },
            ];
        },
        get connectionLabel() {
            if (this.checkingConnection) return 'Verificando el servicio documental…';
            if (this.connectionError) return 'Conexión sin verificar';
            return this.connection?.connected ? 'Servicio documental verificado' : 'Servicio documental necesita atención';
        },
        get documentBusy() { return this.previewChecking || this.analyzing || this.submitting; },
        get documentBusyLabel() {
            return this.submitting ? 'Enviando la ficha para revisión' : this.analyzing ? 'Analizando la evidencia de esta publicación' : 'Buscando información y comprobando duplicados';
        },
        get assistantGreeting() {
            const name = String(this.usuarioNombre || '').trim();
            return name ? `Hola, ${name}. Te ayudo a revisar y localizar información documental.` : 'Hola. Te ayudo a revisar y localizar información documental.';
        },
        get technicalDetail() { return this.languageMode === 'TECNICO'; },
        displayDocumentText(value) {
            const text = String(value || '');
            if (this.technicalDetail) return text;
            return text.replace(/\bel corpus documental\b/gi, match => `${/^[A-Z]/.test(match) ? 'La' : 'la'} biblioteca de documentos`)
                .replace(/\bel corpus\b/gi, match => `${/^[A-Z]/.test(match) ? 'La' : 'la'} biblioteca documental`)
                .replace(/\bcorpus documental\b/gi, 'biblioteca de documentos').replace(/\bcorpus\b/gi, 'biblioteca documental')
                .replace(/\bvectores\b/gi, 'registros para la búsqueda').replace(/\bembeddings\b/gi, 'preparación para búsquedas')
                .replace(/\bhash\b/gi, 'identificación del documento');
        },
        displayAssistantText(value) {
            let text = this.displayDocumentText(value);
            if (this.technicalDetail) return text;
            const sources = this.tutorAnswer?.sources || [];
            let removedKnownCitation = false;
            text = text.replace(/\[(BO-[A-Za-z0-9_.-]+)(?:\s*,\s*p\.?\s*\d+(?:-\d+)?)?\]/g, (citation, id) => {
                const known = sources.some(source => [source.source_id, source.chunk_id].filter(Boolean).some(key => id === key || id.startsWith(`${key}-`)));
                if (known) removedKnownCitation = true;
                return known ? '' : citation;
            });
            if (removedKnownCitation && /^(?:[-*•]\s+|\d+[.)]\s+|#{1,4}\s+)?(?:Fuente|Fuentes|Referencia|Referencias|Cita|Citas)\s*[:.]?\s*$/i.test(text.replace(/\*\*/g, '').trim())) return '';
            return text.replace(/ {2,}/g, ' ').replace(/\s+([.,;:!?])/g, '$1').trim();
        },
        get assistantAnswerBlocks() {
            const answer = this.tutorAnswer?.answer;
            if (typeof answer !== 'string') return [];
            const blocks = [];
            for (const raw of answer.split(/\r?\n/)) {
                const line = this.displayAssistantText(raw).trim();
                if (!line) continue;
                const item = line.match(/^(?:[-*•]\s+|\d+[.)]\s+)(.+)$/);
                if (item) {
                    if (blocks.at(-1)?.kind !== 'list') blocks.push({ kind: 'list', items: [] });
                    blocks.at(-1).items.push(item[1]);
                } else blocks.push({ kind: /^#{1,4}\s/.test(line) ? 'heading' : 'paragraph', text: line.replace(/^#{1,4}\s+/, '') });
            }
            return blocks;
        },
        get analysisHeadline() {
            if (this.analysis?.readiness === 'BLOQUEADA') return 'Fuente bloqueada: revise los controles señalados';
            if (this.analysis?.readiness === 'REQUIERE_REVISION') return 'Hay observaciones que necesitan revisión humana';
            return 'La ficha puede enviarse a revisión administrativa';
        },
        get analysisBlockReason() {
            if (this.canPropose === false) return 'Su permiso permite consultar, pero no proponer fuentes.';
            if (!this.serviceAvailable) return 'El servicio documental no está disponible; conserve la ficha y vuelva a comprobar la conexión.';
            if (this.previewChecking) return 'Espere a que termine la comprobación del documento.';
            if (this.analyzing) return 'El análisis está en curso.';
            if (this.urlIssue) return this.urlIssue;
            if (!this.preview?.can_use) return this.preview?.message || 'Compruebe la URL antes de analizar la ficha.';
            if (Object.keys(this.values).some(name => this.fieldError(name))) return 'Complete o corrija los campos señalados antes de analizar.';
            return '';
        },
        get analysisBannerClass() { return this.analysis?.readiness === 'BLOQUEADA' ? 'ui-alert-danger' : this.analysis?.readiness === 'REQUIERE_REVISION' ? 'ui-alert-warning' : 'ui-alert-success'; },
        checkCardClass(status) { return status === 'BLOQUEA' ? 'kg-check-error' : status === 'CUMPLE' ? 'kg-check-ok' : 'kg-check-pending'; },
        checkBadgeClass(status) { return status === 'BLOQUEA' ? 'ui-badge-danger' : status === 'CUMPLE' ? 'ui-badge-success' : 'ui-badge-warning'; },
        checkIcon(status) { return status === 'BLOQUEA' ? 'ph-x-circle' : status === 'CUMPLE' ? 'ph-check-circle' : 'ph-warning-circle'; },
        get frontendChecks() {
            const required = ['title', 'declared_institution', 'url', 'source_type', 'scope', 'campus', 'city', 'version', 'justification'];
            const complete = required.every(name => !!this.values[name] && !this.fieldError(name));
            return [
                { label: 'Ficha completa', ok: complete, help: complete ? 'Los campos obligatorios están listos.' : 'Complete los campos marcados con asterisco.' },
                { label: 'URL segura', ok: !!this.values.url && !this.urlIssue, help: this.urlMessage },
                { label: 'Universidad y dominio', ok: !!this.values.declared_institution && !!this.matchedUniversity && !this.institutionIssue, help: this.institutionMessage },
                { label: 'Fecha y limitaciones', ok: !this.dateIssue && !this.fieldError('limitations_text'), help: this.dateIssue || this.fieldError('limitations_text') || 'Datos coherentes; los campos opcionales pueden quedar vacíos.' },
                { label: 'Aporte explicado', ok: !!this.values.justification && !this.fieldError('justification'), help: 'Describa el conocimiento concreto que sumará a la investigación.' },
            ];
        },
        get preventionChecks() {
            const fields = Object.keys(this.values);
            const errors = fields.map(name => ({ name, message: this.fieldError(name) })).filter(item => item.message && (this.touched[item.name] || this.values[item.name]));
            return {
                bloqueos: errors,
                advertencias: !this.urlIssue && this.values.url && !this.matchedUniversity ? ['Dominio boliviano sin universidad reconocida: requiere verificación humana.'] : this.matchedUniversity && this.parsedUrl?.pathname === '/' ? ['Esta es la página institucional. Para estudiar una malla o carrera, use el enlace específico de su publicación.'] : [],
                sugerencias: this.urlIssue ? ['Use la publicación oficial de una universidad boliviana y corrija la URL antes de comprobarla.'] : [],
            };
        },
        invalidateAnalysis(event) {
            if (event?.target?.name) delete this.camposDetectados[event.target.name];
            if (this.analysisToken || this.analysis) this.analysisInvalidated = true;
            this.analysisToken = '';
            this.serverMessage = '';
            this.serverErrors = {};
            this.requestVersion++;
            this.requestController?.abort();
            this.analyzing = false;
            this.refreshValues();
            if (event?.target?.name) this.touched[event.target.name] = true;
            if (event?.target?.name === 'url') this.schedulePreview();
            if (event?.target?.name === 'url') this.publicacionOpen = false;
        },
        schedulePreview() {
            // Al cambiar de publicación se retiran sólo datos autocompletados sin editar.
            for (const [name, value] of Object.entries(this.camposDetectados)) {
                const field = this.$refs.sourceForm?.elements[name];
                if (field?.value === value) field.value = '';
            }
            this.camposDetectados = {};
            this.refreshValues();
            this.suggestionSelections = {};
            this.suggestionMessage = '';
            clearTimeout(this.previewTimer);
            this.previewVersion++;
            this.previewController?.abort();
            this.previewChecking = false;
            this.preview = null;
            this.previewError = '';
            this.previewDueAt = 0;
            if (!this.previewUrl || this.canPropose === false || !this.serviceAvailable || !this.values.url || this.urlIssue || this.previewRetryAt > Date.now()) return;
            this.previewDueAt = Date.now() + 1200;
            this.previewTimer = setTimeout(() => this.inspectUrl(), 1200);
        },
        get previewWaitingMessage() {
            if (this.urlIssue && this.values.url) return 'Corrija la URL indicada. No se enviará una solicitud mientras esté bloqueada.';
            if (!this.serviceAvailable) return 'Validación local activa. El servicio documental no está disponible; puede completar la ficha, pero la URL aún no está verificada.';
            if (this.previewRetryAt > this.clockNow) return `Espere ${Math.ceil((this.previewRetryAt - this.clockNow) / 1000)} s antes de volver a comprobar. Su ficha se conserva.`;
            return this.previewDueAt ? 'La URL tiene formato válido. Comprobaremos el documento al terminar de escribir.' : 'Validación inmediata al escribir; comprobación documental tras una pausa de 1,2 segundos o al pulsar el botón.';
        },
        get previewBannerClass() {
            return this.preview?.can_use ? 'ui-alert-success' : this.preview?.status === 'BLOQUEADA' || this.preview?.status === 'DUPLICADA' ? 'ui-alert-danger' : 'ui-alert-warning';
        },
        async inspectUrl() {
            clearTimeout(this.previewTimer);
            this.previewDueAt = 0;
            this.refreshValues();
            if (this.canPropose === false || this.previewChecking) return;
            this.touched.url = true;
            if (!this.values.url || this.urlIssue) { this.previewError = this.urlIssue || 'Ingrese la URL HTTPS oficial.'; return; }
            if (!this.serviceAvailable) { this.previewError = 'La validación local sigue activa. El documento no puede comprobarse mientras el servicio esté fuera de línea.'; return; }
            const now = Date.now();
            this.previewAttempts = this.previewAttempts.filter(at => now - at < 60000);
            if (this.previewRetryAt > now || this.previewAttempts.length >= 6) {
                this.previewRetryAt = Math.max(this.previewRetryAt, (this.previewAttempts[0] || now) + 60000);
                this.previewError = 'Se permiten seis comprobaciones por minuto. Espere el tiempo indicado; su ficha se conserva.';
                return;
            }
            this.previewAttempts.push(now);
            this.invalidateAnalysis();
            this.preview = null;
            this.previewError = '';
            this.previewChecking = true;
            const requestedUrl = this.values.url;
            const version = ++this.previewVersion;
            const controller = new AbortController();
            this.previewController = controller;
            const timeout = setTimeout(() => controller.abort(), 30000);
            try {
                const response = await fetch(this.previewUrl, {
                    method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ url: requestedUrl }), signal: controller.signal,
                });
                const body = await response.json().catch(() => ({}));
                if (version !== this.previewVersion || this.values.url !== requestedUrl) return;
                if (response.status === 429) {
                    const retry = Number(response.headers?.get('Retry-After'));
                    this.previewRetryAt = Date.now() + (Number.isFinite(retry) && retry > 0 ? Math.min(retry, 3600) : 60) * 1000;
                }
                if (!response.ok) throw new Error(response.status === 429 ? 'Espere un minuto: se permiten seis comprobaciones por minuto.' : response.status === 419 ? 'Su sesión venció. Recargue la página.' : Object.values(body.errors || {}).flat()[0] || body.message || 'No se pudo comprobar la URL.');
                if (body.requested_url !== requestedUrl) throw new Error('La respuesta no corresponde a la URL actual. Compruebe nuevamente.');
                this.preview = body;
                if (body.can_use) { this.applyDetectedFields(false); this.fichaExpanded = true; }
            } catch (error) {
                if (version === this.previewVersion) this.previewError = error.name === 'AbortError' || error instanceof TypeError ? 'No se pudo completar la comprobación. Sus datos se conservan; no se considera verificada.' : error.message;
            } finally {
                clearTimeout(timeout);
                if (version === this.previewVersion) this.previewChecking = false;
            }
        },
        applyDetectedFields(replace = true) {
            if (!this.preview?.can_use || this.preview.requested_url !== this.values.url) return;
            for (const name of ['title', 'declared_institution', 'source_type', 'scope', 'publication_date']) {
                const field = this.$refs.sourceForm.elements[name];
                const value = this.preview.suggested_fields?.[name];
                if (field && value && (replace || !field.value.trim())) {
                    field.value = value;
                    this.camposDetectados[name] = value;
                }
            }
            this.invalidateAnalysis();
        },
        get fichaFields() {
            return [
                ['title', 'Título oficial'], ['declared_institution', 'Universidad'],
                ['source_type', 'Tipo de fuente'], ['url', 'URL oficial'], ['scope', 'Alcance académico'],
                ['campus', 'Sede o campus'], ['city', 'Ciudad'], ['publication_date', 'Fecha de publicación'],
                ['version', 'Versión o gestión'], ['justification', 'Justificación'], ['limitations_text', 'Limitaciones'],
            ].map(([name, label]) => ({ name, label, value: this.values[name] || '', error: this.fieldError(name) }));
        },
        sourceTypeLabel(value) {
            if (this.technicalDetail) return value || 'Tipo pendiente de revisión';
            return {
                OFFICIAL_CURRICULUM_PDF: 'Malla curricular',
                OFFICIAL_CAREER_HTML: 'Página oficial de carrera',
                OFFICIAL_REGULATION_PDF: 'Reglamento oficial',
                OFFICIAL_UNIVERSITY_PAGE: 'Otra página universitaria oficial',
            }[value] || value || 'Tipo pendiente de revisión';
        },
        documentStatusLabel(value) {
            if (this.technicalDetail) return value || 'Sin estado informado';
            return {
                VALID: 'Documento revisado', VERIFIED: 'Documento revisado',
                PENDIENTE_REVISION: 'Pendiente de revisión', PENDIENTE_VERIFICACION_DE_DOMINIO: 'Pendiente de verificar la institución',
                APROBADA_PENDIENTE_INGESTA: 'Aprobada, pendiente incorporación', RECHAZADA: 'No aprobada',
                APROBADA: 'Aprobada para continuar', PUBLICADA: 'Documento incorporado', INCORPORADA: 'Documento incorporado',
                REQUIERE_REVISION: 'Necesita revisión', BLOQUEADA: 'No puede incorporarse todavía', ACEPTADA: 'Comprobación aceptada',
                EXTRAIDA_PENDIENTE_REVISION: 'Lectura guardada, pendiente de revisión',
            }[value] || value || 'Sin estado informado';
        },
        get suggestionRows() {
            if (!this.preview?.can_use || this.preview.requested_url !== this.values.url) return [];
            return this.fichaFields.filter(field => ['title', 'declared_institution', 'source_type', 'scope', 'publication_date'].includes(field.name))
                .map(field => ({ ...field, suggested: this.preview.suggested_fields?.[field.name] }))
                .filter(field => typeof field.suggested === 'string' && field.suggested.trim())
                .map(field => ({ ...field, changed: field.value !== field.suggested }));
        },
        openFichaPreview() {
            this.refreshValues();
            this.suggestionSelections = Object.fromEntries(this.suggestionRows.map(field => [field.name, !field.value && field.changed]));
            this.suggestionMessage = '';
            this.fichaPreviewOpen = true;
        },
        get selectedSuggestionCount() {
            return this.suggestionRows.filter(field => field.changed && this.suggestionSelections[field.name] === true).length;
        },
        applySelectedSuggestions() {
            this.refreshValues();
            if (this.canPropose === false || this.submitting || this.previewChecking) return;
            let applied = 0;
            for (const suggestion of this.suggestionRows) {
                if (!suggestion.changed || this.suggestionSelections[suggestion.name] !== true) continue;
                const field = this.$refs.sourceForm.elements[suggestion.name];
                if (!field || (field.maxLength > 0 && suggestion.suggested.length > field.maxLength)) continue;
                field.value = suggestion.suggested;
                delete this.camposDetectados[suggestion.name];
                applied++;
            }
            if (applied) this.invalidateAnalysis();
            this.suggestionSelections = {};
            this.suggestionMessage = applied ? `${applied} sugerencias aplicadas al formulario. Nada se ha guardado; deberá analizar la ficha nuevamente.` : 'Seleccione una sugerencia distinta de su dato actual.';
        },
        normalizeTitle(value) { return (value || '').trim().replace(/\s+/gu, ' ').toLowerCase(); },
        validateForm() {
            this.refreshValues();
            const fields = Object.keys(this.values);
            this.touched = Object.fromEntries(fields.map(name => [name, true]));
            const invalid = fields.find(name => this.fieldError(name));
            if (invalid) {
                this.serverMessage = 'Revise los campos señalados antes de analizar.';
                this.$refs.sourceForm.elements[invalid]?.focus();
                return false;
            }
            return true;
        },
        async analyze() {
            if (this.canPropose === false) return;
            if (this.previewChecking) { this.serverMessage = 'Espere a que termine la comprobación de la URL.'; return; }
            if (this.analyzing) return;
            const form = this.$refs.sourceForm;
            this.serverMessage = '';
            this.serverErrors = {};
            if (!this.validateForm()) return;
            if (!this.serviceAvailable) { this.serverMessage = 'El servicio está temporalmente fuera de línea. Sus datos permanecen en el formulario.'; return; }
            this.analyzing = true;
            this.analysisToken = '';
            this.requestController = new AbortController();
            const controller = this.requestController;
            const version = ++this.requestVersion;
            const timeout = setTimeout(() => controller.abort(), 35000);
            try {
                const formData = new FormData(form);
                formData.delete('_token');
                formData.delete('analysis_token');
                const response = await fetch(this.analysisUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: formData,
                    signal: controller.signal,
                });
                if (version !== this.requestVersion) return;
                const body = await response.json().catch(() => ({}));
                if (version !== this.requestVersion) return;
                if (!response.ok) {
                    this.serverErrors = body.errors || {};
                    const messages = { 419: 'Su sesión venció. Recargue la página e ingrese nuevamente.', 429: 'Alcanzó el límite de análisis. Espere un minuto e intente nuevamente.', 503: 'El servicio está fuera de línea. Conserve la ficha y vuelva a intentar.' };
                    throw new Error(messages[response.status] || Object.values(body.errors || {}).flat()[0] || 'No fue posible analizar la fuente. Revise los datos e intente nuevamente.');
                }
                this.analysis = body;
                this.analysisToken = body.can_submit === true ? body.analysis_token || '' : '';
                this.expiresAt = Date.now() + 10 * 60 * 1000;
                this.analysisInvalidated = false;
                this.analysisOpen = true;
            } catch (error) {
                if (version === this.requestVersion) this.serverMessage = error.name === 'AbortError' ? 'El análisis tardó demasiado. Intente nuevamente; su ficha se conserva.' : error instanceof TypeError ? 'No se pudo conectar. Revise su conexión e intente nuevamente; su ficha se conserva.' : error.message;
            } finally {
                clearTimeout(timeout);
                if (version === this.requestVersion) this.analyzing = false;
            }
        },
        guardSubmission(event) {
            if (this.submitting) { event.preventDefault(); return; }
            if (this.canPropose === false || !this.analysisToken || Date.now() >= this.expiresAt || !this.validateForm()) {
                event.preventDefault();
                this.serverMessage ||= 'Analice nuevamente la fuente para enviarla a revisión.';
                return;
            }
            this.submitting = true;
        },
        resetForm() {
            this.fichaPreviewOpen = false;
            this.publicacionOpen = false;
            this.fichaManual = false;
            this.fichaExpanded = false;
            this.entradaTipo = 'pagina';
            this.analyzing = false;
            this.suggestionSelections = {};
            this.suggestionMessage = '';
            clearTimeout(this.previewTimer);
            this.previewVersion++;
            this.previewController?.abort();
            this.preview = null;
            this.previewError = '';
            this.previewDueAt = 0;
            this.previewChecking = false;
            this.requestVersion++;
            this.requestController?.abort();
            this.$refs.sourceForm.reset();
            this.analysis = null;
            this.analysisToken = '';
            this.analysisInvalidated = false;
            this.serverMessage = '';
            this.serverErrors = {};
            this.touched = {};
            this.camposDetectados = {};
            this.refreshValues();
        },
        async checkConnection(openDetails = true) {
            if (this.checkingConnection) return;
            if (openDetails) this.connectionOpen = true;
            this.checkingConnection = true;
            this.connectionError = '';
            this.connectionController = new AbortController();
            try {
                const response = await fetch(this.connectionUrl, { headers: { Accept: 'application/json' }, signal: AbortSignal.any([this.connectionController.signal, AbortSignal.timeout(35000)]) });
                if (!response.ok) throw new Error('No fue posible comprobar la conexión. Revise su sesión o vuelva a intentar.');
                const body = await response.json();
                if (typeof body.connected !== 'boolean') throw new Error('Respuesta sin confirmar');
                this.connection = body;
                this.serviceAvailable = body.connected;
            } catch (error) {
                if (error.name === 'AbortError') return;
                this.connectionError = 'No se pudo completar la comprobación. La conexión no se considera verificada.';
                this.serviceAvailable = false;
            } finally { this.checkingConnection = false; }
        },
        async loadDirectorySummary() {
            if (this.directoryLoading) return;
            this.directoryLoading = true;
            this.directoryError = '';
            this.directoryController = new AbortController();
            try {
                const response = await fetch(this.directorySummaryUrl, {
                    headers: { Accept: 'application/json' },
                    signal: AbortSignal.any([this.directoryController.signal, AbortSignal.timeout(10000)]),
                });
                if (!response.ok) throw new Error('No se pudo consultar el directorio.');
                const data = await response.json();
                if (!Number.isInteger(data.institution_count) || data.institution_count < 0 || !data.departments || typeof data.departments !== 'object'
                    || !Array.isArray(data.institutions) || data.institutions.length !== data.institution_count) {
                    throw new Error('El directorio no confirmó su resumen.');
                }
                this.directorySummary = data;
            } catch (error) {
                if (error.name === 'AbortError') return;
                this.directoryError = 'No se pudo consultar el total ahora. Abre el directorio o vuelve a intentarlo.';
            } finally {
                this.directoryLoading = false;
            }
        },
        async askTutor() {
            const question = this.tutorQuestion.trim();
            if (this.askingTutor || question.length < 2 || question.length > 2000) return;
            this.askingTutor = true;
            this.tutorError = '';
            this.tutorController = new AbortController();
            try {
                const response = await fetch(this.tutorUrl, {
                    method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ question, conversation_history: this.tutorHistory.slice(-8) }), signal: AbortSignal.any([this.tutorController.signal, AbortSignal.timeout(35000)]),
                });
                const body = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(response.status === 419 ? 'Su sesión venció. Recargue la página.' : response.status === 429 ? 'Espere un minuto antes de realizar otra consulta.' : 'No fue posible consultar el asistente documental. Su pregunta permanece en el formulario.');
                if (typeof body.answer !== 'string' || !body.answer.trim()) throw new Error('El servicio no confirmó una respuesta. Su pregunta permanece en el formulario.');
                this.tutorAnswer = body;
                this.tutorHistory = [...this.tutorHistory, { role: 'user', content: question }, { role: 'assistant', content: body.answer.slice(0, 2000) }].slice(-8);
            } catch (error) {
                if (error.name === 'AbortError') return;
                this.tutorError = error instanceof TypeError || error.name === 'TimeoutError' ? 'No se pudo conectar con el asistente documental. Su pregunta permanece en el formulario.' : error.message;
            } finally { this.askingTutor = false; }
        },
        async loadUpdates() { return this.requestUpdates(this.updatesUrl, null); },
        async configureUpdate(source) {
            const cadence = this.cadenceChoices[source.source_id] || source.cadence;
            if (!['MANUAL', 'MENSUAL', 'SEMESTRAL', 'ANUAL'].includes(cadence)) return;
            return this.requestUpdates(this.updatesConfigureUrl, { source_id: source.source_id, cadence });
        },
        async reviewUpdate(source) { return this.requestUpdates(this.updatesReviewUrl, { source_id: source.source_id }); },
        async requestUpdates(url, body) {
            if (!this.canReview || !url || this.updatesLoading || this.updatingSource) return;
            if (body && this.updatesNeedsCheck) return;
            if (body && !(this.updates?.sources || []).some(source => source.source_id === body.source_id)) return;
            this.updatesLoading = !body;
            this.updatingSource = body?.source_id || '';
            this.updatesError = '';
            this.updatesNotice = '';
            this.updatesController = new AbortController();
            try {
                const response = await fetch(url, {
                    method: body ? 'POST' : 'GET', headers: { Accept: 'application/json', ...(body ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf } : {}) },
                    ...(body ? { body: JSON.stringify(body) } : {}),
                    signal: AbortSignal.any([this.updatesController.signal, AbortSignal.timeout(35000)]),
                });
                if (!response.ok) throw new Error(response.status === 419 ? 'La sesión venció. Recarga la página antes de continuar.'
                    : response.status === 429 ? 'Hay demasiadas solicitudes. Espera un minuto y consulta el estado antes de repetir la revisión.'
                        : 'No se confirmó la actualización. Consulta el estado guardado antes de reintentar.');
                const snapshot = await response.json();
                if (!Array.isArray(snapshot.sources) || !Array.isArray(snapshot.cadences)
                    || snapshot.cadences.some(cadence => !['MANUAL', 'MENSUAL', 'SEMESTRAL', 'ANUAL'].includes(cadence.value) || typeof cadence.label !== 'string')
                    || snapshot.sources.some(source => typeof source.source_id !== 'string' || !source.source_id || !['MANUAL', 'MENSUAL', 'SEMESTRAL', 'ANUAL'].includes(source.cadence)
                        || !['SIN_REVISAR', 'SIN_CAMBIOS', 'CAMBIO_DETECTADO', 'NO_DISPONIBLE'].includes(source.status))
                    || (body && !snapshot.sources.some(source => source.source_id === body.source_id))) {
                    throw new Error('El servicio no confirmó una configuración válida. Conservamos el último estado recibido.');
                }
                this.updates = snapshot;
                this.updatesNeedsCheck = false;
                this.cadenceChoices = Object.fromEntries(snapshot.sources.map(source => [source.source_id, source.cadence]));
                this.updatesNotice = body ? snapshot.message || 'Estado de actualización confirmado por el servidor.' : '';
            } catch (error) {
                if (error.name === 'AbortError') return;
                if (body) this.updatesNeedsCheck = true;
                this.updatesError = error instanceof TypeError || error.name === 'TimeoutError' ? 'No se recibió confirmación del servicio. Conservamos los datos; consulta el estado antes de continuar.' : error.message;
            } finally { this.updatesLoading = false; this.updatingSource = ''; }
        },
        updateStatusLabel(status) {
            if (this.technicalDetail) return status || 'Sin estado informado';
            return ({ SIN_REVISAR: 'Sin revisar', SIN_CAMBIOS: 'Sin cambios respecto de la referencia', CAMBIO_DETECTADO: 'Cambio detectado · requiere revisión', NO_DISPONIBLE: 'No se pudo comprobar' })[status] || 'Estado pendiente de confirmar';
        },
        cadenceLabel(value) { return (this.updates?.cadences || []).find(cadence => cadence.value === value)?.label || 'Sin confirmar'; },
        updateDate(value) {
            const date = value ? new Date(value) : null;
            return date && !Number.isNaN(date.getTime()) ? date.toLocaleString('es-BO', { timeZone: 'America/La_Paz' }) : 'Sin fecha registrada';
        },
        loadExample(kind) {
            const source = this.sources.find(item => item.source_id === 'BO-UPB-LP-SISC-PROFILE-2026');
            const values = {
                title: 'Perfil académico de Ingeniería de Sistemas', declared_institution: 'Universidad Privada Boliviana',
                source_type: 'OFFICIAL_CAREER_HTML', url: source?.url || 'https://www.upb.edu/programas/pregrado/lp-ingenieria-de-sistemas-computacionales',
                scope: 'Perfil académico y áreas de formación de Ingeniería de Sistemas.', campus: 'La Paz', city: 'La Paz',
                publication_date: '', version: 'Por verificar',
                justification: 'Permite comparar áreas de formación y apoyar la preparación académica con evidencia oficial.',
                limitations_text: 'Ejemplo de validación. Revisar la vigencia antes de una incorporación real.',
            };
            if (kind === 'foreign') Object.assign(values, { declared_institution: 'Universidad de California', url: 'https://www.berkeley.edu/academics/', campus: 'Berkeley', city: 'California' });
            if (kind === 'mismatch') Object.assign(values, { declared_institution: 'Universidad Mayor de San Andrés', url: 'https://www.upb.edu/programas/pregrado/lp-ingenieria-de-sistemas-computacionales' });
            if (kind === 'review') Object.assign(values, {
                title: 'Página institucional de la Universidad Privada Boliviana',
                source_type: 'OFFICIAL_UNIVERSITY_PAGE', url: 'https://www.upb.edu/',
                scope: 'Página institucional para localizar documentación académica oficial.',
                campus: 'Nacional', city: 'Por verificar',
                limitations_text: 'Demostración: verificar sede, vigencia y documentos antes de una incorporación real.',
            });
            for (const [name, value] of Object.entries(values)) this.$refs.sourceForm.elements[name].value = value;
            this.invalidateAnalysis();
            this.schedulePreview();
            this.touched = Object.fromEntries(Object.keys(values).map(name => [name, true]));
            this.examplesOpen = false;
            this.fichaManual = true;
            this.fichaExpanded = true;
            this.$nextTick(() => { this.$refs.sourceForm.elements.url.focus(); this.$refs.sourceForm.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
        },
        safeLink(value) {
            try { const url = new URL(value); return url.protocol === 'https:' && !url.username && !url.password ? url.href : null; } catch { return null; }
        },
        trapDialog(event) {
            if (event.key !== 'Tab') return;
            const dialog = [...document.querySelectorAll('[data-dialogo-conocimiento][role="dialog"]')].find(element => element.getClientRects().length);
            if (!dialog) return;
            const items = [...dialog.querySelectorAll('button, a[href], input, select, textarea, [tabindex="0"]')].filter(element => !element.disabled && element.getClientRects().length);
            const first = items[0];
            const last = items.at(-1);
            if (!dialog.contains(document.activeElement)) { event.preventDefault(); first?.focus(); return; }
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        },
        get detailPublished() {
            return this.detailKind === 'proposal'
                && (this.sources || []).some(source => source.source_id === this.detail?.proposal_id);
        },
        openDetail(kind, item) {
            this.detailKind = kind;
            this.detail = item;
            this.extraction = null;
            this.extractionError = '';
            this.showAllFacts = false;
            this.$el.querySelector('[name="review_note"]')?.form.reset();
            this.detailOpen = true;
        },
        reviewUrl(id) { return this.reviewUrlTemplate.replace('__PROPOSAL__', id || ''); },
        async extractApproved(saved = false) {
            if (this.extracting || this.detailKind !== 'proposal' || this.detail?.status !== 'APROBADA_PENDIENTE_INGESTA'
                || !/^KGI-[A-F0-9]{12}$/.test(this.detail?.proposal_id || '')) return;
            const proposalId = this.detail.proposal_id;
            this.extracting = true;
            this.extraction = null;
            this.extractionError = '';
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 34000);
            try {
                const response = await fetch(this.extractionUrlTemplate.replace('__PROPOSAL__', proposalId), {
                    method: saved ? 'GET' : 'POST',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf},
                    ...(saved ? {} : { body: '{}' }),
                    signal: controller.signal,
                });
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 429) {
                        const retry = Number(response.headers?.get('Retry-After'));
                        const seconds = Number.isFinite(retry) && retry > 0 ? Math.ceil(retry) : 60;
                        throw new Error(`Espera ${seconds} segundos antes de otra extracción. La propuesta aprobada se conserva.`);
                    }
                    throw new Error(response.status === 419
                        ? 'Tu sesión venció. Recarga la página para continuar con la propuesta guardada.'
                        : data.message || 'La fuente no pudo extraerse. Revisa su estado e inténtalo otra vez.');
                }
                if (data.proposal_id !== proposalId) throw new Error('El resultado no corresponde a esta propuesta. Vuelve a comprobar la fuente.');
                if (this.detail?.proposal_id === proposalId) this.extraction = data;
            } catch (error) {
                this.extractionError = error.name === 'AbortError'
                    ? 'La lectura tardó demasiado. Revisa la fuente y vuelve a intentarlo.'
                    : error.message || 'No se completó la extracción. Vuelve a intentarlo.';
            } finally {
                clearTimeout(timeout);
                this.extracting = false;
            }
        },
        isPending(status) { return ['PENDIENTE_REVISION', 'PENDIENTE_VERIFICACION_DE_DOMINIO'].includes(status); },
        humanize(value) { return (value || '').replaceAll('_', ' ').toLowerCase().replace(/^./, char => char.toUpperCase()); },
        matchesSource(source) {
            return (this.sourceGroup === 'TODAS' || this.sourceCategory(source) === this.sourceGroup) && this.sourceMatchesSearch(source);
        },
        normalizeLibraryText(value) {
            return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();
        },
        sourceMatchesSearch(source) {
            const query = this.normalizeLibraryText(this.sourceSearch);
            const career = this.sourceCareerParts(source)?.name || '';
            return !query || this.normalizeLibraryText(`${source.title || ''} ${source.institution || ''} ${career} ${source.source_id || ''}`).includes(query);
        },
        sourceCareerParts(source) {
            if (this.sourceCategory(source) !== 'UNIVERSITARIAS') return null;
            const title = String(source.title || '').trim();
            const declared = [source.career_name, source.career, source.program_name].find(value => typeof value === 'string' && value.trim());
            const parts = title.split(/\s+[—–-]\s+/, 2);
            const documentSuffix = parts.length === 2 && /\b(malla|plan|perfil|areas|áreas|programa)\b/i.test(parts[1]);
            const name = declared?.trim() || (documentSuffix ? parts[0].replace(/^Carrera de\s+/i, '').trim() : '');
            if (name) return { key: String(source.career_id || this.normalizeLibraryText(name)), name, documentTitle: documentSuffix ? parts[1] : title };
            // Una página oficial titulada sólo con la carrera permite usar ese título literal.
            if (/^https:\/\/[^/]+\/(?:carreras|pregrado)\/[^/?#]+\/?(?:[?#].*)?$/i.test(String(source.url || ''))
                && this.normalizeLibraryText(title).replace(/\s+/g, '-') === this.normalizeLibraryText(new URL(source.url).pathname.split('/').filter(Boolean).at(-1))) {
                return { key: String(source.career_id || this.normalizeLibraryText(title)), name: title, documentTitle: 'Página oficial de carrera' };
            }
            return null;
        },
        get filteredSources() { return (this.sources || []).filter(source => this.matchesSource(source)); },
        get libraryInstitutions() {
            const institutions = new Map();
            for (const source of this.filteredSources) {
                const name = String(source.institution || 'Institución sin especificar').trim();
                const key = `${this.sourceCategory(source)}:${this.normalizeLibraryText(name)}`;
                if (!institutions.has(key)) institutions.set(key, { key, name, category: this.sourceCategory(source), careers: [], documents: [], count: 0 });
                const institution = institutions.get(key);
                institution.count++;
                const career = this.sourceCareerParts(source);
                if (!career) { institution.documents.push({ source, title: source.title || 'Documento sin título' }); continue; }
                let group = institution.careers.find(item => item.key === career.key);
                if (!group) { group = { key: career.key, name: career.name, documents: [] }; institution.careers.push(group); }
                group.documents.push({ source, title: career.documentTitle });
            }
            return [...institutions.values()];
        },
        sourceCategory(source) {
            if (String(source.authority_tier || '').startsWith('TIER_1_MINISTRY') || /^ministerio\b/i.test(String(source.institution || '').trim())) return 'MINISTERIALES';
            if (String(source.authority_tier || '').startsWith('TIER_2_OFFICIAL_UNIVERSITY') || /\buniversidad\b/i.test(String(source.institution || ''))) return 'UNIVERSITARIAS';
            return 'OTRAS';
        },
        get visibleDirectoryInstitutions() {
            const institutions = this.directorySummary?.institutions || [];
            return this.directoryExpanded ? institutions : institutions.slice(0, 6);
        },
        get libraryGroups() {
            return [{ value: 'UNIVERSITARIAS', label: 'Documentos universitarios' }, { value: 'MINISTERIALES', label: 'Documentos ministeriales' },
                ...(this.sources.some(source => this.sourceCategory(source) === 'OTRAS') ? [{ value: 'OTRAS', label: 'Otras referencias' }] : []), { value: 'TODAS', label: 'Todos los documentos' }]
                .map(group => ({ ...group, count: this.sources.filter(source => this.sourceMatchesSearch(source) && (group.value === 'TODAS' || this.sourceCategory(source) === group.value)).length }));
        },
    };
}
