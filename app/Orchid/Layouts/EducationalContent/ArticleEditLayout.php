<?php

namespace App\Orchid\Layouts\EducationalContent;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class ArticleEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('article.title')
                ->title('Title')
                ->required(),

            Input::make('article.category')
                ->title('Category'),

            Input::make('article.imageUrl')
                ->title('Image URL')
                ->type('url'),

            TextArea::make('article.articlePreview')
                ->title('Preview text')
                ->rows(2)
                ->help('Short teaser shown on the dashboard card in the app.'),

            Quill::make('article.content')
                ->title('Content'),

            CheckBox::make('article.isActive')
                ->title('Status')
                ->placeholder('Published (shown in the mobile app)'),
        ];
    }
}
