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

    public function salePoints(Request $request)
    {
        // $user = Auth::user();
        // $salePoints = SalePoint::whereIn('id', $user->sale_points)->get();
        $salePoints = SalePoint::all();
        $salePointsWithDatas = [];
        // dd($salePoints);
        foreach ($salePoints as $key => $salePoint) {
            $datas = [
                'salePoint' => $salePoint->social_reason,
                'saleTotalAmount' => $this->saleTotalAmountOfSalePoint($salePoint->id, $request->start_date, $request->end_date),
                'purchaseTotalAmount' => $this->purchaseTotalAmountOfSalePoint($salePoint->id, $request->start_date, $request->end_date),
                'pendingOrder' => $this->countPendingOrders($salePoint->id, $request->start_date, $request->end_date)
            ];
            array_push($salePointsWithDatas, $datas);
        }
        return new JsonResponse(['datas' => ['salePointsWithDatas' => $salePointsWithDatas]], 200);
    }

    private function saleTotalAmountOfSalePoint($id, $startDate, $endDate)
    {
        $saleTotalAmount = Sale::where('sale_point_id', $id)->whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');
        return $saleTotalAmount;
    }

    private function purchaseTotalAmountOfSalePoint($id, $startDate, $endDate)
    {
        $purchaseTotalAmount = Purchase::where('sale_point_id', $id)->whereBetween('purchase_date', [$startDate, $endDate])->sum('total_amount');
        return $purchaseTotalAmount;
    }

    private function countPendingOrders($id, $startDate, $endDate)
    {
        $numberOfPendingOrder = count(Order::where('sale_point_id', $id)->whereBetween('order_date', [$startDate, $endDate])->where('state', 'P')->get());
        return $numberOfPendingOrder;
    }
}
