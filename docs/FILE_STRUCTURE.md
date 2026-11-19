# File structure and inventory

## Provenance basis

The project folder was inspected empty at the start of the earlier implementation work in this conversation; a Laravel skeleton was then created and the dashboard files listed below were added. There is no `.git` repository or commit history in the current folder, so Git-based history/diff is unavailable. “Created” and “modified” below refer only to that observed work sequence, not to a historical upstream repository. Any more specific original-project provenance is unverified. During this audit, application files were not modified; only this `docs/` documentation set was added.

## Important files to read first

1. `routes/web.php` — maps `/` and the JSON dataset endpoint.
2. `app/Http/Controllers/DashboardController.php` — receives route requests and coordinates provider/view/JSON response.
3. `app/Services/DashboardDataProvider.php` — sample records, config, allowlist, and PHP validation.
4. `resources/views/dashboard/index.blade.php` — selector, inline JavaScript, fetch, payload validation, and renderer dispatch.
5. `resources/views/components/universal-dashboard.blade.php` — shared dashboard structure and JS target IDs.
6. `resources/views/layouts/app.blade.php` — inline styles and remote Chart.js script.
7. `tests/Feature/DashboardTest.php` — current dashboard-specific automated coverage.

## Application source inventory

| Relative path | Provenance | Purpose, inputs/outputs, dependencies, and features |
| --- | --- | --- |
| `app/Http/Controllers/DashboardController.php` | Created for prototype | `index(DashboardDataProvider)` passes provider keys and fixed `sales` initial ID to the dashboard view. `data(string $dataset, DashboardDataProvider)` returns `{dataset, configuration, data}` JSON; catches provider `InvalidArgumentException` and maps it to 404. Depends on the provider and Laravel response/view helpers. Drives both dashboard routes. |
| `app/Services/DashboardDataProvider.php` | Created for prototype | `all()` returns fixed `sales`, `support`, `inventory` records/configurations; `get()` enforces exact identifier allowlist, validates, and returns one dataset; `validate()` checks type/title/data shape/chart keys/numeric values/pie nonnegative/list columns and values. No DB or external service. |
| `app/Http/Controllers/Controller.php` | Laravel skeleton | Empty abstract base controller; `DashboardController` extends it. No dashboard behavior of its own. |
| `app/Models/User.php` | Laravel skeleton | Eloquent authentication model with fillable/hidden attributes and casts. No dashboard route or provider references it. |
| `app/Providers/AppServiceProvider.php` | Laravel skeleton | Empty `register` and `boot`; no custom provider bindings. The provider class is directly auto-resolved by Laravel. |
| `routes/web.php` | Modified for prototype | Replaces default root welcome closure with named `dashboard` route and adds named `dashboard.data` route constrained to alphanumerics, `_`, `-`. |
| `routes/console.php` | Laravel skeleton | Defines `inspire` Artisan command only; unrelated to dashboard HTTP functionality. |
| `resources/views/layouts/app.blade.php` | Created for prototype | HTML document shell, inline CSS, responsive styles, and deferred Chart.js 4.4.9 CDN include. Does not load Vite CSS/JS. Wraps pushed scripts. |
| `resources/views/dashboard/index.blade.php` | Created for prototype | Extends layout, renders heading/selector and the shared component. Inline JavaScript fetches selected endpoint, validates payload, updates title/count/config summary, and dispatches list/chart rendering. It hardcodes a friendly label mapping for `support`. |
| `resources/views/components/universal-dashboard.blade.php` | Created for prototype | Anonymous Blade component with no props: card, title/count targets, visualization area, and expandable configuration summary. Its DOM IDs are used by the inline JS. |
| `resources/views/welcome.blade.php` | Laravel skeleton; unused by current root route | Default Laravel welcome page and scaffold `@vite` branch. Root is routed to `dashboard.index`, so this template is not used for the dashboard. |
| `resources/js/app.js` | Laravel skeleton; unused by dashboard layout | Contains only the scaffold comment (`//`). Not included in `layouts/app.blade.php`. |
| `resources/css/app.css` | Laravel skeleton; unused by dashboard layout | Tailwind import and source/theme directives. Dashboard-specific styling is in the Blade layout instead. |
| `public/index.php` | Laravel skeleton | HTTP front controller bootstrapping Laravel. |
| `public/robots.txt`, `public/favicon.ico` | Laravel skeleton | Static public assets, unrelated to data rendering. |
| `bootstrap/app.php` | Laravel skeleton | Registers web/console routes and `/up` health route; configures exception JSON negotiation. |
| `bootstrap/providers.php` | Laravel skeleton | Lists application service providers. No dashboard-specific binding is registered. |
| `config/*.php` | Laravel skeleton | Framework configuration for app, auth, cache, DB, filesystems, logging, mail, queue, services, and sessions. Dashboard code does not read these directly except through Laravel runtime. `config/database.php` defaults to SQLite if env does not override it. Sensitive `.env` values were not inspected or copied. |

