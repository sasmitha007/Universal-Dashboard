# Developer guide

## Code path

`routes/web.php` maps the dashboard page and JSON endpoints. `DashboardController::index()` obtains `DashboardDataProvider::initialDashboard()` for the initial page. The anonymous component receives that dashboard and exposes it to `public/js/dashboard.js`. The script renders the initial widgets, builds the editor, and posts edits to the configuration route. `RenderDashboardConfigurationRequest` owns POST validation; `renderConfiguration()` returns only the validated dashboard. `DashboardDataProvider` holds the fixed sample data returned by the data endpoint.

## Configuration schema

The POST body must contain only a `dashboard` object:

```json
{
  "dashboard": {
    "title": "Team Overview",
    "widgets": [
      {
        "id": "weekly-revenue",
        "title": "Weekly Revenue",
        "data": [{ "week": "Week 1", "amount": 120 }],
        "visualization": {
          "type": "line",
          "labelKey": "week",
          "valueKey": "amount"
        },
        "position": { "column": 1, "row": 1 }
      }
    ]
  }
}
```

| Field | Validation and behavior |
| --- | --- |
| `dashboard.title` | Required string, maximum 120 characters. |
| `dashboard.widgets` | Required array of 1–24 widgets. |
| `id` | Required alpha-numeric/dash/underscore string, maximum 64 characters; unique per dashboard. |
| `title` | Required string, maximum 120 characters. |
| `data` | Present list of at most 500 record objects; each record allows up to 50 fields. |
| `visualization.type` | One of `pie`, `bar`, `line`, or `table`. |
| `labelKey`, `valueKey` | Required nonblank strings for chart types; chart records need nonempty scalar labels and finite numeric values. Pie values cannot be negative. |
| `columns` | Optional for tables; when given, 1–20 entries with unique keys and labels are required. When omitted, table columns are inferred from the first record. Chart widgets may not specify columns. |
| `position.column` | Integer from 1 to 2. |
| `position.row` | Integer from 1 to 12. Widget positions must be unique. |

For tables with explicit columns, each record must contain a scalar value for every configured key. With no explicit columns, the frontend infers keys from the first record. Empty data arrays are accepted; the renderer displays an empty state.

## API behavior

- `POST /dashboard/configuration`: web route with Laravel CSRF middleware. Browser submissions include the page's `X-CSRF-TOKEN`; invalid payloads return 422 JSON validation errors. Success returns HTTP 200 with the validated `dashboard`. No storage occurs.
- `GET /dashboard/data/{dataset}`: serves only `sales`, `support`, or `inventory`; response fields are `dataset`, `configuration`, and `data`. Unknown identifiers return 404.

The sample endpoint's provider-level type check currently accepts pie, bar, and list because its sample inventory is still configured with `list`. The dashboard POST request/rendering path accepts the more recent public type name `table` and also supports line charts. This distinction reflects separate validation paths.

## Extending the implementation

### Add another initial widget

Add it to `DashboardDataProvider::initialDashboard()` with a unique ID and position. Ensure its `visualization` and data follow the request schema. Add or update a feature test for the expected payload.

### Add a sample dataset

Add its identifier and validated `configuration`/`data` entry to `DashboardDataProvider::all()`. `GET /dashboard/data/{dataset}` uses the exact identifier allowlist. Add an endpoint test. This is a PHP source change, not runtime registration or external ingestion.

### Add a visualization type

Update `RenderDashboardConfigurationRequest`, the editor's type selector and field behavior, `visualizationRegistry` in `public/js/dashboard.js`, and tests/docs. For charts, configure Chart.js appropriately and preserve chart cleanup behavior. Backend acceptance alone does not provide a renderer.

### Persistence or external data

These require new architecture: persistence needs database schema, model/repository, and authorization decisions; external sources need trusted client configuration, error handling, and record mapping. Neither is part of the current prototype.
