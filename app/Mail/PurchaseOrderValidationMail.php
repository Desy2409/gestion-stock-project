<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseOrder;
    public $productPurchaseOrders;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($purchaseOrder, $productPurchaseOrders)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->productPurchaseOrders = $productPurchaseOrders;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.purchase_order_validation')
            ->with([
                'purchaseOrder' => $this->purchaseOrder,
                'productPurchaseOrders' => $this->productPurchaseOrders,
            ]);
    }
}
