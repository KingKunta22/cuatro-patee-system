<div>
    <!-- SIDE NAVIGATION BAR -->
    <div class="container flex flex-col content-start items-center w-64 bg-main h-screen fixed text-white">
        <img src="{{ asset('assets/imgs/logowhite.png')}}" class="h-auto w-16 m-6">
        <div class="container flex flex-col ">
            <x-nav-link href="/main" :active="request()->is('main')">
                <img src="{{ asset('assets/imgs/icons/dashboard.png')}}" class="w-10 pr-2">Dashboard</x-nav-link>
            <x-nav-link href="/sales" :active="request()->is('sales')">
                <img src="{{ asset('assets/imgs/icons/sales.png')}}" class="w-10 pr-2">Sales</x-nav-link>
            <x-nav-link href="/inventory" :active="request()->is('inventory')">
                <img src="{{ asset('assets/imgs/icons/inventory.png')}}" class="w-10 pr-2">Inventory</x-nav-link>
            <x-nav-link href="/purchase-orders" :active="request()->is('purchase-orders')">
                <img src="{{ asset('assets/imgs/icons/purchaseorders.png')}}" class="w-10 pr-2">Purchase Orders</x-nav-link>
            <x-nav-link href="/delivery-management" :active="request()->is('delivery-management')">
                <img src="{{ asset('assets/imgs/icons/customer.png')}}" class="w-10 pr-2">Delivery Management</x-nav-link>
            <x-nav-link href="/reports" :active="request()->is('reports')">
                <img src="{{ asset('assets/imgs/icons/reports.png')}}" class="w-10 pr-2">Reports</x-nav-link>
            <x-nav-link href="/product-classification" :active="request()->is('product-classification')">
                <img src="{{ asset('assets/imgs/icons/pclassification.png')}}" class="w-10 pr-2">Product Management</x-nav-link>
            <x-nav-link href="/suppliers" :active="request()->is('suppliers')">
                <img src="{{ asset('assets/imgs/icons/supplier.png')}}" class="w-10 pr-2">Suppliers</x-nav-link>
        </div>
    </div>

    <!-- TOP NAVIGATION BAR -->
    <div class="h-20 w-auto px-10 flex content-between items-center ml-64 shadow-md">
        <div class='container'>
            <h1 class="text-lg font-semibold">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-400 text-sm capitalize">
                @if(Auth::user()->isAdmin())
                    Administrator Account
                @else
                    Staff Account
                @endif
            </p>
        </div>
        <div class='container place-items-end flex flex-row items-center justify-end'>
            <!-- NOTIFICATION BAR -->
            <x-notification-dropdown />



            <!-- DROPDOWN PROFILE -->
            <div x-data="{ open: false }" class="relative">
                <!-- Trigger Button (Profile + Down Arrow) -->
                <button @click="open = !open" class="flex items-center gap-2 focus:outline-none w-64 border border-black px-2 py-2 hover:bg-gray-50 transition-colors">
                    <!-- Dynamic Profile Picture -->
                    <img src="{{ Auth::user()->getAvatarUrl() }}" alt="Profile" class="w-8 h-8 rounded-full">
                    <!-- Dynamic Username -->
                    <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                    <!-- Simple Down Arrow (changes direction when open) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-auto text-gray-500" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                    <div class="py-2">
                        <!-- User Info Section -->
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            <p class="text-xs text-gray-500 capitalize mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                    {{ Auth::user()->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ Auth::user()->role }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-1
                                    {{ Auth::user()->isActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ Auth::user()->status }}
                                </span>
                            </p>
                        </div>
                        
                        <!-- Show Manage Account only to admins -->
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('manage.account') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Manage Accounts
                            </a>
                        @endif
                        
                        <!-- Logout Form -->
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>