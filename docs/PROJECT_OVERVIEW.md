# Project overview

## Purpose

The Universal Dashboard Component demonstrates one reusable presentation flow for structured records: a provider returns records plus a separate visualization configuration; the browser chooses a renderer from `configuration.type`. The prototype's examples are sales totals (pie), weekday customer-chat counts (bar), and inventory rows (table).

This addresses a small integration problem: the display code can refer to configured field names rather than embedding product, weekday, or inventory logic into the chart/table render functions. It is a demonstration, not a general dashboard platform.

## Intended objective and confirmed scope

The objective is an application-independent visualization component. The current implementation has a generic chart/table validation and rendering path, but the surrounding application is not fully data-independent: `DashboardDataProvider::all()` holds the three fixed examples, `DashboardController::index()` chooses `sales` as the starting value, and the Blade selector has a special label for the `support` identifier. A new dataset currently requires a provider code change. The component's markup itself has no business-specific data fields and is shared by all examples.

Confirmed functionality:

- Laravel page at `/` with one dataset selector.
- JSON endpoint at `/dashboard/data/{dataset}` for the three allowlisted identifiers.
- Separate configuration and record arrays in `DashboardDataProvider`.
- Configuration-selected `pie`, `bar`, and `list` render paths in browser JavaScript.
- Browser-side fetch, loading/empty/error messages, count and configuration summary.
- Server-side provider validation and client-side payload validation.
- Chart.js instance destruction when the selection changes.
- Feature tests for route/view response, sample JSON, unsupported identifiers, generic field names, empty data, and selected invalid inputs.

No external integrations, dashboard persistence, authentication, dynamic configuration editing, or database reads are present in the dashboard flow.

## Technology actually present

| Technology | Evidence and role |
| --- | --- |
| PHP 8.5.6 in the inspected environment | Laravel backend and provider/controller code. `composer.json` permits PHP `^8.3`. |
| Laravel Framework 13.39.0 in `composer.lock` | Routing, dependency injection, Blade, HTTP responses, and test harness. |
| Blade | `dashboard.index`, layout, and `<x-universal-dashboard />` shared markup. |
| Vanilla JavaScript | Inline in `resources/views/dashboard/index.blade.php`; fetches JSON and selects/renderers. |
| Chart.js 4.4.9 | CDN script in the layout; creates pie/bar charts. Not in Composer or npm dependencies. |
| HTML/CSS | Table DOM and inline responsive CSS in the layout. |
| PHPUnit 12.5.42 | Locked test framework; tests configured in `phpunit.xml`. |
| Vite/Tailwind | Laravel scaffold dependencies and entry files exist, but the dashboard layout does not include `@vite`; they do not build this dashboard's inline assets. |

The dependencies are appropriate for a small server-rendered prototype: Laravel supplies routing and JSON, Blade supplies a shared shell, plain JavaScript avoids another UI framework, and Chart.js handles the two chart types. The CDN choice means the browser needs access to jsDelivr. There is no `package-lock.json` or installed `node_modules` in the inspected tree.

## Limitations and weaknesses

- The data provider is a hardcoded PHP map rather than an external/configurable provider interface.
- Adding a dataset entails editing PHP and may entail changing the selector's display-label logic.
- The reusable Blade component is structural only; it accepts no Blade props. All dataset/configuration communication happens through the JSON endpoint.
- Browser code is inline in a large Blade view and is not independently unit tested.
- Chart.js is a remote dependency and has a possible loading-order race: its script is `defer`, while the inline body script begins the fetch immediately. A fast endpoint response and slow CDN can lead to a transient “Chart.js could not be loaded” error; this was inferred from script ordering, not reproduced in a browser.
- Validation is strongest for the curated provider response. The client can receive only identifiers present in the provider; it is not a public arbitrary-data ingestion endpoint.
- There is no browser automation proving chart pixels, visual responsiveness, keyboard behavior, or repeated switching.
- Generic Laravel auth/user/cache/jobs migrations, model, and factory remain in the scaffold, but the dashboard does not use them.

See [Features and testing](FEATURES_AND_TESTING.md) for evidence labels and [Developer guide](DEVELOPER_GUIDE.md) for practical improvements.
