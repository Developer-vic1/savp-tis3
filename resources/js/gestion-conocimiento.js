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
        helpOpen: false,
        examplesOpen: false,
        sourceSearch: '',
        serverMessage: '',
        serverErrors: {},
        touched: {},
        values: {},
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
        fichaPreviewOpen: false,
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
            });
            this.clockTimer = setInterval(() => {
                this.clockNow = Date.now();
                if (this.analysisToken && this.clockNow >= this.expiresAt) {
                    this.analysisToken = '';
                    this.analysisInvalidated = true;
                    this.serverMessage = 'El análisis venció. Analice nuevamente para enviar la fuente.';
                }
            }, 1000);
            for (const name of ['analysisOpen', 'detailOpen', 'helpOpen', 'examplesOpen', 'connectionOpen', 'tutorOpen', 'fichaPreviewOpen']) {
                this.$watch(name, open => {
                    if (open) {
                        if (this.savedBodyOverflow === null) this.savedBodyOverflow = document.body.style.overflow;
                        document.body.style.overflow = 'hidden';
                        this.modalTrigger = document.activeElement;
                        this.$nextTick(() => {
                            const dialog = [...this.$el.querySelectorAll('[role="dialog"]')].find(element => element.getClientRects().length);
                            dialog?.querySelector('button')?.focus();
                        });
                    } else {
                        this.modalTrigger?.focus();
                        if (!['analysisOpen', 'detailOpen', 'helpOpen', 'examplesOpen', 'connectionOpen', 'tutorOpen', 'fichaPreviewOpen'].some(key => this[key]) && this.savedBodyOverflow !== null) {
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
        get urlIssue() {
            const value = this.values.url || '';
            if (!value) return 'Ingrese la dirección oficial de la fuente.';
            const parsed = this.parsedUrl;
            if (!parsed || /[\s\\\x00-\x1f\x7f]|%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)/i.test(value)) return 'Use una dirección completa, sin espacios ni caracteres inválidos.';
            if (parsed.protocol !== 'https:') return 'Cambie HTTP por HTTPS; se requiere una conexión segura.';
            if (parsed.username || parsed.password) return 'Quite las credenciales de la URL. Use el enlace público oficial.';
            if (parsed.port && parsed.port !== '443') return 'Use el puerto HTTPS estándar, sin puertos personalizados.';
            const host = parsed.hostname.toLowerCase().replace(/\.$/, '');
            if (host === 'localhost' || /^[\d.]+$/.test(host) || host.includes(':') || host.startsWith('[')) return 'Una dirección IP o local no es una fuente universitaria.';
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
            if (field?.required && !value) return 'Complete este campo para continuar.';
            if (value && field?.minLength > 0 && value.length < field.minLength) return `Escriba al menos ${field.minLength} caracteres.`;
            if (field?.maxLength > 0 && value.length > field.maxLength) return `Use como máximo ${field.maxLength} caracteres.`;
            if (name === 'url') return this.urlIssue || (this.preview && !this.preview.can_use ? this.preview.message : '');
            if (name === 'title' && this.preview?.can_use && this.normalizeTitle(value) !== this.normalizeTitle(this.preview.title)) return 'Use el título detectado en la publicación oficial.';
            if (name === 'declared_institution') return this.institutionIssue;
            if (name === 'publication_date') return this.dateIssue;
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
        characterCount(name, maximum) { return `${(this.values[name] || '').length} / ${maximum}`; },
        get passedChecks() { return this.frontendChecks.filter(item => item.ok).length; },
        get validationScore() { return Math.round(this.passedChecks / Math.max(1, this.frontendChecks.length) * 100); },
        get workflowSteps() {
            return [
                { number: 1, title: 'Completar ficha', help: 'Describa universidad, fuente y aporte.', state: this.validationScore === 100 ? 'kg-step-complete' : 'kg-step-current' },
                { number: 2, title: 'Analizar fuente', help: this.analysisToken ? 'Análisis vigente. Puede continuar.' : 'Revise controles y corrija observaciones.', state: this.analysisToken ? 'kg-step-complete' : this.validationScore === 100 ? 'kg-step-current' : '' },
                { number: 3, title: 'Enviar a revisión', help: 'Administración revisa antes de la ingesta.', state: this.analysisToken ? 'kg-step-current' : '' },
                { number: 4, title: 'Revisión administrativa', help: 'Aprobar no activa la fuente en el tutor.', state: '' },
                { number: 5, title: 'Ingesta verificada', help: 'Instantánea, hash y corpus antes de citar.', state: '' },
            ];
        },
        get analysisHeadline() {
            if (this.analysis?.readiness === 'BLOQUEADA') return 'Fuente bloqueada: revise los controles señalados';
            if (this.analysis?.readiness === 'REQUIERE_REVISION') return 'Hay observaciones que necesitan revisión humana';
            return 'La ficha puede enviarse a revisión administrativa';
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
        invalidateAnalysis(event) {
            if (this.analysisToken || this.analysis) this.analysisInvalidated = true;
            this.analysisToken = '';
            this.serverMessage = '';
            this.serverErrors = {};
            this.requestVersion++;
            this.requestController?.abort();
            this.refreshValues();
            if (event?.target?.name === 'url') this.schedulePreview();
        },
        schedulePreview() {
            this.suggestionSelections = {};
            this.suggestionMessage = '';
            clearTimeout(this.previewTimer);
            this.previewVersion++;
            this.previewController?.abort();
            this.previewChecking = false;
            this.preview = null;
            this.previewError = '';
            this.previewDueAt = 0;
            if (!this.previewUrl || this.canPropose === false || !this.values.url || this.urlIssue) return;
            this.previewDueAt = Date.now() + 10000;
            this.previewTimer = setTimeout(() => this.inspectUrl(), 10000);
        },
        get previewWaitingMessage() {
            return this.previewDueAt ? `Comprobación automática en ${Math.min(10, Math.max(0, Math.ceil((this.previewDueAt - this.clockNow) / 1000)))} s después de dejar de escribir.` : 'Pegue la URL: se comprobará tras 10 segundos o al pulsar el botón.';
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
                if (!response.ok) throw new Error(response.status === 429 ? 'Espere un minuto: se permiten seis comprobaciones por minuto.' : response.status === 419 ? 'Su sesión venció. Recargue la página.' : Object.values(body.errors || {}).flat()[0] || body.message || 'No se pudo comprobar la URL.');
                if (body.requested_url !== requestedUrl) throw new Error('La respuesta no corresponde a la URL actual. Compruebe nuevamente.');
                this.preview = body;
                if (body.can_use) this.applyDetectedFields(false);
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
                if (field && value && (replace || !field.value.trim())) field.value = value;
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
            return {
                OFFICIAL_CURRICULUM_PDF: 'Malla o plan curricular PDF',
                OFFICIAL_CAREER_HTML: 'Página oficial de carrera',
                OFFICIAL_REGULATION_PDF: 'Reglamento oficial PDF',
                OFFICIAL_UNIVERSITY_PAGE: 'Otra página universitaria oficial',
            }[value] || 'Tipo pendiente de revisión';
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
                this.analyzing = false;
            }
        },
        guardSubmission(event) {
            if (this.canPropose === false || !this.analysisToken || Date.now() >= this.expiresAt || !this.validateForm()) {
                event.preventDefault();
                this.serverMessage ||= 'Analice nuevamente la fuente para enviarla a revisión.';
                return;
            }
            this.submitting = true;
        },
        resetForm() {
            this.fichaPreviewOpen = false;
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
            this.refreshValues();
        },
        async checkConnection() {
            if (this.checkingConnection) return;
            this.connectionOpen = true;
            this.checkingConnection = true;
            this.connectionError = '';
            this.connection = null;
            try {
                const response = await fetch(this.connectionUrl, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(35000) });
                if (!response.ok) throw new Error('No fue posible comprobar la conexión. Revise su sesión o vuelva a intentar.');
                this.connection = await response.json();
            } catch {
                this.connectionError = 'No se pudo completar la comprobación. La conexión no se considera verificada.';
            } finally { this.checkingConnection = false; }
        },
        async askTutor() {
            const question = this.tutorQuestion.trim();
            if (this.askingTutor || question.length < 2 || question.length > 2000) return;
            this.askingTutor = true;
            this.tutorError = '';
            this.tutorAnswer = null;
            try {
                const response = await fetch(this.tutorUrl, {
                    method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ question, conversation_history: this.tutorHistory.slice(-8) }), signal: AbortSignal.timeout(35000),
                });
                const body = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(response.status === 419 ? 'Su sesión venció. Recargue la página.' : response.status === 429 ? 'Espere un minuto antes de realizar otra consulta.' : body.message || 'No fue posible consultar el tutor.');
                this.tutorAnswer = body;
                this.tutorHistory = [...this.tutorHistory, { role: 'user', content: question }, { role: 'assistant', content: body.answer.slice(0, 2000) }].slice(-8);
            } catch (error) {
                this.tutorError = error instanceof TypeError || error.name === 'TimeoutError' ? 'No se pudo conectar con el tutor. Su pregunta permanece en el formulario.' : error.message;
            } finally { this.askingTutor = false; }
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
            this.$nextTick(() => { this.$refs.sourceForm.elements.url.focus(); this.$refs.sourceForm.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
        },
        safeLink(value) {
            try { const url = new URL(value); return url.protocol === 'https:' && !url.username && !url.password ? url.href : null; } catch { return null; }
        },
        trapDialog(event) {
            if (event.key !== 'Tab') return;
            const dialog = [...this.$el.querySelectorAll('[role="dialog"]')].find(element => element.getClientRects().length);
            if (!dialog) return;
            const items = [...dialog.querySelectorAll('button, a[href], input, select, textarea, [tabindex="0"]')].filter(element => !element.disabled && element.getClientRects().length);
            const first = items[0];
            const last = items.at(-1);
            if (!dialog.contains(document.activeElement)) { event.preventDefault(); first?.focus(); return; }
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        },
        openDetail(kind, item) {
            this.detailKind = kind;
            this.detail = item;
            this.$el.querySelector('[name="review_note"]')?.form.reset();
            this.detailOpen = true;
        },
        reviewUrl(id) { return this.reviewUrlTemplate.replace('__PROPOSAL__', id || ''); },
        isPending(status) { return ['PENDIENTE_REVISION', 'PENDIENTE_VERIFICACION_DE_DOMINIO'].includes(status); },
        humanize(value) { return (value || '').replaceAll('_', ' ').toLowerCase().replace(/^./, char => char.toUpperCase()); },
        matchesSource(source) {
            const query = this.sourceSearch.trim().toLowerCase();
            return !query || `${source.title} ${source.institution} ${source.source_id}`.toLowerCase().includes(query);
        },
    };
}
