<?php

namespace App\Orchid\Layouts\SeasonalProduce;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SeasonalProduceListLayout extends Table
{
    public $target = 'produce';

    protected static array $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];

    public function columns(): array
    {
        return [
            TD::make('produceName', 'Produce name')
                ->sort()
                ->render(fn ($item) => e($item['produceName'] ?? '')),

            TD::make('season', 'Season')
                ->render(fn ($item) => '<span class="badge bg-secondary">' . e(ucfirst(strtolower($item['season'] ?? ''))) . '</span>'),

            TD::make('month', 'Month')
                ->render(fn ($item) => self::$months[(int) ($item['month'] ?? 0)] ?? '—'),

            TD::make('isActive', 'Status')
                ->render(fn ($item) => ! empty($item['isActive'])
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>'),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn ($item) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('bs.pencil')
                            ->modal('produceModal')
                            ->modalTitle('Edit seasonal produce')
                            ->method('save')
                            ->asyncParameters(['id' => $item['id']]),

                        Button::make(! empty($item['isActive']) ? 'Deactivate' : 'Activate')
                            ->icon('bs.toggle2-on')
                            ->method('toggleActive', ['id' => $item['id'], 'isActive' => ! empty($item['isActive'])]),

                        Button::make('Delete')
                            ->icon('bs.trash3')
                            ->confirm('This seasonal produce entry will be permanently deleted.')
                            ->method('remove', ['id' => $item['id']]),
                    ])),
        ];
    }
}
