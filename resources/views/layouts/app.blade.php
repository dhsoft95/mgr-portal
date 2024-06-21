<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite('resources/js/app.js')
    @stack('styles')
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>
