<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Escalated Case</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 min-h-screen flex items-center justify-center">
<div class="bg-white rounded-2xl shadow-xl max-w-lg p-8 space-y-6">
    <h1 class="text-3xl font-extrabold text-gray-800">🚨 New Escalated Case: #{{ $escalatedCase->id }}</h1>
    <p class="text-lg text-gray-600">A new case has been escalated and requires your immediate attention.</p>
    <ul class="space-y-3">
        <li class="flex items-center text-gray-700">
            <span class="font-semibold w-32">Case ID:</span>
            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">{{ $escalatedCase->id }}</span>
        </li>
        <li class="flex items-center text-gray-700">
            <span class="font-semibold w-32">Recipient ID:</span>
            <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full">{{ $escalatedCase->recipient_id }}</span>
        </li>
        <li class="flex items-center text-gray-700">
            <span class="font-semibold w-32">Escalation Level:</span>
            <span class="bg-pink-100 text-pink-700 px-3 py-1 rounded-full">{{ $escalatedCase->escalation_level }}</span>
        </li>
        <li class="flex items-center text-gray-700">
            <span class="font-semibold w-32">Status:</span>
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full">{{ $escalatedCase->status }}</span>
        </li>
        <li class="flex items-center text-gray-700">
            <span class="font-semibold w-32">Created At:</span>
            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full">{{ $escalatedCase->created_at }}</span>
        </li>
    </ul>
    <p class="text-lg text-gray-600">Please review and take appropriate action as soon as possible.</p>
</div>
</body>
</html>

