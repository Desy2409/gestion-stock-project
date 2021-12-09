<?php

namespace App\Mail;

use App\Models\ClientDeliveryNote;
use App\Models\ProductClientDeliveryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientDeliveryNoteValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ClientDeliveryNote $clientDeliveryNote;
    public ProductClientDeliveryNote $productClientDeliveryNotes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($clientDeliveryNote, $productClientDeliveryNotes)
    {
        $this->clientDeliveryNote = $clientDeliveryNote;
        $this->productClientDeliveryNotes = $productClientDeliveryNotes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.client_delivery_note_validation')
            ->with([
                'clientDeliveryNote' => $this->clientDeliveryNote,
                'productClientDeliveryNotes' => $this->productClientDeliveryNotes,
            ]);
    }
}
