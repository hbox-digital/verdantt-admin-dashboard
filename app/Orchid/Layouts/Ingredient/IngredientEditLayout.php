<?php

namespace App\Orchid\Layouts\Ingredient;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class IngredientEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('ingredient.name')
                ->title('Name')
                ->required()
                ->max(255),

            Input::make('ingredient.category')
                ->title('Category'),

            Input::make('ingredient.image_url')
                ->title('Image URL')
                ->type('url'),

            CheckBox::make('ingredient.is_produce')
                ->title('Is produce')
                ->placeholder('Is produce'),
        ];
    }
}
