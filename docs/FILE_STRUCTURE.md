# File structure and inventory

This guide describes the files most relevant to the current multi-widget dashboard. Laravel framework scaffold files not involved in dashboard behavior are grouped below.

## Application files

| Path | Purpose |
| --- | --- |
| `routes/web.php` | Defines `GET /`, `POST /dashboard/configuration`, and `GET /dashboard/data/{dataset}`. |
| `app/Http/Controllers/DashboardController.php` | Renders the initial page, returns validated configuration JSON, and serves sample data JSON. |
| `app/Http/Requests/RenderDashboardConfigurationRequest.php` | Validates dashboard/widget fields, IDs, positions, visualization types, and records. |
| `app/Services/DashboardDataProvider.php` | Holds the initial dashboard and fixed sales, support, and inventory sample datasets. |
| `resources/views/dashboard/index.blade.php` | Dashboard page, passing initial data into its component. |
| `resources/views/components/universal-dashboard.blade.php` | Dashboard editor and grid markup; exposes initial JSON and POST URL to JavaScript. |
| `resources/views/layouts/app.blade.php` | Shared HTML layout, responsive styles, and Chart.js CDN include. |
| `public/js/dashboard.js` | Browser editor, configuration API submission, and widget renderer. |
| `resources/views/welcome.blade.php` | Unused Laravel welcome scaffold; `/` routes to the dashboard view. |
| `resources/css/app.css`, `resources/js/app.js` | Vite/Tailwind scaffold assets; not loaded by the current dashboard layout. |
| `bootstrap/app.php` | Registers Laravel routes and exception behavior. |
| `config/*.php` | Laravel framework configuration; dashboard flow does not use a database. |

## Tests

| Path | Purpose |
| --- | --- |
| `tests/Feature/DashboardConfigurationTest.php` | Configuration API success and validation failure cases. |
| `tests/Feature/DashboardTest.php` | Initial page, sample data endpoints, unknown IDs, and provider checks. |
| `tests/Feature/ExampleTest.php` | Laravel scaffold root-page check. |
| `tests/Unit/ExampleTest.php` | Laravel scaffold placeholder unit test. |
| `tests/TestCase.php` | Base class for application tests. |
| `phpunit.xml` | PHPUnit suites and test environment settings. |

## Data and scaffold files

`database/migrations/`, `database/factories/`, `database/seeders/`, and `app/Models/User.php` are Laravel scaffold files. They do not persist dashboard configuration or provide dashboard data. The dashboard sample data is in `DashboardDataProvider`.

`composer.json` and `composer.lock` define PHP dependencies and scripts. `package.json` and `vite.config.js` define supporting frontend scaffold dependencies. Chart.js is loaded from jsDelivr rather than Composer or NPM. `artisan` is the Laravel CLI entry point; `.env.example` is a local environment template and should not be copied into documentation with secrets. `README.md` gives beginner setup and API usage. The `docs/` directory contains the project overview, architecture, features/testing, setup, developer guide, and this inventory.
