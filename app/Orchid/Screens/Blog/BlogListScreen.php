<?php

namespace App\Orchid\Screens\Blog;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\Blog\BlogEditLayout;
use App\Orchid\Layouts\Blog\BlogListLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class BlogListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        return [
            'blogs' => $this->paginateApi(
                $this->fetchAll(),
                $request,
                ['title', 'description'],
                'title',
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Blog';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add blog')
                ->icon('bs.plus-circle')
                ->modal('blogModal')
                ->modalTitle('Add blog')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', ['placeholder' => 'Search blogs...']),
            BlogListLayout::class,

            Layout::modal('blogModal', BlogEditLayout::class)
                ->applyButton('Save')
                ->deferred('loadOnOpenModal'),
        ];
    }

    public function loadOnOpenModal(Request $request): iterable
    {
        $id = $request->get('id');

        return ['blog' => $id ? $this->find($id) : []];
    }

    public function save(Request $request): void
    {
        $id = $request->get('id');
        $data = $request->input('blog', []);

        $payload = [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'websiteLink' => $data['websiteLink'] ?? '',
            'isActive' => filled($data['isActive'] ?? null),
        ];

        $client = app(VerdanttApiClient::class);
        $response = $id
            ? $client->patch("/admin/blogs/{$id}", $payload)
            : $client->post('/admin/blogs', $payload);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'The API request failed.');

            return;
        }

        Toast::info($id ? 'Blog updated.' : 'Blog created.');
    }

    public function toggleActive(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->patch(
            '/admin/blogs/' . $request->get('id'),
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
        $response = app(VerdanttApiClient::class)->delete('/admin/blogs/' . $request->get('id'));

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the blog.');

            return;
        }

        Toast::info('Blog deleted.');
    }

    protected function fetchAll(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/blogs')->json('data') ?? []);
    }

    protected function find(string $id): ?array
    {
        return $this->fetchAll()->first(fn (array $item) => (string) $item['id'] === (string) $id);
    }
}
