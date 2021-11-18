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

class CouponController_old extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $coupons = Coupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productCoupons')->orderBy('purchase_date')->get();
        // $products = Product::with('subCategory')->orderBy('wording')->get();
        $providers = Provider::with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();

        $lastCouponRegister = CouponRegister::latest()->first();

        $couponRegister = new CouponRegister();
        if ($lastCouponRegister) {
            $couponRegister->code = $this->formateNPosition('BA', $lastCouponRegister->id + 1, 8);
        } else {
            $couponRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $couponRegister->save();

        return new JsonResponse([
            'datas' => ['coupons' => $coupons, 'providers' => $providers, 'salePoints' => $salePoints]
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

    public function indexFromPurchaseOrder($id)
    {
        $purchaseOrders = PurchaseOrder::with('provider')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
        $coupons = Coupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productCoupons')->orderBy('purchase_date')->get();
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        // $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['coupons' => $coupons,  'purchaseOrders' => $purchaseOrders]
        ], 200);
    }

    public function showProductOfPurchaseOrder($id)
    {
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['products' => $products]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required|unique:purchase_coupons',
                'purchase_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
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
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon d'achat.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $lastCoupon = Coupon::latest()->first();

            $coupon = new Coupon();
            if ($lastCoupon) {
                $coupon->code = $this->formateNPosition('BA', $lastCoupon->id + 1, 8);
            } else {
                $coupon->code = $this->formateNPosition('BA', 1, 8);
            }
            $coupon->reference = $request->reference;
            $coupon->purchase_date   = $request->purchase_date;
            $coupon->delivery_date   = $request->delivery_date;
            $coupon->total_amount = $request->total_amount;
            $coupon->observation = $request->observation;
            $coupon->provider_id = $request->provider;
            $coupon->sale_point_id = $request->sale_point;
            $coupon->save();

            $productCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productCoupon = new ProductCoupon();
                $productCoupon->quantity = $request->quantities[$key];
                $productCoupon->unit_price = $request->unit_prices[$key];
                $productCoupon->product_id = $product;
                $productCoupon->purchase_coupon_id = $coupon->id;
                $productCoupon->save();

                array_push($productCoupons, $productCoupon);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'coupon' => $coupon,
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

    public function storeFromPurchaseOrder(Request $request)
    {
        $this->validate(
            $request,
            [
                'purchase_order' => 'required',
                'reference' => 'required|unique:purchase_coupons',
                'purchase_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'purchase_order.required' => "Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon d'achat existe déjà.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon d'achat.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order);

            $coupon = new Coupon();
            $coupon->reference = $request->reference;
            $coupon->purchase_date   = $request->purchase_date;
            $coupon->delivery_date   = $request->delivery_date;
            $coupon->total_amount = $request->total_amount;
            $coupon->observation = $request->observation;
            $coupon->purchase_order_id = $purchaseOrder->id;
            $coupon->provider_id = $purchaseOrder->provider->id;
            $coupon->sale_point_id = $purchaseOrder->sale_point;
            $coupon->save();

            $productCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productCoupon = new ProductCoupon();
                $productCoupon->quantity = $request->quantities[$key];
                $productCoupon->unit_price = $request->unit_prices[$key];
                $productCoupon->product_id = $product;
                $productCoupon->purchase_coupon_id = $coupon->id;
                $productCoupon->save();

                array_push($productCoupons, $productCoupon);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'coupon' => $coupon,
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

    public function show($id)
    {
        $coupon = Coupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productCoupons')->findOrFail($id);
        $productCoupons = $coupon ? $coupon->productCoupons : null; //ProductCoupon::where('purchase_order_id', $coupon->id)->get();

        return new JsonResponse([
            'coupon' => $coupon,
            'datas' => ['productCoupons' => $productCoupons]
        ], 200);
    }

    public function edit($id)
    {
        $coupon = Coupon::with('provider')->with('deliveryNotes')->with('productCoupons')->findOrFail($id);
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $productCoupons = $coupon ? $coupon->productCoupons : null;

        return new JsonResponse([
            'coupon' => $coupon,
            'datas' => ['providers' => $providers, 'productCoupons' => $productCoupons, 'products' => $products]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Ymd||before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'sale_point.required' => "Le choix du point de vente est obligatoire.",
                'provider.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon d'achat.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            // $coupon = new Coupon();
            $coupon->reference = $request->reference;
            $coupon->purchase_date   = $request->purchase_date;
            $coupon->delivery_date   = $request->delivery_date;
            $coupon->total_amount = $request->total_amount;
            $coupon->observation = $request->observation;
            $coupon->provider_id = $request->provider;
            $coupon->sale_point_id = $request->sale_point;
            $coupon->save();

            ProductCoupon::where('purchase_coupon_id', $coupon->id)->delete();

            $productCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productCoupon = new ProductCoupon();
                $productCoupon->quantity = $request->quantities[$key];
                $productCoupon->unit_price = $request->unit_prices[$key];
                $productCoupon->product_id = $product;
                $productCoupon->purchase_coupon_id = $coupon->id;
                $productCoupon->unity_id = $request->unities[$key];
                $productCoupon->save();

                array_push($productCoupons, $productCoupon);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'coupon' => $coupon,
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

    public function editFromPurchaseOrder($id)
    {
        $purchaseOrders = PurchaseOrder::with('provider')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
        $coupon = Coupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productCoupons')->findOrFail($id);
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        $productCoupons = $coupon ? $coupon->productCoupons : null;

        return new JsonResponse([
            'coupon' => $coupon,
            'datas' => ['productCoupons' => $productCoupons, 'purchaseOrders' => $purchaseOrders, 'products' => $products]
        ], 200);
    }

    public function updateFromPurchaseOrder(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $this->validate(
            $request,
            [
                'purchase_order' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Ymd||before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'purchase_order.required' => "Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon d'achat.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order);

            $coupon->reference = $request->reference;
            $coupon->purchase_date   = $request->purchase_date;
            $coupon->delivery_date   = $request->delivery_date;
            $coupon->total_amount = $request->total_amount;
            $coupon->observation = $request->observation;
            $coupon->purchase_order_id = $purchaseOrder->id;
            $coupon->provider_id = $purchaseOrder->provider->id;
            $coupon->sale_point_id = $purchaseOrder->sale_point;
            $coupon->save();

            ProductCoupon::where('purchase_coupon_id', $coupon->id)->delete();

            $productCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productCoupon = new ProductCoupon();
                $productCoupon->quantity = $request->quantities[$key];
                $productCoupon->unit_price = $request->unit_prices[$key];
                $productCoupon->product_id = $product;
                $productCoupon->purchase_coupon_id = $coupon->id;
                $productCoupon->unity_id = $request->unities[$key];
                $productCoupon->save();

                array_push($productCoupons, $productCoupon);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'coupon' => $coupon,
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

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $productCoupons = $coupon ? $coupon->productCoupons : null;
        try {
            $coupon->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'coupon' => $coupon,
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
