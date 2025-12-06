@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Locations</h1>
        <a href="{{ route('locations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i> Add Location
        </a>
    </div>

    <div class="mb-4">
        <input type="text" id="locationSearch" placeholder="Search locations..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p id="searchFeedback" class="mt-1 text-xs text-gray-500"></p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-200 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <p class="text-gray-600">Manage physical storage locations for supplies and inventories.</p>
        </div>

        @if($locations->count())
            <table class="min-w-full divide-y divide-gray-200" id="locationsTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="locationsTbody">
                    @foreach($locations as $location)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('locations.show', $location) }}'" title="View location details">
                            <td class="px-6 py-4 text-gray-800">{{ $location->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $location->description }}</td>
                            <td class="px-6 py-4 text-right"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200" id="paginationBlock">
                {{ $locations->links() }}
            </div>
        @else
            <div class="px-6 py-10 text-center text-gray-500">
                <p>No locations added yet.</p>
                <p class="mt-2">
                    <a href="{{ route('locations.create') }}" class="text-blue-600 hover:text-blue-800">Create your first location</a>.
                </p>
            </div>
        @endif
    </div>
</div>
<script>
let searchTimer;
const input = document.getElementById('locationSearch');
const tbody = document.getElementById('locationsTbody');
const pagination = document.getElementById('paginationBlock');
const feedback = document.getElementById('searchFeedback');
function renderRows(items){
  const base = `{{ url('/locations') }}`;
  tbody.innerHTML = items.map(i => `
    <tr class=\"hover:bg-gray-50 cursor-pointer\" onclick=\"window.location='${base}/${i.id}'\" title=\"View location details\">\n      <td class=\"px-6 py-4 text-gray-800\">${i.name}</td>\n      <td class=\"px-6 py-4 text-gray-600\">${i.description ?? ''}</td>\n      <td class=\"px-6 py-4 text-right\"></td>\n    </tr>
  `).join('');
}
input.addEventListener('input', function(){
  clearTimeout(searchTimer);
  const q = input.value.trim();
  if(q === ''){
    feedback.textContent = '';
    pagination.style.display = '';
    return window.location = '{{ route('locations.index') }}';
  }
  feedback.textContent = 'Searching...';
  pagination.style.display = 'none';
  searchTimer = setTimeout(async () => {
    try{
      const res = await fetch(`{{ route('locations.search') }}?parents_only=1&q=${encodeURIComponent(q)}`);
      const data = await res.json();
      const items = data.results || [];
      renderRows(items);
      feedback.textContent = items.length ? `${items.length} result(s)` : 'No results';
    }catch(e){
      feedback.textContent = 'Search failed';
    }
  }, 250);
});
</script>
@endsection
