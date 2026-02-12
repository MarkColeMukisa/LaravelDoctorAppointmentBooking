<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoctorApplicationReceived extends Mailable
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
        return $this->subject('Doctor Application Received')
            ->markdown('emails.doctor_application_received')
            ->with('applicationData', $this->applicationData);
    }
}
