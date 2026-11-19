# Setup and usage

## Requirements

Verified in the audit environment: PHP 8.5.6, Composer 2.9.8, Node 24.22.0, npm 11.19.0, Laravel Framework 13.39.0, and PHPUnit 12.5.42. `composer.json` requires PHP `^8.3` and Laravel `^13.17`; `composer.lock` fixes the inspected framework to 13.39.0. Laravel's required PHP extensions must also be enabled. Node/npm are not needed to run the dashboard page because its CSS and application JS are inline and Chart.js is remote; they are needed only for the scaffold's optional Vite workflow.

The dashboard does not require a database. The Laravel scaffold contains SQLite configuration/file and migrations, but dashboard requests use only `DashboardDataProvider`. Tests configure SQLite `:memory:` in `phpunit.xml`, and dashboard tests do not query models.

## Install and start (Windows PowerShell)

From the project root:

```powershell
composer install
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}
php artisan serve
```

Do not use `composer run setup` solely to run this dashboard: its configured script also runs migrations and npm install/build, which the dashboard does not require. Do not share `.env` contents. The command above generates a key only when it creates a new `.env`; if `.env` already exists, preserve its configured key. If an existing local `.env` lacks an application key, run `php artisan key:generate` as an explicit setup step.

Open `http://127.0.0.1:8000/`. Chart data is fetched from the same Laravel origin. Pie/bar rendering also requires browser network access to `cdn.jsdelivr.net` for Chart.js 4.4.9. If offline operation is required, vendor Chart.js locally via the chosen frontend asset workflow.

## Routes and example requests

| Method | URI | Response |
| --- | --- | --- |
| `GET` | `/` | HTML dashboard page; named `dashboard`. |
| `GET` | `/dashboard/data/sales` | JSON `{dataset, configuration, data}`, pie config, three rows. |
| `GET` | `/dashboard/data/support` | Same envelope, bar config, five rows. |
| `GET` | `/dashboard/data/inventory` | Same envelope, list config, three rows. |
| `GET` | `/dashboard/data/unknown` | HTTP 404 JSON `{"message":"Unsupported dataset identifier."}`. |
| `GET` / `HEAD` | `/up` | Laravel health-check route configured in `bootstrap/app.php`; not dashboard data. |
| `GET` / `HEAD` | `/storage/{path}` | Laravel local-storage serving route; unrelated to dashboard rendering. |
| `PUT` | `/storage/{path}` | Laravel local-storage upload route; unrelated to dashboard rendering. |

PowerShell examples:

```powershell
Invoke-WebRequest http://127.0.0.1:8000/ -UseBasicParsing
Invoke-RestMethod http://127.0.0.1:8000/dashboard/data/sales
```

The successful response shape is, for example:

```json
{
  "dataset": "inventory",
  "configuration": {
    "title": "Inventory",
    "type": "list",
    "columns": [
      { "key": "product", "label": "Product" },
      { "key": "stock", "label": "Available Stock (units)" }
    ]
  },
  "data": [
    { "product": "Foundation", "stock": 25 },
    { "product": "Lipstick", "stock": 40 },
    { "product": "Face Wash", "stock": 18 }
  ]
}
```

The last three routes are framework-provided routes shown by `php artisan route:list`, not dashboard-specific routes. The dashboard itself uses only `/` and `/dashboard/data/{dataset}`.

## Tests

```powershell
php artisan test --do-not-cache-result
```

The existing result in this audit was 8 passed, 30 assertions. `php artisan test` is also the normal command. Use `--do-not-cache-result` to avoid writing PHPUnit's result cache during a read-only style check.

## Troubleshooting

- **Composer dependency/platform error:** check `php -v`, PHP extensions, Composer, and the locked dependencies. This application requires PHP 8.3 or later.
- **Missing application key:** for a new local environment only, create `.env` from `.env.example` if absent and run `php artisan key:generate`; never paste the key into reports.
- **Page returns Laravel error:** confirm `php artisan serve` is running from the project root and check Laravel logs under `storage/logs` without disclosing secrets.
- **Unknown dataset returns 404:** use exactly `sales`, `support`, or `inventory`; IDs are allowlisted in `DashboardDataProvider::all()`.
- **Chart error while inventory works:** inventory uses native DOM and does not need Chart.js. Check browser network access to jsDelivr and the console. A race between deferred CDN loading and the first fetch is possible.
- **CSS/JS changes in scaffold files have no effect:** current dashboard assets are inline in Blade and the layout does not call `@vite`. `resources/css/app.css` and `resources/js/app.js` belong to the unused scaffold entry path.
- **SQLite/migration issue:** database setup is not necessary for the dashboard request path. Avoid running migration commands just to display sample datasets.
