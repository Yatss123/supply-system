<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentCart;
use App\Models\DepartmentCartItem;
use App\Models\RestockRequest;
use App\Models\Supply;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentCartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Admin-only for edit and finalize
        $this->middleware(function ($request, $next) {
            $restricted = ['updateItem', 'finalize'];
            if (in_array($request->route()->getActionMethod(), $restricted)) {
                $user = Auth::user();
                if (!$user || !$user->hasAdminPrivileges()) {
                    abort(403, 'Unauthorized. Admin access required.');
                }
            }
            return $next($request);
        });
    }

    public function show(Department $department)
    {
        $cart = DepartmentCart::forDepartment($department->id);
        $cart->load(['items.supplyRequest', 'items.supply']);

        $consumables = $cart->items->where('item_type', DepartmentCartItem::TYPE_CONSUMABLE);
        $grantables = $cart->items->where('item_type', DepartmentCartItem::TYPE_GRANTABLE);

        $supplierIds = $cart->items->map(function($item){
            return $item->attributes['supplier_id'] ?? null;
        })->filter()->unique()->values()->all();
        $supplierNames = collect();
        if (count($supplierIds) > 0) {
            $supplierNames = Supplier::whereIn('id', $supplierIds)->get(['id','name'])->keyBy('id')->map->name;
        }
        return view('department_carts.show', compact('department', 'cart', 'consumables', 'grantables', 'supplierNames'));
    }

    public function updateItem(Request $request, DepartmentCart $cart, DepartmentCartItem $item)
    {
        // Ensure the item belongs to the cart to prevent cross-edit
        if ($item->cart_id !== $cart->id) {
            abort(404);
        }

        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'item_type' => 'nullable|in:' . DepartmentCartItem::TYPE_CONSUMABLE . ',' . DepartmentCartItem::TYPE_GRANTABLE,
            'unit' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
        ]);

        $update = [];
        foreach (['quantity', 'item_type', 'unit'] as $field) {
            if (array_key_exists($field, $validated)) {
                $update[$field] = $validated[$field];
            }
        }
        if (array_key_exists('attributes', $validated)) {
            $update['attributes'] = $validated['attributes'];
        }

        if (!empty($update)) {
            $update['status'] = 'edited';
            $item->update($update);
        }

        return redirect()->back()->with('success', 'Cart item updated.');
    }

    public function finalize(Request $request, DepartmentCart $cart)
    {
        // Admin-only enforced in middleware
        $cart->load('items');
        if ($cart->items->count() === 0) {
            return redirect()->back()->withErrors(['error' => 'Cart is empty. Nothing to finalize.']);
        }

        \DB::beginTransaction();
        try {
            foreach ($cart->items as $item) {
                // Resolve or create supply (avoid creating on approval to keep cart flexible)
                $supply = $item->supply;
                if (!$supply) {
                    $supplyType = $item->item_type;
                    $supply = Supply::firstOrCreate(
                        ['name' => $item->item_name],
                        [
                            'unit' => $item->unit ?? 'unit',
                            'quantity' => 0,
                            'minimum_stock_level' => 10,
                            'status' => 'active',
                            'supply_type' => $supplyType,
                        ]
                    );
                    $item->update(['supply_id' => $supply->id]);
                }

                // Create restock request directly as 'ordered' upon finalization
                $payload = [
                    'supply_id' => $supply->id,
                    'quantity' => (int) $item->quantity,
                    'status' => 'ordered',
                ];
                // Optional supplier assignment via attributes
                $supplierId = $item->attributes['supplier_id'] ?? null;
                if ($supplierId) {
                    $payload['supplier_id'] = $supplierId;
                }
                // Capture requesting department from the cart
                if ($cart->department_id) {
                    $payload['requested_department_id'] = (int) $cart->department_id;
                }
                RestockRequest::create($payload);
            }

            $cart->update([
                'status' => 'finalized',
                'updated_by' => Auth::id(),
            ]);
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Finalize failed: ' . $e->getMessage()]);
        }

        return redirect()->route('restock-requests.index')->with('success', 'Order finalized. Restock requests created as ordered.');
    }
}
