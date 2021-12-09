<?php

namespace App\Mail;

use App\Models\ProductPurchase;
use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Purchase $purchase;
    public ProductPurchase $productPurchases;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($purchase, $productPurchases)
    {
        $this->purchase = $purchase;
        $this->productPurchases = $productPurchases;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.purchase_validation')
            ->with([
                'purchase' => $this->purchase,
                'productPurchases' => $this->productPurchases,
            ]);
    }
}
