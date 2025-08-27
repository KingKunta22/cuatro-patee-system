<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-6 flex flex-col items-center content-start">
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
            // Hide success messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const pdfMessage = document.getElementById('pdf-success-message');
                const successMessage = document.getElementById('success-message');
                
                if (pdfMessage) {
                    setTimeout(() => {
                        pdfMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        pdfMessage.style.opacity = '0';
                        pdfMessage.style.transform = 'translate(-50%, -20px)';
                        setTimeout(() => {
                            pdfMessage.remove();
                        }, 500);
                    }, 3000);
                }
                
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
            });
        </script>
        
        <!-- SEARCH BAR AND CREATE BUTTON -->
        <div class="w-full flex items-center justify-between mb-4">

            <!-- LEFT SIDE: Search + Buttons -->
            <form action="" method="GET" id="statusFilterForm" class="flex items-center space-x-3">

                <!-- Search Input -->
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search reports..." 
                        autocomplete="off"
                        class="pl-10 pr-4 py-2 border border-black rounded-md w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Search Button -->
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Search
                </button>

                <!-- Clear Button (only show when filters are active) -->
                @if(request('search'))
                    <a href="" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </form>
            </div>

        </div>

        @include('reports.product-movement-reports')
        @include('reports.inventory-reports')
        @include('reports.sales-reports')
        @include('reports.purchase-order-reports')
    </div>
</x-layout>
