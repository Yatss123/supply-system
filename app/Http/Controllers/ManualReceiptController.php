<?php

namespace App\Http\Controllers;

use App\Models\ManualReceipt;
use App\Models\Supply;
use App\Models\Supplier;
use App\Models\RestockRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManualReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ManualReceipt::with(['supply', 'supplier', 'addedBy']);

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('supply', function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('supplier', function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Filter by status if provided
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }

        $receipts = $query->orderBy('created_at', 'desc')->paginate(15);

        // Preserve query parameters in pagination links
        $receipts->appends($request->query());

        return view('manual_receipts.index', compact('receipts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $supplies = Supply::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('manual_receipts.create', compact('supplies', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'quantity' => 'required|integer|min:1',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'receipt_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Ensure supply is active and has availability
        $supply = Supply::findOrFail($validated['supply_id']);
        if ($supply->status !== 'active') {
            return back()->with('error', 'Selected supply is not active.');
        }
        if (method_exists($supply, 'availableQuantity') && $supply->availableQuantity() <= 0) {
            return back()->with('error', 'Selected supply has no available quantity.');
        }

        $manualReceipt = ManualReceipt::create([
            'supply_id' => $supply->id,
            'quantity' => $request->quantity,
            'supplier_id' => $request->supplier_id,
            'receipt_date' => $request->receipt_date,
            'reference_number' => $request->reference_number,
            'cost_per_unit' => $request->cost_per_unit,
            'notes' => $request->notes,
            'added_by' => auth()->id(),
            'status' => 'verified',
        ]);

        // Update supply quantity
        $supply->quantity += $request->quantity;
        $supply->save();

        // Fulfill any pending restock requests
        $this->fulfillRestockRequests($supply, $request->quantity);

        // Send notification to admin users
        $adminUsers = User::whereHas('role', function($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\ManualReceiptNotification($manualReceipt, 'created'));
        }

        return redirect()->route('supplies.index', ['tab' => 'receipts'])
            ->with('success', 'Manual receipt created successfully and inventory updated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ManualReceipt $manualReceipt)
    {
        $manualReceipt->load(['supply', 'supplier', 'addedBy']);
        return view('manual_receipts.show', compact('manualReceipt'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ManualReceipt $manualReceipt)
    {
        $supplies = Supply::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('manual_receipts.edit', compact('manualReceipt', 'supplies', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ManualReceipt $manualReceipt)
    {
        $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'quantity' => 'required|integer|min:1',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'receipt_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate quantity difference for inventory adjustment
        $oldQuantity = $manualReceipt->quantity;
        $newQuantity = $request->quantity;
        $quantityDifference = $newQuantity - $oldQuantity;

        // Update the manual receipt
        $manualReceipt->update([
            'supply_id' => $request->supply_id,
            'quantity' => $request->quantity,
            'supplier_id' => $request->supplier_id,
            'receipt_date' => $request->receipt_date,
            'reference_number' => $request->reference_number,
            'cost_per_unit' => $request->cost_per_unit,
            'notes' => $request->notes,
        ]);

        // Adjust supply quantity if needed
        if ($quantityDifference != 0) {
            $supply = Supply::find($request->supply_id);
            $supply->quantity += $quantityDifference;
            $supply->save();
        }

        // Send notification to admin users
        $adminUsers = User::whereHas('role', function($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\ManualReceiptNotification($manualReceipt, 'updated'));
        }

        return redirect()->route('supplies.index', ['tab' => 'receipts'])
            ->with('success', 'Manual receipt updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ManualReceipt $manualReceipt)
    {
        DB::transaction(function () use ($manualReceipt) {
            // Reverse the inventory change
            $supply = Supply::find($manualReceipt->supply_id);
            $supply->decrement('quantity', $manualReceipt->quantity);

            // Delete the receipt
            $manualReceipt->delete();
        });

        return redirect()->route('supplies.index', ['tab' => 'receipts'])
            ->with('success', 'Manual receipt deleted and inventory adjusted.');
    }

    /**
     * Fulfill restock requests based on the received quantity
     */
    private function fulfillRestockRequests(Supply $supply, int $receivedQuantity)
    {
        $pendingRequests = RestockRequest::where('supply_id', $supply->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingQuantity = $receivedQuantity;

        foreach ($pendingRequests as $request) {
            if ($remainingQuantity <= 0) {
                break;
            }

            if ($remainingQuantity >= $request->quantity_requested) {
                // Fulfill the entire request
                $request->update([
                    'status' => 'fulfilled',
                    'fulfilled_at' => now(),
                    'fulfilled_by' => auth()->id(),
                ]);
                $remainingQuantity -= $request->quantity_requested;
            } else {
                // Partially fulfill the request
                $request->update([
                    'quantity_requested' => $request->quantity_requested - $remainingQuantity,
                ]);
                $remainingQuantity = 0;
            }
        }
    }
}
