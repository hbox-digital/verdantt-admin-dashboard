<?php

namespace App\Orchid\Screens\Ingredient;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\Ingredient\IngredientEditLayout;
use App\Orchid\Layouts\Ingredient\IngredientLifespanLayout;
use App\Orchid\Layouts\Ingredient\IngredientListLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class IngredientListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        $isProduce = $request->query('is_produce');

        return [
            'ingredients' => $this->paginateApi(
                $this->fetchAllIngredients(),
                $request,
                ['name', 'category'],
                'name',
                10,
                fn (Collection $items) => $isProduce === null || $isProduce === ''
                    ? $items
                    : $items->filter(fn (array $item) => (bool) ($item['is_produce'] ?? false) === (bool) (int) $isProduce),
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Ingredients';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add ingredient')
                ->icon('bs.plus-circle')
                ->modal('ingredientModal')
                ->modalTitle('Add ingredient')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', [
                'placeholder' => 'Search ingredients...',
                'filters' => [
                    ['name' => 'is_produce', 'label' => 'Is produce', 'options' => ['1' => 'Yes', '0' => 'No']],
                ],
            ]),
            IngredientListLayout::class,

            Layout::modal('ingredientModal', IngredientEditLayout::class)
                ->applyButton('Save')
                ->deferred('loadIngredientOnOpenModal'),

            Layout::modal('lifespanModal', IngredientLifespanLayout::class)
                ->applyButton('Save')
                ->deferred('loadLifespanOnOpenModal'),
        ];
    }

    public function loadIngredientOnOpenModal(Request $request): iterable
    {
        $id = $request->get('id');

        return ['ingredient' => $id ? $this->findIngredient($id) : []];
    }

    public function loadLifespanOnOpenModal(Request $request): iterable
    {
        $id = $request->get('id');
        $ingredient = $id ? $this->findIngredient($id) : null;

        return ['lifespan' => $ingredient['lifespan'] ?? []];
    }

    public function save(Request $request): void
    {
        $id = $request->get('id');
        $data = $request->input('ingredient', []);

        $payload = [
            'name' => $data['name'] ?? '',
            'category' => $data['category'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'is_produce' => filled($data['is_produce'] ?? null),
        ];

        $client = app(VerdanttApiClient::class);
        $response = $id
            ? $client->put("/admin/ingredients/{$id}", $payload)
            : $client->post('/admin/ingredients', $payload);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'The API request failed.');

            return;
        }

        Toast::info($id ? 'Ingredient updated.' : 'Ingredient created.');
    }

    public function saveLifespan(Request $request): void
    {
        $id = $request->get('id');
        $data = $request->input('lifespan', []);

        $response = app(VerdanttApiClient::class)->put("/admin/ingredients/{$id}/lifespan", [
            'unripe_days' => filled($data['unripe_days'] ?? null) ? (int) $data['unripe_days'] : null,
            'ripe_days' => filled($data['ripe_days'] ?? null) ? (int) $data['ripe_days'] : null,
            'overripe_days' => filled($data['overripe_days'] ?? null) ? (int) $data['overripe_days'] : null,
            'spoiled_days' => filled($data['spoiled_days'] ?? null) ? (int) $data['spoiled_days'] : null,
            'ideal_storage' => $data['ideal_storage'] ?? null,
            'image_url' => $data['image_url'] ?? null,
        ]);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to update lifespan.');

            return;
        }

        Toast::info('Lifespan updated.');
    }

    public function remove(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->delete('/admin/ingredients/' . $request->get('id'));

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the ingredient.');

            return;
        }

        Toast::info('Ingredient deleted.');
    }

    protected function fetchAllIngredients(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/ingredients')->json('data') ?? []);
    }

    protected function findIngredient(string $id): ?array
    {
        return $this->fetchAllIngredients()->first(fn (array $item) => (string) $item['id'] === (string) $id);
    }
}
