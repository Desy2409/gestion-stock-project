<?php

namespace App\Mail;

use App\Models\ProductSale;
use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaleValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Sale $sale;
    public ProductSale $productSales;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sale, $productSales)
    {
        $this->sale = $sale;
        $this->productSales = $productSales;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.sale_validation')
            ->with([
                'sale' => $this->sale,
                'productSales' => $this->productSales,
            ]);
    }
}
