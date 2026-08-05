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

            $data = $this->normalizeRecipeData($data);
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
        $fields = $this->apiFields($request->input('recipe', []));
        $ingredients = $this->apiIngredients($request->input('ingredients', []));
        $image = $request->file('image');

        if ($image) {
            $multipartFields = $fields + ['ingredients' => json_encode($ingredients)];

            $response = $recipe !== null
                ? $client->postMultipart("/admin/recipes/{$recipe}", $multipartFields, ['image' => $image], 'PUT')
                : $client->postMultipart('/admin/recipes', $multipartFields, ['image' => $image]);
        } else {
            $jsonFields = $fields + ['ingredients' => $ingredients];

            $response = $recipe !== null
                ? $client->put("/admin/recipes/{$recipe}", $jsonFields)
                : $client->post('/admin/recipes', $jsonFields);
        }

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

    protected function normalizeRecipeData(array $data): array
    {
        foreach (['appliance', 'appliance_substitute', 'dietary_restrictions', 'ingredient_allergens', 'keywords'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->stringifyListField($data[$field]);
            }
        }

        return $data;
    }

    protected function stringifyListField(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => is_array($item) ? ($item['name'] ?? json_encode($item)) : $item)
                ->implode(', ');
        }

        return (string) ($value ?? '');
    }

    protected function ingredientOptions(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/ingredients')->json('data') ?? []);
    }

    protected function apiFields(array $recipe): array
    {
        return [
            'title' => $recipe['title'] ?? '',
            // Description is hidden from the form; keep whatever the recipe already had.
            'description' => $this->recipe['description'] ?? '',
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
        ];
    }

    protected function apiIngredients(array $ingredients): array
    {
        return collect($ingredients)
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
    }
}
