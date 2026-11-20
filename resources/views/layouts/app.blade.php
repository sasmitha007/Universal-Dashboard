<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Universal Dashboard')</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #172033; background: #f4f6fa; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e6eaf0; padding: 18px max(24px, calc((100vw - 1100px) / 2)); font-weight: 700; letter-spacing: -.02em; }
        main { width: min(1240px, calc(100% - 40px)); margin: 42px auto; }
        .page-heading { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
        .eyebrow { margin: 0 0 8px; color: #5b67d6; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { font-size: clamp(26px, 4vw, 34px); letter-spacing: -.04em; margin: 0 0 8px; }
        .subtitle { color: #6d7789; margin: 0; }
        .selector { display: grid; gap: 7px; min-width: 210px; color: #566176; font-size: 13px; font-weight: 600; }
        select { width: 100%; padding: 11px 38px 11px 12px; border: 1px solid #d9dfeb; border-radius: 9px; background: #fff; color: #172033; font: inherit; }
        .card { border: 1px solid #e4e8ef; border-radius: 14px; background: white; box-shadow: 0 8px 26px #2739580a; }
        .visual-card { padding: 24px; min-height: 420px; }
        .visual-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        h2 { margin: 0; font-size: 20px; letter-spacing: -.02em; }
        .count { color: #748096; font-size: 13px; }
        .chart-wrap { position: relative; height: 315px; max-width: 720px; margin: auto; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #edf0f5; }
        th { color: #69758b; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
        td { color: #263248; }
        .status { padding: 60px 20px; text-align: center; color: #69758b; }
        .status.error { color: #b42318; }
        details { margin-top: 16px; }
        summary { color: #68758a; cursor: pointer; font-size: 13px; }
        pre { overflow: auto; padding: 16px; border-radius: 9px; background: #101828; color: #d8e0ef; font-size: 12px; }
        .dashboard-panel { margin-bottom: 26px; padding: 20px; }
        .dashboard-panel h2 { margin-bottom: 14px; }
        .dashboard-title-field { width: min(460px, 100%); }
        .widget-editor-list { display: grid; gap: 14px; }
        .widget-editor { padding: 16px; border: 1px solid #e6eaf0; border-radius: 10px; background: #fbfcfe; }
        .widget-editor-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .widget-editor-head h3 { margin: 0; font-size: 15px; }
        .editor-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .field { display: grid; gap: 6px; color: #566176; font-size: 12px; font-weight: 600; }
        .field input, .field select, .field textarea { width: 100%; padding: 9px 10px; border: 1px solid #d9dfeb; border-radius: 7px; background: white; color: #172033; font: inherit; font-weight: 400; }
        .field textarea { min-height: 92px; resize: vertical; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 12px; }
        .field-wide { grid-column: span 2; }
        .editor-actions { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 14px; }
        .button { border: 0; border-radius: 8px; padding: 10px 14px; background: #4f5bd5; color: white; font: inherit; font-weight: 650; cursor: pointer; }
        .button.secondary { background: #edf0f7; color: #33405a; }
        .button.danger { padding: 7px 10px; background: #fff0ef; color: #b42318; }
        .button:disabled { opacity: .55; cursor: wait; }
        .editor-status { min-height: 20px; margin: 10px 0 0; color: #667085; font-size: 13px; }
        .editor-status.error { color: #b42318; }
        .dashboard-heading { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin: 0 0 14px; }
        .dashboard-heading h2 { margin: 0; font-size: 23px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: minmax(320px, auto); gap: 18px; }
        .widget-card { min-width: 0; padding: 20px; }
        .widget-card h3 { overflow-wrap: anywhere; }
        .widget-content { min-height: 250px; }
        .widget-card .chart-wrap { height: 270px; }
        .widget-error { color: #b42318; }
        .empty-table { color: #748096; text-align: center; }
        @media (max-width: 800px) { .editor-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .dashboard-grid { grid-template-columns: minmax(0, 1fr); } .widget-card { grid-column: 1 !important; grid-row: auto !important; } }
        @media (max-width: 640px) { main { width: min(100% - 28px, 1240px); margin: 28px auto; } .page-heading { align-items: stretch; flex-direction: column; } .dashboard-panel, .widget-card { padding: 16px; } .editor-grid { grid-template-columns: minmax(0, 1fr); } .field-wide { grid-column: auto; } .dashboard-heading { align-items: start; flex-direction: column; } .chart-wrap { height: 260px; } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js" defer></script>
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
    @stack('head')
</head>
<body>
    <header class="topbar">Universal Dashboard <span style="font-weight:500;color:#8490a3">/ Prototype</span></header>
    @yield('content')
    @stack('scripts')
</body>
</html>
