<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RenderDashboardConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dashboard' => ['required', 'array:title,widgets'],
            'dashboard.title' => ['required', 'string', 'max:120'],
            'dashboard.widgets' => ['required', 'array', 'min:1', 'max:24'],
            'dashboard.widgets.*' => ['required', 'array:id,title,data,visualization,position'],
            'dashboard.widgets.*.id' => ['required', 'string', 'alpha_dash', 'max:64', 'distinct'],
            'dashboard.widgets.*.title' => ['required', 'string', 'max:120'],
            'dashboard.widgets.*.data' => ['present', 'array', 'max:500'],
            'dashboard.widgets.*.data.*' => ['array', 'max:50'],
            'dashboard.widgets.*.visualization' => ['required', 'array:type,labelKey,valueKey,columns'],
            'dashboard.widgets.*.visualization.type' => ['required', 'string', Rule::in(['pie', 'bar', 'line', 'table'])],
            'dashboard.widgets.*.visualization.labelKey' => ['sometimes', 'nullable', 'string', 'max:64'],
            'dashboard.widgets.*.visualization.valueKey' => ['sometimes', 'nullable', 'string', 'max:64'],
            'dashboard.widgets.*.visualization.columns' => ['sometimes', 'array', 'min:1', 'max:20'],
            'dashboard.widgets.*.visualization.columns.*' => ['required', 'array:key,label'],
            'dashboard.widgets.*.visualization.columns.*.key' => ['required', 'string', 'max:64', 'distinct'],
            'dashboard.widgets.*.visualization.columns.*.label' => ['required', 'string', 'max:120'],
            'dashboard.widgets.*.position' => ['required', 'array:column,row'],
            'dashboard.widgets.*.position.column' => ['required', 'integer', 'min:1', 'max:2'],
            'dashboard.widgets.*.position.row' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }

    /**
     * Validate constraints that depend on multiple widget fields or records.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $input = $this->all();
            if (array_diff(array_keys($input), ['dashboard']) !== []) {
                $validator->errors()->add('dashboard', 'The payload may only contain a dashboard object.');

                return;
            }

            $occupiedPositions = [];
            foreach ($input['dashboard']['widgets'] as $index => $widget) {
                $position = $widget['position'];
                $positionKey = $position['column'].'-'.$position['row'];
                if (isset($occupiedPositions[$positionKey])) {
                    $validator->errors()->add("dashboard.widgets.{$index}.position", 'Each widget must use a unique grid position.');
                }
                $occupiedPositions[$positionKey] = true;

                $type = $widget['visualization']['type'];
                $data = $widget['data'];
                if (! array_is_list($data)) {
                    $validator->errors()->add("dashboard.widgets.{$index}.data", 'Widget data must be a JSON array of records.');

                    continue;
                }

                foreach ($data as $recordIndex => $record) {
                    if (! is_array($record) || array_is_list($record)) {
                        $validator->errors()->add("dashboard.widgets.{$index}.data.{$recordIndex}", 'Each data item must be a record object.');

                        continue;
                    }
                }

                if (in_array($type, ['pie', 'bar', 'line'], true)) {
                    $labelKey = $widget['visualization']['labelKey'] ?? null;
                    $valueKey = $widget['visualization']['valueKey'] ?? null;
                    if (! is_string($labelKey) || trim($labelKey) === '') {
                        $validator->errors()->add("dashboard.widgets.{$index}.visualization.labelKey", 'A label field is required for chart widgets.');
                    }
                    if (! is_string($valueKey) || trim($valueKey) === '') {
                        $validator->errors()->add("dashboard.widgets.{$index}.visualization.valueKey", 'A value field is required for chart widgets.');
                    }

                    if (! is_string($labelKey) || $labelKey === '' || ! is_string($valueKey) || $valueKey === '') {
                        continue;
                    }

                    foreach ($data as $recordIndex => $record) {
                        if (! is_array($record) || array_is_list($record)) {
                            continue;
                        }
                        if (! array_key_exists($labelKey, $record) || ! is_scalar($record[$labelKey]) || trim((string) $record[$labelKey]) === '') {
                            $validator->errors()->add("dashboard.widgets.{$index}.data.{$recordIndex}.{$labelKey}", 'A configured chart label is missing or invalid.');
                        }
                        $value = $record[$valueKey] ?? null;
                        if (! is_numeric($value) || ! is_finite((float) $value)) {
                            $validator->errors()->add("dashboard.widgets.{$index}.data.{$recordIndex}.{$valueKey}", 'A configured chart value must be a finite number.');
                        } elseif ($type === 'pie' && (float) $value < 0) {
                            $validator->errors()->add("dashboard.widgets.{$index}.data.{$recordIndex}.{$valueKey}", 'Pie chart values cannot be negative.');
                        }
                    }

                    if (isset($widget['visualization']['columns'])) {
                        $validator->errors()->add("dashboard.widgets.{$index}.visualization.columns", 'Columns may only be configured for table widgets.');
                    }

                    continue;
                }

                $columns = $widget['visualization']['columns'] ?? null;
                if ($columns === null && $data !== [] && is_array($data[0]) && ! array_is_list($data[0])) {
                    $columns = array_map(
                        fn (string|int $key): array => ['key' => (string) $key, 'label' => (string) $key],
                        array_keys($data[0]),
                    );
                }

                if ($columns !== null) {
                    foreach ($data as $recordIndex => $record) {
                        if (! is_array($record) || array_is_list($record)) {
                            continue;
                        }
                        foreach ($columns as $column) {
                            $key = $column['key'];
                            if (! array_key_exists($key, $record) || ! is_scalar($record[$key])) {
                                $validator->errors()->add("dashboard.widgets.{$index}.data.{$recordIndex}.{$key}", 'Every table record must contain a scalar value for each configured column.');
                            }
                        }
                    }
                }

                if (isset($widget['visualization']['labelKey']) || isset($widget['visualization']['valueKey'])) {
                    $validator->errors()->add("dashboard.widgets.{$index}.visualization", 'Chart field mappings may only be configured for chart widgets.');
                }
            }
        }];
    }
}
