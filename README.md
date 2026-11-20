# Universal Dashboard Component

Universal Dashboard Component is a Laravel prototype for building a dashboard from a JSON configuration. Each configuration can contain multiple widgets, and each widget carries its own identifier, title, data, visualization settings, and grid position. Reusable visualization components let the same rendering code display different records without creating a separate page for every chart or table.

The dashboard starts with three examples: sales (pie chart), customer support (bar chart), and inventory (table). The browser includes an editor for changing the dashboard configuration, then sends it to Laravel for validation before rendering the returned configuration. Line charts are also supported by the renderer and configuration endpoint.

## Features

- Multiple widgets rendered together in a two-column CSS Grid layout.
- Pie, bar, and line charts rendered with Chart.js, plus native HTML tables.
- JSON configuration for dashboard titles, widget IDs, data, visualization mappings, and positions.
- Browser editor to add and remove widgets and edit their configuration and data.
- Laravel request validation, including supported visualization types, unique widget IDs and positions, and chart/table data checks.
- `POST /dashboard/configuration` API submission and JSON response.
- Responsive layout: the widget grid collapses to one column on narrower screens.

## Technologies

| Technology | Purpose |
| --- | --- |
| PHP | Backend language and configuration validation. |
| Laravel | Routing, request validation, Blade views, and JSON responses. |
| JavaScript | Dashboard editor, API submission, and client-side widget rendering. |
| HTML/CSS | Page structure, tables, and responsive layout. |
| Chart.js 4.4.9 | Pie, bar, and line chart rendering; loaded from jsDelivr. |
| JSON | Dashboard configuration and API request/response format. |
| Composer | Install PHP dependencies. |

Vite and Tailwind are present in the Laravel scaffold, but the current dashboard uses its own CSS and JavaScript assets and does not require the Vite build to run.

## Requirements

- PHP 8.3 or newer, with Laravel's required PHP extensions enabled.
- Composer.

The required extensions from the installed Laravel framework are `ctype`, `filter`, `hash`, `mbstring`, `openssl`, `session`, and `tokenizer`.

Node.js and NPM are not required to run the current dashboard. A database is also not required: dashboard data and configuration are held in PHP or sent in the request, and are not stored. The browser needs internet access to jsDelivr for Chart.js charts; tables use native browser APIs.

## Installation and setup (Windows PowerShell)

1. Install PHP 8.3 or newer and Composer. Confirm they are available in PowerShell with `php -v` and `composer --version`.
2. Open this project folder in VS Code.
3. Open VS Code's terminal and confirm it is at the project root (the folder containing `artisan` and `composer.json`).
4. Install the Laravel dependencies:

   ```powershell
   composer install
   ```

5. Create the local environment file only if it does not already exist:

   ```powershell
   if (-not (Test-Path .env)) {
       Copy-Item .env.example .env
   }
   ```

   This preserves an existing `.env` file.

6. Generate the Laravel application key:

   ```powershell
   php artisan key:generate
   ```

   This sets the key used by Laravel for application encryption.

7. Start the local server:

   ```powershell
   php artisan serve
   ```

   Keep this terminal open while using the application.

## Run the dashboard

Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/) in a browser. You should see the Universal Dashboard page with an initial set of sales, support, and inventory widgets, and controls for editing the dashboard. Applying changes submits the configuration for validation and renders the accepted response. Chart widgets need access to the Chart.js CDN; tables do not.

## How it works

1. Laravel serves the dashboard page and its initial configuration.
2. The configuration describes the dashboard and its widgets as JSON-shaped data.
3. Each widget has an ID, title, records, visualization settings, and a grid position.
4. The browser sends edited configuration to Laravel, which validates it and returns the accepted dashboard as JSON.
5. The frontend uses each widget's visualization type and field mappings to render the corresponding chart or table.

See [Architecture and data flow](docs/ARCHITECTURE_AND_DATA_FLOW.md) for component responsibilities and detailed diagrams.

## API

Both endpoints are Laravel web routes. Send and request JSON with `Content-Type: application/json` and `Accept: application/json`. The browser editor also sends the session CSRF token in `X-CSRF-TOKEN` when posting from the page.

### `POST /dashboard/configuration`

Validates the supplied dashboard and returns it in a JSON response. It does not save the configuration. A successful response is HTTP 200 with `{"dashboard": { ... }}`; invalid input returns HTTP 422 with Laravel validation errors.

Example request (chart and table widgets):

