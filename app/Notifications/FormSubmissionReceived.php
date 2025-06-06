<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FormSubmissionReceived extends Notification
{
    use Queueable;

    public $form;
    public $submission;

    public function __construct($form, $submission)
    {
        $this->form = $form;
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Form Submission Received')
            ->line('A new submission has been received for the form: ' . ($this->form->name ?? $this->form->id))
            ->line('Submission Data:')
            ->line(json_encode($this->submission->data))
            ->line('IP Address: ' . $this->submission->ip_address)
            ->line('User Agent: ' . $this->submission->user_agent)
            ->line('Referrer: ' . $this->submission->referrer);
    }
}
