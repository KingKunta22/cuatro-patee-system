<dialog x-ref="modal" class="rounded-lg shadow-lg w-full max-w-lg p-0" 
        x-data="{
            show: false,
            modalConfirm() {
                this.show = false;
                this.$refs.modal.close();
            },
            modalCancel() {
                this.show = false;
                this.$refs.modal.close();
            }
        }" 
        x-show="show" 
        @keydown.window.escape="modalCancel()"
        x-cloak>
    
    <!-- Modal Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40" x-show="show" x-transition></div>
    
    <!-- Modal Content -->
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900"><x-slot:dialogTitle></x-slot:dialogTitle></h3>
                <button @click="modalCancel()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-4">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end gap-3 p-4 border-t bg-gray-50 rounded-b-lg">
                <button @click="modalCancel()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button x-on:click="modalConfirm()" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</dialog>