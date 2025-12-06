@props(['type' => 'card', 'count' => 1, 'class' => ''])

@if($type === 'card')
    @for($i = 0; $i < $count; $i++)
        <div class="skeleton skeleton-card {{ $class }}"></div>
    @endfor
@elseif($type === 'text')
    @for($i = 0; $i < $count; $i++)
        <div class="skeleton skeleton-text {{ $class }}"></div>
    @endfor
@elseif($type === 'avatar')
    <div class="skeleton skeleton-avatar {{ $class }}"></div>
@elseif($type === 'stats')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
        @for($i = 0; $i < 4; $i++)
            <div class="bg-white rounded-xl p-5 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="skeleton skeleton-text w-20 mb-2"></div>
                        <div class="skeleton skeleton-text w-16 h-8"></div>
                    </div>
                    <div class="skeleton w-10 h-10 lg:w-12 lg:h-12 rounded-lg"></div>
                </div>
            </div>
        @endfor
    </div>
@elseif($type === 'actions')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        @for($i = 0; $i < 4; $i++)
            <div class="bg-white rounded-xl p-5 shadow-lg border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="skeleton w-10 h-10 rounded-lg"></div>
                    <div class="flex-1">
                        <div class="skeleton skeleton-text w-24 mb-1"></div>
                        <div class="skeleton skeleton-text w-32"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
@elseif($type === 'low-stock')
    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100">
        <div class="flex items-center mb-5">
            <div class="skeleton w-6 h-6 rounded mr-3"></div>
            <div class="skeleton skeleton-text w-32"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @for($i = 0; $i < 6; $i++)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="skeleton skeleton-text w-full mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 mb-3"></div>
                    <div class="flex justify-between items-center mb-3">
                        <div class="skeleton skeleton-text w-16"></div>
                        <div class="skeleton skeleton-text w-20"></div>
                    </div>
                    <div class="flex space-x-2">
                        <div class="skeleton w-16 h-8 rounded"></div>
                        <div class="skeleton w-16 h-8 rounded"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
@endif