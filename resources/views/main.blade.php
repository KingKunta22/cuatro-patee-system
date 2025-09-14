<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-5 pb-3 flex flex-col items-center content-start">

        <!-- SUCCESS MESSAGE CONTAINER AND STATEMENT -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- ERROR MESSAGE CONTAINER AND STATEMENT -->
        @if(session('error'))
            <div id="error-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE MESSAGES -->
        <script>
            // Hide success/error messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                const errorMessage = document.getElementById('error-message');
                
                if (successMessage) {
                    setTimeout(() => {
                        successMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        successMessage.style.opacity = '0';
                        successMessage.style.transform = 'translate(-50%, -20px)';
                        setTimeout(() => {
                            successMessage.remove();
                        }, 500);
                    }, 3000);
                }
                
                if (errorMessage) {
                    setTimeout(() => {
                        errorMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        errorMessage.style.opacity = '0';
                        errorMessage.style.transform = 'translate(-50%, -20px)';
                        setTimeout(() => {
                            errorMessage.remove();
                        }, 500);
                    }, 3000);
                }
            });
        </script>

        <!-- CONTAINER OUTSIDE MAIN SECTION -->
        <section class="container flex flex-col items-start place-content-start mt-2">

            <!-- TIME PERIOD FILTER -->
            <div class="relative">
                <form method="GET" class="flex">
                    <input type="hidden" name="tab" x-model="activeTab">
                    <select name="timePeriod" class="px-3 py-2 border rounded-md border-black w-48 appearance-none max-h-[200px] overflow-y-auto" onchange="this.form.submit()">
                        <option value="all" {{ ($timePeriod ?? 'all') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ ($timePeriod ?? 'all') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="lastWeek" {{ ($timePeriod ?? 'all') == 'lastWeek' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="lastMonth" {{ ($timePeriod ?? 'all') == 'lastMonth' ? 'selected' : '' }}>Last Month</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </form>
            </div>
        </section>

        <!-- REVENUE STATS -->
        <div class="container flex w-full gap-x-6 text-white mr-auto mb-4 py-4">
            <div class="container w-full flex flex-col px-6 py-4 text-start rounded-md bg-[#5C717B]">
                <span class="font-semibold text-2xl">₱</span>
                <span class="text-sm">Total Sales</span>
            </div>
            <div class="container w-full flex flex-col px-6 py-4 text-start rounded-md bg-[#5C717B]">
                <span class="font-semibold text-2xl">₱</span>
                <span class="text-sm">Total Cost</span>
            </div>
            <div class="container w-full flex flex-col px-6 py-4 text-start rounded-md bg-[#5C717B]">
                <span class="font-semibold text-2xl">₱</span>
                <span class="text-sm">Products Sold</span>
            </div>
            <div class="container w-full flex flex-col px-6 py-4 text-start rounded-md bg-[#2C3747]">
                <span class="font-semibold text-2xl">₱</span>
                <span class="text-sm">Total Transactions</span>
            </div>
        </div>
        
        <!-- CONTAINER FOR STOCKS & PRODUCTS -->
        <section class="flex gap-x-4 w-full">
            <!--STOCK LEVEL CONTAINER -->
            <div class="container shadow-md px-6 py-3 w-full rounded-md">
                <h1 class="font-semibold text-md">Stock Level</h1>
            </div>
            <!--LOW STOCK PRODUCTS CONTAINER -->
            <div class="container shadow-md px-6 py-3 w-full rounded-md">
                <h1 class="font-semibold text-md">Low Stock Products</h1>
            </div>
            <!--EXPIRING PRODUCTS CONTAINER -->
            <div class="container shadow-md px-6 py-3 w-full rounded-md">
                <h1 class="font-semibold text-md">Expiring Products</h1>
            </div>
        </section>

        <!-- CONTAINER FOR TOP SELLING PRODUCTS-->
        <section>
            <!-- 3-ITEM CAROUSEL -->
            <div class="container shadow-md px-6 py-3 w-full rounded-md">
                <h1 class="font-semibold text-md">Top Selling Products</h1>
            </div>
        </section>
    </main>
</x-layout>
