<?php

namespace App\Http\Controllers;

use App\Http\Traits\CurrencyTrait;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SalePoint;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use NumberFormatter;

class DashboardController extends Controller
{
    use CurrencyTrait;

    public function count()
    {
        $numberOfClients = count(Client::all());
        $numberOfProviders = count(Provider::all());
        $numberOfProducts = count(Product::all());
        $numberOfSalePoints = count(SalePoint::all());
        return new JsonResponse([
            'numberOfClients' => $numberOfClients, 'numberOfProviders' => $numberOfProviders,
            'numberOfProducts' => $numberOfProducts, 'numberOfSalePoints' => $numberOfSalePoints
        ], 200);
    }

    public function salePoints(Request $request)
    {
        // $user = Auth::user();
        // $salePoints = SalePoint::whereIn('id', $user->sale_points)->get();
        $salePoints = SalePoint::all();
        $salePointsWithDatas = [];
        // dd($salePoints);
        try {
            $salePointsWithDatas = [];
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
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la recherche.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    private function saleTotalAmountOfSalePoint($id, $startDate, $endDate)
    {
        $saleTotalAmount = Sale::where('sale_point_id', $id)->whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');
        return $saleTotalAmount;
    }

    public function purchaseTotalAmountOfSalePoint($id, $startDate, $endDate)
    {
        $purchaseTotalAmount = Purchase::where('sale_point_id', $id)->whereBetween('purchase_date', [$startDate, $endDate])->sum('total_amount');
        return $purchaseTotalAmount;
    }

    private function countPendingOrders($id, $startDate, $endDate)
    {
        $numberOfPendingOrder = count(Order::where('sale_point_id', $id)->whereBetween('order_date', [$startDate, $endDate])->where('state', 'P')->get());
        return $numberOfPendingOrder;
    }
    
    public function graphicsValues(Request $request)
    {
        try {
            return new JsonResponse([
                'countOrders' => $this->countOrdersBetweenDates($request->start_date, $request->end_date),
                'countPurchaseOrders' => $this->countPurchaseOrdersBetweenDates($request->start_date, $request->end_date),
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la recherche au niveau des graphes.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    private function countOrdersBetweenDates($startDate, $endDate)
    {
        $countOrdersBetweenDates = count(Order::whereBetween('order_date', [$startDate, $endDate])->get());
        // $countOrdersBetweenDates = count(Order::where('state', 'S')->whereBetween('order_date', [$startDate, $endDate])->get());
        return $countOrdersBetweenDates;
    }

    private function countPurchaseOrdersBetweenDates($startDate, $endDate)
    {
        $countPurchaseOrdersBetweenDates = count(PurchaseOrder::whereBetween('purchase_date', [$startDate, $endDate])->get());
        // $countPurchaseOrdersBetweenDates = count(PurchaseOrder::whereDate('purchase_date','>=', $startDate)->whereDate('purchase_date','<=', $endDate)->get());
        // $countPurchaseOrdersBetweenDates = count(PurchaseOrder::where('state', 'S')->whereBetween('purchase_date', [$startDate, $endDate])->get());
        return $countPurchaseOrdersBetweenDates;
    }
}
