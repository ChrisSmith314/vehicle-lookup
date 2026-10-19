import Mustache from 'mustache';

export class VehicleLookup {
    constructor() {
        this.init();
    }

    init(): void {
        window.onload = this.onLoad();
    }

    form: HTMLFormElement;

    searchInput: HTMLElement;
    sessionStart: HTMLElement;
    sessionEnd: HTMLElement;
    useActualEnd: HTMLElement;

    onLoad(): void {

        this.form = document.getElementById('sessionSearchForm');

        this.searchInput = document.getElementById('vehicleSelect');
        this.sessionStart = document.getElementById('sessionStart');
        this.sessionEnd = document.getElementById('sessionEnd');
        this.useActualEnd = document.getElementById('useActualEnd');

        // Setup Listeners
        this.searchInput.addEventListener('input', (e) => this.compileSearch());
        this.sessionStart.addEventListener('change', (e) => this.compileSearch());
        this.sessionEnd.addEventListener('change', (e) => this.compileSearch());
        this.useActualEnd.addEventListener('change', (e) => this.compileSearch());
    }

    compileSearch() {
        let query = {
            'vrm': this.searchInput.value,
            'fuzzy': true,
            'period_from': this.sessionStart.value,
            'period_to': this.sessionEnd.value,
            'use_actual_end': this.useActualEnd.value,
            'api_key': this.form.getAttribute('data-api-key')
        }

        this.searchQuery(query);
    }

    /** Vehicle Lookup */

    searchQuery(query) {
        var queryString = new URLSearchParams(query).toString();
        fetch(`vehicle_lookup.php?${queryString}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();


                return sessions; // Parses JSON response automatically
            })
            .then(data => {

                let rawSessions = data.data || [];

                let formattedSessions = rawSessions.map((session: any) => ({
                    ...session,
                    is_paid: session.session_type === 'paid',
                    is_free: session.session_type === 'free',
                    is_permit: session.session_type === 'permit',
                    is_active: session.session_status === 'active',
                    is_overstay: session.session_status === 'overstay',
                    is_completed: session.session_status === 'completed',

                    session_start: this.formatDate(session.session_start),
                    session_agreed_end: this.formatDate(session.session_agreed_end),
                    session_end: session.session_end ? this.formatDate(session.session_end) : null
                }));

                this.applyTemplates({
                    'sessions': formattedSessions
                });
            })
            .catch(error => {
                console.error('AJAX Error:', error);
            });
    }

    template;

    applyTemplates(json_data) {
        this.template = this.template ?? document.getElementById('template-vehicle-results').innerHTML;

        const renderedHtml = Mustache.render(this.template, json_data);

        document.getElementById('vehicleBody').innerHTML = renderedHtml;
    }

    /** Additional Functions */
    formatDate(timestamp: any): string {
        if (!timestamp) return '';

        // If it's a SQL datetime string (e.g. "2026-10-17 10:00:00"), strip the seconds
        if (typeof timestamp === 'string') {
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(timestamp)) {
                return timestamp.substring(0, 16);
            }
            if (isNaN(Number(timestamp))) {
                return timestamp; // Return as-is if it's another string format
            }
            timestamp = Number(timestamp);
        }

        // Handle Unix timestamp (seconds or milliseconds)
        const date = new Date(timestamp > 10000000000 ? timestamp : timestamp * 1000);
        if (isNaN(date.getTime())) return timestamp;

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}`;
    }
}
