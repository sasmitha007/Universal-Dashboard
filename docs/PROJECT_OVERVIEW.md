# Project overview

## Purpose and objectives

Universal Dashboard Component is a Laravel prototype for rendering multiple configured widgets in one dashboard. A JSON-shaped configuration describes each widget's ID, title, records, visualization, and CSS Grid position. A shared browser renderer maps the configuration to pie, bar, line, or table output, so the presentation logic can be reused across different record fields.

The current page includes a browser editor. It submits a dashboard configuration to Laravel for validation; the controller returns the validated configuration for rendering. The API does not persist it. The built-in initial dashboard combines sales, customer support, and inventory examples.

## Confirmed functionality

- `GET /` renders an initial multi-widget dashboard.
- Browser controls add/remove widgets and edit title, ID, records, visualization type/mappings, and position.
- `POST /dashboard/configuration` validates a dashboard request and returns `{ "dashboard": ... }`; invalid input receives Laravel's 422 validation response.
- `GET /dashboard/data/{dataset}` serves fixed allowlisted samples: `sales`, `support`, and `inventory`.
- The frontend renders pie, bar, and line charts with Chart.js and tables with native DOM APIs.
- Responsive CSS collapses the two-column widget grid on smaller screens.
- Feature tests exercise dashboard routes and important configuration validation cases.

## Technology analysis

| Technology | Role |
| --- | --- |
| PHP and Laravel | HTTP routes, initial dashboard/sample data, request validation, Blade page, and JSON responses. `composer.json` requires PHP `^8.3`. |
| JavaScript | Editor, API submission, and widget renderer in `public/js/dashboard.js`. |
| HTML/CSS and Blade | Page structure, reusable component, table markup, and responsive layout. |
| Chart.js 4.4.9 | Pie, bar, and line chart canvas rendering from jsDelivr. |
| JSON | Configuration request/response representation and sample data API format. |
| Composer | PHP dependency installation. |
| Vite/Tailwind | Laravel scaffold dependencies; the current dashboard layout does not use the Vite bundle for its rendering. |

The dashboard requires no database or Node.js/NPM for normal use. Chart visualizations require browser access to jsDelivr. The Laravel scaffold may contain database and asset build configuration unrelated to this dashboard flow.

## Limitations

- Submitted configurations are validated and returned but not saved. The initial dashboard is restored on a page refresh.
- `GET /dashboard/data/{dataset}` serves only fixed, allowlisted examples. The configuration POST can accept supplied widget data, but no storage or arbitrary dataset ingestion endpoint exists.
- There is no dashboard authentication or external data integration.
- Chart.js is loaded remotely, and the repository does not include browser automation for visual rendering or responsive behavior.
- The initial examples come from PHP source, not a database.

See [architecture and data flow](ARCHITECTURE_AND_DATA_FLOW.md), [features and testing](FEATURES_AND_TESTING.md), and [developer guide](DEVELOPER_GUIDE.md) for implementation details.
