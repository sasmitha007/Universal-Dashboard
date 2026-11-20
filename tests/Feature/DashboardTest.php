<?php

namespace Tests\Feature;

use App\Services\DashboardDataProvider;
use InvalidArgumentException;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_page_loads_with_the_initial_multi_widget_payload(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('dashboard', fn (array $dashboard): bool => $dashboard['title'] === 'Universal Dashboard Demo'
                && count($dashboard['widgets']) === 3)
            ->assertSee('Universal Dashboard')
            ->assertSee('dashboard-grid')
            ->assertSee('sales-chart');
    }

    public function test_each_sample_endpoint_returns_its_configuration_and_records(): void
    {
        $expected = [
            'sales' => ['pie', 3],
            'support' => ['bar', 5],
            'inventory' => ['list', 3],
        ];

        foreach ($expected as $identifier => [$type, $count]) {
            $response = $this->getJson(route('dashboard.data', $identifier));
            $response->assertOk()
                ->assertJsonPath('dataset', $identifier)
                ->assertJsonPath('configuration.type', $type)
                ->assertJsonCount($count, 'data');
        }

        $this->getJson(route('dashboard.data', 'sales'))
            ->assertJsonPath('data.0.name', 'Product A')
            ->assertJsonPath('data.0.value', 500);
        $this->getJson(route('dashboard.data', 'support'))
            ->assertJsonPath('data.0.name', 'Monday')
            ->assertJsonPath('data.0.value', 20);
        $this->getJson(route('dashboard.data', 'inventory'))
            ->assertJsonPath('data.0.product', 'Foundation')
            ->assertJsonPath('data.0.stock', 25);
    }

    public function test_unknown_dataset_returns_not_found(): void
    {
        $this->getJson(route('dashboard.data', 'arbitrary-class'))
            ->assertNotFound()
            ->assertJsonPath('message', 'Unsupported dataset identifier.');
    }

    public function test_supported_visualization_types_and_generic_field_names_are_accepted(): void
    {
        $provider = new DashboardDataProvider;
        $provider->validate([['label' => 'One', 'amount' => 4]], ['title' => 'Generic chart', 'type' => 'bar', 'labelKey' => 'label', 'valueKey' => 'amount']);
        $provider->validate([['label' => 'One', 'amount' => 4]], ['title' => 'Generic pie', 'type' => 'pie', 'labelKey' => 'label', 'valueKey' => 'amount']);
        $provider->validate([['code' => 'X', 'quantity' => 3]], ['title' => 'Generic table', 'type' => 'list', 'columns' => [['key' => 'code', 'label' => 'Code'], ['key' => 'quantity', 'label' => 'Quantity']]]);

        $this->assertTrue(true);
    }

    public function test_empty_data_is_valid_but_invalid_configurations_are_rejected(): void
    {
        $provider = new DashboardDataProvider;
        $provider->validate([], ['title' => 'Empty', 'type' => 'pie', 'labelKey' => 'label', 'valueKey' => 'value']);

        $this->expectException(InvalidArgumentException::class);
        $provider->validate([], ['title' => 'Bad', 'type' => 'scatter']);
    }

    public function test_invalid_chart_values_and_missing_list_values_are_rejected(): void
    {
        $provider = new DashboardDataProvider;
        try {
            $provider->validate([['name' => 'A', 'value' => -1]], ['title' => 'Bad pie', 'type' => 'pie', 'labelKey' => 'name', 'valueKey' => 'value']);
            $this->fail('Negative pie value should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Pie chart values cannot be negative.', $exception->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $provider->validate([['product' => 'A']], ['title' => 'List', 'type' => 'list', 'columns' => [['key' => 'stock', 'label' => 'Stock']]]);
    }
}
