<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Dashboard')
                ->icon('bs.house')
                ->route('platform.main'),

            Menu::make('Users')
                ->icon('bs.people')
                ->route('platform.users')
                ->title('Community'),

            Menu::make('Recipes')
                ->icon('bs.book')
                ->route('platform.recipes')
                ->title('Catalog'),

            Menu::make('Ingredients')
                ->icon('bs.basket')
                ->route('platform.ingredients'),

            Menu::make('Seasonal Produce')
                ->icon('bs.calendar3')
                ->route('platform.seasonal-produce'),

            Menu::make('Blog')
                ->icon('bs.newspaper')
                ->route('platform.blog')
                ->title('Content'),

            Menu::make('Educational Content')
                ->icon('bs.mortarboard')
                ->route('platform.educational-content'),

            Menu::make('Contact Forms')
                ->icon('bs.envelope')
                ->route('platform.contact-forms')
                ->title('Support'),

            Menu::make('Terms of Use')
                ->icon('bs.file-text')
                ->route('platform.cms.terms')
                ->title('Settings'),

            Menu::make('Privacy Policy')
                ->icon('bs.shield-check')
                ->route('platform.cms.privacy'),

            Menu::make('Broadcast Notification')
                ->icon('bs.megaphone')
                ->route('platform.notifications.broadcast'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [];
    }
}
