<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">

        <!-- SUCCESS MESSAGE CONTAINER AND STATEMENT -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE SUCCESS MESSAGES -->
        <script>
            // Hide success messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                
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

        <!-- CONTAINER OUTSIDE THE TABLE -->
        <section class="container flex flex-col items-center place-content-start">
            <div class="container flex items-start justify-start place-content-start w-auto gap-x-4 text-white mr-auto mb-4">
                <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#5C717B]">
                    <span class="font-semibold text-xl">₱(InsertRevenue)</span>
                    <span class="text-xs">Total Revenue</span>
                </div>
                <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#2C3747]">
                    <span class="font-semibold text-xl">₱(InsertProfit)</span>
                    <span class="text-xs">Total Profit</span>
                </div>
               <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#5C717B]">
                    <span class="font-semibold text-xl">₱(InsertCost)</span>
                    <span class="text-xs">Total Cost</span>
                </div>
            </div>

            <!-- SEARCH BAR AND FILTERS - SEPARATE FORM TO AVOID CONFLICTS -->
            <div class="container flex items-center place-content-start gap-4 mb-4">
                <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                <form action="{{ route('sales.index') }}" method="GET" class="flex items-center gap-4 mr-auto">
                    <!-- Simple Search Input -->
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search sales..." 
                            class="pl-10 pr-4 py-2 border border-black rounded-md w-64"
                            autocomplete="off"
                        >
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
                        <a href="{{ route('sales.index') }}" class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                            Clear
                        </a>
                    @endif
                </form>

                <!-- Your existing create button - SEPARATE FROM THE FILTER FORM -->
                <x-form.createBtn @click="$refs.addSaleRef.showModal()">Add New Sale</x-form.createBtn>
            </div>
        </section>

        <!-- CONTAINER FOR TABLE DETAILS -->
        <section class="border w-full rounded-md border-solid border-black my-3">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Invoice Number</th>
                        <th class=" bg-main px-4 py-3">Date</th>
                        <th class=" bg-main px-4 py-3">Customer Name</th>
                        <th class=" bg-main px-4 py-3">Amount</th>
                        <th class=" bg-main px-4 py-3">Quantity</th>
                        <th class=" bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="truncate px-2 py-2 text-center" title="">INVOICE NUM</td>
                        <td class="truncate px-2 py-2 text-center" title="">DATE</td>
                        <td class="truncate px-2 py-2 text-center" title="">CUSTOMER NAME</td>
                        <td class="truncate px-2 py-2 text-center" title="">AMOUNT</td>
                        <td class="truncate px-2 py-2 text-center" title="">QUANTITY</td>
                        <td class="truncate px-2 py-2 text-center" title="">ACTION</td>
                    </tr>
                </tbody>
            </table>

            <!-- PAGINATION VIEW -->
            {{-- <div class="mt-4 px-4 py-2 bg-gray-50"> --}}
            {{--    {{ $inventoryItems->appends(request()->except('page'))->links() }} --}}
            {{-- </div> --}}

        </section>

        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->

        <!-- ADD SALES MODAL -->
        <x-modal.createModal x-ref="addSaleRef">
            <x-slot:dialogTitle>Add Sale</x-slot:dialogTitle>
            <div class="container">
                
            </div>
        </x-modal.createModal>

    </main>
</x-layout>
