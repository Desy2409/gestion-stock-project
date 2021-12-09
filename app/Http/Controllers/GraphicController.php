<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GraphicController extends Controller
{
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