## Data, model, and migrations

| Relative path | Provenance | Purpose and dashboard relationship |
| --- | --- | --- |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Laravel skeleton | Creates users, password reset tokens, sessions. No dashboard records. |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | Laravel skeleton | Creates cache/cache-lock tables. Dashboard sample values are not stored here. |
| `database/migrations/0001_01_01_000002_create_jobs_table.php` | Laravel skeleton | Creates queues, batches, failed jobs. Dashboard does not dispatch jobs. |
| `database/factories/UserFactory.php` | Laravel skeleton | Factory for `User`, unused by dashboard. |
| `database/seeders/DatabaseSeeder.php` | Laravel skeleton | Seeds a test user if invoked; not used by dashboard. Do not run as part of dashboard usage. |
| `database/database.sqlite` | Laravel project setup artifact | SQLite file exists, but dashboard request flow does not access it. Its exact current schema/data state was not queried in this audit. |

## Tests

| Relative path | Provenance | Purpose |
| --- | --- | --- |
| `tests/Feature/DashboardTest.php` | Created for prototype | Six dashboard tests for page, sample endpoint JSON, unknown ID, all renderer types/generic names, empty data/unsupported config, and invalid pie/list data. |
| `tests/Feature/ExampleTest.php` | Laravel skeleton | Basic GET `/` returns 200; overlaps the dashboard page success check. |
| `tests/Unit/ExampleTest.php` | Laravel skeleton | Trivial `true === true` assertion; no application logic coverage. |
| `tests/TestCase.php` | Laravel skeleton | Laravel feature-test base class. |
| `phpunit.xml` | Laravel skeleton, configured for project | Defines Unit/Feature suites and testing env; SQLite is set to `:memory:` for tests. |

## Dependency, build, and documentation files

| Relative path | Provenance | Purpose and status |
| --- | --- | --- |
| `composer.json`, `composer.lock` | Laravel skeleton/dependency setup | PHP/Laravel requirements, scripts, PSR-4 autoloading, and locked PHP packages. Lock records Laravel Framework `v13.39.0`, Tinker `v3.0.2`, PHPUnit `12.5.42`. |
| `package.json` | Laravel skeleton | Vite/Tailwind frontend dev dependencies/scripts. No Chart.js npm package. |
| `vite.config.js` | Laravel skeleton | Configures Laravel Vite, Tailwind, and font plugin; dashboard layout does not use it. |
| `README.md` | Updated for prototype | Existing scaffold README replaced with prototype purpose, architecture, generic schema, samples, setup, tests, and limits. |
| `docs/PROJECT_OVERVIEW.md` | Created during audit | Scope, implementation boundaries, technology stack, reusability assessment, and limitations. |
| `docs/FILE_STRUCTURE.md` | Created during audit | File inventory and provenance notes. |
| `docs/ARCHITECTURE_AND_DATA_FLOW.md` | Created during audit | Actual components, Mermaid diagrams, routes, server/browser boundary, and pie/bar/list request flows. |
| `docs/FEATURES_AND_TESTING.md` | Created during audit | Evidence-labeled feature matrix, test inventory/results, and coverage gaps. |
| `docs/SETUP_AND_USAGE.md` | Created during audit | Requirements, safe setup, routes, test command, and troubleshooting. |
| `docs/DEVELOPER_GUIDE.md` | Created during audit | Configuration schema, extension steps, and maintenance notes. |
| `AGENTS.md`, `CLAUDE.md` | Supporting project instructions | Agent/developer workflow notes; not runtime functionality. |
| `.editorconfig`, `.gitattributes`, `.gitignore`, `.npmrc` | Laravel/tooling scaffold | Editor, Git, ignored files, and npm settings. `.gitignore` excludes `.env`, `vendor`, `node_modules`, and build output. |
| `.env.example` | Laravel skeleton | Environment-variable template. Values are not reproduced here. `.env` exists but was not opened. |
| `artisan` | Laravel skeleton | CLI entry point for Artisan commands. |

## Generated and third-party files

`vendor/` contains Composer-installed framework/development packages and is excluded from this inventory; `composer.lock` is the source of exact locked versions. No `node_modules/` or `package-lock.json` was present. `storage/` and `bootstrap/cache/` contain runtime/generated Laravel artifacts and are not hand-authored dashboard source. The audited source inventory under `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, and `tests` contains 40 files at inspection time; this count excludes root files and generated storage/vendor contents.
