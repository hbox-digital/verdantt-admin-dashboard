<?php

namespace App\Orchid\Layouts\RemoteUser;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Layouts\Rows;

class RemoteUserViewLayout extends Rows
{
    /**
     * Read-only — the API has no admin endpoint to edit a user's own
     * profile fields, and account type/subscription status aren't part
     * of the user object yet (subscriptions are managed entirely through
     * the App Store / Play Store per the FSD).
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Label::make('user.name')->title('Name'),
            Label::make('user.email')->title('Email'),
            Label::make('user.phone_number')->title('Phone'),
            Label::make('user.role')->title('Account type'),
            Label::make('user.status')->title('Account status'),
            Label::make('user.joined')->title('Registration date'),
        ];
    }
}
