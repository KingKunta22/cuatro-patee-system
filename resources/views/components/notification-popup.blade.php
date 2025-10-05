{{-- resources/views/components/notification-popup.blade.php --}}
<div x-data="{
    showPopup: false,
    currentPopup: null,
    popupTimeout: null,
    
    init() {
        // Listen for new notifications from the dropdown component
        this.$watch('$store.notifications.unreadCount', (value) => {
            if (value > 0 && !this.showPopup) {
                this.showNextPopup();
            }
        });
        
        // Check for notifications on page load
        this.$nextTick(() => {
            setTimeout(() => {
                if (this.$store.notifications.unreadCount > 0) {
                    this.showNextPopup();
                }
            }, 2000);
        });
    },
    
    showNextPopup() {
        // Don't show if there's already a popup visible
        if (this.showPopup) return;
        
        // Get unread notifications from the dropdown component
        const unreadNotifications = this.$store.notifications.notifications?.filter(
            notification => !this.$store.notifications.readNotifications.has(notification.id)
        ) || [];
        
        if (unreadNotifications.length > 0 && unreadNotifications[0]) {
            this.currentPopup = unreadNotifications[0];
            this.showPopup = true;
            
            // Auto-hide after 6 seconds
            this.popupTimeout = setTimeout(() => {
                this.closePopup();
            }, 6000);
        }
    },
    
    closePopup() {
        this.showPopup = false;
        if (this.popupTimeout) {
            clearTimeout(this.popupTimeout);
            this.popupTimeout = null;
        }
        
        // Show next popup after a short delay if there are more unread notifications
        setTimeout(() => {
            if (this.$store.notifications.unreadCount > 0) {
                this.showNextPopup();
            }
        }, 1000);
    },
    
    markPopupAsRead() {
        if (this.currentPopup) {
            this.$store.notifications.markAsRead(this.currentPopup.id);
            this.closePopup();
        }
    },
    
    getPopupUrgentLevel(notification) {
        if (!notification) return 'low';
        
        if (notification.title.includes('Low Stock') && notification.message.includes('Stock: 5')) {
            return 'high';
        } else if (notification.title.includes('Expiring') && parseInt(notification.message.match(/\d+/)?.[0] || 30) <= 7) {
            return 'high';
        } else if (notification.title.includes('Low Stock')) {
            return 'medium';
        } else if (notification.title.includes('Expiring')) {
            return 'medium';
        } else {
            return 'low';
        }
    },
    
    getPopupStyles(level) {
        const styles = {
            high: 'border-l-red-400 bg-red-50',
            medium: 'border-l-orange-400 bg-orange-50', 
            low: 'border-l-blue-400 bg-blue-50'
        };
        return styles[level] || styles.low;
    },
    
    getPopupIcon(level) {
        const icons = {
            high: `🔴`,
            medium: `🟠`,
            low: `🔵`
        };
        return icons[level] || icons.low;
    }
}" 
x-show="showPopup"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 translate-x-full"
x-transition:enter-end="opacity-100 translate-x-0"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100 translate-x-0"
x-transition:leave-end="opacity-0 translate-x-full"
class="fixed bottom-4 right-4 w-80 z-50">
    <template x-if="currentPopup">
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <span x-text="getPopupIcon(getPopupUrgentLevel(currentPopup))" class="text-sm"></span>
                    <h3 class="text-sm font-semibold text-gray-900" x-text="currentPopup.title"></h3>
                </div>
                <button @click="closePopup()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-4" :class="getPopupStyles(getPopupUrgentLevel(currentPopup))">
                <p class="text-sm text-gray-700 mb-3" x-text="currentPopup.message"></p>
                <div class="flex gap-2">
                    <a :href="currentPopup.url" 
                    @click="markPopupAsRead()"
                    class="flex-1 text-center bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
                        View Details
                    </a>
                    <button @click="markPopupAsRead()"
                            class="flex-1 text-center bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300 transition-colors">
                        Mark as Read
                    </button>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="h-1 bg-gray-200">
                <div x-show="showPopup" 
                     x-transition:enter="transition-all ease-linear duration-6000"
                     x-transition:enter-start="w-full"
                     x-transition:enter-end="w-0"
                     class="h-full bg-blue-500">
                </div>
            </div>
        </div>
    </template>
</div>