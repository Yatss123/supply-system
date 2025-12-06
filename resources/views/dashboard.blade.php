@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Loading State (Hidden by default, shown via JavaScript) -->
            <div id="dashboard-loading" class="hidden">
                <div class="animate-fade-in">
                    <!-- Welcome Section Skeleton -->
                    <div class="bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-6 mb-6 shadow-xl">
                        <div class="flex items-center space-x-4">
                            <x-skeleton-loader type="avatar" />
                            <div class="flex-1">
                                <div class="skeleton skeleton-text w-48 mb-2 bg-white/20"></div>
                                <div class="skeleton skeleton-text w-64 bg-white/10"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Section Skeleton -->
                    <x-skeleton-loader type="stats" class="mb-6" />

                    <!-- Quick Actions Skeleton -->
                    <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100 mb-6">
                        <div class="flex items-center mb-5">
                            <div class="skeleton w-6 h-6 rounded mr-3"></div>
                            <div class="skeleton skeleton-text w-32"></div>
                        </div>
                        <x-skeleton-loader type="actions" />
                    </div>

                    <!-- Low Stock Alert Skeleton -->
                    <x-skeleton-loader type="low-stock" />
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div id="dashboard-content" class="transition-smooth">
                @if(!auth()->user()->profile_completed && !auth()->user()->hasAdminPrivileges())
                <!-- Profile Completion Banner -->
                <div class="mb-6 animate-fade-in">
                    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border-l-4 border-blue-400 rounded-xl shadow-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-blue-900 mb-2">Complete Your Profile to Access All Features</h3>
                                <div class="text-sm text-blue-800 mb-4">
                                    <p class="mb-3">To ensure security and proper access control, please complete your profile first. Once completed, you'll have access to:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-4">
                                        <li>Supply management and inventory browsing</li>
                                        <li>Supply requests and approval workflows</li>
                                        <li>Borrowing and loan management features</li>
                                        <li>Department-specific functions and reports</li>
                                        @can('viewAny', App\Models\User::class)
                                        <li>User management and administrative tools</li>
                                        @endcan
                                    </ul>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <a href="{{ route('profile.complete') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Complete Profile Now
                                    </a>
                                    <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Current Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Welcome Section -->
                <div class="mb-6 animate-fade-in">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="font-display text-display-md lg:text-display-lg text-gray-900 mb-2 text-shadow-md">
                                    Welcome back, {{ Auth::user()->name }}! 👋
                                </h1>
                                <p class="font-body text-body-lg text-gray-600 flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 mr-2">
                                        {{ Auth::user()->role->name ?? 'No Role Assigned' }}
                                    </span>
                                    @if(auth()->user()->profile_completed || auth()->user()->hasAdminPrivileges())
                                        Ready to manage your supply system
                                    @else
                                        Please complete your profile to get started
                                    @endif
                                </p>
                            </div>
                            <div class="hidden md:block">
                                <div class="w-16 h-16 lg:w-20 lg:h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center">
                                    @if(auth()->user()->profile_completed || auth()->user()->hasAdminPrivileges())
                                        <i class="fas fa-chart-line text-white text-xl lg:text-2xl"></i>
                                    @else
                                        <i class="fas fa-user-edit text-white text-xl lg:text-2xl"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

