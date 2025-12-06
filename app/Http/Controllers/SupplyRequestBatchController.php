<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplyRequestBatch;
use App\Models\SupplyRequest;
use App\Models\RestockRequest;
use App\Models\Supply;
use App\Models\DepartmentCart;
use App\Models\DepartmentCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyRequestBatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Admin-only access for batch actions
        $this->middleware(function ($request, $next) {
            $action = $request->route()->getActionMethod();
            $restricted = ['approveAll', 'approveSelected'];
            if (in_array($action, $restricted)) {
                $user = Auth::user();
                if (!$user || !$user->hasAdminPrivileges()) {
                    abort(403, 'Unauthorized. Admin access required.');
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = SupplyRequestBatch::with(['user', 'department'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('department', fn($d) => $d->where('department_name', 'like', "%{$search}%"));
            });
        }

        // Non-admins see only their own department batches
        if (!Auth::user()->hasAdminPrivileges()) {
            $query->where('department_id', Auth::user()->department_id);
        }

        // Advisers see only their own batches
        if (Auth::user()->hasRole('adviser')) {
            $query->where('user_id', Auth::id());
        }

        $batches = $query->paginate(10);
        return view('supply_requests.batches.index', compact('batches', 'search'));
    }

    public function show(SupplyRequestBatch $batch)
    {
        $batch->load(['items.user', 'items.department', 'items.supply']);
        return view('supply_requests.batches.show', compact('batch'));
    }

    public function approveAll(SupplyRequestBatch $batch)
    {
        $this->authorizeAdmin();

        \DB::beginTransaction();
        try {
            $items = $batch->items()->where('status', 'pending')->get();
            foreach ($items as $item) {
                $item->update(['status' => 'approved']);

                // Add approved item to department cart
                $cart = DepartmentCart::forDepartment((int) $item->department_id);
                $resolvedSupply = Supply::whereRaw('LOWER(name) = ?', [strtolower($item->item_name)])
                    ->first();
                $itemType = DepartmentCartItem::TYPE_CONSUMABLE;
                if ($resolvedSupply && in_array($resolvedSupply->supply_type, [Supply::TYPE_CONSUMABLE, Supply::TYPE_GRANTABLE])) {
                    $itemType = $resolvedSupply->supply_type;
                }
                DepartmentCartItem::create([
                    'cart_id' => $cart->id,
                    'supply_request_id' => $item->id,
                    'supply_id' => $resolvedSupply?->id,
                    'item_name' => $item->item_name,
                    'unit' => $item->unit,
                    'quantity' => (int) $item->quantity,
                    'item_type' => $itemType,
                    'attributes' => [
                        'requested_description' => $item->description,
                        'requested_by' => $item->user_id,
                    ],
                    'status' => 'pending',
                ]);

                // Audit
                \App\Models\SupplyRequestAudit::create([
                    'supply_request_id' => $item->id,
                    'user_id' => Auth::id(),
                    'action' => 'approved',
                ]);

                // Notify owner
                if ($item->user) {
                    $item->user->notify(new \App\Notifications\SupplyRequestStatusNotification($item, 'approved'));
                }
            }

            // Update batch status
            $this->updateBatchStatus($batch);

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Batch approval failed: ' . $e->getMessage()]);
        }

        return redirect()->route('supply-request-batches.show', $batch)->with('success', 'All pending items approved.');
    }

    public function approveSelected(Request $request, SupplyRequestBatch $batch)
    {
        $this->authorizeAdmin();
        $itemIds = $request->input('item_ids', []);
        $notes = $request->input('notes', []);
        if (!is_array($itemIds) || count($itemIds) === 0) {
            return redirect()->back()->withErrors(['error' => 'No items selected for approval.']);
        }

        \DB::beginTransaction();
        try {
            $items = $batch->items()->whereIn('id', $itemIds)->where('status', 'pending')->get();
            foreach ($items as $item) {
                $item->update([
                    'status' => 'approved',
                    'admin_note' => $notes[$item->id] ?? null,
                ]);

                // Add approved item to department cart
                $cart = DepartmentCart::forDepartment((int) $item->department_id);
                $resolvedSupply = Supply::whereRaw('LOWER(name) = ?', [strtolower($item->item_name)])
                    ->first();
                $itemType = DepartmentCartItem::TYPE_CONSUMABLE;
                if ($resolvedSupply && in_array($resolvedSupply->supply_type, [Supply::TYPE_CONSUMABLE, Supply::TYPE_GRANTABLE])) {
                    $itemType = $resolvedSupply->supply_type;
                }
                DepartmentCartItem::create([
                    'cart_id' => $cart->id,
                    'supply_request_id' => $item->id,
                    'supply_id' => $resolvedSupply?->id,
                    'item_name' => $item->item_name,
                    'unit' => $item->unit,
                    'quantity' => (int) $item->quantity,
                    'item_type' => $itemType,
                    'attributes' => [
                        'requested_description' => $item->description,
                        'requested_by' => $item->user_id,
                    ],
                    'status' => 'pending',
                ]);

                \App\Models\SupplyRequestAudit::create([
                    'supply_request_id' => $item->id,
                    'user_id' => Auth::id(),
                    'action' => 'approved',
                    'note' => $notes[$item->id] ?? null,
                ]);

                if ($item->user) {
                    $item->user->notify(new \App\Notifications\SupplyRequestStatusNotification($item, 'approved'));
                }
            }

            $this->updateBatchStatus($batch);
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Selective approval failed: ' . $e->getMessage()]);
        }

        return redirect()->route('supply-request-batches.show', $batch)->with('success', 'Selected items approved.');
    }

    protected function updateBatchStatus(SupplyRequestBatch $batch): void
    {
        $batch->load('items');
        $statuses = $batch->items->pluck('status');
        $newStatus = 'pending';
        if ($statuses->count() > 0) {
            if ($statuses->every(fn($s) => $s === 'approved')) {
                $newStatus = 'approved';
            } elseif ($statuses->every(fn($s) => in_array($s, ['rejected', 'declined']))) {
                $newStatus = 'rejected';
            } elseif ($statuses->contains('approved') || $statuses->contains('rejected') || $statuses->contains('declined')) {
                $newStatus = 'partial';
            }
        }
        $batch->update(['status' => $newStatus]);
    }

    protected function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }
}