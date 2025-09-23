<div x-data="{ open: false, notifications: [], unreadCount: 0 }" class="relative">
    <!-- Bell Button -->
    <button @click="open = !open; $nextTick(() => { if(open) loadNotifications($el) })" 
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 810 810">
        <path d="M405 592.9c-14.9 0-28.3-8.8-34.3-22.5l-17.1 7.5c9 20.4 29.1 33.2 51.4 33.2s42.9-13.4 51.7-34l-17.2-7.3c-6 13.9-19.5 23.1-34.5 23.1z"/>
        <path d="M588.8 529.6l-49.4-41.2c-2.1-1.8-3.4-4.4-3.4-7.1V368.3c0-36.1-14.4-69.8-40.6-94.7-15.4-14.7-33.7-25-53.4-30.8.5-2.9.6-5.8.4-8.8-1.4-16-13.2-29.6-28.7-33-11.4-2.6-23.1.2-32.1 7.4-9 7.2-14 18-14 29.6 0 1.9.3 3.7.6 5.5-54.7 16.3-94.2 67.9-94.2 129v109.4c0 2.8-1.2 5.4-3.3 7.2l-49.4 41.2c-2.1 1.8-3.3 4.5-3.3 7.2v9.4c0 5.2 4.2 9.4 9.4 9.4h355.5c5.2 0 9.4-4.2 9.4-9.4v-9.4c-.1-2.7-1.3-5.4-3.4-7.1zm-202.6-292c0-5.8 2.6-11.2 7-14.8 4.5-3.6 10.2-4.9 15.8-3.7 7.5 1.6 13.4 8.4 14.1 16.2.1 1.1 0 2.1-.1 3.2-8.1-1.2-16.3-1.6-24.9-1.3-4.1.1-8.2.5-12.2 1.2.2-.5.3-1 .3-1.6zm-144.4 291.4l40.8-34c6.4-5.4 10.1-13.2 10.1-21.5V371.8c0-62 47-112.8 107.9-115.6 31.1-1.5 60.5 9.6 82.4 31.3 22.4 21.4 34.7 50.1 34.7 81.7v112.9c0 8.3 3.7 16.1 10.1 21.5l40.8 34z"/>
        </svg>
        
        <!-- Red Dot - FIXED with x-show -->
        <span x-show="unreadCount > 0" 
              class="absolute top-4 right-4 bg-red-500 text-white rounded-full text-xs w-3 h-3 flex items-center justify-center">
        </span>
    </button>

    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-50 border border-gray-200 max-h-96 overflow-y-auto">
        
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
            <span x-show="unreadCount > 0" class="text-sm text-gray-600">
                <span x-text="unreadCount"></span> unread
            </span>
        </div>

        <div class="divide-y divide-gray-100">
            <template x-for="notification in notifications" :key="notification.id">
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900" x-text="notification.title"></h4>
                            <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                            <a :href="notification.url" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 inline-block mt-2">
                                View
                            </a>
                        </div>
                        <span class="text-xs text-gray-400" x-text="notification.time"></span>
                    </div>
                </div>
            </template>
            
            <div x-show="notifications.length === 0" class="p-4 text-center text-gray-500">
                No notifications
            </div>
        </div>
    </div>
</div>

<script>
function loadNotifications(buttonElement) {
    fetch('/check-notifications')
        .then(response => response.json())
        .then(data => {
            const alpineComponent = buttonElement.closest('[x-data]');
            if (alpineComponent && alpineComponent.__x) {
                alpineComponent.__x.$data.notifications = data.notifications;
                alpineComponent.__x.$data.unreadCount = data.notifications.length;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}
</script>