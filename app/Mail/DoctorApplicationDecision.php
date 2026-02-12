<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoctorApplicationDecision extends Mailable
{
    use Queueable, SerializesModels;

    public $decisionData;

    /**
     * Create a new message instance.
     *
     * @param  array  $decisionData
     */
    public function __construct($decisionData)
    {
        $this->decisionData = $decisionData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->decisionData['decision'] === 'approved'
            ? 'Doctor Application Approved'
            : 'Doctor Application Update';

        return $this->subject($subject)
            ->markdown('emails.doctor_application_decision')
            ->with('decisionData', $this->decisionData);
    }
}
