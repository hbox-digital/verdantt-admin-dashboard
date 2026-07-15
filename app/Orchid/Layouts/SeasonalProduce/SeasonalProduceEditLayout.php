<?php

namespace App\Orchid\Layouts\SeasonalProduce;

use App\Services\VerdanttApiClient;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class SeasonalProduceEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('produceItem.produceName')
                ->title('Produce name')
                ->required(),

            Select::make('produceItem.ingredientId')
                ->title('Ingredient')
                ->options($this->ingredientOptions())
                ->required(),

            Select::make('produceItem.season')
                ->title('Season')
                ->options([
                    'SPRING' => 'Spring',
                    'SUMMER' => 'Summer',
                    'FALL' => 'Fall',
                    'WINTER' => 'Winter',
                ])
                ->required(),

            Select::make('produceItem.month')
                ->title('Month')
                ->options([
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                ])
                ->required(),

            CheckBox::make('produceItem.isActive')
                ->title('Status')
                ->placeholder('Active (shown in the mobile app)'),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function ingredientOptions(): array
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/ingredients')->json('data') ?? [])
            ->pluck('name', 'id')
            ->all();
    }
}
