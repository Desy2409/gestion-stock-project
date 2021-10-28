<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPurchaseCoupon;
use App\Models\ProductPurchaseOrder;
use App\Models\Provider;
use App\Models\PurchaseCoupon;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseCouponController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::orderBy('purchase_date')->get();
        $purchaseCoupons = PurchaseCoupon::orderBy('purchase_date')->get();
        $providers = Provider::with('person')->get();

        return new JsonResponse([
            'datas' => ['purchaseCoupons' => $purchaseCoupons, 'providers' => $providers, 'purchaseOrders' => $purchaseOrders]
        ], 200);
    }

    public function showProductOfPurchaseOrder($id)
    {
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['products' => $products]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'provider' => 'required',
                'reference' => 'required|unique:purchase_coupons',
                'purchase_date' => 'required|date|date_format:Y-m-d',
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'provider.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon d'achat existe déjà.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
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
            $purchaseCoupon = new PurchaseCoupon();
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->provider_id = $request->provider;
            $purchaseCoupon->save();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_order_id = $purchaseCoupon->id;
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
                'purchase_order'=>'required',
                'reference' => 'required|unique:purchase_coupons',
                'purchase_date' => 'required|date|date_format:Y-m-d',
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'purchase_order.required'=>"Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon d'achat existe déjà.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
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
            $purchaseCoupon->save();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_order_id = $purchaseCoupon->id;
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
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null; //ProductPurchaseCoupon::where('purchase_order_id', $purchaseCoupon->id)->get();

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons]
        ], 200);
    }

    public function edit($id)
    {
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $providers = Provider::with('person')->get();
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null;

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['providers' => $providers, 'productPurchaseCoupons' => $productPurchaseCoupons]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $this->validate(
            $request,
            [
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Y-m-d',
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'provider.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
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
            // $purchaseCoupon = new PurchaseCoupon();
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->provider_id = $request->provider;
            $purchaseCoupon->save();

            ProductPurchaseCoupon::where('purchase_order_id', $purchaseCoupon->id)->delete();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_order_id = $purchaseCoupon->id;
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
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        // $providers = Provider::with('person')->get();
        $purchaseOrder = $purchaseCoupon ? $purchaseCoupon->purchaseOrder : null;
        $productPurchaseCoupons = $purchaseCoupon ? $purchaseCoupon->productPurchaseCoupons : null;

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['productPurchaseCoupons' => $productPurchaseCoupons]
        ], 200);
    }

    public function updateFromPurchaseOrder(Request $request, $id)
    {
        $purchaseCoupon = PurchaseCoupon::findOrFail($id);
        $this->validate(
            $request,
            [
                'purchase_order'=>'required',
                'reference' => 'required|unique:purchase_coupons',
                'purchase_date' => 'required|date|date_format:Y-m-d',
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'purchase_order.required'=>"Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon d'achat existe déjà.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
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

            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->purchase_date   = $request->purchase_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->purchase_order_id = $purchaseOrder->id;
            $purchaseCoupon->provider_id = $purchaseOrder->provider->id;
            $purchaseCoupon->save();

            ProductPurchaseCoupon::where('purchase_order_id', $purchaseCoupon->id)->delete();

            $productPurchaseCoupons = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseCoupon = new ProductPurchaseCoupon();
                $productPurchaseCoupon->quantity = $request->quantities[$key];
                $productPurchaseCoupon->unit_price = $request->unit_prices[$key];
                $productPurchaseCoupon->product_id = $product;
                $productPurchaseCoupon->purchase_order_id = $purchaseCoupon->id;
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
