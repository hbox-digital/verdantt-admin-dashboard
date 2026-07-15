<?php

namespace App\Orchid\Screens\EducationalContent;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\EducationalContent\ArticleListLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ArticleListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        return [
            'articles' => $this->paginateApi(
                $this->fetchAll(),
                $request,
                ['title', 'category'],
                'title',
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Educational Content';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add content')
                ->icon('bs.plus-circle')
                ->route('platform.educational-content.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', ['placeholder' => 'Search educational content...']),
            ArticleListLayout::class,
        ];
    }

    public function toggleActive(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->patch(
            '/admin/articles/' . $request->get('id'),
            ['isActive' => ! $request->boolean('isActive')],
        );

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to update status.');

            return;
        }

        Toast::info('Status updated.');
    }

    public function remove(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->delete('/admin/articles/' . $request->get('id'));

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the entry.');

            return;
        }

        Toast::info('Educational content deleted.');
    }

    protected function fetchAll(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/articles')->json('data') ?? []);
    }
}
