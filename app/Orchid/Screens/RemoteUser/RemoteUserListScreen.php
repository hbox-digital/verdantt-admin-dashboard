<?php

namespace App\Orchid\Screens\RemoteUser;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\RemoteUser\RemoteUserListLayout;
use App\Orchid\Layouts\RemoteUser\RemoteUserViewLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class RemoteUserListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        $isDeleted = $request->query('is_deleted');
        $role = $request->query('role');

        return [
            'users' => $this->paginateApi(
                $this->fetchAllUsers(),
                $request,
                ['first_name', 'last_name', 'email'],
                'created_at',
                10,
                function (Collection $items) use ($isDeleted, $role) {
                    if ($isDeleted !== null && $isDeleted !== '') {
                        $items = $items->filter(fn (array $item) => (bool) ($item['is_deleted'] ?? false) === (bool) (int) $isDeleted);
                    }

                    if (filled($role)) {
                        $items = $items->filter(fn (array $item) => ($item['role'] ?? null) === $role);
                    }

                    return $items;
                },
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Users';
    }

    public function description(): ?string
    {
        return 'All registered app users, sourced live from the Verdantt API.';
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
                'filters' => [
                    ['name' => 'is_deleted', 'label' => 'Account status', 'options' => ['1' => 'Deleted', '0' => 'Active']],
                    ['name' => 'role', 'label' => 'Role', 'options' => ['user' => 'User', 'admin' => 'Admin', 'super_admin' => 'Super Admin']],
                ],
            ]),
            RemoteUserListLayout::class,

            Layout::modal('userModal', RemoteUserViewLayout::class)
                ->title('User details')
                ->withoutApplyButton()
                ->deferred('loadUserOnOpenModal'),
        ];
    }

    public function loadUserOnOpenModal(Request $request): iterable
    {
        $user = $this->findUser($request->get('id'));

        if ($user === null) {
            return ['user' => []];
        }

        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: '—';

        return [
            'user' => [
                'name' => $name,
                'email' => $user['email'] ?? '—',
                'phone_number' => $user['phone_number'] ?? '—',
                'role' => $user['role'] ?? '—',
                'status' => ! empty($user['is_deleted']) ? 'Deleted' : 'Active',
                'joined' => $user['created_at']
                    ? \Illuminate\Support\Carbon::parse($user['created_at'])->format('M j, Y')
                    : '—',
            ],
        ];
    }

    protected function findUser(?string $id): ?array
    {
        return $this->fetchAllUsers()->first(fn (array $item) => (string) ($item['id'] ?? '') === (string) $id);
    }

    protected function fetchAllUsers(): Collection
    {
        $client = app(VerdanttApiClient::class);
        $page = 1;
        $limit = 100;
        $all = collect();

        do {
            $response = $client->get('/admin/users', ['page' => $page, 'limit' => $limit]);
            $users = $response->json('data.users') ?? [];
            $totalPages = $response->json('data.meta.totalPages') ?? 1;

            $all = $all->concat($users);
            $page++;
        } while ($page <= $totalPages);

        return $all;
    }
}
