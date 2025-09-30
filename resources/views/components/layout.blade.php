<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cuatro Patee</title>
    <!-- Default Font Link (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <!-- Configures tailwind through vite -->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="size-screen">
    <!-- This is the main container that will be shown based on the current navigation tab or route -->
    <div {{ $attributes }}>
        {{ $slot }}
    </div>

    
</body>
</html>