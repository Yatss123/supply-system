<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Supply Management System</h1>
                <p class="text-lg text-gray-600">Manage your supplies, requests, and inventory efficiently</p>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-blue-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Supply Management</h3>
                    <p class="text-gray-600">Track and manage your inventory supplies with ease</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-green-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Request Processing</h3>
                    <p class="text-gray-600">Handle supply requests and approvals efficiently</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-purple-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Role-Based Access</h3>
                    <p class="text-gray-600">Secure access control with different user roles</p>
                </div>
            </div>

            <!-- Authentication Links -->
            <div class="text-center">
                @auth
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Welcome back, {{ Auth::user()->name }}!</h2>
                        <p class="text-gray-600 mb-4">Role: {{ Auth::user()->role->name ?? 'No Role Assigned' }}</p>
                        <div class="space-x-4">
                            <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                Go to Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Get Started</h2>
                        <p class="text-gray-600 mb-6">Please log in to access the supply management system</p>
                        <div class="space-x-4">
                            <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                Register
                            </a>
                        </div>

                        <!-- Inline Login Form removed; use dedicated /login page -->
                    </div>
                @endauth
            </div>

            <!-- Test Credentials -->
            <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Test Credentials</h3>
                <div class="text-sm text-yellow-700">
                    <p><strong>Super Admin:</strong> superadmin@example.com / password</p>
                    <p class="text-xs mt-1">Use these credentials to test the role-based access control system</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>