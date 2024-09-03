<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f7fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
        }
        h1 {
            color: #2d3748;
            font-size: 2.5rem;
            margin-bottom: 2rem;
        }
        .instagram-login-btn {
            background-color: #405DE6;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .instagram-login-btn:hover {
            background-color: #5B7BD5;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome to {{ config('app.name', 'Laravel') }}</h1>
    <a href="{{ route('instagram.auth') }}" class="instagram-login-btn">
        Log in with Instagram
    </a>
</div>
</body>
</html>
