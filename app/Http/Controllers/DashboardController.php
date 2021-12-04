<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\SalePoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function countClients()
    {
        $numberOfClients = count(Client::all());
        return new JsonResponse(['numberOfClients' => $numberOfClients], 200);
    }

    public function countProviders()
    {
        $numberOfProviders = count(Provider::all());
        return new JsonResponse(['numberOfProviders' => $numberOfProviders], 200);
    }

    public function countProducts()
    {
        $numberOfProducts = count(Product::all());
        return new JsonResponse(['numberOfProducts' => $numberOfProducts], 200);
    }

    public function countSalePoints()
    {
        $numberOfSalePoints = count(SalePoint::all());
        return new JsonResponse(['numberOfSalePoints' => $numberOfSalePoints], 200);
    }
}
