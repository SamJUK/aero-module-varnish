<?php

use Illuminate\Support\Facades\Route;
use Samjuk\Varnish\Http\Controllers\VarnishController;

Route::prefix('samjuk-varnish')->name('admin.modules.samjuk-varnish.')->group(function () {
    Route::view('index', 'samjuk-varnish::admin.varnish-cache')->name('index')->middleware('can:cache.view');
    Route::put('purge', [VarnishController::class, 'purge'])->name('purge')->middleware('can:cache');
    Route::put('update', [VarnishController::class, 'update'])->name('update')->middleware('can:cache');
    Route::put('vcl', [VarnishController::class, 'vcl'])->name('vcl')->middleware('can:cache');
});