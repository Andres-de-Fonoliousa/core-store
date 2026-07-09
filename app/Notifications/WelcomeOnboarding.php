<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeOnboarding extends Notification
{
    use Queueable;

    public function __construct(
        private Tenant $tenant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . '!')
            ->greeting("Welcome {$notifiable->name}!")
            ->line("Your store **{$this->tenant->name}** is ready!")
            ->line('Here are your next steps:')
            ->line('1. Set up your store profile (logo, brand colors, currency)')
            ->line('2. Add payment methods')
            ->line('3. Create categories for your products')
            ->line('4. Add providers and products')
            ->line('5. Start selling!')
            ->action('Go to Your Dashboard', url('/admin'))
            ->line('Your 14-day free trial has started. Upgrade anytime to unlock more features.');
    }
}
