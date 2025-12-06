@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Location Details</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('locations.children.create', $location) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Add Child Location
            </a>
            <button type="button" onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</button>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">{{ $location->name }}</h2>
        </div>
        <div class="p-6">
            <p class="text-gray-700">{{ $location->description }}</p>
            <div class="mt-4 text-sm">
                <span class="font-medium text-gray-600">Parent Location:</span>
                @if($location->parent)
                    <a href="{{ route('locations.show', $location->parent) }}" class="text-blue-600 hover:underline">{{ $location->parent->name }}</a>
                @else
                    <span class="text-gray-500">None</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supplies Assigned Here</h3>
            </div>
            <div class="p-6">
                @php $supplies = $suppliesAtAndBelow ?? ($location->supplies ?? collect()); @endphp
                @if($supplies->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supply</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($supplies as $s)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('supplies.show', $s) }}" class="text-blue-600 hover:underline">{{ $s->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $s->unit }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ number_format((int)($s->quantity ?? 0)) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($s->status === 'active') ? 'bg-green-100 text-green-800' : (($s->status === 'damaged') ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($s->status ?? 'unknown') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No supplies assigned to this location.</p>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Inventory Entries</h3>
            </div>
            <div class="p-6">
                @php $inventories = $location->inventories ?? collect(); @endphp
                @if($inventories->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supply</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($inventories as $inv)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        @if($inv->supply)
                                            <a href="{{ route('supplies.show', $inv->supply) }}" class="text-blue-600 hover:underline">{{ $inv->supply->name }}</a>
                                        @else
                                            <span class="text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-900">{{ number_format((int)($inv->quantity ?? 0)) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No inventory entries for this location.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mt-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Child Locations</h3>
        </div>
        <div class="p-6">
            @if($location->children && $location->children->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($location->children as $child)
                        <li class="py-3">
                            <a href="{{ route('locations.show', $child) }}" class="text-blue-600 hover:underline">{{ $child->name }}</a>
                            @if($child->description)
                                <p class="text-gray-600 text-sm">{{ $child->description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No child locations.</p>
            @endif
        </div>
    </div>

</div>
@endsection
