<?php

namespace App\Mail;

use App\Models\DeliveryNote;
use App\Models\ProductDeliveryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeliveryNoteValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public DeliveryNote $deliveryNote;
    public ProductDeliveryNote $productDeliveryNotes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($deliveryNote, $productDeliveryNotes)
    {
        $this->deliveryNote = $deliveryNote;
        $this->productDeliveryNotes = $productDeliveryNotes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.delivery_note_validation')
            ->with([
                'deliveryNote' => $this->deliveryNote,
                'productDeliveryNotes' => $this->productDeliveryNotes,
            ]);
    }
}
