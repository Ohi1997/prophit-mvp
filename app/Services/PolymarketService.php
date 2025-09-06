<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class PolymarketService
{
    protected string $baseUrl;

    /**
     * Constructor for the PolymarketService.
     */
    public function __construct()
    {
        // The base URL for the Polymarket CLOB API
        $this->baseUrl = 'https://clob.polymarket.com';
    }

    /**
     * Fetches all active markets from the Polymarket API.
     *
     * @return array Returns an array of markets or an empty array on failure.
     */
    public function getActiveMarkets(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/markets');

            // Throw an exception if the request was not successful
            $response->throw();

            // Return the decoded JSON response, defaulting to an empty array if 'data' is missing
            return $response->json('data', []);

        } catch (RequestException $e) {
            // Log the error for debugging purposes
            Log::error('Failed to fetch markets from Polymarket API', [
                'error' => $e->getMessage()
            ]);

            // Return an empty array to prevent the application from crashing
            return [];
        }
    }

    /**
     * Fetches the price history for a specific market.
     *
     * @param string $marketId The ID of the market.
     * @return array Returns an array of price history points or an empty array on failure.
     */
    public function getMarketPriceHistory(string $marketId): array
    {
        try {
            $response = Http::get($this->baseUrl . "/markets/{$marketId}/price-history");

            $response->throw();

            return $response->json('data', []);

        } catch (RequestException $e) {
            Log::error("Failed to fetch price history for market {$marketId}", [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }
}