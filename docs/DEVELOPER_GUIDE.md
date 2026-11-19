# Developer guide

## Understand the implementation

Start with `routes/web.php`, then follow `DashboardController` to `DashboardDataProvider`, and then inspect `resources/views/dashboard/index.blade.php`, the anonymous dashboard component, and its layout. The controller receives `DashboardDataProvider` through Laravel's constructor-free automatic dependency injection: Laravel sees the type-hinted argument and resolves the class from the container. A service provider binding is unnecessary for this plain class.

The Blade component is invoked by its conventional `<x-universal-dashboard />` name from `resources/views/components/universal-dashboard.blade.php`. It defines the shared DOM scaffold but has no `@props` declaration and receives no PHP data. The page gets its options from the controller; JS fetches the selected JSON response.

The controller is responsible for HTTP and response formatting. The provider owns fixed sample data and checks the corresponding config/records. The browser validates the JSON again and decides the renderer. Chart.js receives a canvas and options; the list renderer uses native DOM APIs. The detail box presents the configuration JSON.

## Current schema

Every successful endpoint response has:

```json
{
  "dataset": "sales",
  "configuration": {
    "title": "Sales by Product",
    "type": "pie",
    "labelKey": "name",
    "valueKey": "value"
  },
  "data": [{ "name": "Product A", "value": 500 }]
}
```

| Property | Required | Expected type | Current behavior and validation |
| --- | --- | --- | --- |
| `dataset` | Response metadata | String | Set by controller from the route ID; JS does not use it to select renderer. |
| `configuration.title` | Yes | Nonempty string server-side; any string client-side | Sets heading and chart dataset label. |
| `configuration.type` | Yes | `pie`, `bar`, `list` | PHP and JS reject unsupported/missing types. Selects renderer. |
| `configuration.labelKey` | For pie/bar | Nonempty string | Key read from each chart record for labels. No default. |
| `configuration.valueKey` | For pie/bar | Nonempty string | Key read for numeric chart values. PHP accepts `is_numeric` finite numbers; pie additionally rejects negatives. No default. |
| `configuration.columns` | For list | Nonempty array of `{key:string,label:string}` | Creates table columns. PHP requires key/label nonempty strings; configured record values must exist and not be null. No default. |
| `data` | Yes | List of associative PHP arrays serialized as JSON objects | Empty list is valid. Chart/list checks operate per record. |

There are no other rendering options in the schema. Colors, legend placement, bar axis, and chart responsive options are currently fixed in JS/layout, not configurable by JSON.

### Restrictions and edge cases

- Identifiers must match the route pattern and exact provider allowlist; unknown values are 404.
- Keys are ordinary string field names; no nested dot-path lookup exists.
- PHP permits numeric values/numeric strings for charts if finite; the JS converts them to `Number`.
- Pie values cannot be negative; bar values can be negative. Zero values are allowed.
- Chart labels cannot be missing, null, or the empty string on PHP validation. Whitespace-only strings and non-string scalar values are not explicitly rejected.
- List values cannot be missing or null, but nested objects/arrays are not rejected; JS `String(value)` would render an unhelpful coercion for such a value.
- The JS validation is not identical to PHP: for example, JavaScript `Number(null)` is zero, although the server currently rejects null through `is_numeric`. API responses from this provider are server-validated first.
- The component is reusable across schemas, but adding endpoint data requires editing the provider. It does not currently accept supplied records/configuration from an arbitrary caller.

## Extension steps

### Add a fourth sample dataset (supported, requires provider/UI work)

1. Add a new fixed identifier entry to `DashboardDataProvider::all()` with `configuration` and list-shaped `data`.
2. Keep the identifier within the route character pattern (`A-Z`, `a-z`, digits, `_`, `-`).
3. Call `validate()` through `get()` as current code does; add a feature test for payload, fields, and type.
4. `DashboardController::index()` already derives option IDs from `all()`. Update the label expression in `dashboard/index.blade.php` if the ID needs a friendly name; currently only `support` receives a special label.
5. Test `/dashboard/data/{id}` and browser switching.

This avoids changing renderer logic for an existing visualization type, but it is not pure configuration-only registration because the provider is PHP code.

### Change title or field mapping

Edit that dataset's configuration in `DashboardDataProvider::all()`. Ensure each record carries the named field and satisfies provider validation. No chart/list renderer edits are needed for current types. Add/adjust a provider/endpoint test.

### Add or change table columns

Change `configuration.columns` with one or more `{key,label}` entries and include non-null values under each key in every record. `renderList()` will generate the headings and cells; no business-specific table view is required.

### Add a visualization type (requires renderer changes)

1. Extend the allowed type list and PHP-specific validation in `DashboardDataProvider::validate()`.
2. Extend the client `validate()` rules for the new type's schema.
3. Add a branch in `render()` and a renderer implementation in `dashboard/index.blade.php` (or extract JS to a module).
4. If it uses Chart.js, pass the type/options and maintain `activeChart` destruction behavior.
5. Add backend tests and browser tests. Update documentation/schema.

Without both PHP and JS updates, the new type is rejected or cannot render.

### Connect an external data source (architectural extension)

Replace or supplement the hardcoded provider with an interface/service that fetches a trusted external source, handles timeouts/authentication/errors, maps its output to the same generic contract, and validates before returning. Keep credentials in environment/config and never return secrets. The current provider has no HTTP client, retries, caching, or integration configuration. Add tests with a fake client and avoid exposing credentials in logs/responses.

### Persist configurations (architectural extension)

Define migration/model/repository and authorization rules, then make the provider/controller read validated stored config. This introduces actual database dependency and requires migrations, ownership/security decisions, validation at save time, and integration tests. Current User model/migrations are scaffold only and do not implement dashboard persistence.

## Maintenance notes

- Keep server and client validation aligned; currently there are small differences.
- The CDN is `defer` while the inline initialization script is synchronous at the end of HTML. Coordinate chart initialization with CDN readiness or bundle Chart.js locally to remove the possible race.
- The data fetch URL starts at `/dashboard/...`; deployments under a URL prefix need a generated/base-aware URL.
- Consider extracting the inline JS and CSS into `resources/js` and `resources/css` and adding `@vite` to the dashboard layout only after choosing/building the intended asset workflow.
- Keep table values on `textContent`; do not switch to `innerHTML` for convenience.
- Add browser tests before claiming charts or repeated switching are end-to-end verified.
- Keep the generic sample provider independent of inventory/sales-specific branches; the one UI label exception can become a label property if that coupling grows.
