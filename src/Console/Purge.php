<?php

namespace Samjuk\Varnish\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Samjuk\Varnish\Models\Varnish;

class Purge extends Command
{
    protected $signature = 'varnish:purge';
    protected $description = 'Purge Varnish Cache';

    public function handle()
    {
        try {
            (new Varnish)->purgeAll();
            $this->info('Varnish Cache Flushed Successfully');
        } catch(\Exception $e) {
            $this->error('Failed to flush Varnish Cache');
            Log::error("[SamJUK_Varnish] Failed PURGE request", ['err' => $e->getMessage()]);
        }
    }
}
