<?php

namespace App\Orchid\Layouts\RemoteUser;

use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class RemoteUserListLayout extends Table
{
    public $target = 'users';

    public function columns(): array
    {
        return [
            TD::make('view', '')
                ->width('50px')
                ->render(fn ($user) => ModalToggle::make('')
                    ->icon('bs.eye')
                    ->modal('userModal')
                    ->modalTitle('User details')
                    ->asyncParameters(['id' => $user['id']])),

            TD::make('first_name', 'First name')
                ->sort()
                ->render(fn ($user) => e($user['first_name'] ?? '')),

            TD::make('last_name', 'Last name')
                ->sort()
                ->render(fn ($user) => e($user['last_name'] ?? '')),

            TD::make('email', 'Email')
                ->sort()
                ->render(fn ($user) => e($user['email'] ?? '')),

            TD::make('phone_number', 'Phone')
                ->render(fn ($user) => e($user['phone_number'] ?? '—')),

            TD::make('role', 'Role')
                ->render(fn ($user) => '<span class="badge bg-secondary">' . e($user['role'] ?? '') . '</span>'),

            TD::make('is_deleted', 'Status')
                ->render(fn ($user) => ! empty($user['is_deleted'])
                    ? '<span class="badge bg-danger">Deleted</span>'
                    : '<span class="badge bg-success">Active</span>'),

            TD::make('created_at', 'Joined')
                ->sort()
                ->render(fn ($user) => $user['created_at']
                    ? \Illuminate\Support\Carbon::parse($user['created_at'])->diffForHumans()
                    : '—'),
        ];
    }
}
