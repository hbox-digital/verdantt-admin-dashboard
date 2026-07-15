<?php

namespace App\Orchid\Layouts\Ingredient;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class IngredientLifespanLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('lifespan.unripe_days')->type('number')->title('Unripe days'),
                Input::make('lifespan.ripe_days')->type('number')->title('Ripe days'),
            ]),

            Group::make([
                Input::make('lifespan.overripe_days')->type('number')->title('Overripe days'),
                Input::make('lifespan.spoiled_days')->type('number')->title('Spoiled days'),
            ]),

            Input::make('lifespan.ideal_storage')->title('Ideal storage'),

            Input::make('lifespan.image_url')->title('Lifespan image URL')->type('url'),
        ];
    }
}
