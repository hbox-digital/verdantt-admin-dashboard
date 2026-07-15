<?php

namespace App\Orchid\Screens\Account;

use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AccountScreen extends Screen
{
    public function query(): iterable
    {
        $user = Auth::guard('admin')->user();

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => ucfirst($user->role),
        ];
    }

    public function name(): ?string
    {
        return 'My Account';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Label::make('name')->title('Name'),
                Label::make('email')->title('Email'),
                Label::make('role')->title('Role'),
            ]),
        ];
    }
}
