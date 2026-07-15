<?php

namespace App\Orchid\Layouts\ContactForm;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ContactFormListLayout extends Table
{
    public $target = 'submissions';

    public function columns(): array
    {
        return [
            TD::make('is_read', '')
                ->width('40px')
                ->render(fn ($submission) => ! empty($submission['is_read'])
                    ? '<i class="icon-bs.envelope-open text-secondary"></i>'
                    : '<span class="badge bg-primary">New</span>'),

            TD::make('name', 'Name')
                ->sort()
                ->render(fn ($submission) => e($submission['name'] ?? '')),

            TD::make('email', 'Email')
                ->render(fn ($submission) => e($submission['email'] ?? '')),

            TD::make('subject', 'Subject')
                ->render(fn ($submission) => \Illuminate\Support\Str::limit($submission['subject'] ?? '', 40)),

            TD::make('created_at', 'Received')
                ->sort()
                ->render(fn ($submission) => $submission['created_at']
                    ? \Illuminate\Support\Carbon::parse($submission['created_at'])->diffForHumans()
                    : '—'),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('120px')
                ->render(fn ($submission) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('View')
                            ->icon('bs.eye')
                            ->modal('submissionModal')
                            ->modalTitle('Submission from ' . ($submission['name'] ?? ''))
                            ->asyncParameters(['id' => $submission['id']]),

                        Button::make('Mark as read')
                            ->icon('bs.envelope-open')
                            ->method('markAsRead', ['id' => $submission['id']])
                            ->canSee(empty($submission['is_read'])),

                        Button::make('Delete')
                            ->icon('bs.trash3')
                            ->confirm('This submission will be permanently deleted.')
                            ->method('remove', ['id' => $submission['id']]),
                    ])),
        ];
    }
}
