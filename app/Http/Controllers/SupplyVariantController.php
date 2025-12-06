<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class SupplyVariantController extends Controller
{
    /**
     * Display a listing of variants for a specific supply.
     */
    public function index(Supply $supply): JsonResponse
    {
        $variants = $supply->variants()->get();
        return response()->json($variants);
    }

    /**
     * Show the form for creating a new variant.
     */
    public function create(Supply $supply): View
    {
        return view('supplies.variants.create', compact('supply'));
    }

    /**
     * Store a newly created variant in storage.
     */
    public function store(Request $request, Supply $supply): RedirectResponse
    {
        // Debug: Log the incoming request data
        Log::info('Variant creation request data:', $request->all());
        
        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required_with:attributes|string',
            'attributes.*.value' => 'required_with:attributes|string',
            'quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:supply_variants,sku',
            'price' => 'nullable|numeric|min:0',
            'tin' => 'nullable|string|max:255'
        ]);

        // Process attributes
        $attributes = [];
        if (isset($validated['attributes'])) {
            Log::info('Processing attributes:', $validated['attributes']);
            foreach ($validated['attributes'] as $attribute) {
                if (!empty($attribute['key']) && !empty($attribute['value'])) {
                    $attributes[$attribute['key']] = $attribute['value'];
                }
            }
        }
        
        Log::info('Final processed attributes:', $attributes);

        // Enable variants for the supply if not already enabled
        if (!$supply->has_variants) {
            $supply->enableVariants();
        }

        // Generate SKU automatically if not provided
        $sku = $validated['sku'];
        if (empty($sku)) {
            $sku = SupplyVariant::generateSku($supply, $validated['variant_name'], $attributes);
        }

        $supply->createVariant([
            'variant_name' => $validated['variant_name'],
            'attributes' => $attributes,
            'quantity' => $validated['quantity'],
            'sku' => $sku,
            'price' => $validated['price'],
            'tin' => $validated['tin'] ?? null
        ]);

        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Display the specified variant.
     */
    public function show(SupplyVariant $variant): View
    {
        $supply = $variant->supply;
        return view('supplies.variants.show', compact('supply', 'variant'));
    }

    /**
     * Show the form for editing the specified variant.
     */
    public function edit(SupplyVariant $variant): View
    {
        $supply = $variant->supply;
        return view('supplies.variants.edit', compact('supply', 'variant'));
    }

    /**
     * Update the specified variant in storage.
     */
    public function update(Request $request, SupplyVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required_with:attributes|string',
            'attributes.*.value' => 'required_with:attributes|string',
            'quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:supply_variants,sku,' . $variant->id,
            'price' => 'nullable|numeric|min:0',
            'tin' => 'nullable|string|max:255'
        ]);

        // Process attributes
        $attributes = [];
        if (isset($validated['attributes'])) {
            foreach ($validated['attributes'] as $attribute) {
                if (!empty($attribute['key']) && !empty($attribute['value'])) {
                    $attributes[$attribute['key']] = $attribute['value'];
                }
            }
        }

        // Generate SKU automatically if not provided
        $sku = $validated['sku'];
        if (empty($sku)) {
            $sku = SupplyVariant::generateSku($variant->supply, $validated['variant_name'], $attributes);
        }

        $variant->update([
            'variant_name' => $validated['variant_name'],
            'attributes' => $attributes,
            'quantity' => $validated['quantity'],
            'sku' => $sku,
            'price' => $validated['price'],
            'tin' => $validated['tin'] ?? null
        ]);

        return redirect()->route('supplies.show', $variant->supply)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Remove the specified variant from storage.
     */
    public function destroy(SupplyVariant $variant): RedirectResponse
    {
        // Change delete to disable: mark variant as inactive instead of deleting
        $variant->disable();
        $supply = $variant->supply;
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Variant disabled successfully.');
    }

    /**
     * Disable a variant (status -> inactive).
     */
    public function disable(SupplyVariant $variant): RedirectResponse
    {
        $variant->disable();
        $supply = $variant->supply;
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Variant disabled successfully.');
    }

    /**
     * Enable a variant (status -> active).
     */
    public function enable(SupplyVariant $variant): RedirectResponse
    {
        $variant->enable();
        $supply = $variant->supply;
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Variant enabled successfully.');
    }

    /**
     * Get variants for a supply (AJAX endpoint).
     */
    public function getVariants($supplyId): JsonResponse
    {
        try {
            $supply = Supply::findOrFail($supplyId);
            // Fetch full variant models to ensure status and relations are loaded for availability calculation
            $variants = $supply->variants()->where('status', 'active')->get();

            $variants = $variants->map(function ($variant) use ($supply) {
                return [
                    'id' => $variant->id,
                    'display_name' => $variant->display_name,
                    // Keep quantity for backward compatibility; include computed availability
                    'quantity' => (int) $variant->quantity,
                    'available' => (int) (method_exists($variant, 'availableQuantity') ? $variant->availableQuantity() : $variant->quantity),
                    'unit' => $supply->unit, // Get unit from parent supply
                    'minimum_stock_level' => $supply->minimum_stock_level, // Get from parent supply
                    'price' => $variant->price,
                    'tin' => $variant->tin
                ];
            });

            return response()->json([
                'variants' => $variants,
                'unit' => $supply->unit
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Supply not found',
                'message' => 'The requested supply does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load variants',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update variant stock via AJAX.
     */
    public function updateStock(Request $request, SupplyVariant $variant): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $variant->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'variant' => $variant->fresh()
        ]);
    }

    /**
     * Resolve a variant by SKU and return its parent supply and availability.
     */
    public function getVariantBySku(string $sku): JsonResponse
    {
        try {
            $skuNorm = strtoupper(trim($sku));
            $variant = SupplyVariant::whereRaw('UPPER(sku) = ?', [$skuNorm])
                ->where('status', 'active')
                ->first();

            if (!$variant) {
                return response()->json([
                    'error' => 'Variant not found',
                    'message' => 'No active variant matches the provided SKU'
                ], 404);
            }

            $supply = $variant->supply;
            $available = method_exists($variant, 'availableQuantity') ? $variant->availableQuantity() : $variant->quantity;

            return response()->json([
                'variant' => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'display_name' => method_exists($variant, 'getAttribute') ? $variant->display_name : ($variant->variant_name ?? ('Variant #' . $variant->id)),
                    'available' => $available,
                    'unit' => $supply->unit,
                ],
                'supply' => [
                    'id' => $supply->id,
                    'name' => $supply->name,
                    'unit' => $supply->unit,
                    'has_variants' => (bool) $supply->has_variants,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to resolve variant by SKU',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
