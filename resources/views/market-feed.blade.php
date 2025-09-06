@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <!-- Flex container for the title and refresh button -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-white">Market Movement Feed</h2>
        <button id="refresh-btn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-200">
            Refresh
        </button>
    </div>

    <!-- Button to show when new movements are available -->
    <button id="new-movements-indicator" class="hidden w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
        <span>Show new movements</span>
    </button>

    <!-- Loading state placeholder -->
    <div id="loading" class="text-center py-8">
        <p class="text-gray-400">Loading market movements...</p>
    </div>

    <!-- This is where our JavaScript will inject the market data -->
    <div id="market-feed-container" class="hidden space-y-4">
        <!-- Market cards will be injected here -->
    </div>

    <!-- Error message placeholder -->
    <div id="error-message" class="hidden text-center py-8 card rounded-lg">
        <p class="text-red-400"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // STATE MANAGEMENT: keep track of the markets currently being shown
        let currentMarketIds = new Set();
        let pendingMovements = [];

        // ELEMENT REFERENCES
        const feedContainer = document.getElementById('market-feed-container');
        const loadingIndicator = document.getElementById('loading');
        const errorMessageContainer = document.getElementById('error-message');
        const refreshButton = document.getElementById('refresh-btn');
        const newMovementsIndicator = document.getElementById('new-movements-indicator');


        // EVENT LISTENERS
        refreshButton.addEventListener('click', fetchMarketMovements);

        newMovementsIndicator.addEventListener('click', () => {
            // When the indicator is clicked, render the pending data and hide the button
            renderFeed(pendingMovements);
            currentMarketIds = new Set(pendingMovements.map(m => m.market_id));
            pendingMovements = [];
            newMovementsIndicator.classList.add('hidden');
        });


        // CORE FUNCTIONS

        // Fetches data and immediately renders it. Used for initial load and manual refresh.
        async function fetchMarketMovements() {
            // Show loading state for manual refresh
            feedContainer.classList.add('hidden');
            loadingIndicator.classList.remove('hidden');
            newMovementsIndicator.classList.add('hidden'); // Hide indicator during refresh

            try {
                const response = await fetch('/api/movements');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const result = await response.json();
                const movements = result.data;
                
                loadingIndicator.classList.add('hidden');
                feedContainer.classList.remove('hidden');

                renderFeed(movements);
                // Update state with the latest IDs
                currentMarketIds = new Set(movements.map(m => m.market_id));

            } catch (error) {
                console.error("Failed to fetch market movements:", error);
                showError("Could not load market data. Please try again later.");
            }
        }

        // Checks for new data in the background without re-rendering. Used by the timer.
        async function checkForNewMovements() {

            try {
                const response = await fetch('/api/movements');
                if (!response.ok) {
                    console.error('Background fetch failed with status:', response.status);
                    return; 
                }
                 
                const result = await response.json();
                const newMovements = result.data;
                const newMarketIds = new Set(newMovements.map(m => m.market_id));
                const isCountDifferent = newMarketIds.size !== currentMarketIds.size;
                const hasNewItem = newMovements.some(m => !currentMarketIds.has(m.market_id));
                
                if (isCountDifferent || hasNewItem) {
                    pendingMovements = newMovements;
                    
                    const newItemsCount = newMovements.filter(m => !currentMarketIds.has(m.market_id)).length;
                    const buttonText = newItemsCount > 0 
                    ? `Show ${newItemsCount} new movement${newItemsCount > 1 ? 's' : ''}` 
                    : `Feed has been updated`;
                    
                    newMovementsIndicator.querySelector('span').textContent = buttonText;
                    newMovementsIndicator.classList.remove('hidden');
                }

            } catch (error) {
                console.error("Background check threw an exception:", error);
            }
        }

        // Takes data and renders the HTML
        function renderFeed(movements) {
            feedContainer.innerHTML = '';
            if (movements.length === 0) {
                feedContainer.innerHTML = `<div class="card rounded-lg p-6 text-center"><p class="text-gray-400">No significant market movements in the last 24 hours.</p></div>`;
                return;
            }
            movements.forEach(market => {
                const changeClass = market.change_percentage > 0 ? 'positive' : 'negative';
                const sign = market.change_percentage > 0 ? '+' : '';
                const marketCard = `
                    <a href="/market/${market.market_id}" class="card block rounded-lg p-4 sm:p-6 hover:bg-gray-700 transition duration-200">
                        <div class="flex justify-between items-center">
                            <p class="text-lg text-gray-200 w-3/4">${market.question}</p>
                            <div class="text-right">
                                <p class="text-2xl font-bold ${changeClass}">${sign}${market.change_percentage}%</p>
                                <p class="text-sm text-gray-400">${new Date(market.time_of_movement).toLocaleTimeString()}</p>
                            </div>
                        </div>
                    </a>
                `;
                feedContainer.innerHTML += marketCard;
            });
        }

        function showError(message) {
            loadingIndicator.classList.add('hidden');
            errorMessageContainer.classList.remove('hidden');
            errorMessageContainer.querySelector('p').textContent = message;
        }

        // INITIALIZATION
        fetchMarketMovements(); // Load data on page start
        setInterval(checkForNewMovements, 120000); // Check for new data every 2 minute
    });
</script>
@endpush