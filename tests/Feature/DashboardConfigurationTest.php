<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardConfigurationTest extends TestCase
{
    public function test_configuration_endpoint_renders_all_supported_widget_types_and_positions(): void
    {
        $payload = [
            'dashboard' => [
                'title' => 'Team Overview',
                'widgets' => [
                    [
                        'id' => 'revenue-share',
                        'title' => 'Revenue share',
                        'data' => [['label' => 'A', 'amount' => 20]],
                        'visualization' => ['type' => 'pie', 'labelKey' => 'label', 'valueKey' => 'amount'],
                        'position' => ['column' => 1, 'row' => 1],
                    ],
                    [
                        'id' => 'daily-total',
                        'title' => 'Daily total',
                        'data' => [['day' => 'Mon', 'total' => 5]],
                        'visualization' => ['type' => 'bar', 'labelKey' => 'day', 'valueKey' => 'total'],
                        'position' => ['column' => 2, 'row' => 1],
                    ],
                    [
                        'id' => 'weekly-trend',
                        'title' => 'Weekly trend',
                        'data' => [['week' => 'W1', 'count' => 8]],
                        'visualization' => ['type' => 'line', 'labelKey' => 'week', 'valueKey' => 'count'],
                        'position' => ['column' => 1, 'row' => 2],
                    ],
                    [
                        'id' => 'team-members',
                        'title' => 'Team members',
                        'data' => [['name' => 'Kamal', 'department' => 'IT']],
                        'visualization' => ['type' => 'table'],
                        'position' => ['column' => 2, 'row' => 2],
                    ],
                    [
                        'id' => 'empty-table',
                        'title' => 'Empty table',
                        'data' => [],
                        'visualization' => ['type' => 'table'],
                        'position' => ['column' => 1, 'row' => 3],
                    ],
                ],
            ],
        ];

        $this->postJson(route('dashboard.configuration'), $payload)
            ->assertOk()
            ->assertJsonPath('dashboard.title', 'Team Overview')
            ->assertJsonPath('dashboard.widgets.0.visualization.type', 'pie')
            ->assertJsonPath('dashboard.widgets.1.visualization.type', 'bar')
            ->assertJsonPath('dashboard.widgets.2.visualization.type', 'line')
            ->assertJsonPath('dashboard.widgets.3.visualization.type', 'table')
            ->assertJsonPath('dashboard.widgets.3.position.column', 2)
            ->assertJsonPath('dashboard.widgets.3.data.0.department', 'IT')
            ->assertJsonCount(0, 'dashboard.widgets.4.data');
    }

    public function test_configuration_endpoint_returns_422_for_unsupported_visualization_type(): void
    {
        $this->postJson(route('dashboard.configuration'), [
            'dashboard' => [
                'title' => 'Invalid Dashboard',
                'widgets' => [[
                    'id' => 'unknown-chart',
                    'title' => 'Unknown chart',
                    'data' => [],
                    'visualization' => ['type' => 'scatter'],
                    'position' => ['column' => 1, 'row' => 1],
                ]],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dashboard.widgets.0.visualization.type');
    }

    public function test_configuration_endpoint_returns_422_for_negative_pie_values(): void
    {
        $response = $this->postJson(route('dashboard.configuration'), [
            'dashboard' => [
                'title' => 'Invalid Pie',
                'widgets' => [[
                    'id' => 'negative-pie',
                    'title' => 'Negative values',
                    'data' => [['name' => 'A', 'value' => -1]],
                    'visualization' => ['type' => 'pie', 'labelKey' => 'name', 'valueKey' => 'value'],
                    'position' => ['column' => 1, 'row' => 1],
                ]],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dashboard.widgets.0.data.0.value');
        $this->assertSame(
            'Pie chart values cannot be negative.',
            $response->json('errors')['dashboard.widgets.0.data.0.value'][0],
        );
    }

    public function test_configuration_endpoint_returns_422_for_missing_table_values_and_duplicate_positions(): void
    {
        $this->postJson(route('dashboard.configuration'), [
            'dashboard' => [
                'title' => 'Invalid Table',
                'widgets' => [
                    [
                        'id' => 'first-table',
                        'title' => 'First table',
                        'data' => [['name' => 'Kamal']],
                        'visualization' => ['type' => 'table', 'columns' => [['key' => 'department', 'label' => 'Department']]],
                        'position' => ['column' => 1, 'row' => 1],
                    ],
                    [
                        'id' => 'second-table',
                        'title' => 'Second table',
                        'data' => [],
                        'visualization' => ['type' => 'table'],
                        'position' => ['column' => 1, 'row' => 1],
                    ],
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'dashboard.widgets.0.data.0.department',
                'dashboard.widgets.1.position',
            ]);
    }

    public function test_configuration_endpoint_rejects_unexpected_top_level_input(): void
    {
        $this->postJson(route('dashboard.configuration'), [
            'dashboard' => [
                'title' => 'Valid title',
                'widgets' => [[
                    'id' => 'valid-widget',
                    'title' => 'Valid widget',
                    'data' => [],
                    'visualization' => ['type' => 'table'],
                    'position' => ['column' => 1, 'row' => 1],
                ]],
            ],
            'unexpected' => 'value',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dashboard');
    }

    public function test_configuration_endpoint_returns_422_for_duplicate_widget_ids(): void
    {
        $this->postJson(route('dashboard.configuration'), [
            'dashboard' => [
                'title' => 'Duplicate IDs',
                'widgets' => [
                    [
                        'id' => 'same-id',
                        'title' => 'First',
                        'data' => [],
                        'visualization' => ['type' => 'table'],
                        'position' => ['column' => 1, 'row' => 1],
                    ],
                    [
                        'id' => 'same-id',
                        'title' => 'Second',
                        'data' => [],
                        'visualization' => ['type' => 'table'],
                        'position' => ['column' => 2, 'row' => 1],
                    ],
                ],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dashboard.widgets.0.id');
    }
}
