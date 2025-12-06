@php($authUser = $authUser ?? auth()->user())
@php($departmentMatch = $sameDepartment ?? ($authUser && $user && $authUser->department_id === $user->department_id))
@php($canManageLocal = $canManage ?? ($departmentMatch && $authUser && $user && $authUser->id !== $user->id))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Access - {{ $user->name }}</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Dean Access Management</h1>
            <a href="{{ route('users.show', $user) }}" class="text-sm text-blue-600 hover:text-blue-800">Back to User</a>
        </div>

        @if(session('status'))
            <div class="p-3 mb-4 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->has('authorization'))
            <div class="p-3 mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ $errors->first('authorization') }}
            </div>
        @endif

        <!-- Target User Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Role: {{ $user->role->name ?? 'N/A' }}</span>
                    <span class="ml-2 inline-block px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Department: {{ $user->department->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Eligibility Notice -->
        <div class="mt-4">
            @if(!$departmentMatch)
                <div class="p-3 rounded-md bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm">
                    This user is outside your department. You cannot manage their dean-level access.
                </div>
            @elseif($authUser && $authUser->id === $user->id)
                <div class="p-3 rounded-md bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm">
                    You cannot assign or revoke dean-level access to yourself.
                </div>
            @elseif(!in_array($user->role->name ?? null, ['student','adviser']))
                <div class="p-3 rounded-md bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm">
                    Dean-level access can only be delegated to students or advisers.
                </div>
            @endif
        </div>

        <!-- Current Dean-Level Access Status -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Temporary Dean-Level Access</h3>
            @if($user->temp_privilege_type === 'dean' && (!$user->temp_privilege_expires_at || now()->lessThan($user->temp_privilege_expires_at)))
                <div class="p-4 bg-green-50 border border-green-200 rounded-md mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        <p class="text-sm text-green-800">
                            Temporary dean-level privileges are active
                            @if($user->temp_privilege_expires_at)
                                until <span class="font-semibold">{{ $user->temp_privilege_expires_at->format('M d, Y H:i') }}</span>
                            @else
                                (indefinite)
                            @endif
                        </p>
                    </div>
                </div>
                @if($canManageLocal)
                    <form method="POST" action="{{ route('dean.users.access.revoke', $user) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                            Revoke Dean-Level Access
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">You cannot revoke access for this user.</p>
                @endif
            @else
                @if($canManageLocal)
                    <form method="POST" action="{{ route('dean.users.access.assign', $user) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                            <select id="duration" name="duration" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="1_day">1 day</option>
                                <option value="1_week">1 week</option>
                                <option value="custom">Custom</option>
                                <option value="indefinite">Until I change it</option>
                            </select>
                        </div>
                        <div id="customExpiry" class="hidden">
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Custom Expiration</label>
                            <input type="datetime-local" id="expires_at" name="expires_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">Grant Dean-Level Access</button>
                        </div>
                    </form>
                    <script>
                        (function(){
                            const duration = document.getElementById('duration');
                            const customExpiry = document.getElementById('customExpiry');
                            const expiresAt = document.getElementById('expires_at');
                            function toggleCustom(){
                                if(duration.value === 'custom'){
                                    customExpiry.classList.remove('hidden');
                                    const d = new Date(Date.now() + 24*60*60*1000);
                                    const pad = n => String(n).padStart(2,'0');
                                    const local = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                                    expiresAt.value = local;
                                } else {
                                    customExpiry.classList.add('hidden');
                                    expiresAt.value = '';
                                }
                            }
                            duration.addEventListener('change', toggleCustom);
                            toggleCustom();
                        })();
                    </script>
                @else
                    <p class="text-sm text-gray-600">You cannot grant dean-level access for this user.</p>
                @endif
            @endif
        </div>
    </div>
</body>
</html>