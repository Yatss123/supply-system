@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Department Cart</h1>
        <div class="mt-2 flex items-center justify-between">
            <div class="text-sm text-gray-600 flex items-center">
                <span>Department:</span>
                <a href="{{ route('departments.show', $department) }}" class="ml-1 font-medium text-blue-600 hover:underline">
                    {{ $department->department_name ?? $department->name ?? 'N/A' }}
                </a>
                <span class="ml-4">Status: <span class="font-medium capitalize">{{ $cart->status }}</span></span>
                @if(auth()->user()->hasRole('dean') && !auth()->user()->hasAdminPrivileges())
                    <span class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Read-only (Dean)</span>
                @endif
            </div>
            <button type="button" onclick="window.history.back()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 text-sm rounded hover:bg-gray-200">
                Back
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <button id="btn-consumables" type="button" class="px-4 py-2 text-sm border rounded-l {{ request('view') === 'grantables' ? 'bg-white text-gray-700' : 'bg-blue-600 text-white' }}" onclick="window.toggleList && window.toggleList('consumables')">Consumables</button>
            <button id="btn-grantables" type="button" class="px-4 py-2 text-sm border rounded-r {{ request('view') === 'grantables' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' }}" onclick="window.toggleList && window.toggleList('grantables')">Grantables</button>
        </div>
    </div>

    <div class="mt-4">
        <div id="consumables-section" class="{{ request('view') === 'grantables' ? 'hidden' : '' }}">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Consumables</h2>
            @if($consumables->count() === 0)
                <p class="text-gray-500">No consumable items in the cart.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-700">
                                <th class="px-3 py-2">Item</th>
                                <th class="px-3 py-2">Quantity</th>
                                <th class="px-3 py-2">Unit</th>
                                <th class="px-3 py-2">Available</th>
                                <th class="px-3 py-2">Supplier</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Save</th>
                                <th class="px-3 py-2">Monthly Allocation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($consumables as $item)
                                @if(auth()->user()->hasAdminPrivileges())
                                    <form id="update-item-{{ $item->id }}" method="POST" action="{{ route('department-carts.items.update', [$cart, $item]) }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endif
                                <tr class="align-top">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                        <div class="text-xs text-gray-600">Requested Qty: {{ $item->quantity }} {{ $item->unit }}</div>
                                        @if($item->supply)
                                            <div class="text-xs text-gray-600">Linked Supply: <span class="font-mono">#{{ $item->supply->id }}</span> ({{ $item->supply->getSupplyTypeLabel() }})</div>
                                        @endif
                                    </td>
                                    @if(auth()->user()->hasAdminPrivileges())
                                        <td class="px-3 py-2">
                                            <input form="update-item-{{ $item->id }}" type="number" name="quantity" min="1" value="{{ $item->quantity }}" class="w-24 border rounded px-2 py-1 text-sm" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <input form="update-item-{{ $item->id }}" type="text" name="unit" value="{{ $item->unit }}" class="w-24 border rounded px-2 py-1 text-sm" />
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $item->supply ? ($item->supply->availableQuantity() . ' ' . ($item->supply->unit ?? $item->unit)) : '—' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <input form="update-item-{{ $item->id }}" type="text" name="attributes[supplier_id]" value="{{ $item->attributes['supplier_id'] ?? '' }}" class="w-32 border rounded px-2 py-1 text-sm" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <select form="update-item-{{ $item->id }}" name="item_type" class="border rounded px-2 py-1 text-sm">
                                                <option value="consumable" {{ $item->item_type === 'consumable' ? 'selected' : '' }}>Consumable</option>
                                                <option value="grantable" {{ $item->item_type === 'grantable' ? 'selected' : '' }}>Grantable</option>
                                            </select>
                                        </td>
                                    @else
                                        <td class="px-3 py-2">{{ $item->quantity }}</td>
                                        <td class="px-3 py-2">{{ $item->unit }}</td>
                                        <td class="px-3 py-2">{{ $item->supply ? ($item->supply->availableQuantity() . ' ' . ($item->supply->unit ?? $item->unit)) : '—' }}</td>
                                        <td class="px-3 py-2">{{ isset($item->attributes['supplier_id']) ? ($supplierNames[$item->attributes['supplier_id']] ?? '—') : '—' }}</td>
                                        <td class="px-3 py-2 capitalize">{{ $item->item_type }}</td>
                                    @endif
                                    <td class="px-3 py-2">
                                        <span class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded">{{ ucfirst($item->status) }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if(auth()->user()->hasAdminPrivileges())
                                            <button form="update-item-{{ $item->id }}" type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Save</button>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if(auth()->user()->hasAdminPrivileges() && $item->supply && $item->supply->isConsumable())
                                            <form method="POST" action="{{ route('admin.allocations.items.add', $department->id) }}" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="supply_id" value="{{ $item->supply->id }}" />
                                                <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="border rounded px-2 py-1 text-sm" />
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Add</button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div id="grantables-section" class="{{ request('view') === 'consumables' ? 'hidden' : '' }}">
            <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Grantables</h2>
            @if($grantables->count() === 0)
                <p class="text-gray-500">No grantable items in the cart.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-700">
                                <th class="px-3 py-2">Item</th>
                                <th class="px-3 py-2">Quantity</th>
                                <th class="px-3 py-2">Unit</th>
                                <th class="px-3 py-2">Available</th>
                                <th class="px-3 py-2">Supplier</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Save</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($grantables as $item)
                                @if(auth()->user()->hasAdminPrivileges())
                                    <form id="update-grantable-{{ $item->id }}" method="POST" action="{{ route('department-carts.items.update', [$cart, $item]) }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endif
                                <tr class="align-top">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                        <div class="text-xs text-gray-600">Requested Qty: {{ $item->quantity }} {{ $item->unit }}</div>
                                        @if($item->supply)
                                            <div class="text-xs text-gray-600">Linked Supply: <span class="font-mono">#{{ $item->supply->id }}</span> ({{ $item->supply->getSupplyTypeLabel() }})</div>
                                        @endif
                                    </td>
                                    @if(auth()->user()->hasAdminPrivileges())
                                        <td class="px-3 py-2">
                                            <input form="update-grantable-{{ $item->id }}" type="number" name="quantity" min="1" value="{{ $item->quantity }}" class="w-24 border rounded px-2 py-1 text-sm" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <input form="update-grantable-{{ $item->id }}" type="text" name="unit" value="{{ $item->unit }}" class="w-24 border rounded px-2 py-1 text-sm" />
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $item->supply ? ($item->supply->availableQuantity() . ' ' . ($item->supply->unit ?? $item->unit)) : '—' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <input form="update-grantable-{{ $item->id }}" type="text" name="attributes[supplier_id]" value="{{ $item->attributes['supplier_id'] ?? '' }}" class="w-32 border rounded px-2 py-1 text-sm" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <select form="update-grantable-{{ $item->id }}" name="item_type" class="border rounded px-2 py-1 text-sm">
                                                <option value="consumable" {{ $item->item_type === 'consumable' ? 'selected' : '' }}>Consumable</option>
                                                <option value="grantable" {{ $item->item_type === 'grantable' ? 'selected' : '' }}>Grantable</option>
                                            </select>
                                        </td>
                                    @else
                                        <td class="px-3 py-2">{{ $item->quantity }}</td>
                                        <td class="px-3 py-2">{{ $item->unit }}</td>
                                        <td class="px-3 py-2">{{ $item->supply ? ($item->supply->availableQuantity() . ' ' . ($item->supply->unit ?? $item->unit)) : '—' }}</td>
                                        <td class="px-3 py-2">{{ isset($item->attributes['supplier_id']) ? ($supplierNames[$item->attributes['supplier_id']] ?? '—') : '—' }}</td>
                                        <td class="px-3 py-2 capitalize">{{ $item->item_type }}</td>
                                    @endif
                                    <td class="px-3 py-2">
                                        <span class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded">{{ ucfirst($item->status) }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if(auth()->user()->hasAdminPrivileges())
                                            <button form="update-grantable-{{ $item->id }}" type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Save</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Finalize Department Cart</h2>
                <p class="text-sm text-gray-600">This will create order requests in 'ordered' status for all items.</p>
            </div>
            @if(auth()->user()->hasAdminPrivileges())
            <form method="POST" action="{{ route('department-carts.finalize', $cart) }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                    {{ $cart->status !== 'active' ? 'disabled' : '' }}>
                    Finalize Order
                </button>
            </form>
            @else
            <div class="text-xs text-gray-500">Finalization is restricted to admins.</div>
            @endif
        </div>
    </div>
</div>
<script>
(function(){
  function setView(type){
    var c = document.getElementById('consumables-section');
    var g = document.getElementById('grantables-section');
    if(!c||!g) return;
    c.classList.toggle('hidden', type !== 'consumables');
    g.classList.toggle('hidden', type !== 'grantables');
    var bc = document.getElementById('btn-consumables');
    var bg = document.getElementById('btn-grantables');
    if(bc&&bg){
      bc.classList.remove('bg-blue-600','text-white','bg-white','text-gray-700');
      bg.classList.remove('bg-blue-600','text-white','bg-white','text-gray-700');
      if(type==='consumables'){
        bc.classList.add('bg-blue-600','text-white');
        bg.classList.add('bg-white','text-gray-700');
      } else {
        bg.classList.add('bg-blue-600','text-white');
        bc.classList.add('bg-white','text-gray-700');
      }
    }
    try { history.replaceState(null, '', '?view=' + type); } catch(e){}
  }
  window.toggleList = setView;
  document.addEventListener('DOMContentLoaded', function(){
    setView('{{ request('view') === 'grantables' ? 'grantables' : 'consumables' }}');
  });
})();
</script>
@endsection
