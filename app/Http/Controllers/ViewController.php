<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ViewController extends Controller
{
    /**
     * Display the main market movement feed page.
     */
    public function marketFeed(): View
    {
        return view('market-feed');
    }

    /**
     * Display the detail page for a single market.
     */
    public function marketDetail(string $market_id): View
    {
        // Pass market_id to the view so JavaScript can use it
        return view('market-detail', ['marketId' => $market_id]);
    }
}
