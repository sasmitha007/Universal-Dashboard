# Universal Dashboard Component — Initial Prototype v0.1

A Laravel prototype that serves three local sample datasets as JSON and renders them through one configuration-driven Blade/JavaScript dashboard: pie, bar, or table. It does not include database-backed dashboards, authentication, or external data integrations. Chart.js 4.4.9 is loaded from jsDelivr.

## Start locally

Requirements: PHP 8.3+, Composer, and Laravel's required PHP extensions.

```powershell
composer install
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}
php artisan serve
```

Open `http://127.0.0.1:8000/`. Use `GET /dashboard/data/sales`, `/support`, or `/inventory` to inspect sample JSON. Run tests with `php artisan test`.

## Documentation

- [Project overview](docs/PROJECT_OVERVIEW.md)
- [File structure and inventory](docs/FILE_STRUCTURE.md)
- [Architecture and end-to-end data flow](docs/ARCHITECTURE_AND_DATA_FLOW.md)
- [Features, verification, and test gaps](docs/FEATURES_AND_TESTING.md)
- [Setup, routes, and troubleshooting](docs/SETUP_AND_USAGE.md)
- [Developer and extension guide](docs/DEVELOPER_GUIDE.md)
The detailed guides distinguish confirmed code, automated test evidence, and browser behavior. Review [known limitations](docs/PROJECT_OVERVIEW.md#limitations-and-weaknesses) before presenting this as more than a prototype.
