<?php

namespace App\Orchid\Layouts\EducationalContent;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ArticleListLayout extends Table
{
    public $target = 'articles';

    public function columns(): array
    {
        return [
            TD::make('imageUrl', '')
                ->width('50px')
                ->render(fn ($article) => $article['imageUrl']
                    ? '<img src="' . e($article['imageUrl']) . '" class="rounded" style="width:36px;height:36px;object-fit:cover;">'
                    : ''),

            TD::make('title', 'Title')
                ->sort()
                ->render(fn ($article) => e($article['title'] ?? '')),

            TD::make('category', 'Category')
                ->render(fn ($article) => $article['category'] ?? '—'),

            TD::make('isActive', 'Status')
                ->render(fn ($article) => ! empty($article['isActive'])
                    ? '<span class="badge bg-success">Published</span>'
                    : '<span class="badge bg-secondary">Unpublished</span>'),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn ($article) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('Edit')
                            ->icon('bs.pencil')
                            ->route('platform.educational-content.edit', $article['id']),

                        Button::make(! empty($article['isActive']) ? 'Unpublish' : 'Publish')
                            ->icon('bs.toggle2-on')
                            ->method('toggleActive', ['id' => $article['id'], 'isActive' => ! empty($article['isActive'])]),

                        Button::make('Delete')
                            ->icon('bs.trash3')
                            ->confirm('This educational content entry will be permanently deleted.')
                            ->method('remove', ['id' => $article['id']]),
                    ])),
        ];
    }
}
