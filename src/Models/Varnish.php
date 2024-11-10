<?php

namespace Samjuk\Varnish\Models;

use Samjuk\Varnish\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Varnish
{

    public function purgeAll() : bool
    {
        return $this->sendPurgeRequest([
            Config::HEADER_PURGE_ALL => 1
        ]);
    }

    public function purgeTags(string $tags) : bool
    {
        return $this->sendPurgeRequest([
            Config::HEADER_PURGE_TAGS => $tags
        ]);
    }

    public function purge(string $uri) : bool
    {
        return $this->sendPurgeRequest([], $uri);
    }

    private function sendPurgeRequest(array $headers = [], string $uri = '') : bool
    {
        $uri = ltrim($uri, '/');
        $url = "{$this->getHost()}/$uri";
        $hdrs = $this->getHeaders($headers);
        Log::debug(sprintf("[SamJUK_Varnish] Sending PURGE request to $url with headers: " . json_encode($hdrs)));
        return Http::withHeaders($hdrs)->send('PURGE', $url)->successful();
    }

    private function getHost() : string
    {
        return sprintf("http://%s:%s", setting('samjuk-varnish.varnish_host'), setting('samjuk-varnish.varnish_port'));
    }

    private function getHeaders(array $extraHeaders = []) : array
    {
        return array_merge([
            'Host' => setting('samjuk-varnish.app_host')
        ], $extraHeaders);
    }
}