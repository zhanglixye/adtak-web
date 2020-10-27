<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $attachment;
    protected $channel;
    protected $name;
    protected $icon;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message = null, $attachment = null)
    {
        $this->message = $message;
        $this->attachment = $attachment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $message = (new SlackMessage)
                    ->from($this->name)
                    ->image($this->icon)
                    ->to($this->channel);

        if (!is_null($this->message)) {
            $message->content($this->message);
        }

        if (!is_null($this->attachment) && is_array($this->attachment)) {
            $message->attachment(function (SlackAttachment $attachment) {
                if (isset($this->attachment['title'])) {
                    $attachment->title($this->attachment['title']);
                }
                if (isset($this->attachment['content'])) {
                    $attachment->content($this->attachment['content']);
                }
                if (isset($this->attachment['color'])) {
                    $attachment->color($this->attachment['color']);
                }
                if (isset($this->attachment['fields']) && is_array($this->attachment['fields'])) {
                    foreach ($this->attachment['fields'] as $k => $v) {
                        $attachment->field($k, $v);
                    }
                }
            });
        }

        return $message;
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
            //
        ];
    }
}
