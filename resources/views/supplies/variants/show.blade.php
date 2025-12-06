@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Variant Details</h1>
                    <p class="text-gray-600 mt-1">For supply: <strong>{{ $supply->name }}</strong></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('supplies.show', $supply) }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Supply
                    </a>
                    @if(auth()->user()->hasAdminPrivileges())
                        <a href="{{ route('supply-variants.edit', $variant) }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Edit Variant
                        </a>
                        @if(($variant->status ?? 'active') === 'active')
                            <form action="{{ route('supply-variants.disable', $variant) }}" method="POST" class="inline" onsubmit="return confirm('Disable this variant? It will no longer be selectable for issuing.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Disable</button>
                            </form>
                        @else
                            <form action="{{ route('supply-variants.enable', $variant) }}" method="POST" class="inline" onsubmit="return confirm('Enable this variant? It will become available for issuing again.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Enable</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Variant Details Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Overview</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <span class="text-sm font-medium text-gray-500">Variant Name:</span>
                    <p class="text-sm text-gray-900">{{ $variant->variant_name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Status:</span>
                    <p class="text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium @if(($variant->status ?? 'active') === 'active') bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                            {{ ($variant->status ?? 'active') === 'active' ? 'Active' : 'Disabled' }}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">SKU:</span>
                    <p class="text-sm text-gray-900">{{ $variant->sku ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Tin:</span>
                    <p class="text-sm text-gray-900">{{ $variant->tin ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Unit:</span>
                    <p class="text-sm text-gray-900">{{ ucfirst($supply->unit) }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Current Quantity:</span>
                    <p class="text-sm text-gray-900">{{ $variant->quantity }} {{ $supply->unit }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Currently Available:</span>
                    <p class="text-sm text-gray-900">{{ method_exists($variant, 'availableQuantity') ? $variant->availableQuantity() : $variant->quantity }} {{ $supply->unit }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Missing:</span>
                    <p class="text-sm text-gray-900">{{ method_exists($variant, 'totalMissingCount') ? number_format($variant->totalMissingCount()) : 0 }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Damaged:</span>
                    <p class="text-sm text-gray-900">{{ method_exists($variant, 'totalDamagedCount') ? number_format($variant->totalDamagedCount()) : 0 }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Price:</span>
                    <p class="text-sm text-gray-900">@if($variant->price) ${{ number_format($variant->price, 2) }} @else <span class="text-gray-400">N/A</span> @endif</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Supply Type:</span>
                    <p class="text-sm text-gray-900">{{ ucfirst($supply->supply_type) }}</p>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">QR Code</h2>
            </div>
            <div class="p-6 text-center">
                <img src="{{ $variant->getQrCodeImageUrl() }}"
                     alt="QR Code for {{ $supply->name }} — {{ $variant->display_name }}"
                     class="mx-auto border border-gray-300 rounded-lg shadow-sm">
                <p class="text-xs text-gray-500 mt-2">Scan to view actions for this variant</p>
                <div class="mt-3 flex justify-center space-x-2">
                    <a href="{{ route('qr.code.generate', ['supply' => $supply->id, 'action' => 'actions', 'supply_variant_id' => $variant->id, 'size' => 600, 'format' => 'png']) }}"
                       class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
                       download="variant-{{ $variant->id }}-qr.png">
                        <i class="fas fa-download mr-2"></i>
                        Download PNG
                    </a>
                    <a href="{{ route('qr.code.generate', ['supply' => $supply->id, 'action' => 'actions', 'supply_variant_id' => $variant->id, 'format' => 'svg']) }}"
                       class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
                       download="variant-{{ $variant->id }}-qr.svg">
                        <i class="fas fa-download mr-2"></i>
                        Download SVG
                    </a>
                </div>
            </div>
        </div>

        <!-- Attributes -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Attributes</h2>
            </div>
            <div class="p-6">
                @php
                    $attrs = $variant->attributes;
                    if (is_string($attrs)) {
                        $decoded = json_decode($attrs, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $attrs = $decoded;
                        } else {
                            $attrs = $attrs !== '' ? ['' => $attrs] : [];
                        }
                    } elseif (!is_array($attrs)) {
                        $attrs = [];
                    }
                @endphp
                @if(!empty($attrs))
                    <div class="flex flex-wrap gap-2">
                        @foreach($attrs as $key => $value)
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ $key ? ucfirst($key) . ': ' : '' }}{{ is_array($value) ? implode(', ', $value) : $value }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No attributes defined for this variant.</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if(auth()->user()->hasAdminPrivileges())
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Variant Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('supply-variants.edit', $variant) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center">Edit Variant</a>
                    @if(($variant->status ?? 'active') === 'active')
                        <form action="{{ route('supply-variants.disable', $variant) }}" method="POST" onsubmit="return confirm('Disable this variant? It will no longer be selectable for issuing.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Disable Variant</button>
                        </form>
                    @else
                        <form action="{{ route('supply-variants.enable', $variant) }}" method="POST" onsubmit="return confirm('Enable this variant? It will become available for issuing again.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Enable Variant</button>
                        </form>
                    @endif
                    <a href="{{ route('supplies.show', $supply) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">View Parent Supply</a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection