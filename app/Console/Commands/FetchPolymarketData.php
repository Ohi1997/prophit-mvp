<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PolymarketService;
use App\Models\Market;
use Carbon\Carbon;

class FetchPolymarketData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polymarket:fetch-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and stores market data and price snapshots from the Polymarket API';

    /**
     * Execute the console command.
     */
    public function handle(PolymarketService $polymarketService)
    {
        $this->info('Starting to fetch data from Polymarket API...');

        $markets = $polymarketService->getActiveMarkets();

        if (empty($markets)) {
            $this->error('No markets found or failed to fetch markets. Exiting.');
            return 1;
        }

        $this->info(count($markets) . ' active markets found. Processing each market...');

        foreach ($markets as $marketData) {
            // check for the data
            if (
                !isset($marketData['condition_id']) ||
                !isset($marketData['question']) ||
                !isset($marketData['tokens'][0]['price']) // Check for the probability here
            ) {
                // skip malformed markets
                continue;
            }

            // Default to 0 if 'volume' key is not present
            $volume = $marketData['volume'] ?? 0;

            // Production Filter: Skip markets with low volume to avoid noise.
            if ($volume < 1000) {
                continue;
            }

            $this->line("Processing market: {$marketData['question']}");

            // Use 'condition_id' as the unique identifier for the market
            $market = Market::updateOrCreate(
                ['market_id' => $marketData['condition_id']],
                ['question' => $marketData['question']]
            );

            // Create the snapshot using the correct data path
            $market->snapshots()->create([
                'probability' => $marketData['tokens'][0]['price'], // Get probability from tokens array
                'volume' => $volume,
                'recorded_at' => Carbon::now(),
            ]);
        }

        $this->info('Successfully fetched and stored Polymarket data.');
        return 0;
    }
}
