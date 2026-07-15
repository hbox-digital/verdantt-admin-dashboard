<?php

namespace App\Orchid\Layouts\Recipe;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class RecipeListLayout extends Table
{
    public $target = 'recipes';

    public function columns(): array
    {
        return [
            TD::make('image_url', '')
                ->width('60px')
                ->render(fn ($recipe) => $recipe['image_url']
                    ? '<img src="' . e($recipe['image_url']) . '" class="rounded" style="width:40px;height:40px;object-fit:cover;">'
                    : ''),

            TD::make('title', 'Title')
                ->sort()
                ->render(fn ($recipe) => e(\Illuminate\Support\Str::limit($recipe['title'] ?? '', 40))),

            TD::make('cookbook_title', 'Cookbook')
                ->render(fn ($recipe) => $recipe['cookbook_title'] ?? '—'),

            TD::make('servings', 'Servings')
                ->sort()
                ->render(fn ($recipe) => $recipe['servings'] ?? '—'),

            TD::make('average_rating', 'Rating')
                ->sort()
                ->render(fn ($recipe) => $recipe['average_rating'] ?? '—'),

            TD::make('reviews_count', 'Reviews')
                ->sort()
                ->render(fn ($recipe) => $recipe['reviews_count'] ?? 0),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn ($recipe) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('Edit')
                            ->icon('bs.pencil')
                            ->route('platform.recipes.edit', $recipe['id']),

                        Button::make('Delete')
                            ->icon('bs.trash3')
                            ->confirm('This recipe will be permanently deleted.')
                            ->method('remove', ['id' => $recipe['id']]),
                    ])),
        ];
    }
}
