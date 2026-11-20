(() => {
    'use strict';

    const app = document.getElementById('dashboard-app');
    if (!app) {
        return;
    }

    const editorList = document.getElementById('widget-editor-list');
    const editorStatus = document.getElementById('editor-status');
    const dashboardTitleInput = document.getElementById('dashboard-title-input');
    const dashboardTitle = document.getElementById('rendered-dashboard-title');
    const widgetCount = document.getElementById('widget-count');
    const dashboardGrid = document.getElementById('dashboard-grid');
    const applyButton = document.getElementById('apply-dashboard-button');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const charts = new Map();
    const editorInputErrors = new Set();
    let dashboard = JSON.parse(app.dataset.initialDashboard);
    let widgetSequence = 1;

    function setEditorStatus(message, isError = false) {
        editorStatus.textContent = message;
        editorStatus.classList.toggle('error', isError);
    }

    function makeField(labelText, control, wide = false) {
        const label = document.createElement('label');
        label.className = `field${wide ? ' field-wide' : ''}`;
        label.append(document.createTextNode(labelText));
        label.append(control);
        return label;
    }

    function makeInput(value, type = 'text', attributes = {}) {
        const input = document.createElement('input');
        input.type = type;
        input.value = value ?? '';
        Object.entries(attributes).forEach(([name, attributeValue]) => input.setAttribute(name, String(attributeValue)));
        return input;
    }

    function makeTextarea(value) {
        const textarea = document.createElement('textarea');
        textarea.spellcheck = false;
        textarea.value = JSON.stringify(value, null, 2);
        return textarea;
    }

    function renderEditor() {
        editorInputErrors.clear();
        dashboardTitleInput.value = dashboard.title;
        editorList.replaceChildren();

        dashboard.widgets.forEach((widget, index) => {
            const editor = document.createElement('section');
            editor.className = 'widget-editor';

            const editorHeader = document.createElement('div');
            editorHeader.className = 'widget-editor-head';
            const heading = document.createElement('h3');
            heading.textContent = `Widget ${index + 1}`;
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'button danger';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', () => {
                dashboard.widgets.splice(index, 1);
                renderEditor();
                setEditorStatus('Widget removed. Apply the configuration to render the change.');
            });
            editorHeader.append(heading, removeButton);

            const fields = document.createElement('div');
            fields.className = 'editor-grid';

            const idInput = makeInput(widget.id, 'text', { maxlength: 64, required: true });
            idInput.addEventListener('input', () => { widget.id = idInput.value; });
            fields.append(makeField('Widget ID', idInput));

            const titleInput = makeInput(widget.title, 'text', { maxlength: 120, required: true });
            titleInput.addEventListener('input', () => { widget.title = titleInput.value; });
            fields.append(makeField('Widget title', titleInput));

            const typeSelect = document.createElement('select');
            ['pie', 'bar', 'line', 'table'].forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type.charAt(0).toUpperCase() + type.slice(1);
                typeSelect.append(option);
            });
            typeSelect.value = widget.visualization.type;
            typeSelect.addEventListener('change', () => {
                const type = typeSelect.value;
                if (type === 'table') {
                    widget.visualization = { type };
                } else {
                    const keys = Object.keys(widget.data[0] ?? {});
                    widget.visualization = {
                        type,
                        labelKey: keys[0] ?? 'name',
                        valueKey: keys[1] ?? 'value',
                    };
                }
                renderEditor();
            });
            fields.append(makeField('Visualization', typeSelect));

            const columnInput = makeInput(widget.position.column, 'number', { min: 1, max: 2, required: true });
            columnInput.addEventListener('input', () => { widget.position.column = Number(columnInput.value); });
            fields.append(makeField('Grid column (1–2)', columnInput));

            const rowInput = makeInput(widget.position.row, 'number', { min: 1, max: 12, required: true });
            rowInput.addEventListener('input', () => { widget.position.row = Number(rowInput.value); });
            fields.append(makeField('Grid row (1–12)', rowInput));

            if (['pie', 'bar', 'line'].includes(widget.visualization.type)) {
                const labelKeyInput = makeInput(widget.visualization.labelKey, 'text', { maxlength: 64, required: true });
                labelKeyInput.addEventListener('input', () => { widget.visualization.labelKey = labelKeyInput.value; });
                fields.append(makeField('Label field', labelKeyInput));

                const valueKeyInput = makeInput(widget.visualization.valueKey, 'text', { maxlength: 64, required: true });
                valueKeyInput.addEventListener('input', () => { widget.visualization.valueKey = valueKeyInput.value; });
                fields.append(makeField('Value field', valueKeyInput));
            } else {
                const columnsInput = makeTextarea(widget.visualization.columns ?? []);
                columnsInput.addEventListener('change', () => {
                    try {
                        const columns = JSON.parse(columnsInput.value);
                        if (!Array.isArray(columns)) {
                            throw new Error('Columns must be a JSON array.');
                        }
                        if (columns.length === 0) {
                            delete widget.visualization.columns;
                        } else {
                            widget.visualization.columns = columns;
                        }
                        editorInputErrors.delete(`columns-${index}`);
                        setEditorStatus('Table columns updated in the editor.');
                    } catch (error) {
                        editorInputErrors.add(`columns-${index}`);
                        setEditorStatus(error.message, true);
                    }
                });
                fields.append(makeField('Columns JSON (optional; inferred from first record)', columnsInput, true));
            }

            const dataInput = makeTextarea(widget.data);
            dataInput.addEventListener('change', () => {
                try {
                    const data = JSON.parse(dataInput.value);
                    if (!Array.isArray(data)) {
                        throw new Error('Widget data must be a JSON array.');
                    }
                    widget.data = data;
                    editorInputErrors.delete(`data-${index}`);
                    setEditorStatus('Widget data updated in the editor.');
                } catch (error) {
                    editorInputErrors.add(`data-${index}`);
                    setEditorStatus(error.message, true);
                }
            });
            fields.append(makeField('Widget data (JSON array)', dataInput, true));

            editor.append(editorHeader, fields);
            editorList.append(editor);
        });
    }

    function renderTable(widget, container) {
        const columns = widget.visualization.columns ?? Object.keys(widget.data[0] ?? {}).map(key => ({ key, label: key }));
        if (columns.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'empty-table';
            emptyMessage.textContent = 'No records or table columns are configured.';
            container.append(emptyMessage);
            return;
        }

        const table = document.createElement('table');
        const tableHead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        columns.forEach(column => {
            const heading = document.createElement('th');
            heading.scope = 'col';
            heading.textContent = column.label ?? column.key;
            headerRow.append(heading);
        });
        tableHead.append(headerRow);

        const tableBody = document.createElement('tbody');
        widget.data.forEach(record => {
            const row = document.createElement('tr');
            columns.forEach(column => {
                const cell = document.createElement('td');
                const value = record[column.key];
                cell.textContent = value === null || value === undefined ? '' : String(value);
                row.append(cell);
            });
            tableBody.append(row);
        });

        table.append(tableHead, tableBody);
        const wrapper = document.createElement('div');
        wrapper.className = 'table-wrap';
        wrapper.append(table);
        container.append(wrapper);
    }

    function chartRenderer(type) {
        return (widget, container) => {
            if (typeof window.Chart === 'undefined') {
                throw new Error('Chart.js did not load. Check network access to jsDelivr.');
            }

            const chartWrap = document.createElement('div');
            chartWrap.className = 'chart-wrap';
            const canvas = document.createElement('canvas');
            canvas.setAttribute('role', 'img');
            canvas.setAttribute('aria-label', widget.title);
            chartWrap.append(canvas);
            container.append(chartWrap);

            const chart = new window.Chart(canvas, {
                type,
                data: {
                    labels: widget.data.map(record => String(record[widget.visualization.labelKey])),
                    datasets: [{
                        label: widget.title,
                        data: widget.data.map(record => Number(record[widget.visualization.valueKey])),
                        backgroundColor: type === 'pie'
                            ? ['#5b67d6', '#36a68a', '#f3a44b', '#d96c83', '#69a8d6', '#9474c9']
                            : '#5b67d6',
                        borderColor: type === 'line' ? '#5b67d6' : undefined,
                        borderWidth: type === 'line' ? 3 : 0,
                        borderRadius: type === 'bar' ? 6 : 0,
                        tension: type === 'line' ? 0.25 : 0,
                        fill: false,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    ...(type === 'bar' || type === 'line'
                        ? { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                        : {}),
                },
            });
            charts.set(widget.id, chart);
        };
    }

    const visualizationRegistry = Object.freeze({
        pie: chartRenderer('pie'),
        bar: chartRenderer('bar'),
        line: chartRenderer('line'),
        table: renderTable,
    });

    function destroyCharts() {
        charts.forEach(chart => chart.destroy());
        charts.clear();
    }

    function renderDashboard() {
        destroyCharts();
        dashboardTitle.textContent = dashboard.title;
        widgetCount.textContent = `${dashboard.widgets.length} ${dashboard.widgets.length === 1 ? 'widget' : 'widgets'}`;
        dashboardGrid.replaceChildren();

        dashboard.widgets.forEach(widget => {
            const card = document.createElement('article');
            card.className = 'card widget-card';
            card.dataset.widgetId = widget.id;
            card.style.gridColumnStart = String(widget.position.column);
            card.style.gridRowStart = String(widget.position.row);

            const heading = document.createElement('h3');
            heading.textContent = widget.title;
            const content = document.createElement('div');
            content.className = 'widget-content';
            card.append(heading, content);
            dashboardGrid.append(card);

            try {
                if (widget.data.length === 0) {
                    const emptyMessage = document.createElement('p');
                    emptyMessage.className = 'status';
                    emptyMessage.textContent = 'No records are available for this widget.';
                    content.append(emptyMessage);
                    return;
                }

                const render = visualizationRegistry[widget.visualization.type];
                if (!render) {
                    throw new Error(`Unsupported visualization: ${widget.visualization.type}`);
                }
                render(widget, content);
            } catch (error) {
                const errorMessage = document.createElement('p');
                errorMessage.className = 'widget-error';
                errorMessage.textContent = error.message;
                content.append(errorMessage);
            }
        });
    }

    dashboardTitleInput.addEventListener('input', () => {
        dashboard.title = dashboardTitleInput.value;
    });

    document.getElementById('add-widget-button').addEventListener('click', () => {
        if (dashboard.widgets.length >= 24) {
            setEditorStatus('A dashboard can contain at most 24 widgets.', true);
            return;
        }

        const index = dashboard.widgets.length;
        const keys = ['name', 'value'];
        let id;
        do {
            id = `widget-${Date.now().toString(36)}-${widgetSequence}`;
            widgetSequence += 1;
        } while (dashboard.widgets.some(widget => widget.id === id));
        dashboard.widgets.push({
            id,
            title: 'New widget',
            data: [{ name: 'Example', value: 1 }],
            visualization: { type: 'bar', labelKey: keys[0], valueKey: keys[1] },
            position: { column: (index % 2) + 1, row: Math.floor(index / 2) + 1 },
        });
        renderEditor();
        setEditorStatus('Widget added. Edit its fields and apply the configuration.');
    });

    applyButton.addEventListener('click', async () => {
        if (editorInputErrors.size > 0) {
            setEditorStatus('Fix invalid JSON in the widget data or columns before applying.', true);
            return;
        }
        applyButton.disabled = true;
        setEditorStatus('Validating dashboard configuration…');

        try {
            const response = await fetch(app.dataset.submitUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ dashboard }),
            });
            const result = await response.json();
            if (!response.ok) {
                const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
                throw new Error(validationMessage ?? result.message ?? `Request failed with status ${response.status}.`);
            }

            dashboard = result.dashboard;
            renderEditor();
            renderDashboard();
            setEditorStatus('Configuration validated and rendered.');
        } catch (error) {
            setEditorStatus(error.message || 'Could not apply the dashboard configuration.', true);
        } finally {
            applyButton.disabled = false;
        }
    });

    renderEditor();
    renderDashboard();
})();
