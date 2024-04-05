<?php

namespace App\Notifications;

use App\Helpers\HttpHelper;
use App\Helpers\RequestsHelper;
use App\Helpers\StringUtils;
use App\Models\Post;
use App\Services\Telegram\TelegramMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequest extends Notification implements Telegramable, ShouldQueue
{
    use Queueable;
    private $request;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Post $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['telegram'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id'=>$this->request->id,
            'price'=>$this->request->total_price,
            'payment_method' => $this->request->payment_method,
            'promo_code'=>$this->request->getPromoCode(),
            'status'=> RequestsHelper::getStatusString($this->request->status),
            'name'=>$this->request->name,
            'email' => $this->request->email,
            'phone_number'=>$this->request->phone_number,
            'created_at'=>$this->request->created_at,
            'tickets'=> implode(',', $this->request->getTicketsArray()),
            'event'=>$this->request->event,
        ];
    }

    public function toTelegram($notifiable): TelegramMessage{
        return (new TelegramMessage)
                    ->greeting('Hello, '. $notifiable->name)
                    ->subject('New request in '. $this->request->event->name)
                    ->separator()
                    ->line("Event: " . $this->request->event->name)
                    ->line("Request ID: " . $this->request->id)
                    ->line("Tickets: " . implode(', ', $this->request->getTicketsArray()))
                    ->line('Name: '.$this->request->name)
                    ->line('Email: '.$this->request->email)
                    ->line('Phone: '.$this->request->phone_number)
                    ->line('Price: '.$this->request->total_price)
                    ->line('Payment method: '.StringUtils::toTitleCase($this->request->payment_method))
                    ->line('Current Status: '. RequestsHelper::getStatusString($this->request->status))
                    ->line($this->request->getPromoCode() ? 'Promo code: '.$this->request->getPromoCode() : '')
                    ->line('Date: '.$this->request->created_at)
                    ->actionText("View requests")
                    ->actionUrl(HttpHelper::getSafeRoute('admin.requests', $this->request->event->id));

    }
}
