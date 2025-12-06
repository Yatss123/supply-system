@php($pageTitle = 'Consolidated Supply Request Batches')
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $pageTitle }}</h1>
        <form method="GET" action="{{ route('supply-request-batches.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search batches..." class="border rounded px-3 py-2 text-sm w-64" />
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Search</button>
        </form>
    </div>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-4 overflow-x-auto">
            @if($batches->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Batch ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Created</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Requester</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($batches as $batch)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">#{{ $batch->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $batch->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $batch->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $batch->department->department_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $batch->description ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                {{ $batch->status === 'approved' ? 'bg-green-100 text-green-800' : ($batch->status === 'rejected' ? 'bg-red-100 text-red-800' : ($batch->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $batch->items()->count() }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('supply-request-batches.show', $batch) }}" class="px-3 py-1 bg-blue-600 text-white rounded text-xs">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $batches->appends(['search' => $search])->links() }}
            </div>
            @else
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2v2H8v2h2v2h2v-2h2v-2h-2v-2c0-.55.45-1 1-1h1V8h-1z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No batches found</h3>
                    <p class="mt-1 text-sm text-gray-500">Submitted requests will appear here once created.</p>
                </div>
            @endif
        </div>
    </div>
</div>