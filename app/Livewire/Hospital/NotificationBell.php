<?php

namespace App\Livewire\Hospital;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];
    public ?int $lastNotificationId = null;

    public function mount(): void
    {
        $this->refreshNotifications(false);
    }

    public function poll(): void
    {
        $this->refreshNotifications(true);
    }

    private function refreshNotifications(bool $dispatchOnNew): void
    {
        $user = auth()->user();

        if (!$user) {
            $this->unreadCount = 0;
            $this->notifications = [];
            return;
        }

        $latestNotification = Notification::where('user_id', $user->id)
            ->latest()
            ->first();

        if ($dispatchOnNew && $latestNotification) {
            $hasNew = $this->lastNotificationId === null
                || $latestNotification->id > $this->lastNotificationId;

            if ($hasNew) {
                $newCountQuery = Notification::where('user_id', $user->id);

                if ($this->lastNotificationId !== null) {
                    $newCountQuery->where('id', '>', $this->lastNotificationId);
                }

                $this->dispatch(
                    'notification-arrived',
                    title: $latestNotification->title,
                    body: $latestNotification->body,
                    count: $newCountQuery->count()
                );
            }
        }

        if ($latestNotification) {
            $this->lastNotificationId = $latestNotification->id;
        }

        $this->unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $this->notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'body' => $note->body,
                    'type' => $note->type ?? 'general',
                    'is_read' => (bool) $note->is_read,
                    'created_at' => $note->created_at?->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.hospital.notification-bell');
    }
}
