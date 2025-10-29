{{-- In your main layout file --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cuatro Patee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="size-screen">
    <!-- Add Alpine store for global notification state -->
    <div x-data x-init="$store.notifications = {
        notifications: [],
        unreadCount: 0,
        readNotifications: new Set(),
        markAsRead: function(id) {
            this.readNotifications.add(id);
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            localStorage.setItem('readNotifications', JSON.stringify(Array.from(this.readNotifications)));
        }
    }">
        
        <!-- This is the main container that will be shown based on the current navigation tab or route -->
        <div {{ $attributes }}>
            {{ $slot }}
        </div>

        <!-- Notification Popup Component -->
        <x-notification-popup />
    </div>
</body>
</html>