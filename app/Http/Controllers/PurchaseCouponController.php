<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductPurchaseCoupon;
use App\Models\ProductPurchaseOrder;
use App\Models\Provider;
use App\Models\PurchaseCoupon;
use App\Models\PurchaseCouponRegister;
use App\Models\PurchaseOrder;
use App\Models\SalePoint;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseCouponController extends Controller
{
    use UtilityTrait;

    public function index($purchaseType)
    {
        $purchaseCoupons = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->orderBy('purchase_date')->get();
        $lastPurchaseCouponRegister = PurchaseCouponRegister::latest()->first();

        $purchaseCouponRegister = new PurchaseCouponRegister();
        if ($lastPurchaseCouponRegister) {
            $purchaseCouponRegister->code = $this->formateNPosition('BA', $lastPurchaseCouponRegister->id + 1, 8);
        } else {
            $purchaseCouponRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $purchaseCouponRegister->save();

        if ($purchaseType == "Achat direct") {
            $providers = Provider::with('person')->get();
            $salePoints = SalePoint::orderBy('social_reason')->get();
            $products = Product::with('subCategory')->get();

            return new JsonResponse([
                'datas' => ['providers' => $providers, 'salePoints' => $salePoints, 'products' => $products]
            ]);
        } else {
            $purchaseOrders = PurchaseOrder::with('provider')->with('salePoint')->orderBy('code')->get();
            return new JsonResponse([
                'datas' => ['purchaseOrders' => $purchaseOrders]
            ]);
        }
    }

    public function productFromProductPurchaseOrder($id)
    {
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['products' => $products]
        ], 200);
    }

    public function showNextCode()
    {
        $lastPurchaseCouponRegister = PurchaseCouponRegister::latest()->first();
        if ($lastPurchaseCouponRegister) {
            $code = $this->formateNPosition('BA', $lastPurchaseCouponRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BA', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }


    public function store(Request $request, $purchaseType){
        if ($purchaseType=="Achat direct") {
            
        }else{
            
        }
    }
}