```json
{
  "dashboard": {
    "title": "Team Overview",
    "widgets": [
      {
        "id": "weekly-revenue",
        "title": "Weekly Revenue",
        "data": [
          { "week": "Week 1", "amount": 120 },
          { "week": "Week 2", "amount": 155 }
        ],
        "visualization": {
          "type": "line",
          "labelKey": "week",
          "valueKey": "amount"
        },
        "position": { "column": 1, "row": 1 }
      },
      {
        "id": "team-members",
        "title": "Team Members",
        "data": [
          { "name": "Asha", "department": "Design" }
        ],
        "visualization": {
          "type": "table",
          "columns": [
            { "key": "name", "label": "Name" },
            { "key": "department", "label": "Department" }
          ]
        },
        "position": { "column": 2, "row": 1 }
      }
    ]
  }
}
```

Validation allows 1 to 24 widgets, IDs up to 64 characters, titles up to 120 characters, up to 500 records per widget, columns 1 to 2, and rows 1 to 12. IDs and grid positions must be unique. Chart types (`pie`, `bar`, `line`) require `labelKey` and `valueKey` and numeric finite values; pie values cannot be negative. Tables accept optional column definitions and infer columns from the first data record when omitted. See [Developer guide](docs/DEVELOPER_GUIDE.md) for the full schema and edge cases.

PowerShell example, after starting the server:

```powershell
$session = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$page = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/' -WebSession $session
$csrfToken = [regex]::Match($page.Content, '<meta name="csrf-token" content="([^"]+)"').Groups[1].Value

$payload = @{
    dashboard = @{
        title = 'One table'
        widgets = @(
            @{
                id = 'stock-table'
                title = 'Stock'
                data = @(@{ product = 'Product A'; stock = 25 })
                visualization = @{
                    type = 'table'
                    columns = @(
                        @{ key = 'product'; label = 'Product' }
                        @{ key = 'stock'; label = 'Units' }
                    )
                }
                position = @{ column = 1; row = 1 }
            }
        )
    }
} | ConvertTo-Json -Depth 10

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/dashboard/configuration' `
    -Method Post `
    -ContentType 'application/json' `
    -WebSession $session `
    -Headers @{ Accept = 'application/json'; 'X-CSRF-TOKEN' = $csrfToken } `
    -Body $payload
```

### `GET /dashboard/data/{dataset}`

Returns a fixed, allowlisted sample dataset with its configuration and records as JSON. Supported identifiers are `sales`, `support`, and `inventory`; unknown identifiers return HTTP 404. The dataset identifier selects the sample data and is included in the response as `dataset`.

```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/dashboard/data/sales' `
    -Headers @{ Accept = 'application/json' }
```

Successful response shape:

```json
{
  "dataset": "sales",
  "configuration": {
    "title": "Sales by Product",
    "type": "pie",
    "labelKey": "name",
    "valueKey": "value"
  },
  "data": [
    { "name": "Product A", "value": 500 },
    { "name": "Product B", "value": 300 },
    { "name": "Product C", "value": 200 }
  ]
}
```

This endpoint only serves the three built-in examples. It does not accept arbitrary dataset IDs, persist data, or poll for updates.

## Tests

Run the backend automated test suite from the project root:

```powershell
php artisan test
```

This runs Laravel's PHPUnit feature and unit tests, including configuration validation and API response checks. These tests do not run a browser or verify chart appearance, responsive behavior, or visual rendering. See [Features and testing](docs/FEATURES_AND_TESTING.md) for the coverage inventory and any recorded test evidence.

## Project structure

```text
app/
  Http/Controllers/       HTTP endpoints and page controller
  Http/Requests/          Dashboard configuration validation
  Services/               Initial dashboard and sample data provider
resources/views/          Blade page, layout, and reusable component
public/js/                Dashboard editor and renderers
routes/web.php             Dashboard web and JSON routes
tests/                     Automated feature and unit tests
docs/                      Detailed project and developer documentation
```

## Current limitations

- Dashboard configurations submitted to the API are validated and returned, not persisted; refreshing resets to the initial configuration.
- Sample datasets are hardcoded and allowlisted. External data sources and arbitrary incoming datasets are not supported by `GET /dashboard/data/{dataset}`.
- Dashboard routes do not implement authentication.
- Widget data may be supplied in a configuration request, within validation limits, but is not retained after the response or refresh.
- Charts depend on the jsDelivr-hosted Chart.js script. Browser-based visual tests are not included in the current automated suite.

## Documentation

- [Project overview](docs/PROJECT_OVERVIEW.md): goals, technologies, and limitations.
- [Architecture and data flow](docs/ARCHITECTURE_AND_DATA_FLOW.md): components, request flow, and diagrams.
- [Setup and usage](docs/SETUP_AND_USAGE.md): expanded setup, endpoints, and troubleshooting.
- [Developer guide](docs/DEVELOPER_GUIDE.md): configuration schema and extension guidance.
- [Features and testing](docs/FEATURES_AND_TESTING.md): feature evidence and test coverage.
- [File structure](docs/FILE_STRUCTURE.md): detailed source inventory.
