<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prophit MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        /* Simple dark mode */
        body { background-color: #111827; color: #d1d5db; }
        .card { background-color: #1f2937; border-color: #374151; }
        .positive { color: #22c55e; }
        .negative { color: #ef4444; }
    </style>
</head>
<body class="antialiased">
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-white text-center">Prophit MVP</h1>
            <p class="text-center text-gray-400">Significant Prediction Market Movements</p>
        </header>

        <main>
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>