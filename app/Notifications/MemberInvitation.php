<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberInvitation extends Notification
{
    use Queueable;

    public function __construct(
        private Tenant $tenant,
        private TenantUser $membership,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('register', [
            'invitation' => $this->membership->id,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject("You've been invited to join {$this->tenant->name}")
            ->greeting("Hello!")
            ->line("You have been invited to join **{$this->tenant->name}** on " . config('app.name') . '.')
            ->action('Accept Invitation', $url)
            ->line('This invitation expires in 7 days.')
            ->line('If you did not expect this invitation, you may ignore this email.');
    }
}
