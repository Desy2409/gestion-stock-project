<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\SalePoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

    public function countPendingOrders($startDate, $endDate)
    {
        $user = Auth::user();
        // $pendingOrders = Order::whereIn('sale_point_id', $user->sale_points)->whereBetween('order_date', [$startDate, $endDate])->where('state', 'P')->get();
        $pendingOrders = Order::whereBetween('order_date', [$startDate, $endDate])->where('state', 'P')->get();
        return new JsonResponse(['datas' => ['pendingOrders' => $pendingOrders]], 200);
    }
}
