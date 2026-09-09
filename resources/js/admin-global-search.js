window.adminGlobalSearch = function ($wire) {
    return {
        query: '',
        open: false,
        navigation: [],
        timer: null,

        init() {
            this.collectNavigation();
        },

        collectNavigation() {
            const sidebar =
                document.querySelector('[data-admin-sidebar]')
                ?? document.querySelector('aside');

            if (!sidebar) {
                this.navigation = [];
                return;
            }

            const seen = new Set();

            this.navigation = Array
                .from(sidebar.querySelectorAll('a[href]'))
                .map(link => {
                    const href = link.href;
                    const label = (
                        link.dataset.searchLabel
                        ?? link.textContent
                        ?? ''
                    )
                        .replace(/\s+/g, ' ')
                        .trim();

                    return {
                        href,
                        label,
                        keywords:
                            link.dataset.searchKeywords ?? '',
                    };
                })
                .filter(item => {
                    if (!item.href || !item.label) {
                        return false;
                    }

                    if (
                        item.href.endsWith('#')
                        || item.href.startsWith('javascript:')
                    ) {
                        return false;
                    }

                    const key = `${item.href}|${item.label}`;

                    if (seen.has(key)) {
                        return false;
                    }

                    seen.add(key);

                    return true;
                });
        },

        search() {
            this.open = true;

            clearTimeout(this.timer);

            this.timer = setTimeout(() => {
                $wire.set('query', this.query);
            }, 220);
        },

        get filteredNavigation() {
            const term = this.normalize(this.query);

            if (term.length < 2) {
                return [];
            }

            return this.navigation
                .filter(item => {
                    const target = this.normalize(
                        `${item.label} ${item.keywords}`
                    );

                    return target.includes(term);
                })
                .slice(0, 6);
        },

        normalize(value) {
            return String(value ?? '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        },

        go(href) {
            window.location.assign(href);
        },

        clear() {
            this.query = '';
            this.open = false;

            $wire.clearSearch();
        },
    };
};