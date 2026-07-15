<?php

namespace App\Orchid\Layouts\ContactForm;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Layouts\Rows;

class ContactFormViewLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Label::make('submission.name')->title('Name'),
            Label::make('submission.email')->title('Email'),
            Label::make('submission.subject')->title('Subject'),
            Label::make('submission.message')->title('Message'),
        ];
    }
}
