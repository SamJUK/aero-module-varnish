<?php

namespace Samjuk\Varnish\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Aero\Search\Events\RebuildFinished;
use Samjuk\Varnish\Models\Varnish;

class SearchUpdate implements ShouldQueue
{
    public function handle(RebuildFinished $event)
    {
        // @TODO: Decide we we really need to flush the cache. And if we can get away without purging certain pages (e.g dont flush cat/search due to less data shown)
        (new Varnish)->purgeTags(".*SP_.*");
    }
}
