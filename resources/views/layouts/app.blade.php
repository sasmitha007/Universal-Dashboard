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
        main { width: min(1100px, calc(100% - 40px)); margin: 42px auto; }
        .page-heading { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
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
        @media (max-width: 640px) { main { width: min(100% - 28px, 1100px); margin: 28px auto; } .page-heading { align-items: stretch; flex-direction: column; } .selector { min-width: 0; } .visual-card { padding: 17px; min-height: 350px; } .chart-wrap { height: 260px; } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js" defer></script>
    @stack('head')
</head>
<body>
    <header class="topbar">Universal Dashboard <span style="font-weight:500;color:#8490a3">/ Prototype</span></header>
    @yield('content')
    @stack('scripts')
</body>
</html>
