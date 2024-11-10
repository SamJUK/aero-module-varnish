<?php

namespace Samjuk\Varnish\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

use Samjuk\Varnish\Config;

class Cacheable 
{
    public function handle(Request $request, Closure $next): Response
    {
        $isRequestCacheable = $this->shouldCacheRequest($request->getRequestUri());

        Log::debug(sprintf(
            "[SamJUK_Varnish] Is URL Cacheable - %s: %s", 
            $isRequestCacheable ? 'Yes' : 'No', 
            $request->getRequestUri()
        ));

        if ($isRequestCacheable) {
            $sessionData = session()->all();
            session()->flush();
        } else {
            $sessionData = null;
        }

        return tap($next($request), function($response) use($request, $isRequestCacheable, $sessionData) {
            $response = $this->setVaryHeader($response, $request);
            $response = $this->setCacheTags($response, $request);
            $response = $isRequestCacheable
                ? $this->addCacheHeaderToResponse($response) 
                : $this->addNoCacheHeaderToResponse($response);

            $this->addDebugCacheHeader($response);
            if ($isRequestCacheable) {
                session()->replace($sessionData);
            }

            return $response;
        });
    }

    private function setVaryHeader(Response $response, $request) : Response
    {
        $customer = $request->customer();
        $isLoggedIn = $customer ? 1 : 0;
        $groupId = $isLoggedIn ? $customer->customer_group_id : '-1';
        $vary = sha1("li:$isLoggedIn,cg:$groupId");
        Cookie::queue(Config::COOKIE_CACHE_VARY, $vary, 30);

        Log::debug(sprintf("[SamJUK_Varnish] %s: %s", Config::COOKIE_CACHE_VARY, $vary));

        return $response;
    }

    private function addCacheHeaderToResponse(Response $response, int $maxAge = 86400) : Response
    {
        $response->headers->add([
            'Cache-Control' => "max-age=$maxAge, public, s-maxage=$maxAge",
        ]);
        return $response;
    }

    private function addNoCacheHeaderToResponse(Response $response) : Response
    {
        $response->headers->add([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
        return $response;
    }

    private function addDebugCacheHeader(Response $response) : Response
    {
        if (setting('samjuk-varnish.debug_mode')) {
            $response->headers->add([
                'X-Cache-Debug' => '1'
            ]);
        }
        return $response;
    }

    private function shouldCacheRequest(string $url) : bool
    {
        return setting('samjuk-varnish.enabled') && 
            preg_match('/^\/(personaldata|admin|account|cart|checkout)/', $url) === 0;
    }


    public function setCacheTags(Response $response, Request $request): Response
    {
        $tags = [];
        $controller = Route::getCurrentRoute()->controller;

        $tags = match (true) {
            $controller instanceof \Aero\Store\Http\Controllers\PageController => $this->getCmsPageTags(),
            $controller instanceof \Aero\Store\Http\Controllers\ListingController => $this->getCategoryPageTags(),
            $controller instanceof \Aero\Store\Http\Controllers\ProductController => $this->getProductPageTags(),
            $controller instanceof \Aero\Store\Http\Controllers\SearchController => $this->getSearchPageTags($request),
            default => []
        };

        $tags = implode(',', $tags);
        Log::debug(sprintf('[SamJUK_Varnish] %s: %s', Config::HEADER_CACHE_TAGS, $tags));
        $response->headers->add([ Config::HEADER_CACHE_TAGS => $tags ]);
        return $response;
    }

    private function getCmsPageTags()
    {
        // @TODO: Potential for widget style product listings?
        return ["PG_" . Route::getCurrentRoute()->controller->getRouteSlug()];
    }

    private function getCategoryPageTags()
    {
        $category = Route::getCurrentRoute()->parameters['slugs']->models()->first();
        $productIds = array_map(static function($id) { return "P_" . $id; }, $category->products()->pluck('id')->toArray());
        return ['C_' . $category->id, ...$productIds];
    }

    private function getProductPageTags()
    {
        // @TODO: Related/Upsell Products?
        return ['P_' . Route::getCurrentRoute()->parameters['product']];
    }

    private function getSearchPageTags(Request $request)
    {
        $query = $request->input('q');
        $productIds = []; // @TODO
        return ['SP_' . $query, ...$productIds];
    }

}