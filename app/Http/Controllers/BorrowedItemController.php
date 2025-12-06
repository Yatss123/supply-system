<?php

namespace App\Http\Controllers;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemLog;
use App\Models\Supply;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BorrowedItemController extends Controller
{
    public function index()
    {
        $borrowedItems = BorrowedItem::all();
        return view('borrowed_items.index', compact('borrowedItems'));
    }

    public function create()
    {
        $supplies = Supply::all();
        $departments = Department::all();
        return view('borrowed_items.create', compact('supplies', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'department_id' => 'required|exists:departments,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $supply = Supply::find($request->supply_id);
        if ($supply->availableQuantity() < $request->quantity) {
            return redirect()->back()->withErrors('Not enough quantity available.');
        }

        $borrowedItem = BorrowedItem::create([
            'supply_id' => $request->supply_id,
            'department_id' => $request->department_id,
            'user_id' => Auth::id(),
            'quantity' => $request->quantity,
            'borrowed_at' => now()
        ]);

        // Do not adjust total supply quantity on borrow; availability is computed dynamically

        // Log borrow action
        BorrowedItemLog::create([
            'borrowed_item_id' => $borrowedItem->id,
            'user_id' => Auth::id(),
            'action' => 'borrowed',
            'quantity' => $request->quantity,
            'notes' => null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Item borrowed successfully.');
    }

    public function return(BorrowedItem $borrowedItem)
    {
        $borrowedItem->update([
            'returned_at' => now()
        ]);

        // Do not adjust total supply quantity on return; availability is computed dynamically

        return redirect()->route('dashboard')->with('success', 'Item returned successfully.');
    }

    public function returnItem(BorrowedItem $borrowedItem)
    {
        $user = Auth::user();

        // Only the borrower can return the item
        if (!$user || $borrowedItem->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Only the borrower can return this item.');
        }

        // Validate optional note and photo
        request()->validate([
            'note' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:5120', // up to 5MB
        ]);

        // Handle optional photo upload
        $photoPath = null;
        if (request()->hasFile('photo')) {
            $photoPath = Storage::disk('public')->putFile('returns', request()->file('photo'));
        }

        // Initiate return for admin verification (pending)
        $borrowedItem->returnItem(request('note'), $photoPath);

        // Log return initiation
        BorrowedItemLog::create([
            'borrowed_item_id' => $borrowedItem->id,
            'user_id' => $user->id,
            'action' => 'return_pending',
            'quantity' => $borrowedItem->quantity,
            'notes' => request('note'),
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('loan-requests.show', $borrowedItem->loanRequest ?? $borrowedItem)
            ->with('success', 'Return submitted for admin verification.');
    }

    public function verifyReturn(Request $request, BorrowedItem $borrowedItem)
    {
        $user = Auth::user();

        // Only admin or super admin can verify returns
        if (!$user || !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Only admin can verify returns.');
        }

        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        // Ensure there is a pending return
        if (is_null($borrowedItem->return_pending_at) || !is_null($borrowedItem->returned_at)) {
            return redirect()->back()->with('error', 'No pending return to verify.');
        }

        $borrowedItem->verifyReturnWithStatus($user, 'returned', null, null, $request->verification_notes);

        // Log verified return
        BorrowedItemLog::create([
            'borrowed_item_id' => $borrowedItem->id,
            'user_id' => $user->id,
            'action' => 'verified_return',
            'quantity' => $borrowedItem->quantity,
            'notes' => $request->verification_notes,
        ]);

        return redirect()->route('loan-requests.show', $borrowedItem->loanRequest ?? $borrowedItem)
            ->with('success', 'Return verified and item marked as returned.');
    }

    public function destroy(BorrowedItem $borrowedItem)
    {
        if (is_null($borrowedItem->returned_at)) {
            $borrowedItem->supply->increment('quantity', $borrowedItem->quantity);
        }
        $borrowedItem->delete();

        return redirect()->route('dashboard')->with('success', 'Record deleted successfully.');
    }

    public function getAvailableQuantity($supplyId)
    {
        $supply = Supply::find($supplyId);
        if ($supply) {
            return response()->json(['quantity' => $supply->availableQuantity()]);
        }
        return response()->json(['error' => 'Supply not found'], 404);
    }
}
