<x-layout>
    <x-sidebar/>
    <main x-data="{ activeTab: 'overview' }" class="w-auto ml-64 px-8 pt-6 pb-6 flex flex-col min-h-screen bg-gray-50">
        <!-- Notification Messages -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div id="error-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Auto-hide script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                const errorMessage = document.getElementById('error-message');
                
                const hideMessage = (message) => {
                    if (message) {
                        setTimeout(() => {
                            message.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            message.style.opacity = '0';
                            message.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => message.remove(), 500);
                        }, 3000);
                    }
                };
                
                hideMessage(successMessage);
                hideMessage(errorMessage);
            });
        </script>

        <!-- Header with filter -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
            <div class="relative">
                <form method="GET" class="flex">
                    <input type="hidden" name="tab" x-model="activeTab">
                    <select name="timePeriod" class="pl-3 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none" onchange="this.form.submit()">
                        <option value="all" {{ ($timePeriod ?? 'all') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ ($timePeriod ?? 'all') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="lastWeek" {{ ($timePeriod ?? 'all') == 'lastWeek' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="lastMonth" {{ ($timePeriod ?? 'all') == 'lastMonth' ? 'selected' : '' }}>Last Month</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Sales -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-50">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-500">Total Sales</h2>
                        <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalSales, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Cost -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-50">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-500">Total Cost</h2>
                        <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalCost, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Products Sold -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-50">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-500">Products Sold</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $productsSold }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-indigo-50">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-500">Total Transactions</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalTransactions }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Section: Stock Levels, Low Stock, Expiring -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Stock Level -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Stock Level</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $inStock + $lowStock + $outOfStock }} products</span>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-3"></div>
                            <span class="text-sm text-gray-600">In Stock</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $inStock }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-yellow-500 mr-3"></div>
                            <span class="text-sm text-gray-600">Low Stock</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $lowStock }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-red-500 mr-3"></div>
                            <span class="text-sm text-gray-600">Out of Stock</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $outOfStock }}</span>
                    </div>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Low Stock Products</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-100 text-amber-800">Attention needed</span>
                </div>
                <div class="space-y-3">
                    @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-md bg-amber-100 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-800">{{ $product->productName }}</h3>
                                <p class="text-xs text-gray-500">Stock: {{ $product->productStock }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-800">Low</span>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        <p>No low stock products</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Expiring Products -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Expiring Products</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-100 text-blue-800">Soon</span>
                </div>
                <div class="space-y-3">
                    @forelse($expiringProducts as $product)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-md bg-blue-100 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-800">{{ $product->productName }}</h3>
                                <p class="text-xs text-gray-500">Expires: {{ \Carbon\Carbon::parse($product->productExpirationDate)->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-blue-600">
                            {{ \Carbon\Carbon::parse($product->productExpirationDate)->diffForHumans() }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        <p>No products expiring soon</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Top Selling Products Carousel -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-800">Top Selling Products</h2>
                <div class="flex space-x-2">
                    <button class="top-selling-prev p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button class="top-selling-next p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="relative">
                <div class="top-selling-carousel overflow-hidden">
                    <div class="flex -mx-3">
                        @forelse($topSellingProducts as $product)
                        <div class="w-full md:w-1/2 lg:w-1/3 px-3 carousel-item">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-3">
                                    <div class="w-12 h-12 rounded-md bg-gray-200 flex items-center justify-center overflow-hidden">
                                        @if(isset($product->productImage) && $product->productImage)
                                            <img src="{{ asset('storage/' . $product->productImage) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-800">{{ $product->product_name }}</h3>
                                        <p class="text-xs text-gray-500">SKU: {{ $product->product_sku ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800">
                                        Sold: {{ $product->total_sold }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-800">
                                        ₱{{ number_format($product->price ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="w-full px-3">
                            <div class="text-center py-6 text-gray-500">
                                <p>No sales data available</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Carousel Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simple carousel functionality
            const carousel = document.querySelector('.top-selling-carousel');
            const items = document.querySelectorAll('.carousel-item');
            const prevBtn = document.querySelector('.top-selling-prev');
            const nextBtn = document.querySelector('.top-selling-next');
            
            if (items.length > 0) {
                let currentIndex = 0;
                const itemWidth = items[0].offsetWidth + 24; // width + margin
                
                function updateCarousel() {
                    carousel.querySelector('.flex').style.transform = `translateX(-${currentIndex * itemWidth}px)`;
                }
                
                nextBtn.addEventListener('click', function() {
                    if (currentIndex < items.length - 3) {
                        currentIndex++;
                        updateCarousel();
                    }
                });
                
                prevBtn.addEventListener('click', function() {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateCarousel();
                    }
                });
                
                // Initialize carousel
                carousel.querySelector('.flex').style.transition = 'transform 0.3s ease';
            }
        });
    </script>
</x-layout>