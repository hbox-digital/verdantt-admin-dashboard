<?php

namespace App\Orchid\Layouts\Blog;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class BlogListLayout extends Table
{
    public $target = 'blogs';

    public function columns(): array
    {
        return [
            TD::make('title', 'Title')
                ->sort()
                ->render(fn ($blog) => e($blog['title'] ?? '')),

            TD::make('description', 'Description')
                ->render(fn ($blog) => e(\Illuminate\Support\Str::limit($blog['description'] ?? '', 60))),

            TD::make('websiteLink', 'Website link')
                ->render(fn ($blog) => filled($blog['websiteLink'] ?? null)
                    ? '<a href="' . e($blog['websiteLink']) . '" target="_blank" rel="noopener">' . e(\Illuminate\Support\Str::limit($blog['websiteLink'], 30)) . '</a>'
                    : '—'),

            TD::make('isActive', 'Status')
                ->render(fn ($blog) => ! empty($blog['isActive'])
                    ? '<span class="badge bg-success">Published</span>'
                    : '<span class="badge bg-secondary">Unpublished</span>'),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn ($blog) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('bs.pencil')
                            ->modal('blogModal')
                            ->modalTitle('Edit blog')
                            ->method('save')
                            ->asyncParameters(['id' => $blog['id']]),

                        Button::make(! empty($blog['isActive']) ? 'Unpublish' : 'Publish')
                            ->icon('bs.toggle2-on')
                            ->method('toggleActive', ['id' => $blog['id'], 'isActive' => ! empty($blog['isActive'])]),

                        Button::make('Delete')
                            ->icon('bs.trash3')
                            ->confirm('This blog post will be permanently deleted.')
                            ->method('remove', ['id' => $blog['id']]),
                    ])),
        ];
    }
}
