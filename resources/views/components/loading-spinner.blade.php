@props(['size' => 'md', 'type' => 'spinner', 'color' => 'blue', 'class' => ''])

@php
    $sizeClasses = [
        'sm' => 'w-4 h-4',
        'md' => 'w-6 h-6',
        'lg' => 'w-8 h-8',
        'xl' => 'w-12 h-12'
    ];
    
    $colorClasses = [
        'blue' => 'text-blue-600',
        'gray' => 'text-gray-600',
        'white' => 'text-white',
        'green' => 'text-green-600',
        'red' => 'text-red-600'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $colorClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

@if($type === 'spinner')
    <div class="inline-flex items-center justify-center {{ $class }}">
        <svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
@elseif($type === 'dots')
    <div class="loading-dots {{ $colorClass }} {{ $class }}">
        <span></span>
        <span></span>
        <span></span>
    </div>
@elseif($type === 'pulse')
    <div class="inline-flex items-center space-x-1 {{ $class }}">
        <div class="w-2 h-2 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded-full animate-pulse"></div>
        <div class="w-2 h-2 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
        <div class="w-2 h-2 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
    </div>
@elseif($type === 'bars')
    <div class="inline-flex items-end space-x-1 {{ $class }}">
        <div class="w-1 h-4 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded animate-pulse"></div>
        <div class="w-1 h-6 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded animate-pulse" style="animation-delay: 0.1s;"></div>
        <div class="w-1 h-3 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded animate-pulse" style="animation-delay: 0.2s;"></div>
        <div class="w-1 h-5 {{ $colorClass === 'text-white' ? 'bg-white' : 'bg-blue-600' }} rounded animate-pulse" style="animation-delay: 0.3s;"></div>
    </div>
@endif