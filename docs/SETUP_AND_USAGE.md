# Setup and usage

## Requirements

- PHP 8.3 or newer with Laravel's required PHP extensions.
- Composer.

Required Laravel PHP extensions: `ctype`, `filter`, `hash`, `mbstring`, `openssl`, `session`, and `tokenizer`.

Node.js/NPM and a database are not needed for normal dashboard use. Vite/Tailwind are scaffold dependencies; the dashboard uses its existing CSS and JavaScript assets. Charts require browser access to jsDelivr for Chart.js 4.4.9.

## Install and start (Windows PowerShell)

From the project root in VS Code's terminal:

```powershell
composer install
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}
php artisan key:generate
php artisan serve
```

The conditional copy preserves an existing `.env`. `key:generate` creates the Laravel application key. Keep the server terminal open and browse to `http://127.0.0.1:8000/`. Do not share `.env` values. The dashboard does not require database migrations.

## Implemented routes

| Method | URI | Purpose / response |
| --- | --- | --- |
| `GET` | `/` | Initial multi-widget dashboard HTML. |
| `POST` | `/dashboard/configuration` | Validates submitted dashboard JSON; returns `{"dashboard": ...}` or 422 validation errors. Does not persist the configuration. |
| `GET` | `/dashboard/data/{dataset}` | Fixed sample data for `sales`, `support`, or `inventory`; unknown identifiers return 404 JSON. |

The POST endpoint expects the `dashboard` object described in the root README. Send `Content-Type: application/json` and `Accept: application/json`; the browser editor also sends `X-CSRF-TOKEN` from the page. PowerShell examples and full JSON shapes are in the README.

## Tests

```powershell
php artisan test
```

This runs backend PHPUnit feature and unit tests. It does not launch a browser or verify chart visuals and responsive behavior.

## Troubleshooting

- **Composer platform error:** check `php -v`, `composer --version`, and that Laravel's required PHP extensions are enabled.
- **Missing application key:** create `.env` from `.env.example` only when `.env` does not exist, then run `php artisan key:generate`.
- **Chart widgets show an error:** check browser access to `cdn.jsdelivr.net` and the browser console. Tables do not depend on Chart.js.
- **Unknown dataset:** use exactly `sales`, `support`, or `inventory`; the data provider uses a fixed allowlist.
- **Database/migration prompt:** database setup is not needed for the dashboard flow; sample data and submitted widget configurations are not stored.
