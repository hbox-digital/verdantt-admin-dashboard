<?php

namespace App\Orchid\Screens;

use App\Services\VerdanttApiClient;
use Orchid\Screen\Layouts\Chart;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class DashboardScreen extends Screen
{
    public function query(): iterable
    {
        $client = app(VerdanttApiClient::class);
        $data = $client->get('/admin/dashboard')->json('data') ?? [];
        $users = $data['users'] ?? [];

        // The dashboard endpoint doesn't report these yet, so they're
        // counted from their own list endpoints instead.
        $blogCount = count($client->get('/admin/blogs')->json('data') ?? []);
        $articleCount = count($client->get('/admin/articles')->json('data') ?? []);
        $seasonalCount = count($client->get('/admin/seasonal-produce')->json('data') ?? []);

        return [
            'metrics' => [
                [
                    'label' => 'Total Users',
                    'value' => $users['total'] ?? 0,
                    'description' => ($users['active'] ?? 0) . ' active, ' . ($users['deleted'] ?? 0) . ' deleted',
                    'color' => 'success',
                ],
                ['label' => 'Recipes', 'value' => $data['recipes'] ?? 0, 'color' => 'primary'],
                ['label' => 'Ingredients', 'value' => $data['ingredients'] ?? 0, 'color' => 'primary'],
                ['label' => 'Seasonal Produce', 'value' => $seasonalCount, 'color' => 'primary'],
                ['label' => 'Blog Posts', 'value' => $blogCount, 'color' => 'secondary'],
                ['label' => 'Educational Content', 'value' => $articleCount, 'color' => 'secondary'],
                ['label' => 'Pantry Items', 'value' => $data['pantry_items'] ?? 0, 'color' => 'warning'],
                ['label' => 'Grocery Items', 'value' => $data['grocery_items'] ?? 0, 'color' => 'warning'],
                ['label' => 'Active Subscriptions', 'value' => $data['subscriptions']['active'] ?? 0, 'color' => 'success'],
                ['label' => 'Notifications Sent', 'value' => $data['notifications'] ?? 0, 'color' => 'secondary'],
            ],

            'userChart' => [
                [
                    'name' => 'Users',
                    'values' => [$users['active'] ?? 0, $users['deleted'] ?? 0],
                    'labels' => ['Active', 'Deleted'],
                ],
            ],

            'contentChart' => [
                [
                    'name' => 'Count',
                    'values' => [
                        $data['recipes'] ?? 0,
                        $data['ingredients'] ?? 0,
                        $seasonalCount,
                        $blogCount,
                        $articleCount,
                    ],
                    'labels' => ['Recipes', 'Ingredients', 'Seasonal Produce', 'Blog Posts', 'Educational Content'],
                ],
            ],

            'activityChart' => [
                [
                    'name' => 'Count',
                    'values' => [
                        $data['pantry_items'] ?? 0,
                        $data['grocery_items'] ?? 0,
                        $data['subscriptions']['active'] ?? 0,
                        $data['notifications'] ?? 0,
                    ],
                    'labels' => ['Pantry Items', 'Grocery Items', 'Active Subscriptions', 'Notifications Sent'],
                ],
            ],
        ];
    }

    public function name(): ?string
    {
        return 'Dashboard';
    }

    public function description(): ?string
    {
        return 'Live overview of the Verdantt platform.';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.dashboard.metrics'),

            Layout::columns([
                Layout::chart('userChart', 'User Accounts')
                    ->type(Chart::TYPE_PIE)
                    ->height(280),

                Layout::chart('contentChart', 'Content Overview')
                    ->type(Chart::TYPE_BAR)
                    ->height(280),
            ]),

            Layout::chart('activityChart', 'Platform Activity')
                ->type(Chart::TYPE_BAR)
                ->height(280),
        ];
    }
}
