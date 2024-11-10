<?php

use Illuminate\Support\Facades\Route;
use Samjuk\Varnish\Http\Controllers\VarnishController;

Route::get('personaldata', [VarnishController::class, 'personalData'])->name('samjuk-varnish.personaldata');