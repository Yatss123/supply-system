<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        gray: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'scale-in': 'scaleIn 0.2s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.95)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Import Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
        
        /* Typography Hierarchy */
        .font-display {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        
        .font-heading {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            letter-spacing: -0.015em;
        }
        
        .font-subheading {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            letter-spacing: -0.01em;
        }
        
        .font-body {
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            line-height: 1.6;
        }
        
        .font-caption {
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .font-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.875rem;
            letter-spacing: 0.025em;
            text-transform: uppercase;
        }
        
        /* Text Sizes with Better Hierarchy */
        .text-display-xl { font-size: 3.75rem; line-height: 1; }
        .text-display-lg { font-size: 3rem; line-height: 1.1; }
        .text-display-md { font-size: 2.25rem; line-height: 1.2; }
        .text-display-sm { font-size: 1.875rem; line-height: 1.3; }
        
        .text-heading-xl { font-size: 1.5rem; line-height: 1.4; }
        .text-heading-lg { font-size: 1.25rem; line-height: 1.4; }
        .text-heading-md { font-size: 1.125rem; line-height: 1.5; }
        .text-heading-sm { font-size: 1rem; line-height: 1.5; }
        
        .text-body-xl { font-size: 1.125rem; line-height: 1.6; }
        .text-body-lg { font-size: 1rem; line-height: 1.6; }
        .text-body-md { font-size: 0.875rem; line-height: 1.6; }
        .text-body-sm { font-size: 0.75rem; line-height: 1.6; }
        
        /* Enhanced Text Colors */
        .text-primary { color: #1f2937; }
        .text-secondary { color: #4b5563; }
        .text-tertiary { color: #6b7280; }
        .text-quaternary { color: #9ca3af; }
        
        .text-accent-blue { color: #3b82f6; }
        .text-accent-purple { color: #8b5cf6; }
        .text-accent-green { color: #10b981; }
        .text-accent-red { color: #ef4444; }
        
        /* Font Weight Utilities */
        .font-light { font-weight: 300; }
        .font-normal { font-weight: 400; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .font-extrabold { font-weight: 800; }
        .font-black { font-weight: 900; }
        
        /* Text Effects */
        .text-gradient {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .text-gradient-warm {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .text-shadow-sm { text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); }
        .text-shadow-md { text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .text-shadow-lg { text-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
        
        /* Reading Experience */
        .prose {
            max-width: 65ch;
            line-height: 1.7;
            font-family: 'Inter', sans-serif;
        }
        
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        
        /* Responsive Typography */
        @media (max-width: 640px) {
            .text-display-xl { font-size: 2.5rem; }
            .text-display-lg { font-size: 2rem; }
            .text-display-md { font-size: 1.75rem; }
            .text-display-sm { font-size: 1.5rem; }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }
        
        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        /* Custom Animations */
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes scale-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.6), 0 0 30px rgba(239, 68, 68, 0.4); }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        @keyframes skeleton-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        
        @keyframes loading-dots {
            0%, 20% { opacity: 0; }
            50% { opacity: 1; }
            80%, 100% { opacity: 0; }
        }
        
        .animate-fade-in { animation: fade-in 0.6s ease-out; }
        .animate-slide-up { animation: slide-up 0.8s ease-out; }
        .animate-scale-in { animation: scale-in 0.5s ease-out; }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        .animate-skeleton-pulse { animation: skeleton-pulse 1.5s ease-in-out infinite; }
        .animate-loading-dots { animation: loading-dots 1.4s ease-in-out infinite; }
        
        .shimmer {
            position: relative;
            overflow: hidden;
        }
        
        .shimmer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: translateX(-100%);
            animation: shimmer 2s ease-in-out infinite;
        }
        
        /* Skeleton Loading States */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-pulse 1.5s ease-in-out infinite;
            border-radius: 0.375rem;
        }
        
        .skeleton-text {
            height: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .skeleton-text:last-child {
            width: 60%;
        }
        
        .skeleton-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
        }
        
        .skeleton-card {
            height: 8rem;
            border-radius: 0.75rem;
        }
        
        /* Loading Dots */
        .loading-dots {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .loading-dots span {
            width: 0.375rem;
            height: 0.375rem;
            background-color: currentColor;
            border-radius: 50%;
            animation: loading-dots 1.4s ease-in-out infinite;
        }
        
        .loading-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .loading-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        /* Smooth Transitions */
        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .transition-bounce {
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .transition-elastic {
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        /* Enhanced Hover States */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }
        
        /* Navigation Styles */
        .nav-link {
            @apply inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition-all duration-200 relative;
        }
        
        .nav-link-active {
            @apply text-primary-600 bg-primary-50/80 hover:bg-primary-100/80;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #0ea5e9, #3b82f6);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link-active::before {
            width: 80%;
        }
        
        .dropdown-link {
            @apply flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 hover:text-gray-900 transition-all duration-200;
        }
        
        .dropdown-link-active {
            @apply bg-primary-50/80 text-primary-700 border-r-2 border-primary-500;
        }
        
        .dropdown-item {
            @apply flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 hover:text-gray-900 transition-all duration-200;
        }
        
        .dropdown-item-active {
            @apply bg-primary-50/80 text-primary-700;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md shadow-lg border-b border-gray-200/50 sticky top-0 z-50" x-data="{ open: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transform group-hover:scale-105">
                                    <i class="fas fa-boxes text-white text-lg"></i>
                                </div>
                                <div class="hidden sm:block">
                                    <h1 class="text-xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                                        Supply Management
                                    </h1>
                                    <p class="text-xs text-gray-500 -mt-1">Modern & Efficient</p>
                                </div>
                            </a>
                        </div>



                        <!-- Navigation Links -->
                        <div class="hidden lg:flex space-x-1 ml-10">
                            <a href="{{ route('dashboard') }}" 
                               class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Dashboard
                            </a>

                            @if(auth()->user()->profile_completed || auth()->user()->hasAdminPrivileges())
                                <!-- Supplies (Hidden from student, adviser, and dean users) -->
                                @if(!auth()->user()->hasRole('student') && !auth()->user()->hasRole('adviser') && !auth()->user()->hasRole('dean'))
                                <a href="{{ route('supplies.index') }}" 
                                   class="nav-link {{ request()->routeIs('supplies.*') ? 'nav-link-active' : '' }}">
                                    <i class="fas fa-boxes mr-2"></i>
                                    Supplies
                                </a>
                                @endif

                                <!-- Issued Items (Hidden from student, adviser, and dean users) -->
                                @if(!auth()->user()->hasRole('student') && !auth()->user()->hasRole('adviser') && !auth()->user()->hasRole('dean'))
                                <a href="{{ route('issued-items.index') }}" 
                                   class="nav-link {{ request()->routeIs('issued-items.*') ? 'nav-link-active' : '' }}">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    Issued Items
                                </a>
                                @endif

                                <!-- Dean-only Departments link -->
                                @if(auth()->user()->hasRole('dean'))
                                <a href="{{ route('dean.departments') }}" 
                                   class="nav-link {{ request()->routeIs('dean.departments') ? 'nav-link-active' : '' }}">
                                    <i class="fas fa-sitemap mr-2"></i>
                                    Departments
                                </a>
                                <a href="{{ route('dean.allocations.show', auth()->user()->department_id) }}" 
                                   class="nav-link {{ request()->routeIs('dean.allocations.*') ? 'nav-link-active' : '' }}">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    My Department Allocation
                                </a>
                                @endif

                                @if(auth()->user()->hasRole('adviser') || auth()->user()->hasRole('dean'))
                                <div class="dropdown">
                                    <a href="#" class="nav-link dropdown-toggle {{ (request()->routeIs('supply-requests.*') || request()->routeIs('loan-requests.*')) ? 'nav-link-active' : '' }}" id="requestsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-clipboard-list mr-2"></i>
                                        Requests
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="requestsDropdown">
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('supply-requests.*') ? 'active' : '' }}" href="{{ route('supply-requests.index') }}">
                                                <i class="fas fa-shopping-cart me-2"></i>
                                                Supply Requests
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('loan-requests.*') ? 'active' : '' }}" href="{{ route('loan-requests.index') }}">
                                                <i class="fas fa-handshake me-2"></i>
                                                Borrow Requests
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                @endif

                                @if(auth()->user()->hasRole('student'))
                                <a href="{{ route('loan-requests.index') }}" 
                                   class="nav-link {{ request()->routeIs('loan-requests.*') ? 'nav-link-active' : '' }}">
                                    <i class="fas fa-handshake mr-2"></i>
                                    Borrow Requests
                                </a>
                                @endif

{{-- Monthly Allocations dropdown removed from navbar as requested --}}
                            @else
                                <!-- Hidden Menu Items with Tooltips for Incomplete Profiles -->
                                <div class="relative" x-data="{ showTooltip: false }">
                                    <div @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                         class="nav-link opacity-50 cursor-not-allowed">
                                        <i class="fas fa-boxes mr-2"></i>
                                        Supplies
                                        <i class="fas fa-lock ml-1 text-xs text-gray-400"></i>
                                    </div>
                                    <div x-show="showTooltip" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-1 transform scale-100"
                                         class="absolute top-full left-0 mt-2 w-64 bg-gray-900 text-white text-sm rounded-lg shadow-lg p-3 z-50">
                                        <div class="font-medium mb-1">Complete Your Profile</div>
                                        <div class="text-gray-300">Access to Supplies is locked until you complete your profile information.</div>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ showTooltip: false }">
                                    <div @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                         class="nav-link opacity-50 cursor-not-allowed">
                                        <i class="fas fa-clipboard-list mr-2"></i>
                                        Requests
                                        <i class="fas fa-lock ml-1 text-xs text-gray-400"></i>
                                    </div>
                                    <div x-show="showTooltip" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-1 transform scale-100"
                                         class="absolute top-full left-0 mt-2 w-64 bg-gray-900 text-white text-sm rounded-lg shadow-lg p-3 z-50">
                                        <div class="font-medium mb-1">Complete Your Profile</div>
                                        <div class="text-gray-300">Access to Request functions is locked until you complete your profile information.</div>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ showTooltip: false }">
                                    <div @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                         class="nav-link opacity-50 cursor-not-allowed">
                                        <i class="fas fa-clipboard-check mr-2"></i>
                                        Issued Items
                                        <i class="fas fa-lock ml-1 text-xs text-gray-400"></i>
                                    </div>
                                    <div x-show="showTooltip" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-1 transform scale-100"
                                         class="absolute top-full left-0 mt-2 w-64 bg-gray-900 text-white text-sm rounded-lg shadow-lg p-3 z-50">
                                        <div class="font-medium mb-1">Complete Your Profile</div>
                                        <div class="text-gray-300">Access to Issued Items is locked until you complete your profile information.</div>
                                    </div>
                                </div>
                            @endif

                            <!-- [Removed] QR Testing dropdown for Super Admin -->

{{-- System Management dropdown removed from navbar as requested --}}
                        </div>
                    </div>

                    <!-- Notifications Dropdown -->
                    @if(Auth::check())
                    @php
                        $unreadCount = Auth::user()->unreadNotifications()->count();
                        $latestNotifications = Auth::user()->notifications()->latest()->limit(10)->get();
                    @endphp
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative inline-flex items-center px-3 py-2 rounded-lg text-gray-700 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-bell"></i>
                                @if($unreadCount > 0)
                                    <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{ $unreadCount }}</span>
                                @endif
                            </button>
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                                 x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                                 class="origin-top-right absolute right-0 mt-3 w-80 rounded-xl shadow-xl bg-white/95 backdrop-blur-sm ring-1 ring-gray-200 border border-gray-100 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-primary-600 hover:text-primary-800">Mark all read</button>
                                        </form>
                                    </div>
                                    <div class="max-h-80 overflow-auto">
                                        @forelse($latestNotifications as $notification)
                                            @php $data = $notification->data ?? []; @endphp
                                            <a href="{{ $data['url'] ?? '#' }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                                <div class="flex items-start">
                                                    <div class="mr-3">
                                                        <i class="fas fa-info-circle text-blue-500"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="font-medium text-gray-900">{{ $data['message'] ?? 'Notification' }}</div>
                                                        <div class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                                                    </div>
                                                    @if($notification->read_at === null)
                                                        <span class="ml-2 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                                                    @endif
                                                </div>
                                            </a>
                                        @empty
                                            <div class="px-4 py-6 text-center text-sm text-gray-500">No notifications</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Settings Dropdown -->
                    @auth
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative" x-data="{ open: false }">
                            <div>
                                <button @click="open = !open" 
                                        class="flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                        </div>
                                        <div class="text-left">
                                            <div class="font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                            <div class="text-xs text-gray-500">{{ Auth::user()->role->name ?? 'No Role' }}</div>
                                        </div>
                                    </div>
                                    <svg class="ml-2 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                                 x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                                 class="origin-top-right absolute right-0 mt-3 w-56 rounded-xl shadow-xl bg-white/95 backdrop-blur-sm ring-1 ring-gray-200 border border-gray-100 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                                <div class="text-sm text-gray-500">{{ Auth::user()->role->name ?? 'No Role' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="py-2">
                                        <a href="{{ route('profile.edit') }}" 
                                           class="dropdown-item">
                                            <i class="fas fa-user-circle text-blue-500 mr-3"></i>
                                            <div>
                                                <div class="font-medium">Profile Settings</div>
                                                <div class="text-xs text-gray-500">Manage your account</div>
                                            </div>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="dropdown-item w-full text-left">
                                                <i class="fas fa-sign-out-alt text-red-500 mr-3"></i>
                                                <div>
                                                    <div class="font-medium">Sign Out</div>
                                                    <div class="text-xs text-gray-500">End your session</div>
                                                </div>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="open = !open" 
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-1 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-1 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="sm:hidden bg-white/95 backdrop-blur-sm border-t border-gray-200">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <!-- Mobile Dashboard Link -->
                    <a href="{{ route('dashboard') }}" 
                       class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard
                    </a>

                    @if(auth()->user()->profile_completed || auth()->user()->hasAdminPrivileges())
                        <!-- Mobile Supplies Link (hidden from student, adviser, and dean users) -->
                        @if(!auth()->user()->hasRole('student') && !auth()->user()->hasRole('adviser') && !auth()->user()->hasRole('dean'))
                        <a href="{{ route('supplies.index') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('supplies.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                            <i class="fas fa-boxes mr-2"></i>
                            Supplies
                        </a>
                        @endif

                        <!-- Mobile Requests Section -->
                        <div class="px-3 py-2">
                            <div class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Requests</div>
                            
                            @if(!auth()->user()->hasRole('student'))
                            <a href="{{ route('supply-requests.index') }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('supply-requests.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Supply Requests
                            </a>
                            @endif
                            
                            @if(!auth()->user()->hasRole('student') && !auth()->user()->hasRole('adviser') && !auth()->user()->hasRole('dean'))
                            <a href="{{ route('restock-requests.index') }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('restock-requests.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Order Requests
                            </a>
                            @endif

                            @if(auth()->user()->hasAdminPrivileges())
                            <a href="{{ route('supply-request-batches.index') }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('supply-request-batches.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                <i class="fas fa-tasks mr-2"></i>
                                Consolidated Requests
                            </a>
                            @endif
                            
                            <a href="{{ route('loan-requests.index') }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('loan-requests.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                <i class="fas fa-handshake mr-2"></i>
                                Borrow Requests
                            </a>
                            
                            <a href="{{ route('inter-department-loans.index') }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ (request()->routeIs('inter-department-loans.*') || request()->routeIs('loan-requests.inter-department.*')) ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Inter-Department Loans
                            </a>
                        </div>

                        @if(auth()->user()->hasRole('dean'))
                        <a href="{{ route('dean.departments') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dean.departments') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                            <i class="fas fa-sitemap mr-2"></i>
                            Departments
                        </a>
                        <a href="{{ route('dean.allocations.show', auth()->user()->department_id) }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dean.allocations.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            My Department Allocation
                        </a>
                        @endif

{{-- Mobile Monthly Allocations section removed from navbar as requested --}}

                        <!-- Mobile Issued Items -->
                        @if(!auth()->user()->hasRole('student') && !auth()->user()->hasRole('adviser') && !auth()->user()->hasRole('dean'))
                        <a href="{{ route('issued-items.index') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('issued-items.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Issued Items
                        </a>
                        @endif

{{-- Mobile System Management section removed from navbar as requested --}}
                    @else
                        <!-- Mobile Locked Menu Items -->
                        <div class="px-3 py-2">
                            <div class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Complete Profile to Access</div>
                            <div class="text-xs text-gray-500 mb-3 px-3">Complete your profile to unlock all features</div>
                            
                            <div class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 cursor-not-allowed">
                                <i class="fas fa-boxes mr-2"></i>
                                Supplies
                                <i class="fas fa-lock ml-2 text-xs"></i>
                            </div>
                            
                            <div class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 cursor-not-allowed">
                                <i class="fas fa-clipboard-list mr-2"></i>
                                Requests
                                <i class="fas fa-lock ml-2 text-xs"></i>
                            </div>
                            
                            <div class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 cursor-not-allowed">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Issued Items
                                <i class="fas fa-lock ml-2 text-xs"></i>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Mobile User Menu -->
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-5">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm text-gray-500">{{ Auth::user()->role->name ?? 'No Role' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('profile.edit') }}" 
                           class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-user-circle mr-2"></i>
                            Profile Settings
                        </a>
                       <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="dropdown-item w-full text-left">
                                                <i class="fas fa-sign-out-alt text-red-500 mr-3"></i>
                                                <div>
                                                    <div class="font-medium">Sign Out</div>
                                                    <div class="text-xs text-gray-500">End your session</div>
                                                </div>
                                            </button>
                                        </form>
                    </div>
                </div>
            </div>
        </nav>

        @if(auth()->user()->hasAdminPrivileges())
        <div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel" style="--bs-offcanvas-width: 280px;">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="adminSidebarLabel"><i class="fas fa-tools me-2"></i>Admin Sidebar</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="list-group list-group-flush">
                    <a href="{{ url('/admin/allocations') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i> Monthly Allocations
                    </a>
                    <a href="{{ route('to-order.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-basket me-2 text-info"></i> To Order
                    </a>

                    <div class="mt-3 small text-muted px-3">System Management</div>
                    <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-truck me-2 text-info"></i> Suppliers
                    </a>
                    <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags me-2 text-warning"></i> Categories
                    </a>
                    <a href="{{ route('departments.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2 text-purple-600"></i> Departments
                    </a>
                    <a href="{{ route('locations.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-location-dot me-2 text-teal-600"></i> Locations
                    </a>
                    @can('viewAny', App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2 text-orange-500"></i> Users
                    </a>
                    @endcan

                    <div class="mt-3 small text-muted px-3">Requests</div>
                    @if(!auth()->user()->hasRole('student'))
                    <a href="{{ route('supply-requests.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart me-2 text-blue-500"></i> Supply Requests
                    </a>
                    @endif
                    <a href="{{ route('loan-requests.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-handshake me-2 text-purple-500"></i> Borrow Requests
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasAdminPrivileges())
        <button type="button"
                class="fixed left-1 top-1/2 -translate-y-1/2 z-50 bg-white/90 hover:bg-white text-gray-700 shadow-lg border border-gray-200 rounded-r-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                style="border-left: none;"
                data-bs-toggle="offcanvas"
                data-bs-target="#adminSidebar"
                aria-controls="adminSidebar"
                aria-label="Open admin sidebar"
                title="Open admin sidebar">
            <i class="fas fa-chevron-right"></i>
        </button>
        @endif

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    @stack('scripts')
</body>
</html>
