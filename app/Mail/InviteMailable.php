<?php

namespace App\Mail;

use App\Models\Invite;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\LaravelDriver\MailerSendTrait;

class InviteMailable extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    public Invite $invite;
    public Team $team;
    /**
     * Create a new message instance.
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
        $this->team = Team::find($this->invite->team_id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $env = env('APP_ENV') !== 'production' ? '[TEST]' : '';
        $inviteType = $this->invite->type;

        // subject for team_member invite is different than other invites
        $subject = $inviteType === 'team_member' ? "{$env}[Action Required] Join {$this->team->name} on BOIR App" : "{$env}[Action Required] Invitation to Ball Morse Lowe";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        try {
            $team = Team::find($this->invite->team_id);
            $inviteType = Str::headline($this->invite->type);

            $url = env("APP_URL") . "/{$team->slug}/{$this->invite->type}/{$this->invite->token}";
            $expiration = Carbon::parse($this->invite->expires_at)->setTimezone('GMT-6')->addHours(36)->format('F j, Y g:i A');
            Log::info('InviteMailNotification toMail fired for user: ' . $this->invite->email);

            $to = $this->invite->email;

            $this->mailersend(
                template_id: 'k68zxl2z3o9lj905',
                personalization: [
                    new Personalization($to, [
                        'url' => $url,
                        'name' => $this->invite->name,
                        'teamName' => $team->name,
                        'expiration' => $expiration,
                        'inviteType' => $inviteType,
                        'support_email' => env('MAIL_HELP_EMAIL'),
                        'team_member' => ($this->invite->type === 'team_member') ? true : false,
                    ])
                ]
            );

            return new Content();
        } catch (\Throwable $th) {
            Log::error('InviteMailable content error: ' . $th->getMessage());
            // throw $th;
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
