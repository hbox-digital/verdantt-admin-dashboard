<?php

namespace App\Orchid\Screens\Notification;

use App\Services\VerdanttApiClient;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Radio;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class BroadcastNotificationScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'notification' => [
                'audience' => 'all',
            ],
        ];
    }

    public function name(): ?string
    {
        return 'Broadcast Notification';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Send')
                ->icon('bs.send')
                ->method('send'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Radio::make('notification.audience')
                    ->title('Send to')
                    ->options([
                        'all' => 'All users',
                        'paid' => 'Premium (paid) users',
                        'unpaid' => 'Free users',
                    ])
                    ->required(),

                Input::make('notification.title')
                    ->title('Title')
                    ->required()
                    ->max(255),

                TextArea::make('notification.message')
                    ->title('Message')
                    ->rows(4)
                    ->required(),
            ]),
        ];
    }

    public function send(Request $request): void
    {
        $data = $request->input('notification', []);

        $endpoint = match ($data['audience'] ?? 'all') {
            'paid' => '/admin/notifications/broadcast/paid',
            'unpaid' => '/admin/notifications/broadcast/unpaid',
            default => '/admin/notifications/broadcast',
        };

        $response = app(VerdanttApiClient::class)->post($endpoint, [
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
        ]);

        if (! $response->successful()) {
            Toast::error($response->json('message') ?? 'Failed to send the notification.');

            return;
        }

        Toast::info('Notification broadcast sent.');
    }
}