@if(auth()->user()->hasAdminPrivileges())
            <!-- Reports -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6" x-data="{ openGenerate:false, selectedTypes:['missing'], mode:'month', month:'', from:'', to:'', period:'monthly' }">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                        Reports
                    </h2>
                    <button @click="openGenerate=true" class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                        <i class="fas fa-file-alt mr-2"></i> Generate Report
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Missing Report Card -->
                    <a href="{{ route('reports.missing-items') }}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Missing Report</h3>
                                <p class="text-sm text-gray-600">Track missing items</p>
                            </div>
                            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-search-minus text-white text-sm"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Damaged Report Card -->
                    <a href="{{ route('reports.damaged-items') }}" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:bg-yellow-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Damaged Report</h3>
                                <p class="text-sm text-gray-600">Review damaged items</p>
                            </div>
                            <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tools text-white text-sm"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Issued Activity Card -->
                    <a href="{{ route('reports.issued-activity') }}" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 hover:bg-indigo-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Issued Activity</h3>
                                <p class="text-sm text-gray-600">View issued items</p>
                            </div>
                            <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-share-square text-white text-sm"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Ordered Activity Card -->
                    <a href="{{ route('reports.ordered-activity') }}" class="bg-teal-50 border border-teal-200 rounded-lg p-4 hover:bg-teal-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Ordered Activity</h3>
                                <p class="text-sm text-gray-600">View ordered items</p>
                            </div>
                            <div class="w-10 h-10 bg-teal-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-sm"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Generate Report Modal -->
                <div x-cloak x-show="openGenerate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Generate Report</h3>
                            <button @click="openGenerate=false" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Report Types</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="selectedTypes" value="missing" class="rounded border-gray-300" />
                                        <span>Missing Items</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="selectedTypes" value="damaged" class="rounded border-gray-300" />
                                        <span>Damaged Items</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="selectedTypes" value="issued" class="rounded border-gray-300" />
                                        <span>Issued Activity</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="selectedTypes" value="ordered" class="rounded border-gray-300" />
                                        <span>Ordered Activity</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date Selection</label>
                                <select x-model="mode" class="w-full border rounded-md p-2">
                                    <option value="month">By Month</option>
                                    <option value="range">Custom Range</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-if="mode === 'month'">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                                    <input type="month" x-model="month" class="w-full border rounded-md p-2" />
                                </div>
                            </template>
                            <template x-if="mode === 'range'">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                                        <input type="date" x-model="from" class="w-full border rounded-md p-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                                        <input type="date" x-model="to" class="w-full border rounded-md p-2" />
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button @click="openGenerate=false" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</button>
                            <button @click="(() => {
                                    const types = selectedTypes || [];
                                    if (!types.length) { alert('Select at least one report type'); return; }
                                    const buildUrl = (t) => {
                                        let base='';
                                        if (t==='missing') base='{{ route('reports.missing-items') }}';
                                        else if (t==='damaged') base='{{ route('reports.damaged-items') }}';
                                        else if (t==='issued') base='{{ route('reports.issued-activity') }}';
                                        else base='{{ route('reports.ordered-activity') }}';
                                        const params = new URLSearchParams();
                                        if ((t==='issued' || t==='ordered') && period && period!=='custom') {
                                            params.set('period', period);
                                        }
                                        if (mode==='month' && month) params.set('month', month);
                                        if (mode==='range') {
                                            if (from) params.set('from', from);
                                            if (to) params.set('to', to);
                                        }
                                        params.set('export','csv');
                                        return base + (params.toString()? ('?'+params.toString()):'');
                                    };
                                    const urls = types.map(buildUrl);
                                    urls.forEach(u => {
                                        window.open(u, '_blank');
                                    });
                                })()" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Generate</button>
                        </div>
                    </div>
                </div>

                <!-- Top Items Charts -->
                <div class="mt-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">Top Items (Monthly)</h3>
                            <span class="text-sm text-gray-600">By department • stacked</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="h-64">
                                <canvas id="issuedTopChartCanvas"></canvas>
                            </div>
                            <div class="h-64">
                                <canvas id="orderedTopChartCanvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                (function(){
                  const issued = @json($issuedTopChart);
                  const ordered = @json($orderedTopChart);
                  function buildDatasets(data){
                    const palette = ['#4f46e5','#06b6d4','#f59e0b','#ef4444','#22c55e','#a855f7','#0ea5e9','#84cc16','#f97316','#64748b'];
                    return data.departments.map((dept, idx) => {
                      const color = palette[idx % palette.length];
                      return {
                        label: dept.name,
                        data: data.series[dept.id] || Array(data.labels.length).fill(0),
                        backgroundColor: color,
                        borderColor: color,
                        borderWidth: 1,
                        stack: 'stack1'
                      };
                    });
                  }
                  function renderStackedBar(el, data, title){
                    if (!data || !data.labels || data.labels.length === 0) return;
                    const ctx = document.getElementById(el).getContext('2d');
                    new Chart(ctx, {
                      type: 'bar',
                      data: {
                        labels: data.labels,
                        datasets: buildDatasets(data)
                      },
                      options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                          legend: { position: 'bottom' },
                          title: { display: true, text: title }
                        },
                        scales: {
                          x: { stacked: true, ticks: { autoSkip: false } },
                          y: { stacked: true, beginAtZero: true }
                        }
                      }
                    });
                  }
                  renderStackedBar('issuedTopChartCanvas', issued, issued.title);
                  renderStackedBar('orderedTopChartCanvas', ordered, ordered.title);
                })();
                </script>
            </div>
