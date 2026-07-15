<?php

namespace App\Orchid\Screens\Cms;

use App\Orchid\Layouts\Cms\CmsPrivacyLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class PrivacyScreen extends Screen
{
    public function query(): iterable
    {
        $privacy = app(VerdanttApiClient::class)->get('/admin/cms/privacy')->json('data') ?? [];

        return [
            'privacy' => [
                'title' => $privacy['title'] ?? 'Privacy Policy',
                'content' => $privacy['content'] ?? '',
                'status' => $privacy['status'] ?? 'published',
            ],
        ];
    }

    public function name(): ?string
    {
        return 'Privacy Policy';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            CmsPrivacyLayout::class,
        ];
    }

    public function save(Request $request): void
    {
        $data = $request->input('privacy', []);

        $response = app(VerdanttApiClient::class)->put('/admin/cms/privacy', [
            'title' => $data['title'] ?? '',
            'content' => $data['content'] ?? '',
            'status' => $data['status'] ?? 'published',
        ]);

        if ($response->successful()) {
            Toast::info('Privacy Policy saved.');

            return;
        }

        Toast::error($response->json('message') ?? 'Failed to save Privacy Policy.');
    }
}
