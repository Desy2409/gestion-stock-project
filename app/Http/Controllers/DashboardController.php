<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SalePoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function salePoints()
    {
        $user = Auth::user();
        // $salePoints = SalePoint::whereIn('id', $user->sale_points)->get();
        $salePoints = SalePoint::all();
        return new JsonResponse(['datas' => ['salePoints' => $salePoints]], 200);
    }

    public function saleTotalAmountOfSalePoint($id, $startDate, $endDate)
    {
        // $salePoint=SalePoint::findOrFail($id);
        $saleTotalAmount = Sale::where('sale_point_id', $id)->whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');
        return new JsonResponse(['saleTotalAmount' => $saleTotalAmount], 200);
    }

    public function purchaseTotalAmountOfSalePoint($id, $startDate, $endDate)
    {
        $purchaseTotalAmount = Purchase::where('sale_point_id', $id)->whereBetween('purchase_date', [$startDate, $endDate])->sum('total_amount');
        return new JsonResponse(['purchaseTotalAmount' => $purchaseTotalAmount], 200);
    }

    public function countPendingOrders($id,$startDate, $endDate)
    {
        // $user = Auth::user();
        // $pendingOrders = Order::whereIn('sale_point_id', $user->sale_points)->whereBetween('order_date', [$startDate, $endDate])->where('state', 'P')->get();
        $pendingOrders = Order::where('sale_point_id',$id)->whereBetween('order_date', [$startDate, $endDate])->where('state', 'P')->get();
        return new JsonResponse(['datas' => ['pendingOrders' => $pendingOrders]], 200);
    }
}
