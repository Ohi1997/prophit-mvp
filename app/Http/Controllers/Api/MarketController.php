<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import this for better error handling
use Throwable; // Import this for general error handling
use Illuminate\Support\Facades\Log; // Import Log for error logging

class MarketController extends Controller
{
    /**
     * Display a listing of markets with significant movements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $threshold = 0.10; // Threshold set for 10% change
        $timeframe = Carbon::now()->subHours(24);

        $marketsWithSnapshots = Market::with(['snapshots' => function ($query) use ($timeframe) {
            $query->where('recorded_at', '>=', $timeframe)
                  ->orderBy('recorded_at', 'asc');
        }])->get();

        $movements = [];

        foreach ($marketsWithSnapshots as $market) {
            if ($market->snapshots->count() < 2) {
                continue; // Need at least two points to calculate a change
            }

            $firstSnapshot = $market->snapshots->first();
            $lastSnapshot = $market->snapshots->last();

            // calculate change
            $change = $lastSnapshot->probability - $firstSnapshot->probability;
            
            if (abs($change) >= $threshold) {
                $movements[] = [
                    'market_id' => $market->market_id,
                    'question' => $market->question,
                    'current_odds' => (float) $lastSnapshot->probability,
                    'change_percentage' => round($change * 100, 2),
                    'time_of_movement' => $lastSnapshot->recorded_at->toIso8601String(),
                ];
            }
        }

        // Sort by largest movement (absolute)
        usort($movements, function ($a, $b) {
            return abs($b['change_percentage']) <=> abs($a['change_percentage']);
        });

        return response()->json(['data' => $movements]);
    }

    /**
     * Display the specified market's history.
     *
     * @param string $market_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $market_id): JsonResponse
    {
        try {
            $market = Market::where('market_id', $market_id)->firstOrFail();

            $history = $market->snapshots()
                          ->where('recorded_at', '>=', Carbon::now()->subHours(24))
                          ->orderBy('recorded_at', 'asc')
                          ->get(['recorded_at', 'probability']);

            return response()->json([
                'data' => [
                    'question' => $market->question,
                    'history' => $history,
                ]
            ]);
        // Error handling for the API
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Market not found.'], 404);
        } catch (Throwable $e) {
            // Save error in the log
            Log::error("Error fetching history for market {$market_id}.", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An internal server error occurred.'], 500);
        }
    }
}