<?php

namespace App\Orchid\Screens\EducationalContent;

use App\Orchid\Layouts\EducationalContent\ArticleEditLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleEditScreen extends Screen
{
    public ?string $articleId = null;

    public array $article = [];

    public function query(?string $article = null): iterable
    {
        $this->articleId = $article;

        $data = [];

        if ($article !== null) {
            $data = $this->findArticle($article);

            if ($data === null) {
                throw new NotFoundHttpException('Educational content entry not found.');
            }
        }

        $this->article = $data;

        return ['article' => $data];
    }

    public function name(): ?string
    {
        return $this->articleId ? 'Edit Educational Content' : 'Add Educational Content';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash3')
                ->confirm('This educational content entry will be permanently deleted.')
                ->method('remove')
                ->canSee($this->articleId !== null),

            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ArticleEditLayout::class,
        ];
    }

    public function save(Request $request, ?string $article = null): RedirectResponse
    {
        $data = $request->input('article', []);

        $payload = [
            'title' => $data['title'] ?? '',
            'category' => $data['category'] ?? null,
            'imageUrl' => $data['imageUrl'] ?? null,
            'articlePreview' => $data['articlePreview'] ?? '',
            'content' => $data['content'] ?? '',
            'isActive' => filled($data['isActive'] ?? null),
        ];

        $client = app(VerdanttApiClient::class);
        $response = $article !== null
            ? $client->patch("/admin/articles/{$article}", $payload)
            : $client->post('/admin/articles', $payload);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'The API request failed.');

            return back()->withInput();
        }

        Toast::info($article !== null ? 'Educational content updated.' : 'Educational content created.');

        return redirect()->route('platform.educational-content');
    }

    public function remove(?string $article = null): RedirectResponse
    {
        $response = app(VerdanttApiClient::class)->delete("/admin/articles/{$article}");

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the entry.');

            return back();
        }

        Toast::info('Educational content deleted.');

        return redirect()->route('platform.educational-content');
    }

    protected function findArticle(string $id): ?array
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/articles')->json('data') ?? [])
            ->first(fn (array $item) => (string) $item['id'] === (string) $id);
    }
}
