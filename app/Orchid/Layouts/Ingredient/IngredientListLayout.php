<?php

namespace App\Orchid\Layouts\Ingredient;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class IngredientListLayout extends Table
{
    public $target = 'ingredients';

    public function columns(): array
    {
        return [
            TD::make('image_url', '')
                ->width('50px')
                ->render(fn ($ingredient) => $ingredient['image_url']
                    ? '<img src="' . e($ingredient['image_url']) . '" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">'
                    : ''),

            TD::make('name', 'Name')
                ->sort()
                ->render(fn ($ingredient) => e($ingredient['name'] ?? '')),

            TD::make('category', 'Category')
                ->render(fn ($ingredient) => $ingredient['category'] ?? '—'),

            TD::make('is_produce', 'Produce')
                ->render(fn ($ingredient) => ! empty($ingredient['is_produce'])
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>'),

            TD::make('lifespan', 'Lifespan')
                ->render(fn ($ingredient) => ! empty($ingredient['lifespan'])
                    ? '<span class="badge bg-success">Configured</span>'
                    : '<span class="badge bg-secondary">Not set</span>'),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn ($ingredient) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('bs.pencil')
                            ->modal('ingredientModal')
                            ->modalTitle('Edit ingredient')
                            ->method('save')
                            ->asyncParameters(['id' => $ingredient['id']]),

                        ModalToggle::make('Lifespan')
                            ->icon('bs.clock')
                            ->modal('lifespanModal')
                            ->modalTitle('Lifespan — ' . $ingredient['name'])
                            ->method('saveLifespan')
                            ->asyncParameters(['id' => $ingredient['id']]),

                        Button::make('Delete')
                            ->icon('bs.trash3')
                            ->confirm('This ingredient will be permanently deleted.')
                            ->method('remove', ['id' => $ingredient['id']]),
                    ])),
        ];
    }
}
