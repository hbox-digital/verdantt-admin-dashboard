<?php

namespace App\Orchid\Screens\Recipe;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\Recipe\RecipeListLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class RecipeListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        return [
            'recipes' => $this->paginateApi(
                collect($this->fetchAllRecipes()),
                $request,
                ['title', 'cookbook_title', 'keywords'],
                'title',
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Recipes';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add recipe')
                ->icon('bs.plus-circle')
                ->route('platform.recipes.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', ['placeholder' => 'Search recipes...']),
            RecipeListLayout::class,
        ];
    }

    public function remove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $response = app(VerdanttApiClient::class)->delete('/admin/recipes/' . $request->get('id'));

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the recipe.');

            return back();
        }

        Toast::info('Recipe deleted.');

        return redirect()->route('platform.recipes');
    }

    protected function fetchAllRecipes(): Collection
    {
        $client = app(VerdanttApiClient::class);
        $page = 1;
        $limit = 100;
        $all = collect();

        do {
            $response = $client->get('/admin/recipes', ['page' => $page, 'limit' => $limit]);
            $recipes = $response->json('data.recipes') ?? [];
            $totalPages = $response->json('data.meta.totalPages') ?? 1;

            $all = $all->concat($recipes);
            $page++;
        } while ($page <= $totalPages);

        return $all;
    }
}
