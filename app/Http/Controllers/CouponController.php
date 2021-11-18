<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductCoupon;
use App\Models\ProductPurchaseOrder;
use App\Models\Provider;
use App\Models\Coupon;
use App\Models\CouponRegister;
use App\Models\PurchaseOrder;
use App\Models\SalePoint;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    use UtilityTrait;

    public function index($purchaseType)
    {
        $coupons = Coupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productCoupons')->orderBy('code')->orderBy('purchase_date')->get();
        $lastCouponRegister = CouponRegister::latest()->first();

        $couponRegister = new CouponRegister();
        if ($lastCouponRegister) {
            $couponRegister->code = $this->formateNPosition('BA', $lastCouponRegister->id + 1, 8);
        } else {
            $couponRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $couponRegister->save();

        if ($purchaseType == "Achat direct") {
            $providers = Provider::with('person')->get();
            $salePoints = SalePoint::orderBy('social_reason')->get();
            $products = Product::with('subCategory')->get();

            return new JsonResponse([
                'datas' => ['providers' => $providers, 'salePoints' => $salePoints, 'products' => $products, 'coupons' => $coupons]
            ]);
        } else {
            $purchaseOrders = PurchaseOrder::with('provider')->with('salePoint')->orderBy('code')->get();
            return new JsonResponse([
                'datas' => ['purchaseOrders' => $purchaseOrders, 'coupons' => $coupons]
            ]);
        }
    }

    public function datasFromProductPurchaseOrder($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $idOfProducts = ProductPurchaseOrder::where('order_id', $purchaseOrder->id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        $provider = Provider::with('person')->where('id', $purchaseOrder->provider_id)->first();
        return new JsonResponse([
            'provider' => $provider, 'datas' => ['products' => $products]
        ], 200);
    }

    public function showNextCode()
    {
        $lastCouponRegister = CouponRegister::latest()->first();
        if ($lastCouponRegister) {
            $code = $this->formateNPosition('BA', $lastCouponRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BA', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function show($id)
    {
        $purchaseCoupon = Coupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productCoupons')->findOrFail($id);
        $productCoupons = $purchaseCoupon ? $purchaseCoupon->productCoupons : null; //ProductCoupon::where('order_id', $purchaseCoupon->id)->get();

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['productCoupons' => $productCoupons]
        ], 200);
    }

    public function store(Request $request, $purchaseType)
    {
        if ($purchaseType == "Achat direct") {
            $this->validate(
                $request,
                [
                    'sale_point' => 'required',
                    'provider' => 'required',
                    'reference' => 'required|unique:coupons',
                    'purchase_date' => 'required|date|date_format:Ymd|before:today',
                    'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'products_of_purchase_coupon' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                ],
                [
                    'sale_point.required' => "Le choix du point de vente est obligatoire.",
                    'provider.required' => "Le choix du fournisseur est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_purchase_coupon.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                ]
            );

            try {
                $totalAmount = 0;

                $lastCoupon = Coupon::latest()->first();

                $purchaseCoupon = new Coupon();
                if ($lastCoupon) {
                    $purchaseCoupon->code = $this->formateNPosition('BA', $lastCoupon->id + 1, 8);
                } else {
                    $purchaseCoupon->code = $this->formateNPosition('BA', 1, 8);
                }
                $purchaseCoupon->reference = $request->reference;
                $purchaseCoupon->purchase_date   = $request->purchase_date;
                $purchaseCoupon->delivery_date   = $request->delivery_date;
                foreach ($request->unit_prices as $key => $unitPrice) {
                    $totalAmount += $unitPrice;
                }
                $purchaseCoupon->amount_gross = $totalAmount;
                $purchaseCoupon->ht_amount = $totalAmount;
                $purchaseCoupon->discount = $request->discount;
                $purchaseCoupon->amount_token = $request->amount_token;
                $purchaseCoupon->observation = $request->observation;
                $purchaseCoupon->provider_id = $request->provider;
                $purchaseCoupon->sale_point_id = $request->sale_point;
                $purchaseCoupon->save();

                $productCoupons = [];
                foreach ($request->products_of_purchase_coupon as $key => $product) {
                    $productCoupon = new ProductCoupon();
                    $productCoupon->quantity = $request->quantities[$key];
                    $productCoupon->unit_price = $request->unit_prices[$key];
                    $productCoupon->product_id = $product;
                    $productCoupon->purchase_coupon_id = $purchaseCoupon->id;
                    $productCoupon->save();

                    array_push($productCoupons, $productCoupon);
                }

                $savedProductCoupons = ProductCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->get();
                if (empty($savedProductCoupons) || sizeof($savedProductCoupons) == 0) {
                    $purchaseCoupon->delete();
                }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'purchaseCoupon' => $purchaseCoupon,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productCoupons' => $productCoupons],
                ], 200);
            } catch (Exception $e) {
                $success = false;
                $message = "Erreur survenue lors de l'enregistrement.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ], 400);
            }
        } else {
            $this->validate(
                $request,
                [
                    'order' => 'required',
                    'reference' => 'required|unique:coupons',
                    'purchase_date' => 'required|date|date_format:Ymd|before:today',
                    'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'products_of_purchase_coupon' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                ],
                [
                    'order.required' => "Le choix d'un bon de commande est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_purchase_coupon.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                ]
            );

            try {
                $purchaseOrder = PurchaseOrder::findOrFail($request->order);

                $lastCoupon = Coupon::latest()->first();

                $purchaseCoupon = new Coupon();
                if ($lastCoupon) {
                    $purchaseCoupon->code = $this->formateNPosition('BA', $lastCoupon->id + 1, 8);
                } else {
                    $purchaseCoupon->code = $this->formateNPosition('BA', 1, 8);
                }
                $purchaseCoupon->reference = $request->reference;
                $purchaseCoupon->purchase_date   = $request->purchase_date;
                $purchaseCoupon->delivery_date   = $request->delivery_date;
                $purchaseCoupon->amount_gross = $purchaseOrder->total_amount;
                $purchaseCoupon->ht_amount = $purchaseOrder->total_amount;
                $purchaseCoupon->discount = $request->discount;
                $purchaseCoupon->amount_token = $request->amount_token;
                $purchaseCoupon->observation = $request->observation;
                $purchaseCoupon->order_id = $purchaseOrder->id;
                $purchaseCoupon->provider_id = $purchaseOrder->provider->id;
                $purchaseCoupon->sale_point_id = $purchaseOrder->sale_point->id;
                $purchaseCoupon->save();

                $productCoupons = [];
                foreach ($request->products_of_purchase_coupon as $key => $product) {
                    $productCoupon = new ProductCoupon();
                    $productCoupon->quantity = $request->quantities[$key];
                    $productCoupon->unit_price = $request->unit_prices[$key];
                    $productCoupon->product_id = $product;
                    $productCoupon->purchase_coupon_id = $purchaseCoupon->id;
                    $productCoupon->save();

                    array_push($productCoupons, $productCoupon);
                }

                $savedProductCoupons = ProductCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->get();
                if (empty($savedProductCoupons) || sizeof($savedProductCoupons) == 0) {
                    $purchaseCoupon->delete();
                }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'purchaseCoupon' => $purchaseCoupon,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productCoupons' => $productCoupons],
                ], 200);
            } catch (Exception $e) {
                dd($e);
                $success = false;
                $message = "Erreur survenue lors de l'enregistrement.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ], 400);
            }
        }
    }

    public function update(Request $request, $id, $purchaseType)
    {
        $purchaseCoupon = Coupon::findOrFail($id);
        if ($purchaseType == "Achat direct") {
            $this->validate(
                $request,
                [
                    'sale_point' => 'required',
                    'provider' => 'required',
                    'reference' => 'required|unique:coupons',
                    'purchase_date' => 'required|date|date_format:Ymd|before:today',
                    'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'products_of_purchase_coupon' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                ],
                [
                    'sale_point.required' => "Le choix du point de vente est obligatoire.",
                    'provider.required' => "Le choix du fournisseur est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_purchase_coupon.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                ]
            );

            try {
                $totalAmount = 0;

                $purchaseCoupon->reference = $request->reference;
                $purchaseCoupon->purchase_date   = $request->purchase_date;
                $purchaseCoupon->delivery_date   = $request->delivery_date;
                foreach ($request->unit_prices as $key => $unitPrice) {
                    $totalAmount += $unitPrice;
                }
                $purchaseCoupon->amount_gross = $totalAmount;
                $purchaseCoupon->ht_amount = $totalAmount;
                $purchaseCoupon->discount = $request->discount;
                $purchaseCoupon->amount_token = $request->amount_token;
                $purchaseCoupon->observation = $request->observation;
                $purchaseCoupon->provider_id = $request->provider;
                $purchaseCoupon->sale_point_id = $request->sale_point;
                $purchaseCoupon->save();

                ProductCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->delete();

                $productCoupons = [];
                foreach ($request->products_of_purchase_coupon as $key => $product) {
                    $productCoupon = new ProductCoupon();
                    $productCoupon->quantity = $request->quantities[$key];
                    $productCoupon->unit_price = $request->unit_prices[$key];
                    $productCoupon->product_id = $product;
                    $productCoupon->purchase_coupon_id = $purchaseCoupon->id;
                    $productCoupon->save();

                    array_push($productCoupons, $productCoupon);
                }

                $savedProductCoupons = ProductCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->get();
                if (empty($savedProductCoupons) || sizeof($savedProductCoupons) == 0) {
                    $purchaseCoupon->delete();
                }

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'purchaseCoupon' => $purchaseCoupon,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productCoupons' => $productCoupons],
                ], 200);
            } catch (Exception $e) {
                $success = false;
                $message = "Erreur survenue lors de la modification.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ], 400);
            }
        } else {
            $this->validate(
                $request,
                [
                    'order' => 'required',
                    'reference' => 'required|unique:coupons',
                    'purchase_date' => 'required|date|date_format:Ymd|before:today',
                    'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'products_of_purchase_coupon' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                ],
                [
                    'order.required' => "Le choix d'un bon de commande est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_purchase_coupon.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                ]
            );

            try {
                $purchaseOrder = PurchaseOrder::findOrFail($request->order);

                $purchaseCoupon->reference = $request->reference;
                $purchaseCoupon->purchase_date   = $request->purchase_date;
                $purchaseCoupon->delivery_date   = $request->delivery_date;
                $purchaseCoupon->amount_gross = $purchaseOrder->total_amount;
                $purchaseCoupon->ht_amount = $purchaseOrder->total_amount;
                $purchaseCoupon->discount = $request->discount;
                $purchaseCoupon->amount_token = $request->amount_token;
                $purchaseCoupon->observation = $request->observation;
                $purchaseCoupon->order_id = $purchaseOrder->id;
                $purchaseCoupon->provider_id = $purchaseOrder->provider->id;
                $purchaseCoupon->sale_point_id = $purchaseOrder->sale_point->id;
                $purchaseCoupon->save();

                ProductCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->delete();

                $productCoupons = [];
                foreach ($request->products_of_purchase_coupon as $key => $product) {
                    $productCoupon = new ProductCoupon();
                    $productCoupon->quantity = $request->quantities[$key];
                    $productCoupon->unit_price = $request->unit_prices[$key];
                    $productCoupon->product_id = $product;
                    $productCoupon->purchase_coupon_id = $purchaseCoupon->id;
                    $productCoupon->save();

                    array_push($productCoupons, $productCoupon);
                }

                $savedProductCoupons = ProductCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->get();
                if (empty($savedProductCoupons) || sizeof($savedProductCoupons) == 0) {
                    $purchaseCoupon->delete();
                }

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'purchaseCoupon' => $purchaseCoupon,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productCoupons' => $productCoupons],
                ], 200);
            } catch (Exception $e) {
                dd($e);
                $success = false;
                $message = "Erreur survenue lors de la modification.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ], 400);
            }
        }
    }

    public function destroy($id)
    {
        $purchaseCoupon = Coupon::findOrFail($id);
        $productCoupons = $purchaseCoupon ? $purchaseCoupon->productCoupons : null;
        try {
            $purchaseCoupon->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productCoupons' => $productCoupons],
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
