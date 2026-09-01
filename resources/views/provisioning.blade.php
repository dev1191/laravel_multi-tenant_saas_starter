<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preparing Workspace — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="5">
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full text-center bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700">
        <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Setting up your workspace</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6 text-sm">
            We are creating an isolated database and preparing all components for <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $tenant->name }}</span>. This page will refresh automatically in a few seconds.
        </p>
        <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
            <div class="bg-indigo-600 h-full rounded-full animate-pulse" style="width: 75%"></div>
        </div>
    </div>
</body>
</html>
