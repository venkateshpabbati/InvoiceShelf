<?php

namespace App\Providers;

use App\Platform\Operations\Installation\Application\InstallationState;
use App\Platform\Persistence\ModelIdentityMap;
use App\Support\Bouncer\BouncerDefaultScope;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Silber\Bouncer\Database\Models as BouncerModels;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Where a signed-in staff user lands.
     *
     * The authentication layer redirects here once credentials check out.
     *
     * @var string
     */
    public const HOME = '/admin/dashboard';

    /**
     * Where a signed-in portal customer lands.
     *
     * The customer guard redirects here once credentials check out.
     *
     * @var string
     */
    public const CUSTOMER_HOME = '/customer/dashboard';

    /**
     * Boot the application-wide behaviour.
     */
    public function boot(): void
    {
        ModelIdentityMap::enforce();

        Factory::guessFactoryNamesUsing(
            fn (string $model): string => 'Database\\Factories\\'.class_basename($model).'Factory'
        );

        // Navigation is built from config only once there is a schema to talk
        // to; during a fresh install the tables do not exist yet.
        if (InstallationState::isDbCreated()) {
            $this->addMenus();
        }

        $this->bootBroadcast();

        // The public demo build must never put real mail on the wire.
        if (config('app.env') === 'demo') {
            Mail::fake();
            Notification::fake();
        }
    }

    /**
     * Register container bindings.
     */
    public function register(): void
    {
        BouncerModels::scope(new BouncerDefaultScope);
    }

    /**
     * Publish every navigation tree the SPA can ask for.
     *
     * Keys are the registered menu names; values are the config entries each
     * one is built from. Note the customer portal menu is registered under a
     * name that differs from its config key.
     */
    public function addMenus()
    {
        $sources = [
            'main_menu' => 'invoiceshelf.main_menu',
            'admin_menu' => 'invoiceshelf.admin_menu',
            'setting_menu' => 'invoiceshelf.setting_menu',
            'customer_portal_menu' => 'invoiceshelf.customer_menu',
        ];

        foreach ($sources as $name => $configKey) {
            \Menu::make($name, function ($menu) use ($configKey) {
                foreach (config($configKey) as $data) {
                    $this->generateMenu($menu, $data);
                }
            });
        }
    }

    /**
     * Append one configured entry to a menu under construction.
     *
     * Everything past the title and link rides along as item metadata, which
     * is what the bootstrap endpoints filter and hand to the frontend.
     */
    public function generateMenu($menu, $data)
    {
        $item = $menu->add($data['title'], $data['link']);

        $meta = [
            'icon' => $data['icon'],
            'name' => $data['name'],
            'owner_only' => $data['owner_only'],
            'super_admin_only' => $data['super_admin_only'] ?? false,
            'ability' => $data['ability'],
            'model' => $data['model'],
            'group' => $data['group'],
            'group_label' => $data['group_label'] ?? '',
            'priority' => $data['priority'] ?? 100,
        ];

        foreach ($meta as $key => $value) {
            $item->data($key, $value);
        }
    }

    /**
     * Expose the broadcasting auth endpoint behind the API guard.
     */
    public function bootBroadcast()
    {
        Broadcast::routes(['middleware' => 'api.auth']);
    }
}
