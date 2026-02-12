<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoctorApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $applicationData;

    /**
     * Create a new message instance.
     *
     * @param  array  $applicationData
     */
    public function __construct($applicationData)
    {
        $this->applicationData = $applicationData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Doctor Application Submitted')
            ->markdown('emails.doctor_application_submitted')
            ->with('applicationData', $this->applicationData);
    }
}
