<?php

namespace Samjuk\Varnish\Http\Controllers;

use Aero\Cart\Cart;
use Aero\Common\Facades\Settings;
use Aero\Admin\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Samjuk\Varnish\Models\Varnish;
use Samjuk\Varnish\Requests\UpdateVarnishRequest;

class VarnishController extends Controller
{
    public function personalData()
    {
        $cart = app()->make(Cart::class);

        $customer = request()->customer();
        $isLoggedIn = $customer ? 1 : 0;
        $groupId = $isLoggedIn ? $customer->customer_group_id : '-1';
        $vary = sha1("li:$isLoggedIn,cg:$groupId");

        return response()->json([
            'time' => time(),
            'csrf_token' => csrf_token(),
            'cart_count' => $cart->count(),
            'vary' => $vary
        ]);
    }

    public function purge()
    {
        $varnish = new Varnish;

        try {
            $success = match(request()->input('purge')) {
                'entities' => $varnish->purgeTags(".*(" . implode('|', request()->input('entities')) . ").*"),
                'tags' => $varnish->purgeTags(request()->input('tags')),
                'uri' => $varnish->purge(request()->input('uri')),
                default => $varnish->purgeAll()
            };
        } catch (\Exception $e) {
            $success = false;
            Log::error("[SamJUK_Varnish] Failed PURGE request", ['err' => $e->getMessage()]);
        }

        $payload = $success
            ? ['message' => __('Varnish Cache has been purged')]
            : ['error' => __('Varnish Cache failed to purge, see log file')];

        return redirect(route('admin.modules.samjuk-varnish.index'))->with($payload);
    }

    public function vcl()
    {
        $vclContent = \Illuminate\Support\Facades\File::get(base_path("vendor/samjuk/aero-varnish/fixtures/default.vcl.template"));
        $vclContent = str_replace('${BACKEND_HOST}', request()->input('backend_host'), $vclContent);
        $vclContent = str_replace('${BACKEND_PORT}', request()->input('backend_port'), $vclContent);
        $vclContent = str_replace('${ACL_PURGE_HOST}', request()->input('purge_allow'), $vclContent);
        return response($vclContent, 200)->header('Content-Type', 'text/text');
    }

    public function update(UpdateVarnishRequest $request, Varnish $model)
    {
        Settings::save('samjuk-varnish', $request->validated());
        return redirect(route('admin.modules.samjuk-varnish.index'))->with([
            'message' => __('Your changes have been saved'),
        ]);
    }
}