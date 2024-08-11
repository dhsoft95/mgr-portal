<?php

namespace App\Mail;

use App\Models\EscalatedCase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EscalationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public EscalatedCase $escalatedCase;

    public function __construct(EscalatedCase $escalatedCase)
    {
        $this->escalatedCase = $escalatedCase;
    }

    public function build()
    {
        return $this->subject('New Escalated Case: #' . $this->escalatedCase->id)
            ->view('emails.escalation-notification');
    }
}
