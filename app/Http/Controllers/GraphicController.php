<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Repositories\PurchaseRepository;
use App\Repositories\SaleRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GraphicController extends Controller
{
    // private $purchaseRepository;
    // private $saleRepository;

    // public function __construct(PurchaseRepository $purchaseRepository, SaleRepository $saleRepository)
    // {
    //     $this->purchaseRepository = $purchaseRepository;
    //     $this->saleRepository = $saleRepository;
    // }


    public function months()
    {
        $monthsLongForm = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $monthsShortForm = ['Jan', 'Fév', 'Mars', 'Avr', 'Mai', 'Juin', 'Juill', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        return new JsonResponse([
            'datas' => ['monthsShortForm' => $monthsShortForm, 'monthsLongForm' => $monthsLongForm]
        ]);
    }

    
}
