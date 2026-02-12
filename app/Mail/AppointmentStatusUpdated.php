<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $appointmentData;

    /**
     * Create a new message instance.
     *
     * @param  array  $appointmentData
     */
    public function __construct($appointmentData)
    {
        $this->appointmentData = $appointmentData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Appointment Status Updated')
            ->markdown('emails.appointment_status_updated')
            ->with('appointmentData', $this->appointmentData);
    }
}
