<?php

namespace Samjuk\Varnish;

use Aero\Common\Facades\Settings;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Common\Settings\SettingGroup;

use Samjuk\Varnish\Listeners\ProductUpdate;
use Samjuk\Varnish\Listeners\CategoryUpdate;
use Samjuk\Varnish\Listeners\BlockUpdate;
use Samjuk\Varnish\Listeners\CartUpdate;
use Samjuk\Varnish\Listeners\SearchUpdate;
use Samjuk\Varnish\Console\Purge as PurgeCommand;

class ServiceProvider extends ModuleServiceProvider
{
    protected $listen = [
        \Aero\Catalog\Events\ProductCreated::class => [ProductUpdate::class],
        \Aero\Catalog\Events\ProductDeleted::class => [ProductUpdate::class],
        \Aero\Catalog\Events\ProductUpdated::class => [ProductUpdate::class],

        \Aero\Catalog\Events\CategoryUpdated::class => [CategoryUpdate::class],
        \Aero\Catalog\Events\CategoryDeleted::class => [CategoryUpdate::class],

        \Aero\Content\Events\BlockCreated::class => [BlockUpdate::class],
        \Aero\Content\Events\BlockDeleted::class => [BlockUpdate::class],
        \Aero\Content\Events\BlockUpdated::class => [BlockUpdate::class],

        \Aero\Catalog\Cart\Events\CartItemAdded::class => [CartUpdate::class],
        \Aero\Catalog\Cart\Events\CartItemRemoved::class => [CartUpdate::class],
        \Aero\Catalog\Cart\Events\CartItemUpdated::class => [CartUpdate::class],
        \Aero\Catalog\Cart\Events\CartEmptied::class => [CartUpdate::class],

        \Aero\Search\Events\RebuildFinished::class => [SearchUpdate::class]
        
        // @TODO: Price List Changes
    ];

    public function register()
    {
        parent::register();
        $this->app->bind('command.varnish.purge', PurgeCommand::class);
        $this->commands([ 'command.varnish.purge' ]);
    }

    public function assetLinks()
    {
        return [
            'samjuk/aero-varnish' => __DIR__.'/../public',
        ];
    }

    public function setup()
    {
        $router = $this->app->make('router');
        $router->addStoreRoutes(__DIR__.'/../routes/store.php');
        $this->loadViewsFrom(base_path("vendor/samjuk/aero-varnish/resources/views"), 'samjuk-varnish');
        $this->replaceCacheMiddleware($router);
        $this->preventEncryptingVaryCookie();
        $this->registerSettings();
        $this->registerAdminModule();
        $this->injectPersonalDataScript();
    }

    private function registerSettings()
    {
        Settings::group('samjuk-varnish', function (SettingGroup $group) {
            $group->boolean('enabled')->default(true);
            $group->boolean('debug_mode')->default(false);
            $group->string('varnish_host')->default('localhost');
            $group->string('varnish_port')->default('6081');
            $group->string('app_host')->default('app.aero.test');
        });
    }

    private function registerAdminModule()
    {
        \Aero\Admin\AdminModule::create('samjuk-varnish')
            ->title('Varnish')
            ->summary('Varnish Implementation')
            ->routes(__DIR__.'/../routes/admin.php')
            ->route('admin.modules.samjuk-varnish.index');
    }

    private function replaceCacheMiddleware($router)
    {
        $router->removeMiddlewareFromGroup('store', \Aero\Store\Http\Middleware\AddNoCacheHeader::class);
        $router->pushMiddlewareToGroup('store', \Samjuk\Varnish\Http\Middleware\Cacheable::class);
    }

    private function preventEncryptingVaryCookie()
    {
        config(['aero.routing.encrypt_cookies_exceptions' => array_merge(config('aero.routing.encrypt_cookies_exceptions'), ['X-Cache-Vary'])]);
        return;
    }

    private function injectPersonalDataScript()
    {
        \Aero\Store\Pipelines\ContentForHead::extend(function (&$content) {
            $content .= view('samjuk-varnish::personal-data');
        });
    }
}
