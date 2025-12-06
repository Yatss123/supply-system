<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $suppliers = Supplier::when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('contact_person', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->paginate(10);

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone1' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'facebook_messenger' => 'nullable|string|max:255',
            'preferred_contact_method' => 'required|in:email,phone,facebook_messenger',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
        ]);

        Supplier::create($request->only([
            'name',
            'contact_person',
            'phone1',
            'phone2',
            'email',
            'facebook_messenger',
            'preferred_contact_method',
            'address1',
            'address2'
        ]));

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function show(Supplier $supplier)
    {
        // Paginate related supplies (10 per page) and eager load categories
        $supplies = $supplier->supplies()
            ->with('categories')
            ->orderBy('name')
            ->paginate(10);

        return view('suppliers.show', compact('supplier', 'supplies'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone1' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'facebook_messenger' => 'nullable|string|max:255',
            'preferred_contact_method' => 'required|in:email,phone,facebook_messenger',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
        ]);

        $supplier->update($request->only([
            'name',
            'contact_person',
            'phone1',
            'phone2',
            'email',
            'facebook_messenger',
            'preferred_contact_method',
            'address1',
            'address2'
        ]));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Activate a supplier.
     */
    public function activate(Supplier $supplier)
    {
        $supplier->activate();
        return redirect()->back()->with('success', 'Supplier activated successfully.');
    }

    /**
     * Deactivate a supplier.
     */
    public function deactivate(Supplier $supplier)
    {
        $supplier->deactivate();
        return redirect()->back()->with('success', 'Supplier deactivated successfully.');
    }
}
