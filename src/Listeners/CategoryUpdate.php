<?php

namespace Samjuk\Varnish\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Aero\Catalog\Events\CategoryUpdated;
use Aero\Catalog\Events\CategoryDeleted;
use Samjuk\Varnish\Models\Varnish;

class CategoryUpdate implements ShouldQueue
{
    public function handle(CategoryUpdated | CategoryDeleted $event)
    {
        // @TODO: Decide we we really need to flush the cache. And if we can get away without purging certain pages (e.g dont flush cat/search due to less data shown)
        (new Varnish)->purgeTags(".*C_" . $event->category->id . ".*");
    }
}
