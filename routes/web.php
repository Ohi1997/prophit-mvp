<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViewController;

// Route for the main market movement feed page
Route::get('/', [ViewController::class, 'marketFeed']);

// Route for the individual market detail page
Route::get('/market/{market_id}', [ViewController::class, 'marketDetail']);