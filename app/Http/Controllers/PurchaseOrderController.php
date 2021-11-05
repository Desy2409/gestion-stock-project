<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPurchaseOrder;
use App\Models\Provider;
use App\Models\PurchaseOrder;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('provider')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $unities = Unity::orderBy('wording');

        return new JsonResponse([
            'datas' => ['purchaseOrders' => $purchaseOrders, 'providers' => $providers, 'products' => $products, 'unities' => $unities]
        ], 200);
    }

    public function store(Request $request)
    {
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
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
                'purchase_date.date' => "La date du bon de commande est incorrecte.",
                'purchase_date.date_format' => "La date du bon de commande doit être sous le format : AAAA-MM-JJ.",
                'purchase_date.date_equals' => "La date du bon de commande ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
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
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->provider_id = $request->provider;
            $purchaseOrder->sale_point_id = $request->sale_point;
            $purchaseOrder->save();

            $productsPurchaseOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $request->quantities[$key];
                $productPurchaseOrder->unit_price = $request->unit_prices[$key];
                $productPurchaseOrder->product_id = $product;
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productPurchaseOrder->unity_id = $request->unities[$key];
                $productPurchaseOrder->save();

                array_push($productsPurchaseOrders, $productPurchaseOrder);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders],
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
        $purchaseOrder = PurchaseOrder::with('provider')->with('productPurchaseOrders')->findOrFail($id);
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productPurchaseOrders : null; //ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->get();

        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200);
    }

    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('provider')->with('productPurchaseOrders')->findOrFail($id);
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productsPurchaseOrders : null;

        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['providers' => $providers, 'products' => $products, 'productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        // $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Y-m-d', //|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
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
                'purchase_date.date' => "La date du bon de commande est incorrecte.",
                'purchase_date.date_format' => "La date du bon de commande doit être sous le format : AAAA-MM-JJ.",
                // 'purchase_date.date_equals' => "La date du bon de commande ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
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
            // $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->provider_id = $request->provider;
            $purchaseOrder->sale_point_id = $request->sale_point;
            $purchaseOrder->save();

            ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->delete();

            $productsPurchaseOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $request->quantities[$key];
                $productPurchaseOrder->unit_price = $request->unit_prices[$key];
                $productPurchaseOrder->product_id = $product;
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productPurchaseOrder->unity_id = $request->unities[$key];
                $productPurchaseOrder->save();

                array_push($productsPurchaseOrders, $productPurchaseOrder);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders],
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
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productsPurchaseOrders : null;
        try {
            $purchaseOrder->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders],
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
