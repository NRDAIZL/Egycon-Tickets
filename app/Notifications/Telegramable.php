<?php

namespace App\Notifications;

use App\Services\Telegram\TelegramMessage;
use Illuminate\Notifications\Notifiable;

interface Telegramable
{
    /**
     * Create telegram notification body
     *
     * @return TelegramMessage
     */
    public function toTelegram($notifiable): TelegramMessage;
}
