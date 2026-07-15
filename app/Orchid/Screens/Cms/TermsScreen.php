<?php

namespace App\Orchid\Screens\Cms;

use App\Orchid\Layouts\Cms\CmsTermsLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class TermsScreen extends Screen
{
    public function query(): iterable
    {
        $terms = app(VerdanttApiClient::class)->get('/admin/cms/terms')->json('data') ?? [];

        return [
            'terms' => [
                'title' => $terms['title'] ?? 'Terms of Use',
                'content' => $terms['content'] ?? '',
                'status' => $terms['status'] ?? 'published',
            ],
        ];
    }

    public function name(): ?string
    {
        return 'Terms of Use';
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
            CmsTermsLayout::class,
        ];
    }

    public function save(Request $request): void
    {
        $data = $request->input('terms', []);

        $response = app(VerdanttApiClient::class)->put('/admin/cms/terms', [
            'title' => $data['title'] ?? '',
            'content' => $data['content'] ?? '',
            'status' => $data['status'] ?? 'published',
        ]);

        if ($response->successful()) {
            Toast::info('Terms of Use saved.');

            return;
        }

        Toast::error($response->json('message') ?? 'Failed to save Terms of Use.');
    }
}
