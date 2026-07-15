<?php

namespace App\Orchid\Layouts\Recipe;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class RecipeEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('recipe.title')
                ->title('Title')
                ->required()
                ->max(255),

            TextArea::make('recipe.description')
                ->title('Description')
                ->rows(3),

            TextArea::make('recipe.instructions')
                ->title('Instructions')
                ->rows(6),

            Group::make([
                Input::make('recipe.prep_time')->type('number')->title('Prep time'),
                Input::make('recipe.prep_unit')->title('Prep unit')->value('minutes'),
                Input::make('recipe.cook_time')->type('number')->title('Cook time'),
                Input::make('recipe.cook_unit')->title('Cook unit')->value('minutes'),
            ]),

            Group::make([
                Input::make('recipe.servings')->type('number')->title('Servings'),
                Input::make('recipe.appliance')->title('Appliance'),
                Input::make('recipe.appliance_substitute')->title('Appliance substitute'),
            ]),

            Input::make('recipe.cookbook_title')->title('Cookbook title'),

            Input::make('recipe.dietary_restrictions')
                ->title('Dietary restrictions')
                ->help('Comma-separated, e.g. Vegetarian, Gluten-Free'),

            Input::make('recipe.ingredient_allergens')
                ->title('Allergens')
                ->help('Comma-separated, e.g. Milk, Peanuts'),

            Input::make('recipe.keywords')
                ->title('Keywords')
                ->help('Comma-separated'),
        ];
    }
}
