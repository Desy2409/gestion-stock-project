<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferDemandProcessingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transferDemand;
    public $productsTransfersDemandsLines;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($transferDemand, $productsTransfersDemandsLines)
    {
        $this->transferDemand = $transferDemand;
        $this->productsTransfersDemandsLines = $productsTransfersDemandsLines;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.transfer_demand_processing')
            ->with([
                'transferDemand' => $this->transferDemand,
                'productsTransfersDemandsLines' => $this->productsTransfersDemandsLines,
            ]);
    }
}
