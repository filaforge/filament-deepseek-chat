export default function clipboardHistory(options = {}) {
    const storageKey = 'filaforge.clipboard.history';
    const maxItems = Number(options.max ?? 10);

    return {
        input: '',
        selected: '',
        history: [],

        init() {
            try {
                const raw = localStorage.getItem(storageKey);
                this.history = Array.isArray(JSON.parse(raw)) ? JSON.parse(raw) : [];
            } catch (_) {
                this.history = [];
            }
            if (this.history.length > 0) this.selected = this.history[0] ?? '';
        },

        persist() {
            try { localStorage.setItem(storageKey, JSON.stringify(this.history.slice(0, maxItems))); } catch (_) {}
        },

        add() {
            const value = (this.input || '').trim();
            if (!value) return;
            // Deduplicate, move to top
            this.history = [value, ...this.history.filter(v => v !== value)].slice(0, maxItems);
            this.selected = value;
            this.input = '';
            this.persist();
        },

        copySelected() {
            const value = (this.selected || '').trim();
            if (!value || !navigator.clipboard) return;
            navigator.clipboard.writeText(value).catch(() => {});
        },

        clear() {
            this.history = [];
            this.selected = '';
            try { localStorage.removeItem(storageKey); } catch (_) {}
        },
    }
}



