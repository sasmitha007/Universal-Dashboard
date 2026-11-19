@extends('layouts.app')

@section('title', 'Universal Dashboard Prototype')

@section('content')
<main>
    <div class="page-heading">
        <div>
            <h1>Universal Dashboard</h1>
            <p class="subtitle">A configurable renderer for structured data.</p>
        </div>
        <label class="selector" for="dataset-select">Sample dataset
            <select id="dataset-select" aria-label="Select a sample dataset">
                @foreach ($datasetIds as $datasetId)
                    <option value="{{ $datasetId }}" @selected($datasetId === $initialDataset)>{{ ucfirst($datasetId === 'support' ? 'Customer support' : $datasetId) }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <x-universal-dashboard />
</main>
@endsection

@push('scripts')
<script>
(() => {
    const selector = document.getElementById('dataset-select');
    const title = document.getElementById('visual-title');
    const count = document.getElementById('record-count');
    const area = document.getElementById('visualization');
    const summary = document.getElementById('configuration-summary');
    let activeChart = null;

    function showStatus(message, isError = false) {
        area.replaceChildren();
        const messageNode = document.createElement('p');
        messageNode.className = `status${isError ? ' error' : ''}`;
        messageNode.textContent = message;
        area.append(messageNode);
    }

    function renderList(data, configuration) {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-wrap';
        const table = document.createElement('table');
        const head = document.createElement('thead');
        const headerRow = document.createElement('tr');
        configuration.columns.forEach(column => {
            const th = document.createElement('th');
            th.scope = 'col';
            th.textContent = column.label;
            headerRow.append(th);
        });
        head.append(headerRow);
        const body = document.createElement('tbody');
        data.forEach(record => {
            const row = document.createElement('tr');
            configuration.columns.forEach(column => {
                const cell = document.createElement('td');
                const value = record[column.key];
                cell.textContent = value === null || value === undefined ? '' : String(value);
                row.append(cell);
            });
            body.append(row);
        });
        table.append(head, body);
        wrapper.append(table);
        area.append(wrapper);
    }

    function validate(payload) {
        const configuration = payload.configuration;
        if (!configuration || !['pie', 'bar', 'list'].includes(configuration.type) || typeof configuration.title !== 'string') {
            throw new Error('The server returned an incomplete or unsupported configuration.');
        }
        if (!Array.isArray(payload.data)) throw new Error('The server returned invalid dataset records.');
        if (configuration.type === 'list') {
            if (!Array.isArray(configuration.columns) || configuration.columns.length === 0) throw new Error('List configuration has no columns.');
            payload.data.forEach(record => configuration.columns.forEach(column => {
                if (!column.key || !Object.hasOwn(record, column.key) || record[column.key] === null) throw new Error('A configured list value is missing.');
            }));
            return;
        }
        if (typeof configuration.labelKey !== 'string' || typeof configuration.valueKey !== 'string') throw new Error('Chart configuration is missing its label or value field.');
        payload.data.forEach(record => {
            const value = record[configuration.valueKey];
            if (!Object.hasOwn(record, configuration.labelKey) || record[configuration.labelKey] === '' || !Number.isFinite(Number(value))) throw new Error('A chart label or numeric value is invalid.');
            if (configuration.type === 'pie' && Number(value) < 0) throw new Error('Pie chart values cannot be negative.');
        });
    }

    function render(payload) {
        validate(payload);
        if (activeChart) { activeChart.destroy(); activeChart = null; }
        title.textContent = payload.configuration.title;
        count.textContent = `${payload.data.length} ${payload.data.length === 1 ? 'record' : 'records'}`;
        summary.textContent = JSON.stringify(payload.configuration, null, 2);
        area.replaceChildren();
        if (payload.data.length === 0) { showStatus('No records are available for this dataset.'); return; }
        if (payload.configuration.type === 'list') { renderList(payload.data, payload.configuration); return; }
        if (typeof Chart === 'undefined') throw new Error('Chart.js could not be loaded. Check your network connection and try again.');
        const chartWrap = document.createElement('div');
        chartWrap.className = 'chart-wrap';
        const canvas = document.createElement('canvas');
        canvas.setAttribute('role', 'img');
        canvas.setAttribute('aria-label', payload.configuration.title);
        chartWrap.append(canvas);
        area.append(chartWrap);
        activeChart = new Chart(canvas, {
            type: payload.configuration.type,
            data: {
                labels: payload.data.map(record => String(record[payload.configuration.labelKey])),
                datasets: [{
                    label: payload.configuration.title,
                    data: payload.data.map(record => Number(record[payload.configuration.valueKey])),
                    backgroundColor: ['#5b67d6', '#36a68a', '#f3a44b', '#d96c83', '#69a8d6', '#9474c9'],
                    borderWidth: 0,
                    borderRadius: payload.configuration.type === 'bar' ? 6 : 0,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, ...(payload.configuration.type === 'bar' ? { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } : {}) },
        });
    }

    async function loadDataset(identifier) {
        if (activeChart) { activeChart.destroy(); activeChart = null; }
        showStatus('Loading dataset…');
        try {
            const response = await fetch(`/dashboard/data/${encodeURIComponent(identifier)}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || `Request failed (${response.status}).`);
            render(payload);
        } catch (error) {
            showStatus(error.message || 'Unable to load this dataset. Please try again.', true);
        }
    }

    selector.addEventListener('change', event => loadDataset(event.target.value));
    loadDataset(selector.value);
})();
</script>
@endpush
