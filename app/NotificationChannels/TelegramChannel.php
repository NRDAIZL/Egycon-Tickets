<?php

namespace App\NotificationChannels;

use App\Exceptions\UserNotFoundException;
use App\Notifications\Telegramable;
use App\Services\Telegram\TelegramService;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Log;

/**
 * Class TelegramChannel.
 */
class TelegramChannel
{
    private $dispatcher;

    /**
     * Channel constructor.
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function send($notifiable, Telegramable $notification): ?array
    {
        // @phpstan-ignore-next-line
        $message = $notification->toTelegram($notifiable);
        try{
            $telegramService = new TelegramService($notifiable);
        }catch(UserNotFoundException $e){
            $this->dispatcher->dispatch(new NotificationFailed(
                $notifiable,
                $notification,
                'telegram'
            ));
            return null;
        }
        $response = $telegramService->send($message);
        return $response instanceof Response ? json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR) : $response;
    }
}