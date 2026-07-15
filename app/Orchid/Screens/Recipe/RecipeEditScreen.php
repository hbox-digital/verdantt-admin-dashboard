<?php

namespace App\Orchid\Screens\Recipe;

use App\Orchid\Layouts\Recipe\RecipeEditLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecipeEditScreen extends Screen
{
    public ?string $recipeId = null;

    public array $recipe = [];

    public function query(?string $recipe = null): iterable
    {
        $this->recipeId = $recipe;

        $data = [];

        if ($recipe !== null) {
            $data = $this->findRecipe($recipe);

            if ($data === null) {
                throw new NotFoundHttpException('Recipe not found.');
            }
        }

        $this->recipe = $data;

        return [
            'recipe' => $data,
            'ingredientOptions' => $this->ingredientOptions()->all(),
        ];
    }

    public function name(): ?string
    {
        return $this->recipeId ? 'Edit Recipe' : 'Create Recipe';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash3')
                ->confirm('This recipe will be permanently deleted.')
                ->method('remove')
                ->canSee($this->recipeId !== null),

            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            RecipeEditLayout::class,

            Layout::view('orchid.recipe.image'),

            Layout::view('orchid.recipe.ingredients'),
        ];
    }

    public function save(Request $request, ?string $recipe = null): RedirectResponse
    {
        $client = app(VerdanttApiClient::class);
        $fields = $this->apiFields($request->input('recipe', []), $request->input('ingredients', []));
        $files = ['image' => $request->file('image')];

        $response = $recipe !== null
            ? $client->postMultipart("/admin/recipes/{$recipe}", $fields, $files, 'PUT')
            : $client->postMultipart('/admin/recipes', $fields, $files);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'The API request failed.');

            return back()->withInput();
        }

        Toast::info($recipe !== null ? 'Recipe updated.' : 'Recipe created.');

        return redirect()->route('platform.recipes');
    }

    public function remove(?string $recipe = null): RedirectResponse
    {
        $response = app(VerdanttApiClient::class)->delete("/admin/recipes/{$recipe}");

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the recipe.');

            return back();
        }

        Toast::info('Recipe deleted.');

        return redirect()->route('platform.recipes');
    }

    protected function findRecipe(string $id): ?array
    {
        $client = app(VerdanttApiClient::class);
        $page = 1;
        $limit = 100;

        do {
            $response = $client->get('/admin/recipes', ['page' => $page, 'limit' => $limit]);
            $recipes = $response->json('data.recipes') ?? [];
            $totalPages = $response->json('data.meta.totalPages') ?? 1;

            foreach ($recipes as $item) {
                if ((string) $item['id'] === (string) $id) {
                    return $item;
                }
            }

            $page++;
        } while ($page <= $totalPages);

        return null;
    }

    protected function ingredientOptions(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/ingredients')->json('data') ?? []);
    }

    protected function apiFields(array $recipe, array $ingredients): array
    {
        $ingredients = collect($ingredients)
            ->filter(fn (array $row) => filled($row['ingredient_id'] ?? null))
            ->map(fn (array $row) => [
                'ingredient_id' => (int) $row['ingredient_id'],
                'quantity' => filled($row['quantity'] ?? null) ? (float) $row['quantity'] : null,
                'quantity_label' => $row['quantity_label'] ?? null,
                'unit' => $row['unit'] ?? null,
                'prefix' => $row['prefix'] ?? null,
                'notes' => $row['notes'] ?? null,
                'is_optional' => filled($row['is_optional'] ?? null),
            ])
            ->values()
            ->all();

        return [
            'title' => $recipe['title'] ?? '',
            'description' => $recipe['description'] ?? '',
            'instructions' => $recipe['instructions'] ?? '',
            'prep_time' => $recipe['prep_time'] ?? '',
            'prep_unit' => $recipe['prep_unit'] ?? '',
            'cook_time' => $recipe['cook_time'] ?? '',
            'cook_unit' => $recipe['cook_unit'] ?? '',
            'servings' => $recipe['servings'] ?? '',
            'appliance' => $recipe['appliance'] ?? '',
            'appliance_substitute' => $recipe['appliance_substitute'] ?? '',
            'dietary_restrictions' => $recipe['dietary_restrictions'] ?? '',
            'ingredient_allergens' => $recipe['ingredient_allergens'] ?? '',
            'cookbook_title' => $recipe['cookbook_title'] ?? '',
            'keywords' => $recipe['keywords'] ?? '',
            'ingredients' => json_encode($ingredients),
        ];
    }
}
