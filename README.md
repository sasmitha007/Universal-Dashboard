# Universal Dashboard Component — Initial Prototype v0.1

A Laravel prototype that serves three local sample datasets as JSON and renders them through one configuration-driven Blade/JavaScript dashboard: pie, bar, or table. It does not include database-backed dashboards, authentication, or external data integrations. Chart.js 4.4.9 is loaded from jsDelivr.

## Installation and Setup Guide

Requirements: PHP 8.3+, Composer, and Laravel's required PHP extensions. Install Composer before continuing. Composer is required to install the project's PHP dependencies. Download and install it from the [official Composer download page](https://getcomposer.org/download/).

From the project's root directory, install the dependencies:

```sh
composer install
```

Create your own `.env` file by copying the example file. This command is for Windows Command Prompt or PowerShell:

```cmd
copy .env.example .env
```

Generate the Laravel application encryption key required by the application:

```sh
php artisan key:generate
```

Start the Laravel development server:

```sh
php artisan serve
```

The server will provide a local URL, usually [http://127.0.0.1:8000](http://127.0.0.1:8000), which you can open in a browser. Use `GET /dashboard/data/sales`, `/support`, or `/inventory` to inspect sample JSON. Run tests with `php artisan test`.

## Documentation

- [Project overview](docs/PROJECT_OVERVIEW.md)
- [File structure and inventory](docs/FILE_STRUCTURE.md)
- [Architecture and end-to-end data flow](docs/ARCHITECTURE_AND_DATA_FLOW.md)
- [Features, verification, and test gaps](docs/FEATURES_AND_TESTING.md)
- [Setup, routes, and troubleshooting](docs/SETUP_AND_USAGE.md)
- [Developer and extension guide](docs/DEVELOPER_GUIDE.md)
The detailed guides distinguish confirmed code, automated test evidence, and browser behavior. Review [known limitations](docs/PROJECT_OVERVIEW.md#limitations-and-weaknesses) before presenting this as more than a prototype.
