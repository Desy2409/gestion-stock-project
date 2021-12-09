<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\ProductOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public ProductOrder $productOrders;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $productOrders)
    {
        $this->order = $order;
        $this->productOrders = $productOrders;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.order_validation')
            ->with([
                'order' => $this->order,
                'productOrders' => $this->productOrders,
            ]);
    }
}
