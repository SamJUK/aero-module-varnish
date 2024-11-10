<?php

namespace Samjuk\Varnish\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Aero\Content\Events\BlockCreated;
use Aero\Content\Events\BlockDeleted;
use Aero\Content\Events\BlockUpdated;
use Samjuk\Varnish\Models\Varnish;

class BlockUpdate implements ShouldQueue
{
    public function handle(BlockCreated | BlockDeleted | BlockUpdated $event)
    {
        // @TODO: Decide we we really need to flush the cache. And if we can get away without purging certain pages (e.g dont flush cat/search due to less data shown)
        (new Varnish)->purgeTags(".*B_" . $event->block->id . ".*");
    }
}
