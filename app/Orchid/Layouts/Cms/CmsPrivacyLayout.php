<?php

namespace App\Orchid\Layouts\Cms;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CmsPrivacyLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('privacy.title')->title('Title')->required(),

            Select::make('privacy.status')
                ->title('Status')
                ->options(['published' => 'Published', 'draft' => 'Draft'])
                ->required(),

            Quill::make('privacy.content')->title('Content'),
        ];
    }
}
