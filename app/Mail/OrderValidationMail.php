<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $productOrders;

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
