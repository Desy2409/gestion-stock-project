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

    public function index()
    {
        $purchaseCoupons = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->orderBy('purchase_date')->get();
        // $products = Product::with('subCategory')->orderBy('wording')->get();
        $providers = Provider::with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();

        $lastPurchaseCouponRegister = PurchaseCouponRegister::latest()->first();

        $purchaseCouponRegister = new PurchaseCouponRegister();
        if ($lastPurchaseCouponRegister) {
            $purchaseCouponRegister->code = $this->formateNPosition('BA', $lastPurchaseCouponRegister->id + 1, 8);
        } else {
            $purchaseCouponRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $purchaseCouponRegister->save();

        return new JsonResponse([
            'datas' => ['purchaseCoupons' => $purchaseCoupons, 'providers' => $providers, 'salePoints' => $salePoints]
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

    public function indexFromPurchaseOrder($id)
    {
        $purchaseOrders = PurchaseOrder::with('provider')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
        $purchaseCoupons = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->orderBy('purchase_date')->get();
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        // $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['purchaseCoupons' => $purchaseCoupons,  'purchaseOrders' => $purchaseOrders]
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
                'purchase_date' => 'required|date|date_format:d-m-Y|before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:purchase_date',
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
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : JJ/MM/AAAA.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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
            $lastPurchaseCoupon = PurchaseCoupon::latest()->first();

            $purchaseCoupon = new PurchaseCoupon();
            if ($lastPurchaseCoupon) {
                $purchaseCoupon->code = $this->formateNPosition('BA', $lastPurchaseCoupon->id + 1, 8);
            } else {
                $purchaseCoupon->code = $this->formateNPosition('BA', 1, 8);
            }
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->provider_id = $request->provider;
            $purchaseCoupon->sale_point_id = $request->sale_point;
            $purchaseCoupon->save();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_coupon_id = $purchaseCoupon->id;
                $productPurchaseCoupon->save();

                array_push($productPurchaseCoupons, $productPurchaseCoupon);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons],
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
                'purchase_date' => 'required|date|date_format:d-m-Y|before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:purchase_date',
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
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : JJ/MM/AAAA.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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

            $purchaseCoupon = new PurchaseCoupon();
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->purchase_order_id = $purchaseOrder->id;
            $purchaseCoupon->provider_id = $purchaseOrder->provider->id;
            $purchaseCoupon->sale_point_id = $purchaseOrder->sale_point;
            $purchaseCoupon->save();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_coupon_id = $purchaseCoupon->id;
                $productPurchaseCoupon->save();

                array_push($productPurchaseCoupons, $productPurchaseCoupon);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons],
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
        $purchaseCoupon = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->findOrFail($id);
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null; //ProductPurchaseCoupon::where('purchase_order_id', $purchaseCoupon->id)->get();

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons]
        ], 200);
    }

    public function edit($id)
    {
        $purchaseCoupon = PurchaseCoupon::with('provider')->with('deliveryNotes')->with('productPurchaseCoupons')->findOrFail($id);
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null;

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['providers' => $providers, 'productPurchaseCoupons' => $productPurchaseCoupons, 'products' => $products]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:d-m-Y||before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:purchase_date',
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
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : JJ/MM/AAAA.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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
            // $purchaseCoupon = new PurchaseCoupon();
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->provider_id = $request->provider;
            $purchaseCoupon->sale_point_id = $request->sale_point;
            $purchaseCoupon->save();

            ProductPurchaseCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->delete();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_coupon_id = $purchaseCoupon->id;
                $productPurchaseCoupon->unity_id = $request->unities[$key];
                $productPurchaseCoupon->save();

                array_push($productPurchaseCoupons, $productPurchaseCoupon);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons],
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
        $purchaseCoupon = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->findOrFail($id);
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null;

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons, 'purchaseOrders' => $purchaseOrders, 'products' => $products]
        ], 200);
    }

    public function updateFromPurchaseOrder(Request $request, $id)
    {
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $this->validate(
            $request,
            [
                'purchase_order' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:d-m-Y||before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:purchase_date',
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
                'purchase_date.date_format' => "La du bon d'achat doit être sous le format : JJ/MM/AAAA.",
                'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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

            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->purchase_order_id = $purchaseOrder->id;
            $purchaseCoupon->provider_id = $purchaseOrder->provider->id;
            $purchaseCoupon->sale_point_id = $purchaseOrder->sale_point;
            $purchaseCoupon->save();

            ProductPurchaseCoupon::where('purchase_coupon_id', $purchaseCoupon->id)->delete();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_coupon_id = $purchaseCoupon->id;
                $productPurchaseCoupon->unity_id = $request->unities[$key];
                $productPurchaseCoupon->save();

                array_push($productPurchaseCoupons, $productPurchaseCoupon);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons],
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
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null;
        try {
            $purchaseCoupon->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons],
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