@endif
            <!-- Stats Grid -->
            <!-- Reporting Summary -->
            @if(auth()->user()->hasAdminPrivileges())
            <div class="mb-6 animate-slide-up hidden">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1021 12h-2a7 7 0 11-7-7V3.055z"/></svg>
                        <h2 class="text-lg font-semibold text-gray-900">Reporting Summary</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                            <p class="text-xs text-gray-600">Total Supplies</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Supply::count() }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100">
                            <p class="text-xs text-gray-600">Pending Requests</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $pendingRequests }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                            <p class="text-xs text-gray-600">Low Stock Items</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $lowStockItems->count() }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-100">
                            <p class="text-xs text-gray-600">Missing / Damaged</p>
                            <p class="text-lg font-semibold text-gray-900">Missing: {{ $missingItemsTotal }}</p>
                            <p class="text-lg font-semibold text-gray-900">Damaged: {{ $damagedItemsTotal }}</p>
                        </div>
                    </div>

                    <!-- Issued & Ordered Items Activity -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                            <p class="text-sm font-semibold text-indigo-700 mb-2">Issued Items</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Daily</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $issuedDaily }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Weekly</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $issuedWeekly }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Monthly</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $issuedMonthly }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-teal-50 border border-teal-100">
                            <p class="text-sm font-semibold text-teal-700 mb-2">Ordered Items</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Daily</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $orderedDaily }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Weekly</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $orderedWeekly }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Monthly</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $orderedMonthly }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-3">
                        <a href="{{ route('reports.missing-items') }}" class="text-sm text-blue-600 hover:text-blue-800">View Missing Report</a>
                        <a href="{{ route('reports.damaged-items') }}" class="text-sm text-blue-600 hover:text-blue-800">View Damaged Report</a>
                        <a href="{{ route('reports.issued-activity') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View Issued Activity</a>
                        <a href="{{ route('reports.ordered-activity') }}" class="text-sm text-teal-600 hover:text-teal-800">View Ordered Activity</a>
                    </div>
                </div>
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 animate-slide-up hidden">
                <!-- Total Supplies (Admin & Super Admin Only) -->
                @if(auth()->user()->hasAdminPrivileges())
                <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                    <!-- Gradient Border Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-blue-500/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                    
                    <!-- Content -->
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="font-label text-tertiary mb-1">Total Supplies</p>
                            <p class="font-display text-display-sm lg:text-display-md text-primary group-hover:text-blue-600 transition-colors duration-300">{{ \App\Models\Supply::count() }}</p>
                            <p class="text-xs text-green-600 mt-1 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                Active inventory
                            </p>
                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-blue-500/25">
                            <i class="fas fa-boxes text-white text-base lg:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Shimmer Effect -->
                    <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                </div>
                @endif

                <!-- Pending Requests (Admin & Super Admin Only) -->
                @if(auth()->user()->hasAdminPrivileges())
                <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                    <!-- Gradient Border Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/20 via-orange-500/20 to-yellow-500/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                    
                    <!-- Content -->
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="font-label text-tertiary mb-1">Pending Requests</p>
                            <p class="font-display text-display-sm lg:text-display-md text-primary group-hover:text-yellow-600 transition-colors duration-300">{{ \App\Models\SupplyRequest::where('status', 'pending')->count() }}</p>
                            <p class="font-caption text-yellow-600 mt-1 flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                Awaiting approval
                            </p>
                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-yellow-500/25">
                            <i class="fas fa-hourglass-half text-white text-base lg:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Shimmer Effect -->
                    <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                </div>
                @endif

                <!-- Low Stock Items (Admin & Super Admin Only) -->
                @if(auth()->user()->hasAdminPrivileges())
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-label text-tertiary mb-1">Low Stock Items</p>
                            <p class="font-display text-display-sm lg:text-display-md text-primary">{{ \App\Models\Supply::where('quantity', '<=', 10)->count() }}</p>
                            <p class="font-caption text-red-600 mt-1 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Need attention
                            </p>
                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-red-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white text-base lg:text-lg"></i>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Missing Items (Admin & Super Admin Only) -->
                @if(auth()->user()->hasAdminPrivileges())
                <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                    <!-- Gradient Border Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/20 via-amber-500/20 to-orange-500/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                    
                    <!-- Content -->
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="font-label text-tertiary mb-1">Missing Items</p>
                            <p class="font-display text-display-sm lg:text-display-md text-primary group-hover:text-orange-600 transition-colors duration-300">{{ $missingItemsTotal }}</p>
                            <p class="font-caption text-orange-600 mt-1 flex items-center">
                                <i class="fas fa-minus-circle mr-1"></i>
                                Reported missing
                            </p>
                            <a href="{{ route('reports.missing-items') }}" class="text-blue-600 hover:text-blue-800 text-sm">View details</a>
                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-amber-500/25">
                            <i class="fas fa-search-minus text-white text-base lg:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Shimmer Effect -->
                    <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                </div>
                @endif

                <!-- Damaged Items (Admin & Super Admin Only) -->
                @if(auth()->user()->hasAdminPrivileges())
                <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                    <!-- Gradient Border Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-rose-500/20 via-red-500/20 to-rose-500/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                    
                    <!-- Content -->
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="font-label text-tertiary mb-1">Damaged Items</p>
                            <p class="font-display text-display-sm lg:text-display-md text-primary group-hover:text-rose-600 transition-colors duration-300">{{ $damagedItemsTotal }}</p>
                            <p class="font-caption text-rose-600 mt-1 flex items-center">
                                <i class="fas fa-tools mr-1"></i>
                                Reported damaged
                            </p>
                            <a href="{{ route('reports.damaged-items') }}" class="text-blue-600 hover:text-blue-800 text-sm">View details</a>
                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-rose-500 to-red-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-rose-500/25">
                            <i class="fas fa-band-aid text-white text-base lg:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Shimmer Effect -->
                    <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                </div>
                @endif

                <!-- Total Users -->
                @can('viewAny', App\Models\User::class)
                <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                    <!-- Gradient Border Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 via-emerald-500/20 to-green-500/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                    
                    <!-- Content -->
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="font-label text-tertiary mb-1">Total Users</p>
                            <p class="font-display text-display-sm lg:text-display-md text-primary group-hover:text-green-600 transition-colors duration-300">{{ \App\Models\User::count() }}</p>
                            <p class="font-caption text-green-600 mt-1 flex items-center">
                                <i class="fas fa-users mr-1"></i>
                                System users
                            </p>
                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-green-500/25">
                            <i class="fas fa-users text-white text-base lg:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Shimmer Effect -->
                    <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                </div>
                @endcan
            </div>

            <!-- Loan Request Status Section (Non-admin users except 'user' role) -->
            @if(!auth()->user()->hasAdminPrivileges() && !auth()->user()->hasRole('user'))
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-6 mb-6 animate-fade-in hover:shadow-2xl transition-all duration-500 overflow-hidden">
                <!-- Gradient Border Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-blue-500/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                
                <!-- Content -->
                <div class="relative">
                    <div class="flex items-center mb-5">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-clipboard-list text-white text-sm"></i>
                        </div>
                        <h2 class="font-heading text-heading-xl text-primary group-hover:text-blue-600 transition-colors duration-300">My Borrow Requests</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-yellow-600 font-medium">Pending</p>
                                    <p class="text-2xl font-bold text-yellow-700">{{ $loanRequestStats['pending'] }}</p>
                                </div>
                                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-green-600 font-medium">Approved</p>
                                    <p class="text-2xl font-bold text-green-700">{{ $loanRequestStats['approved'] }}</p>
                                </div>
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-red-600 font-medium">Declined</p>
                                    <p class="text-2xl font-bold text-red-700">{{ $loanRequestStats['declined'] }}</p>
                                </div>
                                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-times text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-600 font-medium">Completed</p>
                                    <p class="text-2xl font-bold text-blue-700">{{ $loanRequestStats['completed'] }}</p>
                                </div>
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check-double text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Shimmer Effect -->
                <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            </div>

            <!-- Inter-Department Loan Status Section (Non-admin users except 'user' role) -->
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-6 mb-6 animate-fade-in hover:shadow-2xl transition-all duration-500 overflow-hidden">
                <!-- Gradient Border Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 via-pink-500/10 to-purple-500/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="absolute inset-[1px] bg-white/90 backdrop-blur-sm rounded-xl"></div>
                
                <!-- Content -->
                <div class="relative">
                    <div class="flex items-center mb-5">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                        </div>
                        <h2 class="font-heading text-heading-xl text-primary group-hover:text-purple-600 transition-colors duration-300">My Inter-Department Loans</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-4">
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-3 border border-yellow-200">
                            <div class="text-center">
                                <p class="text-xs text-yellow-600 font-medium mb-1">Pending</p>
                                <p class="text-xl font-bold text-yellow-700">{{ $interDeptLoanStats['pending'] }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-3 border border-indigo-200">
                            <div class="text-center">
                                <p class="text-xs text-indigo-600 font-medium mb-1">Lending Approved</p>
                                <p class="text-xl font-bold text-indigo-700">{{ $interDeptLoanStats['lending_approved'] }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg p-3 border border-cyan-200">
                            <div class="text-center">
                                <p class="text-xs text-cyan-600 font-medium mb-1">Borrowing Confirmed</p>
                                <p class="text-xl font-bold text-cyan-700">{{ $interDeptLoanStats['borrowing_confirmed'] }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-3 border border-green-200">
                            <div class="text-center">
                                <p class="text-xs text-green-600 font-medium mb-1">Admin Approved</p>
                                <p class="text-xl font-bold text-green-700">{{ $interDeptLoanStats['admin_approved'] }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-3 border border-red-200">
                            <div class="text-center">
                                <p class="text-xs text-red-600 font-medium mb-1">Declined</p>
                                <p class="text-xl font-bold text-red-700">{{ $interDeptLoanStats['declined'] }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200">
                            <div class="text-center">
                                <p class="text-xs text-blue-600 font-medium mb-1">Completed</p>
                                <p class="text-xl font-bold text-blue-700">{{ $interDeptLoanStats['completed'] }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-3 border border-teal-200">
                            <div class="text-center">
                                <p class="text-xs text-teal-600 font-medium mb-1">Returned</p>
                                <p class="text-xl font-bold text-teal-700">{{ $interDeptLoanStats['returned'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Shimmer Effect -->
                <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            </div>
            @endif

            

            <!-- Dean Approval Panel (Dean users only) -->
            @if(auth()->user()->hasRole('dean'))
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-user-check text-blue-600 mr-3"></i>
                        <h2 class="text-xl font-semibold text-gray-900">Requests Awaiting Dean Approval</h2>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-medium">
                        {{ $deanApprovalRequests->count() }} pending
                    </span>
                </div>
                
                @if($deanApprovalRequests->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($deanApprovalRequests as $request)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            @if($request instanceof App\Models\LoanRequest)
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium mr-2">
                                                    Loan Request
                                                </span>
                                            @else
                                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium mr-2">
                                                    Inter-Dept Loan
                                                </span>
                                            @endif
                                            <span class="text-sm text-gray-600">
                                                {{ $request->created_at->format('M d, Y') }}
                                            </span>
                                        </div>
                                        
                                        <h4 class="font-semibold text-gray-900 mb-1">
                                            @if($request instanceof App\Models\LoanRequest)
                                                {{ $request->supply->name ?? 'N/A' }}
                                            @else
                                                {{ $request->issuedItem->supply->name ?? 'N/A' }}
                                            @endif
                                        </h4>
                                        
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <div>
                                                <strong>Requester:</strong> {{ $request->requestedBy->name ?? 'N/A' }}
                                                <span class="ml-2 text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">
                                                    {{ $request->requestedBy?->role?->display_name ?? 'N/A' }}
                                                </span>
                                            </div>
                                             
                                            @if($request instanceof App\Models\LoanRequest)
                                                <div><strong>Department:</strong> {{ $request->department->name ?? 'N/A' }}</div>
                                                <div><strong>Quantity:</strong> {{ $request->quantity_requested ?? 'N/A' }}</div>
                                            @else
                                                <div><strong>From:</strong> {{ $request->lendingDepartment->name ?? 'N/A' }} → {{ $request->borrowingDepartment->name ?? 'N/A' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4">
                                        @if($request instanceof App\Models\LoanRequest)
                                            <a href="{{ route('loan-requests.show', $request->id) }}" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                                View
                                            </a>
                                            @if($request->needsDeanApproval())
                                                <form action="{{ route('loan-requests.dean-approve', $request->id) }}" method="POST" class="inline-block ml-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                                        Approve
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('inter-department-loans.show', $request->id) }}" 
                                               class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm">
                                                View
                                            </a>
                                            @if(method_exists($request, 'isBorrowingConfirmed') && $request->isBorrowingConfirmed() && !$request->isDeanApproved())
                                                <form action="{{ route('inter-department-loans.dean-approve', $request->id) }}" method="POST" class="inline-block ml-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                                        Approve
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-600">
                                These requests require your approval as department dean.
                            </p>
                            <div class="flex space-x-2">
                                <a href="{{ route('loan-requests.index') }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    View All Borrow Requests
                                </a>
                                <span class="text-gray-400">|</span>
                                <a href="{{ route('inter-department-loans.index') }}" 
                                   class="text-purple-600 hover:text-purple-800 text-sm">
                                    View All Inter-Dept Loans
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-green-600 text-3xl mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">All Caught Up!</h3>
                        <p class="text-gray-600">No requests are currently awaiting your approval.</p>
                    </div>
                @endif
            </div>
            @endif
            @if(auth()->user()->hasRole('dean'))
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-undo text-purple-600 mr-3"></i>
                        Pending Return Approvals
                    </h2>
                </div>

                @if(isset($deanReturnApprovals) && $deanReturnApprovals->count() > 0)
                    <div class="space-y-4">
                        @foreach($deanReturnApprovals as $request)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow transition-shadow">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">
                                                <i class="fas fa-clock mr-1"></i> Return Pending Verification
                                            </span>
                                            <span class="text-sm text-gray-600">
                                                {{ $request->updated_at->format('M d, Y') }}
                                            </span>
                                        </div>

                                        <h4 class="font-semibold text-gray-900 mb-1">
                                            {{ $request->issuedItem->supply->name ?? 'N/A' }}
                                        </h4>

                                        <div class="text-sm text-gray-600 space-y-1">
                                            <div>
                                                <strong>Departments:</strong>
                                                {{ $request->lendingDepartment->name ?? 'N/A' }} → {{ $request->borrowingDepartment->name ?? 'N/A' }}
                                            </div>
                                            <div>
                                                <strong>Items:</strong> {{ $request->interDepartmentBorrowedItems->count() }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex items-center space-x-2">
                                        <a href="{{ route('inter-department-loans.show', $request->id) }}"
                                           class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm">
                                            View Details
                                        </a>
                                        <form method="POST" action="{{ route('inter-department-loans.verify-return', $request->id) }}" onsubmit="return confirm('Approve this return?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                                                <i class="fas fa-check mr-1"></i> Approve Return
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-600">
                                These returns await your verification as lending department dean.
                            </p>
                            <div class="flex space-x-2">
                                <a href="{{ route('inter-department-loans.index') }}" 
                                   class="text-purple-600 hover:text-purple-800 text-sm">
                                    View All Inter-Dept Loans
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-green-600 text-3xl mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Return Approvals Pending</h3>
                        <p class="text-gray-600">You have no returns waiting for verification.</p>
                    </div>
                @endif
            </div>
            @endif

            <!-- Quick Actions -->
            @if(auth()->user()->profile_completed || auth()->user()->hasAdminPrivileges())
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6 hidden">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt text-blue-600 mr-3"></i>
                        Quick Actions
                    </h2>
                </div>
                    
                    @if(auth()->user()->hasAdminPrivileges())
                    <!-- Admin Quick Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- View Supplies -->
                        <a href="{{ route('dean.supplies.index') }}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">View Supplies</h3>
                                    <p class="text-sm text-gray-600">Browse inventory</p>
                                </div>
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-boxes text-white text-sm"></i>
                                </div>
                            </div>
                        </a>

                        <!-- Supply Requests -->
                        <a href="{{ route('supply-requests.index') }}" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Supply Requests</h3>
                                    <p class="text-sm text-gray-600">Manage requests</p>
                                </div>
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-white text-sm"></i>
                                </div>
                            </div>
                        </a>

                        <!-- Low Stock Items -->
                        <a href="{{ route('supplies.index', ['low_stock' => 1]) }}" class="bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Low Stock Items</h3>
                                    <p class="text-sm text-gray-600">Critical inventory</p>
                                </div>
                                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                                </div>
                            </div>
                        </a>

                        <!-- User Management -->
                        @can('viewAny', App\Models\User::class)
                        <a href="{{ route('users.index') }}" class="bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">User Management</h3>
                                    <p class="text-sm text-gray-600">Manage users</p>
                                </div>
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-white text-sm"></i>
                                </div>
                            </div>
                        </a>
                        @endcan
                    </div>
                    @elseif(auth()->user()->hasRole('dean') || auth()->user()->hasRole('adviser') || auth()->user()->hasRole('student') || (!auth()->user()->hasRole('user')))
                    <!-- Non-Admin Quick Actions - For dean, adviser, students, and other non-basic users -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Supply Requests (Advisers only) -->
                        @if(auth()->user()->hasRole('adviser'))
                        <a href="{{ route('supply-requests.index') }}" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Supply Requests</h3>
                                    <p class="text-sm text-gray-600">Create and manage supply requests</p>
                                </div>
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-white text-sm"></i>
                                </div>
                            </div>
                        </a>
                        @endif

                        @if(auth()->user()->hasRole('dean'))
                        <a href="{{ route('supply-requests.create') }}" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 hover:bg-indigo-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">New Supply Request</h3>
                                    <p class="text-sm text-gray-600">Quickly create a supply request</p>
                                </div>
                                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-plus text-white text-sm"></i>
                                </div>
                            </div>
                        </a>
                        @endif

                        <!-- Loan Requests -->
                        <a href="{{ route('loan-requests.index') }}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Borrow Requests</h3>
                                    <p class="text-sm text-gray-600">Manage your borrow requests</p>
                                </div>
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-white text-sm"></i>
                                </div>
                            </div>
                        </a>

                        <!-- Inter-Department Loans -->
                        <a href="{{ route('inter-department-loans.index') }}" class="bg-purple-50 border border-purple-200 rounded-lg p-4 hover:bg-purple-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Inter-Department Loans</h3>
                                    <p class="text-sm text-gray-600">Cross-department borrowing</p>
                                </div>
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-exchange-alt text-white text-sm"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    @else
                    <!-- Basic user role - No quick actions available -->
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-info-circle text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Quick Actions Available</h3>
                        <p class="text-gray-600">Contact your administrator for access to additional features.</p>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <!-- Profile Incomplete - Limited Dashboard (Only for non-admin users) -->
            @if(!auth()->user()->hasAdminPrivileges())
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 p-8 mb-6 text-center hidden">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Features Locked</h3>
                    <p class="text-gray-600 mb-6">Complete your profile to unlock all dashboard features and system functionality.</p>
                    <div class="bg-gray-50 rounded-lg p-4 text-left">
                        <h4 class="font-medium text-gray-900 mb-2">Available after profile completion:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Quick action buttons</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Supply management</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Request workflows</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Full navigation menu</li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            @endif

            <!-- Low Stock Alert Section (Admin & Super Admin Only) -->
            @if(auth()->user()->hasAdminPrivileges() && $lowStockItems->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-6 hidden">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                            </div>
                            <div>
                                <h2 class="font-heading text-heading-xl text-primary">Low Stock Alert</h2>
                                <p class="font-body text-body-md text-red-600 font-medium">{{ $lowStockItems->count() }} {{ $lowStockItems->count() === 1 ? 'item needs' : 'items need' }} immediate attention</p>
                            </div>
                        </div>
                        <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                            Critical
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
                        @foreach($lowStockItems->take(6) as $item)
                        <div class="bg-red-50 rounded-xl p-4 border border-red-200">
                            <div>
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">
                                            {{ $item->name }}
                                            @if(isset($orderedSupplyIds) && in_array($item->id, $orderedSupplyIds))
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-800 font-semibold">Ordered</span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($item->description, 50) }}</p>
                                        <div class="flex items-center space-x-4 text-sm">
                                            <span class="text-red-600 font-semibold">Stock: {{ $item->quantity }}</span>
                                            <span class="text-gray-500">Min: {{ $item->minimum_stock ?? 10 }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs">
                                                {{ $item->category->name ?? 'No Category' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center ml-3">
                                        <i class="fas fa-box text-white text-sm"></i>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <a href="{{ route('supplies.show', $item) }}" class="flex-1 bg-white text-red-600 hover:bg-red-50 hover:text-red-700 px-3 py-2 rounded-lg text-sm font-medium text-center border border-red-200 hover:border-red-300">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                    <a href="{{ route('to-order.create', ['supply_id' => $item->id]) }}" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium text-center">
                                        <i class="fas fa-plus mr-1"></i>
                                        Order
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($lowStockItems->count() > 6)
                    <div class="text-center">
                        <a href="{{ route('supplies.index', ['low_stock' => 1]) }}" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl">
                            <i class="fas fa-list mr-2"></i>
                            View All {{ $lowStockItems->count() }} Low Stock Items
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Close dashboard-content div -->
    </div>

    @if(auth()->user()->hasAdminPrivileges())
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6 hidden">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-undo text-purple-600 mr-3"></i>
                Recent Returns
            </h2>
        </div>
        @php
            $recentReturns = \App\Models\InterDepartmentReturnRecord::with(['initiatedBy','verifiedBy'])->latest()->take(10)->get();
        @endphp
        @if($recentReturns->count() > 0)
        <div class="space-y-3">
            @foreach($recentReturns as $record)
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div>
                    <div class="text-sm text-gray-600">{{ $record->created_at->format('M d, Y g:i A') }}</div>
                    <div class="font-medium text-gray-900">Initiated by {{ optional($record->initiatedBy)->name ?? 'Unknown' }}</div>
                    <div class="text-sm text-gray-700">
                        Missing: {{ $record->missing_count ?? 0 }} | Damaged: {{ $record->damaged_count ?? 0 }}
                        @if($record->damage_severity)
                            <span class="ml-2">Severity: {{ ucfirst(str_replace('_', ' ', $record->damage_severity)) }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    @if($record->verified_at)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                    @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Awaiting Verification</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
            <p class="text-sm text-gray-600">No recent returns.</p>
        @endif
    </div>
    @endif
    <!-- Loading State JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingElement = document.getElementById('dashboard-loading');
            const contentElement = document.getElementById('dashboard-content');
            
            // Show loading state initially
            loadingElement.classList.remove('hidden');
            contentElement.style.opacity = '0';
            
            // Simulate loading time and then show content
            setTimeout(() => {
                loadingElement.classList.add('hidden');
                contentElement.style.opacity = '1';
                contentElement.classList.add('animate-fade-in');
            }, 1500);
            
            // Debug: Log all quick action links
            console.log('Dashboard loaded, checking quick actions...');
            
            // Check for loan requests quick action
            const loanRequestsLink = document.querySelector('a[href*="loan-requests"]');
            if (loanRequestsLink) {
                console.log('Found loan-requests link:', loanRequestsLink);
                loanRequestsLink.addEventListener('click', function(e) {
                    console.log('Loan requests link clicked!', this.href);
                });
            } else {
                console.log('Loan requests link NOT found');
            }
            
            // Check for inter-department loans quick action
            const interDeptLink = document.querySelector('a[href*="inter-department-loans"]');
            if (interDeptLink) {
                console.log('Found inter-department-loans link:', interDeptLink);
                interDeptLink.addEventListener('click', function(e) {
                    console.log('Inter-department loans link clicked!', this.href);
                });
            } else {
                console.log('Inter-department loans link NOT found');
            }
            
            // Add smooth transitions to all interactive elements (excluding Quick Action links)
            const cards = document.querySelectorAll('.hover-lift, .group:not(.group\\/card)');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Add loading states to buttons (but not anchor links)
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    console.log('Button clicked:', this);
                    if (!this.classList.contains('no-loading')) {
                        const originalContent = this.innerHTML;
                        this.innerHTML = `
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading...
                            </div>
                        `;
                        this.disabled = true;
                        
                        // Restore original content after navigation
                        setTimeout(() => {
                            this.innerHTML = originalContent;
                            this.disabled = false;
                        }, 2000);
                    }
                });
            });
        });

        // Dean rejection helper: prompt for reason and submit form
        function handleDeclineClick(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;
            const reason = prompt('Please provide a rejection reason:');
            if (!reason) return false;
            const input = form.querySelector('input[name="decline_reason"]');
            if (input) input.value = reason;
            if (!confirm('Confirm rejection?')) return false;
            form.submit();
            return false;
        }
    </script>
@endsection
