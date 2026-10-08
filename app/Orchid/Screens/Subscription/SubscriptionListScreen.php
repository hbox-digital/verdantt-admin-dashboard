<?php

namespace App\Orchid\Screens\Subscription;

use App\Orchid\Layouts\Subscription\SubscriptionListLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SubscriptionListScreen extends Screen
{
    /**
     * Page size sent to the API. The endpoint defaults to 10 and caps at 50.
     */
    public const PER_PAGE = 10;

    /**
     * Load the users that an admin may manage from the API.
     *
     * `GET /admin/subscriptions/users` returns only non-deleted regular users
     * that do **not** carry an active store subscription object — active store
     * subscribers are never eligible for admin-granted access — ordered by
     * `created_at` descending, so the most recently registered users come first.
     * Only `id`, `first_name`, `last_name`, `email` and the subscription flags
     * are returned by that endpoint.
     *
     * Search and pagination are handled server-side (`search`, `page`, `limit`).
     */
    public function query(Request $request): iterable
    {
        $page = max((int) $request->query('page', 1), 1);
        $search = $request->query('search');

        $query = [
            'page' => $page,
            'limit' => self::PER_PAGE,
        ];

        if (filled($search)) {
            $query['search'] = $search;
        }

        $response = app(VerdanttApiClient::class)->get('/admin/subscriptions/users', $query);

        if ($response->successful()) {
            $users = $this->eligibleUsers($response->json('data.users') ?? []);
            $meta = $response->json('data.meta') ?? [];
        } else {
            Toast::error($response->json('message') ?? 'Unable to load subscriptions from the API.');

            $users = [];
            $meta = [];
        }

        return [
            'users' => new LengthAwarePaginator(
                $users,
                (int) ($meta['total'] ?? count($users)),
                (int) ($meta['limit'] ?? self::PER_PAGE),
                (int) ($meta['page'] ?? $page),
                ['path' => LengthAwarePaginator::resolveCurrentPath()],
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Manage subscriptions';
    }

    public function description(): ?string
    {
        return 'Grant or revoke premium access for the registered users.';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', [
                'placeholder' => 'Search users...',
                'debounce' => 300,
                'focus' => true,
            ]),

            SubscriptionListLayout::class,

            Layout::view('orchid.partials.subscription-select-script'),
        ];
    }

    /**
     * Enable or disable the admin-granted subscription for a single user.
     *
     * `PATCH /admin/subscriptions/users/{id}/subscription` with
     * `{"subscribed": true|false}`.
     */
    public function updateSubscription(Request $request): void
    {
        $subscribed = $request->boolean('subscribed');

        $response = app(VerdanttApiClient::class)->patch(
            '/admin/subscriptions/users/'.$request->get('id').'/subscription',
            ['subscribed' => $subscribed],
        );

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to update the subscription.');

            return;
        }

        Toast::info($subscribed ? 'Subscription enabled.' : 'Subscription disabled.');
    }

    /**
     * Keep users that already carry a subscription object out of the list.
     *
     * The API already enforces this, but the rule is part of the screen's
     * contract, so it is asserted here as well.
     *
     * @param  array<int, array<string, mixed>>  $users
     * @return array<int, array<string, mixed>>
     */
    protected function eligibleUsers(array $users): array
    {
        return array_values(array_filter(
            $users,
            static fn ($user) => is_array($user) && ! isset($user['subscription']),
        ));
    }
}
