<?php

namespace Samjuk\Varnish;

class Config
{
    const COOKIE_CACHE_VARY = 'X-CACHE-VARY';
    const HEADER_CACHE_TAGS = 'X-CACHE-TAGS';

    const HEADER_PURGE_ALL = 'x-invalidate-all';
    const HEADER_PURGE_TAGS = 'x-invalidate-tags';
}