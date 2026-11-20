<?php

namespace App\Services;

use InvalidArgumentException;

/** Supplies generic records and their separate rendering configuration. */
class DashboardDataProvider
{
    /** @return array<string, array{configuration: array<string, mixed>, data: list<array<string, mixed>>}> */
    public function all(): array
    {
        return [
            'sales' => [
                'configuration' => ['title' => 'Sales by Product', 'type' => 'pie', 'labelKey' => 'name', 'valueKey' => 'value'],
                'data' => [
                    ['name' => 'Product A', 'value' => 500],
                    ['name' => 'Product B', 'value' => 300],
                    ['name' => 'Product C', 'value' => 200],
                ],
            ],
            'support' => [
                'configuration' => ['title' => 'Daily Customer Chats', 'type' => 'bar', 'labelKey' => 'name', 'valueKey' => 'value'],
                'data' => [
                    ['name' => 'Monday', 'value' => 20],
                    ['name' => 'Tuesday', 'value' => 35],
                    ['name' => 'Wednesday', 'value' => 28],
                    ['name' => 'Thursday', 'value' => 42],
                    ['name' => 'Friday', 'value' => 31],
                ],
            ],
            'inventory' => [
                'configuration' => [
                    'title' => 'Inventory',
                    'type' => 'list',
                    'columns' => [
                        ['key' => 'product', 'label' => 'Product'],
                        ['key' => 'stock', 'label' => 'Available Stock (units)'],
                    ],
                ],
                'data' => [
                    ['product' => 'Product A', 'stock' => 25],
                    ['product' => 'Product B', 'stock' => 40],
                    ['product' => 'Product C', 'stock' => 18],
                ],
            ],
        ];
    }

    /**
     * Provide a multi-widget example that exercises each existing sample shape.
     *
     * @return array{title: string, widgets: list<array<string, mixed>>}
     */
    public function initialDashboard(): array
    {
        $datasets = $this->all();

        return [
            'title' => 'Universal Dashboard Demo',
            'widgets' => [
                [
                    'id' => 'sales-chart',
                    'title' => $datasets['sales']['configuration']['title'],
                    'data' => $datasets['sales']['data'],
                    'visualization' => ['type' => 'pie', 'labelKey' => 'name', 'valueKey' => 'value'],
                    'position' => ['column' => 1, 'row' => 1],
                ],
                [
                    'id' => 'support-chart',
                    'title' => $datasets['support']['configuration']['title'],
                    'data' => $datasets['support']['data'],
                    'visualization' => ['type' => 'bar', 'labelKey' => 'name', 'valueKey' => 'value'],
                    'position' => ['column' => 2, 'row' => 1],
                ],
                [
                    'id' => 'inventory-table',
                    'title' => $datasets['inventory']['configuration']['title'],
                    'data' => $datasets['inventory']['data'],
                    'visualization' => [
                        'type' => 'table',
                        'columns' => [
                            ['key' => 'product', 'label' => 'Product'],
                            ['key' => 'stock', 'label' => 'Available Stock (units)'],
                        ],
                    ],
                    'position' => ['column' => 1, 'row' => 2],
                ],
            ],
        ];
    }

    /** @return array{configuration: array<string, mixed>, data: list<array<string, mixed>>} */
    public function get(string $identifier): array
    {
        $datasets = $this->all();

        if (! array_key_exists($identifier, $datasets)) {
            throw new InvalidArgumentException('Unsupported dataset identifier.');
        }

        $dataset = $datasets[$identifier];
        $this->validate($dataset['data'], $dataset['configuration']);

        return $dataset;
    }

    /** Validate generic records/configuration before any renderer consumes them. */
    public function validate(array $data, array $configuration): void
    {
        $type = $configuration['type'] ?? null;
        if (! in_array($type, ['pie', 'bar', 'list'], true)) {
            throw new InvalidArgumentException('Unsupported or missing visualization type.');
        }
        if (! isset($configuration['title']) || ! is_string($configuration['title']) || trim($configuration['title']) === '') {
            throw new InvalidArgumentException('Configuration must include a title.');
        }
        if (! array_is_list($data)) {
            throw new InvalidArgumentException('Dataset must be a list of records.');
        }

        if ($type === 'list') {
            $columns = $configuration['columns'] ?? null;
            if (! is_array($columns) || $columns === []) {
                throw new InvalidArgumentException('List configuration must include columns.');
            }
            foreach ($columns as $column) {
                if (! is_array($column) || ! is_string($column['key'] ?? null) || $column['key'] === '' || ! is_string($column['label'] ?? null) || $column['label'] === '') {
                    throw new InvalidArgumentException('Each list column must include a key and label.');
                }
            }
            foreach ($data as $record) {
                if (! is_array($record)) {
                    throw new InvalidArgumentException('Each dataset item must be a record.');
                }
                foreach ($columns as $column) {
                    if (! array_key_exists($column['key'], $record) || $record[$column['key']] === null) {
                        throw new InvalidArgumentException('A configured list value is missing.');
                    }
                }
            }

            return;
        }

        $labelKey = $configuration['labelKey'] ?? null;
        $valueKey = $configuration['valueKey'] ?? null;
        if (! is_string($labelKey) || $labelKey === '' || ! is_string($valueKey) || $valueKey === '') {
            throw new InvalidArgumentException('Chart configuration must include labelKey and valueKey.');
        }
        foreach ($data as $record) {
            if (! is_array($record) || ! array_key_exists($labelKey, $record) || $record[$labelKey] === null || $record[$labelKey] === '') {
                throw new InvalidArgumentException('A configured chart label is missing.');
            }
            $value = $record[$valueKey] ?? null;
            if (! is_numeric($value) || ! is_finite((float) $value)) {
                throw new InvalidArgumentException('A configured chart value must be numeric.');
            }
            if ($type === 'pie' && (float) $value < 0) {
                throw new InvalidArgumentException('Pie chart values cannot be negative.');
            }
        }
    }
}
