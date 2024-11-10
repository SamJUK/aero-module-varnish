<?php

namespace Samjuk\Varnish\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Aero\Catalog\Events\ProductCreated;
use Aero\Catalog\Events\ProductDeleted;
use Aero\Catalog\Events\ProductUpdated;
use Samjuk\Varnish\Models\Varnish;

class ProductUpdate implements ShouldQueue
{
    public function handle(ProductCreated | ProductDeleted | ProductUpdated $event)
    {
        // @TODO: Decide we we really need to flush the cache. And if we can get away without purging certain pages (e.g dont flush cat/search due to less data shown)
        (new Varnish)->purgeTags(".*P_" . $event->product->id . ".*");
    }
}
