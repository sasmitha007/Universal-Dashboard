@props(['dashboard'])

<div id="dashboard-app"
    data-initial-dashboard="{{ json_encode($dashboard, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) }}"
    data-submit-url="{{ route('dashboard.configuration') }}">
    <section class="card dashboard-panel" aria-labelledby="editor-title">
        <h2 id="editor-title">Configure dashboard</h2>
        <label class="field dashboard-title-field" for="dashboard-title-input">Dashboard title
            <input id="dashboard-title-input" type="text" maxlength="120" autocomplete="off">
        </label>
        <div id="widget-editor-list" class="widget-editor-list" aria-label="Dashboard widgets"></div>
        <div class="editor-actions">
            <button class="button secondary" id="add-widget-button" type="button">Add widget</button>
            <button class="button" id="apply-dashboard-button" type="button">Apply configuration</button>
        </div>
        <p id="editor-status" class="editor-status" role="status" aria-live="polite"></p>
    </section>

    <section aria-labelledby="rendered-dashboard-title">
        <header class="dashboard-heading">
            <h2 id="rendered-dashboard-title"></h2>
            <span id="widget-count" class="count"></span>
        </header>
        <div id="dashboard-grid" class="dashboard-grid" aria-live="polite"></div>
    </section>
</div>
