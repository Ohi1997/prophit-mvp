@extends('layouts.app')

@section('content')
<div class="card rounded-lg p-4 sm:p-6">
    {{-- Link to go back to the main feed --}}
    <a href="/" class="text-blue-400 hover:underline mb-6 block">&larr; Back to Feed</a>

    {{-- Title placeholder, will be filled by JS --}}
    <h2 id="market-question" class="text-2xl font-semibold text-white mb-4">Loading market question...</h2>

    {{-- The element where the chart will be rendered --}}
    <div id="chart-container" class="bg-gray-800 p-2 rounded-md">
         <div id="chart"></div>
    </div>

    {{-- Loading and Error states --}}
    <div id="loading" class="text-center py-8">
        <p class="text-gray-400">Loading chart data...</p>
    </div>
    <div id="error-message" class="hidden text-center py-8">
         <p class="text-red-400"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get the market ID that is passed from the ViewController
        const marketId = "{{ $marketId }}";

        // Get references to HTML elements
        const questionTitle = document.getElementById('market-question');
        const chartContainer = document.getElementById('chart-container');
        const loadingIndicator = document.getElementById('loading');
        const errorMessageContainer = document.getElementById('error-message');

        // Hide chart until data is loaded
        chartContainer.style.display = 'none';

        async function fetchMarketHistory() {
            try {
                const response = await fetch(`/api/markets/${marketId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();
                renderChart(result.data);
            } catch (error) {
                console.error("Failed to fetch market history:", error);
                showError("Could not load chart data.");
            }
        }

        function renderChart(data) {
            loadingIndicator.style.display = 'none';
            chartContainer.style.display = 'block';

            // Update the market question title
            questionTitle.textContent = data.question;

            // Format the data for ApexCharts
            const seriesData = data.history.map(snapshot => {
                return {
                    x: new Date(snapshot.recorded_at).getTime(),
                    y: snapshot.probability
                };
            });

            const options = {
                series: [{
                    name: 'Probability',
                    data: seriesData
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                theme: {
                    mode: 'dark'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                },
                xaxis: {
                    type: 'datetime',
                },
                yaxis: {
                    min: 0,
                    max: 1,
                    labels: {
                        formatter: function (val) {
                            return (val * 100).toFixed(0) + '%';
                        }
                    }
                },
                tooltip: {
                    x: {
                        format: 'MMM dd, HH:mm'
                    },
                    y: {
                        formatter: function (val) {
                            return (val * 100).toFixed(2) + '%';
                        }
                    }
                },
            };

            const chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        }

        function showError(message) {
            loadingIndicator.style.display = 'none';
            errorMessageContainer.classList.remove('hidden');
            errorMessageContainer.querySelector('p').textContent = message;
        }

        // Fetch data as soon as the page loads
        fetchMarketHistory();
    });
</script>
@endpush