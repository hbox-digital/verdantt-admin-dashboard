<?php

namespace App\Orchid\Screens\SeasonalProduce;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\SeasonalProduce\SeasonalProduceEditLayout;
use App\Orchid\Layouts\SeasonalProduce\SeasonalProduceListLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SeasonalProduceListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        $season = $request->query('season');

        return [
            'produce' => $this->paginateApi(
                $this->fetchAll(),
                $request,
                ['produceName'],
                'produceName',
                10,
                fn (Collection $items) => filled($season)
                    ? $items->filter(fn (array $item) => ($item['season'] ?? null) === $season)
                    : $items,
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Seasonal Produce';
    }

    public function description(): ?string
    {
        return 'Only active items are shown in the mobile app.';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add seasonal produce')
                ->icon('bs.plus-circle')
                ->modal('produceModal')
                ->modalTitle('Add seasonal produce')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', [
                'placeholder' => 'Search seasonal produce...',
                'filters' => [
                    ['name' => 'season', 'label' => 'Season', 'options' => [
                        'SPRING' => 'Spring', 'SUMMER' => 'Summer', 'FALL' => 'Fall', 'WINTER' => 'Winter',
                    ]],
                ],
            ]),
            SeasonalProduceListLayout::class,

            Layout::modal('produceModal', SeasonalProduceEditLayout::class)
                ->applyButton('Save')
                ->deferred('loadOnOpenModal'),
        ];
    }

    public function loadOnOpenModal(Request $request): iterable
    {
        $id = $request->get('id');

        return ['produceItem' => $id ? $this->find($id) : []];
    }

    public function save(Request $request): void
    {
        $id = $request->get('id');
        $data = $request->input('produceItem', []);

        $payload = [
            'produceName' => $data['produceName'] ?? '',
            'ingredientId' => filled($data['ingredientId'] ?? null) ? (int) $data['ingredientId'] : null,
            'season' => $data['season'] ?? null,
            'month' => filled($data['month'] ?? null) ? (int) $data['month'] : null,
            'isActive' => filled($data['isActive'] ?? null),
        ];

        $client = app(VerdanttApiClient::class);
        $response = $id
            ? $client->patch("/admin/seasonal-produce/{$id}", $payload)
            : $client->post('/admin/seasonal-produce', $payload);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'The API request failed.');

            return;
        }

        Toast::info($id ? 'Seasonal produce updated.' : 'Seasonal produce created.');
    }

    public function toggleActive(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->patch(
            '/admin/seasonal-produce/' . $request->get('id'),
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
        $response = app(VerdanttApiClient::class)->delete('/admin/seasonal-produce/' . $request->get('id'));

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the entry.');

            return;
        }

        Toast::info('Seasonal produce deleted.');
    }

    protected function fetchAll(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/seasonal-produce')->json('data') ?? []);
    }

    protected function find(string $id): ?array
    {
        return $this->fetchAll()->first(fn (array $item) => (string) $item['id'] === (string) $id);
    }
}
