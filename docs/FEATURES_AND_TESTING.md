# Features and testing

## Feature status

| Feature | Evidence and limits |
| --- | --- |
| Multi-widget dashboard | Initial dashboard and browser renderer support multiple widgets. Feature test checks the page's three-widget initial payload. |
| Pie, bar, line, and table renderers | Types are selectable in the editor and dispatched in `public/js/dashboard.js`; backend request test accepts each type. Chart pixels and table DOM are not browser-tested. |
| Widget IDs and grid positions | Request validation requires unique IDs and unique positions within columns 1–2 and rows 1–12. Tests cover duplicate IDs and positions. |
| Browser configuration editor | Source supports adding/removing widgets and editing their fields/data. No automated browser interaction test exists. |
| Configuration API | Feature tests cover successful POST, unsupported chart type, negative pie data, missing table values, duplicate positions/IDs, and unexpected top-level input. |
| Fixed sample API | `GET /dashboard/data/{dataset}` serves `sales`, `support`, and `inventory`; tests exercise all three and unknown-ID 404 behavior. |
| Responsive layout | CSS uses media queries to collapse the grid and editor columns. Source inspected; viewport behavior is not browser-tested. |
| Persistence, authentication, external sources | Not implemented in the dashboard flow. |

## Automated test inventory

`tests/Feature/DashboardConfigurationTest.php` covers successful configuration submission and representative 422 validation cases. `tests/Feature/DashboardTest.php` covers initial page rendering, the three sample endpoint responses, unknown dataset handling, and provider validation. Laravel scaffold example tests remain in `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php`.

Run the suite with:

```powershell
php artisan test
```

The existing test documentation records a prior result of 8 tests passed and 30 assertions from the earlier, smaller test suite. The current multi-widget tests were added after that recorded run, so that result does not represent the current suite. No fresh test run was made for this documentation update.

## Browser testing boundary and gaps

The PHPUnit suite exercises backend responses and validation only. It does not run the editor, check JavaScript rendering, test chart lifecycle, verify CDN failure behavior, or measure mobile layouts. Browser automation would be needed before describing those behaviors as visually verified.
