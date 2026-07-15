<?php

declare(strict_types=1);

use App\Orchid\Screens\Account\AccountScreen;
use App\Orchid\Screens\Blog\BlogListScreen;
use App\Orchid\Screens\Cms\PrivacyScreen;
use App\Orchid\Screens\Cms\TermsScreen;
use App\Orchid\Screens\ContactForm\ContactFormListScreen;
use App\Orchid\Screens\DashboardScreen;
use App\Orchid\Screens\EducationalContent\ArticleEditScreen;
use App\Orchid\Screens\EducationalContent\ArticleListScreen;
use App\Orchid\Screens\Ingredient\IngredientListScreen;
use App\Orchid\Screens\Notification\BroadcastNotificationScreen;
use App\Orchid\Screens\Recipe\RecipeEditScreen;
use App\Orchid\Screens\Recipe\RecipeListScreen;
use App\Orchid\Screens\RemoteUser\RemoteUserListScreen;
use App\Orchid\Screens\SeasonalProduce\SeasonalProduceListScreen;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Every screen here is backed live by the Verdantt API — nothing is
| persisted to a local database.
|
*/

Route::screen('/main', DashboardScreen::class)
    ->name('platform.main');

Route::screen('profile', AccountScreen::class)
    ->name('platform.profile');

Route::screen('recipes', RecipeListScreen::class)
    ->name('platform.recipes');

Route::screen('recipes/create', RecipeEditScreen::class)
    ->name('platform.recipes.create');

Route::screen('recipes/{recipe}/edit', RecipeEditScreen::class)
    ->name('platform.recipes.edit');

Route::screen('ingredients', IngredientListScreen::class)
    ->name('platform.ingredients');

Route::screen('seasonal-produce', SeasonalProduceListScreen::class)
    ->name('platform.seasonal-produce');

Route::screen('blog', BlogListScreen::class)
    ->name('platform.blog');

Route::screen('educational-content', ArticleListScreen::class)
    ->name('platform.educational-content');

Route::screen('educational-content/create', ArticleEditScreen::class)
    ->name('platform.educational-content.create');

Route::screen('educational-content/{article}/edit', ArticleEditScreen::class)
    ->name('platform.educational-content.edit');

Route::screen('contact-forms', ContactFormListScreen::class)
    ->name('platform.contact-forms');

Route::screen('users', RemoteUserListScreen::class)
    ->name('platform.users');

Route::screen('cms/terms', TermsScreen::class)
    ->name('platform.cms.terms');

Route::screen('cms/privacy', PrivacyScreen::class)
    ->name('platform.cms.privacy');

Route::screen('notifications/broadcast', BroadcastNotificationScreen::class)
    ->name('platform.notifications.broadcast');
