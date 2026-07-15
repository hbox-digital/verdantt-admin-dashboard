<?php

namespace App\Orchid\Screens\ContactForm;

use App\Orchid\Concerns\PaginatesApiCollection;
use App\Orchid\Layouts\ContactForm\ContactFormListLayout;
use App\Orchid\Layouts\ContactForm\ContactFormViewLayout;
use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ContactFormListScreen extends Screen
{
    use PaginatesApiCollection;

    public function query(Request $request): iterable
    {
        $isRead = $request->query('is_read');

        return [
            'submissions' => $this->paginateApi(
                $this->fetchAllSubmissions(),
                $request,
                ['name', 'email', 'subject'],
                'created_at',
                10,
                fn (Collection $items) => $isRead === null || $isRead === ''
                    ? $items
                    : $items->filter(fn (array $item) => (bool) ($item['is_read'] ?? false) === (bool) (int) $isRead),
            ),
        ];
    }

    public function name(): ?string
    {
        return 'Contact Forms';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.partials.search-box', [
                'placeholder' => 'Search submissions...',
                'filters' => [
                    ['name' => 'is_read', 'label' => 'Read status', 'options' => ['1' => 'Read', '0' => 'Unread']],
                ],
            ]),
            ContactFormListLayout::class,

            Layout::modal('submissionModal', ContactFormViewLayout::class)
                ->title('Submission')
                ->withoutApplyButton()
                ->deferred('loadSubmissionOnOpenModal'),
        ];
    }

    public function loadSubmissionOnOpenModal(Request $request): iterable
    {
        $submission = $this->findSubmission($request->get('id')) ?? [];

        return ['submission' => $submission];
    }

    public function markAsRead(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->put('/admin/contact-forms/' . $request->get('id') . '/read');

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to mark as read.');

            return;
        }

        Toast::info('Marked as read.');
    }

    public function remove(Request $request): void
    {
        $response = app(VerdanttApiClient::class)->delete('/admin/contact-forms/' . $request->get('id'));

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to delete the submission.');

            return;
        }

        Toast::info('Submission deleted.');
    }

    protected function fetchAllSubmissions(): Collection
    {
        return collect(app(VerdanttApiClient::class)->get('/admin/contact-forms')->json('data') ?? []);
    }

    protected function findSubmission(string $id): ?array
    {
        return $this->fetchAllSubmissions()->first(fn (array $item) => (string) $item['id'] === (string) $id);
    }
}
