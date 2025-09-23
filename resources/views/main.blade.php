<x-layout>
    <x-sidebar/>
    <main x-data="{ activeTab: 'overview', salesTrendPeriod: 'lastMonth' }" class="w-auto ml-64 px-8 pt-6 pb-6 flex flex-col min-h-screen bg-gray-50">
        <!-- Notification Messages -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
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
                        <option value="today" {{ ($timePeriod ?? 'today') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="lastWeek" {{ ($timePeriod ?? 'today') == 'lastWeek' ? 'selected' : '' }}>This Week</option>
                        <option value="lastMonth" {{ ($timePeriod ?? 'today') == 'lastMonth' ? 'selected' : '' }}>This Month</option>
                        <option value="lastYear" {{ ($timePeriod ?? 'today') == 'lastYear' ? 'selected' : '' }}>This Year</option>
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
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg text-lg text-blue-400 bg-blue-50">
                        ₱
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-500">Total Sales</h2>
                        <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalSales, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Cost -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-50">
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" 
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 
                                3 .895 3 2-1.343 2-3 2m0-8
                                c1.11 0 2.08.402 2.599 1M12 8V7
                                m0 1v8m0 0v1
                                m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-500">Total Cost</h2>
                        <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalCost, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Products Sold -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8" style="min-height: 380px;">

            <!-- Stock Level Pie Chart -->
            <div class="bg-white rounded-xl shadow-sm pb-0 p-6 border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Stock Level</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $inStock + $lowStock + $outOfStock }} products</span>
                </div>
                
                <!-- Pie Chart -->
                <div class="h-52 flex items-between justify-center">
                    <canvas id="stockLevelChart" class="w-full h-full max-w-xs"></canvas>
                </div>
                
                <!-- Stock Information Below the Chart -->
                <div class="grid grid-cols-3 gap-4 my-auto pt-8 border-t border-gray-200">
                    <!-- In Stock -->
                    <div class="flex flex-col items-center">
                        <div class="flex items-center mb-2">
                            <div class="w-3 h-3 bg-green-500 mr-2"></div>
                            <span class="text-xs font-medium text-gray-700">In Stock</span>
                        </div>
                        <span class="text-xs font-bold text-gray-900">{{ $inStock }} items</span>
                    </div>
                    
                    <!-- Low Stock -->
                    <div class="flex flex-col items-center">
                        <div class="flex items-center mb-2">
                            <div class="w-3 h-3 bg-amber-500 mr-2"></div>
                            <span class="text-xs font-medium text-gray-700">Low Stock</span>
                        </div>
                        <span class="text-xs font-bold text-gray-900">{{ $lowStock }} items</span>
                    </div>
                    
                    <!-- Out of Stock -->
                    <div class="flex flex-col items-center">
                        <div class="flex items-center mb-2">
                            <div class="w-3 h-3 bg-red-500 mr-2"></div>
                            <span class="text-xs font-medium text-gray-700">Out of Stock</span>
                        </div>
                        <span class="text-xs font-bold text-gray-900">{{ $outOfStock }} items</span>
                    </div>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Low Stock Products</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-100 text-amber-800">Attention needed</span>
                </div>
                <div class="flex-1 overflow-y-auto space-y-3">
                    @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-md bg-amber-100 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2 0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class='w-5/6'>
                                <h3 class="text-sm font-medium text-gray-800">{{ $product['productName'] }}</h3>
                            </div>
                        </div>
                        <p class="text-xs text-red-400">
                            {{ $product['productStock'] }} {{ $product['productStock'] > 1 ? ' stocks left' : 'stock left' }}
                        </p>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        <p>No low stock products</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Expiring Products -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Expiring Products</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-100 text-blue-800">Soon</span>
                </div>
                <div class="flex-1 overflow-y-auto space-y-3">
                    @forelse($expiringProducts as $product)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-md bg-blue-100 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-800">{{ $product['productName'] }}</h3>
                                <p class="text-xs text-gray-500">{{ $product['productSKU'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-blue-600" title="Expires on {{ \Carbon\Carbon::parse($product['productExpirationDate'])->format('M d, Y') }}">
                            {{ \Carbon\Carbon::parse($product['productExpirationDate'])->diffForHumans() }}
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
                    <button id="prevButton" class="top-selling-prev p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="nextButton" class="top-selling-next p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="relative">
                <div class="top-selling-carousel overflow-hidden">
                    <div class="flex transition-transform duration-300 ease-in-out">
                        @forelse($topSellingProducts as $index => $product)
                        <div class="w-1/3 flex-shrink-0 px-3 carousel-item">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow h-auto flex flex-col">
                                <div class="flex flex-col items-center mb-3 flex-grow">
                                    <div class="w-56 h-60 rounded-md bg-gray-200 flex items-center justify-center overflow-hidden mb-3">
                                        @if(isset($product['product']) && $product['product']->productImage)
                                            <img src="{{ asset('storage/' . $product['product']->productImage) }}" 
                                                alt="{{ $product['product_name'] }}" 
                                                class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="text-center">
                                        @php
                                            $productName = $product['product_name'] ?? 'Unknown Product';
                                            if (preg_match('/\((INV-\d+-\d+)\)/', $productName, $matches)) {
                                                $sku = $matches[1];
                                                $productName = trim(str_replace("($sku)", "", $productName));
                                            } else {
                                                $sku = $product['inventory']['productSKU'] ?? 'N/A';
                                            }
                                        @endphp
                                        <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ \Illuminate\Support\Str::limit($productName, 30) }}</h3>
                                        <p class="text-xs text-gray-500">SKU: {{ $sku }}</p>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-auto">
                                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800">
                                        Sold: {{ $product['total_sold'] }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-800">
                                        ₱{{ number_format($product['unit_price'] ?? ($product['inventory']['productSellingPrice'] ?? 0), 2) }}
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

        <!-- Debug Sales Trends Data (remove after testing) -->
        <div style="display: none;">
            <h3>Sales Trends Debug Info:</h3>
            <pre>@json($salesTrends, JSON_PRETTY_PRINT)</pre>
        </div>

        <!-- Sales Trends Section -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-800">Sales Trends</h2>
                <div class="relative">
                    <select x-model="salesTrendPeriod" class="pl-3 pr-10 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none" @change="updateSalesTrendChart($event.target.value)">
                        <option value="lastWeek">Past Week</option>
                        <option value="lastMonth">Past Month</option>
                        <option value="last6Months">Past 6 Months</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>
    </main>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize carousel with button disabling logic
            initCarousel();
            
            // Initialize charts
            initStockLevelChart();
            initSalesTrendChart(); // Simple chart initialization
        });

        function initCarousel() {
            const carousel = document.querySelector('.top-selling-carousel');
            const track = carousel.querySelector('.flex');
            const items = document.querySelectorAll('.carousel-item');
            const prevBtn = document.getElementById('prevButton');
            const nextBtn = document.getElementById('nextButton');
            
            if (items.length <= 3) {
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
                nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            
            let currentIndex = 0;
            const itemsToShow = 3;
            const itemWidth = items[0].offsetWidth;
            
            function updateCarousel() {
                track.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
                
                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex >= items.length - itemsToShow;
                
                prevBtn.classList.toggle('opacity-50', currentIndex === 0);
                prevBtn.classList.toggle('cursor-not-allowed', currentIndex === 0);
                nextBtn.classList.toggle('opacity-50', currentIndex >= items.length - itemsToShow);
                nextBtn.classList.toggle('cursor-not-allowed', currentIndex >= items.length - itemsToShow);
            }
            
            nextBtn.addEventListener('click', function() {
                if (currentIndex < items.length - itemsToShow) {
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
            
            track.style.transition = 'transform 0.3s ease';
            updateCarousel();
        }


        // FUNCTION FOR THE PIE CHART
        function initStockLevelChart() {
            const ctx = document.getElementById('stockLevelChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['In Stock (>10)', 'Low Stock (1-10)', 'Out of Stock (0)'],
                    datasets: [{
                        data: [{{ $inStock }}, {{ $lowStock }}, {{ $outOfStock }}],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false // Removes the legend completely
                        }
                    }
                }
            });
        }


        // SALES TRENDS CHART
        function initSalesTrendChart() {
            const ctx = document.getElementById('salesTrendChart').getContext('2d');
            const trendsData = @json($salesTrends);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendsData.labels,
                    datasets: [{
                        label: 'Sales',
                        data: trendsData.data,
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Simple update function (optional)
        function updateSalesTrendChart(period) {
            // Just reload with new period parameter
            const url = new URL(window.location.href);
            url.searchParams.set('salesPeriod', period);
            window.location.href = url.toString();
        }
    </script>
</x-layout>