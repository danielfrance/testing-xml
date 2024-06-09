<?php

namespace App\Notifications;

use App\Models\Invite;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\LaravelDriver\MailerSendTrait;


class InviteMailNotification extends Notification
{
    use Queueable, MailerSendTrait;

    public Invite $invite;
    /**
     * Create a new notification instance.
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {

        $team = Team::find($this->invite->team_id);
        $inviteType = Str::headline($this->invite->type);

        $url = url("/{$team->slug}/{$this->invite->type}/{$this->invite->token}");

        $expiration = Carbon::parse($this->invite->expires_at)->setTimezone('GMT-6')->addHours(36)->format('F j, Y g:i A');
        Log::info('InviteMailNotification toMail fired for user: ' . $this->invite->email);

        // if ($this->invite->type === 'team_member') {
        //     return (new MailMessage)
        //         ->replyTo(env('MAIL_HELP_EMAIL'), env('MAIL_HELP_NAME'))
        //         ->subject("[Action Required] Set up your Ball Morse Lowe {$inviteType} profile")
        //         ->greeting("Hello {$this->invite->name},")
        //         ->line("You've been invited to join the Ball Morse Lowe BOIR Application as a team member.")
        //         ->line("Please click the button below to accept the invitation.")
        //         ->action('Accept Invite', $url)
        //         ->line("The link above will expire at {$expiration}.");
        // } else {
        //     return (new MailMessage)
        //         ->replyTo(env('MAIL_HELP_EMAIL'), env('MAIL_HELP_NAME'))
        //         ->subject("[Action Required] Set up your Ball Morse Lowe {$inviteType} profile")
        //         ->greeting("Hello {$this->invite->name},")
        //         ->line("You've been invited to create your {$inviteType} profile on the Ball Morese Lowe BOIR Application.")
        //         ->line("Please click the button below to begin the process. You will need the following information to complete your profile:")
        //         ->line('- A FinCEN ID')
        //         ->line('')
        //         ->line('OR')
        //         ->line('')
        //         ->line('- Full Legal Name')
        //         ->line('- Date of Birth')
        //         ->line('- Current Address')
        //         ->line('- Identifying Documents like a Passport or Drivers License')
        //         ->line("The link below will expire at {$expiration}. Please click the button below to begin.")
        //         ->action('Create Profile', $url);
        // }

        $to = $this->invite->email;


        $personalization = [
            new Personalization('recipient@email.com', [
                'url' => $url,
                'name' => $this->invite->name,
                'teamName' => $team->name,
                'expiration' => $expiration,
                'inviteType' => $inviteType,
                'support_email' => env('MAIL_HELP_EMAIL'),
            ])
        ];

        return $this->mailersend('k68zxl2z3o9lj905');
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
