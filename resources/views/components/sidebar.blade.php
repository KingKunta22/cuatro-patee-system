<div>
    <!-- SIDE NAVIGATION BAR -->
    <div class="container flex flex-col content-start items-center w-64 bg-main h-screen fixed text-white">
        <img src="{{ asset('assets/imgs/logowhite.png')}}" class="h-auto w-16 m-6">
        <div class="container flex flex-col ">
            <!--the nav-link component is made inside the nav-link.blade to avoid repeating the same code and to make it cleaner -->
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
            <h1 class="text-lg font-semibold">Welcome back, Admin!</h1>
            <p class="text-gray-400 text-sm">Here is the summary..</p>
        </div>
        <div class='container place-items-end flex flex-row items-center justify-end'>
            <!-- NOTIFICATION BAR -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 mr-6 cursor-pointer">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>

            <!-- DROPDOWN PROFILE -->
            <div x-data="{ open: false }" class="relative">
                <!-- Trigger Button (Profile + Down Arrow) -->
                <button @click="open = !open" class="flex items-center gap-2 focus:outline-none w-64 border border-black px-2 py-2">
                    <img src="{{ asset('assets/imgs/profilePicture.png')}}" alt="Profile" class="w-8 h-8 rounded-full">
                    <span class="text-gray-500">Cuatro_Patee0516</span>
                    <!-- Simple Down Arrow (changes direction when open) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-auto" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Menu (Hidden by default, shown when `open=true`) -->
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                    <div class="py-1">
                        <!-- Manage Account Link -->
                        <a href="" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Manage Account
                        </a>
                        <!-- Logout Form (Laravel) -->
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
