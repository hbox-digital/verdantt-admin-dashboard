<?php

namespace App\Orchid\Layouts\Subscription;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SubscriptionListLayout extends Table
{
    public $target = 'users';

    public function columns(): array
    {
        return [
            TD::make('first_name', 'First name')
                ->render(fn (array $user) => e($user['first_name'] ?? '')),

            TD::make('last_name', 'Last name')
                ->render(fn (array $user) => e($user['last_name'] ?? '')),

            TD::make('email', 'Email')
                ->render(fn (array $user) => e($user['email'] ?? '')),

            TD::make('subscription', 'Subscription')
                ->align(TD::ALIGN_CENTER)
                ->width('160px')
                ->render(fn (array $user) => $this->subscriptionSelect($user)),
        ];
    }

    /**
     * Dropdown that enables/disables the admin-granted subscription.
     *
     * A plain <select> cannot carry a `formaction` the way Orchid's action
     * buttons do, so the change event is wired up in
     * `orchid.partials.subscription-select-script`, which resubmits the screen
     * form to `.../updateSubscription?id=<id>&subscribed=<0|1>`.
     *
     * @param  array<string, mixed>  $user
     */
    protected function subscriptionSelect(array $user): string
    {
        $enabled = ! empty($user['admin_subscribed']);

        return sprintf(
            '<select class="form-select form-select-sm" data-role="subscription-select" data-user-id="%d" aria-label="Subscription status">'
            .'<option value="1"%s>Enabled</option>'
            .'<option value="0"%s>Disabled</option>'
            .'</select>',
            (int) ($user['id'] ?? 0),
            $enabled ? ' selected' : '',
            $enabled ? '' : ' selected',
        );
    }
}
