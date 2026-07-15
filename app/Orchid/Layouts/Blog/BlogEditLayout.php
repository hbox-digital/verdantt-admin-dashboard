<?php

namespace App\Orchid\Layouts\Blog;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class BlogEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('blog.title')
                ->title('Title')
                ->required(),

            TextArea::make('blog.description')
                ->title('Description')
                ->rows(3)
                ->required(),

            Input::make('blog.websiteLink')
                ->title('Website link')
                ->type('url')
                ->help('The card in the app links out to this URL — there is no in-app content field yet.')
                ->required(),

            CheckBox::make('blog.isActive')
                ->title('Status')
                ->placeholder('Active (shown in the mobile app)'),
        ];
    }
}
