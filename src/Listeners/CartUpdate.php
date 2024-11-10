<?php

namespace Samjuk\Varnish\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Aero\Catalog\Cart\Events\CartItemAdded;
use Aero\Catalog\Cart\Events\CartItemRemoved;
use Aero\Catalog\Cart\Events\CartItemUpdated;
use Aero\Catalog\Cart\Events\CartEmptied;
use Samjuk\Varnish\Models\Varnish;

class CartUpdate implements ShouldQueue
{
    public function handle(CartItemAdded | CartItemRemoved | CartItemUpdated | CartEmptied $event)
    {
        // @TODO: Force a personal data refresh
    }
}
