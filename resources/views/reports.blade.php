<x-layout>
    <x-sidebar/>
    <div x-data="{ activeTab: 'product' }" 
         class="container w-auto ml-64 px-10 py-6 flex flex-col items-center content-start">
        
        <!-- SUCCESS MESSAGE POPUP -->
        @if(session('download_pdf'))
            <div id="pdf-success-message" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>Purchase order saved successfully! PDF download will start automatically.</p>
            </div>
        @endif
        
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE SUCCESS MESSAGES -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                ['pdf-success-message','success-message'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        setTimeout(() => {
                            el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            el.style.opacity = '0';
                            el.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => el.remove(), 500);
                        }, 3000);
                    }
                });
            });
        </script>
        
        <!-- SEARCH BAR AND CREATE BUTTON -->
        <div class="w-full flex items-center justify-between mb-4">
            <!-- SEARCH BAR -->
            <form action="" method="GET" class="flex items-center gap-4 mr-auto">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search reports..." 
                        class="pl-10 pr-4 py-2 border border-black rounded-md w-64"
                        autocomplete="off"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Search
                </button>

                @if(request('search'))
                    <a href="" class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                        Clear
                    </a>
                @endif
            </form>

            <!-- RIGHT SIDE: Time Period Dropdown -->
            <div class="relative">
                <form method="GET" class="flex">
                    <select name="timePeriod" class="px-3 py-2 border rounded-md border-black w-48 appearance-none max-h-[200px] overflow-y-auto" onchange="this.form.submit()">
                        <option value="daily" {{ request('timePeriod') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ request('timePeriod') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ request('timePeriod') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- NAVIGATION TABS -->
        <div class="w-full flex items-center justify-between bg-white rounded-lg pb-2 pt-4">
            <!-- TAB BUTTONS -->
            <div class="flex space-x-2">
                <a href="#"
                @click.prevent="activeTab = 'product'"
                :class="activeTab === 'product' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    Product Movements
                </a>
                <a href="#"
                @click.prevent="activeTab = 'sales'"
                :class="activeTab === 'sales' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    Sales Reports
                </a>
                <a href="#"
                @click.prevent="activeTab = 'inventory'"
                :class="activeTab === 'inventory' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    Inventory Reports
                </a>
                <a href="#"
                @click.prevent="activeTab = 'po'"
                :class="activeTab === 'po' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    PO Reports
                </a>
            </div>

            <!-- PAGE TITLE -->
            <div class="font-bold text-xl uppercase text-gray-800 ml-6 whitespace-nowrap"
                x-text="activeTab === 'product' ? 'Product Movements Report'
                    : activeTab === 'sales' ? 'Sales Report'
                    : activeTab === 'inventory' ? 'Inventory Report'
                    : 'Purchase Order Report'">
            </div>
        </div>

        <!-- MAIN CONTENT (TAB SWITCHING with x-if) -->
        <div class="bg-white rounded-lg w-full h-full">
            <template x-if="activeTab === 'product'">
                @include('reports.product-movement-reports')
            </template>
            <template x-if="activeTab === 'sales'">
                @include('reports.sales-reports')
            </template>
            <template x-if="activeTab === 'inventory'">
                @include('reports.inventory-reports')
            </template>
            <template x-if="activeTab === 'po'">
                @include('reports.purchase-order-reports')
            </template>
        </div>

    </div>
</x-layout>
