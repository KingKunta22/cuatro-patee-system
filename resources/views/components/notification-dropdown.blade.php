<div x-data="{ 
    open: false, 
    notifications: [], 
    unreadCount: 0,
    readNotifications: new Set(),
    
    loadNotifications() {
        fetch('/check-notifications')
            .then(response => response.json())
            .then(data => {
                this.notifications = data.notifications;
                // Calculate unread count excluding already read notifications
                this.unreadCount = this.notifications.length - this.readNotifications.size;
            })
            .catch(error => console.error('Error:', error));
    },
    
    markAsRead(notificationId) {
        this.readNotifications.add(notificationId);
        this.unreadCount = Math.max(0, this.notifications.length - this.readNotifications.size);
    },
    
    markAllAsRead() {
        this.notifications.forEach(notification => {
            this.readNotifications.add(notification.id);
        });
        this.unreadCount = 0;
    },
    
    isRead(notificationId) {
        return this.readNotifications.has(notificationId);
    },
    
    getUrgentLevel(notification) {
        if (notification.title.includes('Low Stock') && notification.message.includes('Stock: 5')) {
            return 'high'; // Very low stock (5 or less)
        } else if (notification.title.includes('Expiring') && parseInt(notification.message.match(/\d+/)[0]) <= 7) {
            return 'high'; // Expiring in 7 days or less
        } else if (notification.title.includes('Low Stock')) {
            return 'medium'; // Low stock (6-10)
        } else if (notification.title.includes('Expiring')) {
            return 'medium'; // Expiring in 8-30 days
        } else {
            return 'low'; // Delivery notifications
        }
    }
}" class="relative">
    <!-- Bell Button -->
    <button @click="open = !open; if(open) loadNotifications()" 
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 810 810">
        <path d="M405 592.9c-14.9 0-28.3-8.8-34.3-22.5l-17.1 7.5c9 20.4 29.1 33.2 51.4 33.2s42.9-13.4 51.7-34l-17.2-7.3c-6 13.9-19.5 23.1-34.5 23.1z"/>
        <path d="M588.8 529.6l-49.4-41.2c-2.1-1.8-3.4-4.4-3.4-7.1V368.3c0-36.1-14.4-69.8-40.6-94.7-15.4-14.7-33.7-25-53.4-30.8.5-2.9.6-5.8.4-8.8-1.4-16-13.2-29.6-28.7-33-11.4-2.6-23.1.2-32.1 7.4-9 7.2-14 18-14 29.6 0 1.9.3 3.7.6 5.5-54.7 16.3-94.2 67.9-94.2 129v109.4c0 2.8-1.2 5.4-3.3 7.2l-49.4 41.2c-2.1 1.8-3.3 4.5-3.3 7.2v9.4c0 5.2 4.2 9.4 9.4 9.4h355.5c5.2 0 9.4-4.2 9.4-9.4v-9.4c-.1-2.7-1.3-5.4-3.4-7.1zm-202.6-292c0-5.8 2.6-11.2 7-14.8 4.5-3.6 10.2-4.9 15.8-3.7 7.5 1.6 13.4 8.4 14.1 16.2.1 1.1 0 2.1-.1 3.2-8.1-1.2-16.3-1.6-24.9-1.3-4.1.1-8.2.5-12.2 1.2.2-.5.3-1 .3-1.6zm-144.4 291.4l40.8-34c6.4-5.4 10.1-13.2 10.1-21.5V371.8c0-62 47-112.8 107.9-115.6 31.1-1.5 60.5 9.6 82.4 31.3 22.4 21.4 34.7 50.1 34.7 81.7v112.9c0 8.3 3.7 16.1 10.1 21.5l40.8 34z"/>
        </svg>
        
        <!-- Red Dot -->
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
        
        <div class="px-4 py-2 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h3 class="text-md font-semibold text-gray-900">Notifications</h3>
                <span x-show="unreadCount > 0" class="text-xs text-gray-600">
                    <span x-text="unreadCount"></span> unread
                </span>
            </div>
            <button x-show="unreadCount > 0" @click="markAllAsRead()" 
                    class="text-xs text-blue-600 hover:text-blue-800 underline">
                Mark all read
            </button>
        </div>

        <div class="divide-y divide-gray-100">
            <template x-for="notification in notifications" :key="notification.id">
                <div class="p-4 hover:bg-gray-50 transition-colors duration-200"
                     :class="{
                         'bg-red-50 border-l-4 border-l-red-400': !isRead(notification.id) && getUrgentLevel(notification) === 'high',
                         'bg-orange-50 border-l-4 border-l-orange-400': !isRead(notification.id) && getUrgentLevel(notification) === 'medium',
                         'bg-blue-50 border-l-4 border-l-blue-400': !isRead(notification.id) && getUrgentLevel(notification) === 'low',
                         'bg-white': isRead(notification.id)
                     }">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-medium text-gray-900" x-text="notification.title"></h4>
                                <span x-show="!isRead(notification.id) && getUrgentLevel(notification) === 'high'" 
                                      class="bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full font-medium">
                                    URGENT
                                </span>
                                <span x-show="!isRead(notification.id) && getUrgentLevel(notification) === 'medium'" 
                                      class="bg-orange-100 text-orange-800 text-xs px-2 py-0.5 rounded-full font-medium">
                                    IMPORTANT
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                            <div class="mt-2 flex gap-2">
                                <a :href="notification.url" 
                                   @click="markAsRead(notification.id)"
                                   class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 inline-block transition-colors">
                                    View
                                </a>
                                <button x-show="!isRead(notification.id)" 
                                        @click="markAsRead(notification.id)"
                                        class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded hover:bg-gray-300 inline-block transition-colors">
                                    Mark as read
                                </button>
                            </div>
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