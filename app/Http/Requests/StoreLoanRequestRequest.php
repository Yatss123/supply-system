<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Supply;
use App\Models\SupplyVariant;

class StoreLoanRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'department_id' => [
                'required',
                'integer',
                'exists:departments,id'
            ],
            'purpose' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'needed_from_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'expected_return_date' => [
                'required',
                'date',
                'after:needed_from_date'
            ],
            // New unified payload: JSON string describing requested items
            'request' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Must be valid JSON array of items
                    $decoded = null;
                    try {
                        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable $e) {
                        return $fail('Request must be valid JSON.');
                    }

                    if (!is_array($decoded)) {
                        return $fail('Request must be a JSON array of items.');
                    }
                    if (count($decoded) < 1) {
                        return $fail('Request must include at least one item.');
                    }
                    // Track aggregated quantities per supply/variant to ensure totals don't exceed availability
                    $totalsBySupply = [];
                    $totalsByVariant = [];

                    foreach ($decoded as $idx => $item) {
                        $sid = data_get($item, 'supply_id');
                        $qty = (int) data_get($item, 'quantity');
                        $vid = data_get($item, 'variant_id');
                        if (!$sid || !is_numeric($sid)) {
                            $fail("Item #" . ($idx + 1) . " must include a valid supply_id.");
                            continue;
                        }
                        if ($qty < 1) {
                            $fail("Item #" . ($idx + 1) . " must include a quantity of at least 1.");
                            continue;
                        }
                        $supply = Supply::find($sid);
                        if (!$supply) {
                            $fail("Item #" . ($idx + 1) . " references a missing supply.");
                            continue;
                        }
                        if (!$supply->isBorrowable()) {
                            $fail("Item #" . ($idx + 1) . " cannot be borrowed (supply not borrowable).");
                            continue;
                        }
                        // Variant-aware validation
                        if ($supply->hasVariants()) {
                            if (!$vid || !is_numeric($vid)) {
                                $fail("Item #" . ($idx + 1) . " must include a valid variant_id for supplies with variants.");
                                continue;
                            }
                            $variant = SupplyVariant::find($vid);
                            if (!$variant || (int)$variant->supply_id !== (int)$supply->id) {
                                $fail("Item #" . ($idx + 1) . " references an invalid variant for the selected supply.");
                                continue;
                            }
                            if ((int)$variant->quantity < $qty) {
                                $fail('Requested quantity exceeds available stock for the selected variant of ' . ($supply->name ?? 'selected supply') . '. Available: ' . (int)$variant->quantity);
                                continue;
                            }
                            // Accumulate totals per variant
                            $totalsByVariant[$variant->id] = ($totalsByVariant[$variant->id] ?? 0) + $qty;
                        } else {
                            // Non-variant supplies
                            if ($supply->availableQuantity() < $qty) {
                                $fail('Requested quantity exceeds available stock for ' . ($supply->name ?? 'selected supply') . '. Available: ' . $supply->availableQuantity());
                                continue;
                            }
                            $totalsBySupply[$supply->id] = ($totalsBySupply[$supply->id] ?? 0) + $qty;
                        }
                    }

                    // Validate aggregated totals against availability to prevent duplicate-item over-requests
                    if (!empty($totalsBySupply)) {
                        $supplies = Supply::whereIn('id', array_keys($totalsBySupply))->get()->keyBy('id');
                        foreach ($totalsBySupply as $sid => $sumQty) {
                            $s = $supplies->get($sid);
                            if (!$s) { continue; }
                            $available = (int) $s->availableQuantity();
                            if ($sumQty > $available) {
                                $fail('Total requested for ' . ($s->name ?? 'selected supply') . ' exceeds available stock. Requested: ' . $sumQty . ', Available: ' . $available);
                            }
                        }
                    }

                    if (!empty($totalsByVariant)) {
                        $variants = SupplyVariant::whereIn('id', array_keys($totalsByVariant))->get()->keyBy('id');
                        foreach ($totalsByVariant as $vid => $sumQty) {
                            $v = $variants->get($vid);
                            if (!$v) { continue; }
                            $available = (int) $v->quantity;
                            if ($sumQty > $available) {
                                $fail('Total requested for a selected variant exceeds available stock. Requested: ' . $sumQty . ', Available: ' . $available);
                            }
                        }
                    }
                }
            ],
        ];

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'request.required' => 'Please provide the requested items as JSON.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'The selected department does not exist.',
            'needed_from_date.required' => 'Please specify when you need the item from.',
            'needed_from_date.after_or_equal' => 'Start date must be today or in the future.',
            'purpose.max' => 'Purpose cannot exceed 1000 characters.',
            'expected_return_date.required' => 'Please specify the expected return date.',
            'expected_return_date.after' => 'Expected return date must be after the start date.'
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'request' => 'request JSON payload',
            'department_id' => 'department',
            'needed_from_date' => 'start date',
            'expected_return_date' => 'expected return date'
        ];
    }
}