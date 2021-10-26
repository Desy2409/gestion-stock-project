<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductPurchaseOrder;
use App\Models\Provider;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Session;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::orderBy('purchase_date')->orderBy('order_number')->get();
        $providers = Provider::with('person')->get();
        $products = Product::orderBy('wording')->get();

        return new JsonResource([
            'datas' => ['purchaseOrders' => $purchaseOrders, 'providers' => $providers, 'products' => $products]
        ], 200 | 400);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'provider' => 'required',
                'reference' => 'required|unique:purchase_orders',
                'purchase_date' => 'required|date',
                'delivery_date' => 'required|date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'provider.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon de commande existe déjà.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "Format de date incorrect.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "Format de date incorrect.",
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
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->save();

            $productsPurchaseOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $request->quantities[$key];
                $productPurchaseOrder->unit_price = $request->unit_prices[$key];
                $productPurchaseOrder->product_id = $product;
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productPurchaseOrder->provider_id = $request->provider;
                $productPurchaseOrder->save();

                array_push($productsPurchaseOrders, $productPurchaseOrder);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders],
                'success' => $success,
                'message' => $message,
            ], 200 | 400);
        } catch (Exception $e) {
            dd($e);
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200 | 400);
        }
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productsPurchaseOrders = ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->get();

        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200 | 400);
    }

    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $providers = Provider::with('person')->get();
        $products = Product::orderBy('wording')->get();
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productsPurchaseOrders : null;

        return new JsonResource([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['providers' => $providers, 'products' => $products, 'productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200 | 400);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $this->validate(
            $request,
            [
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date',
                'delivery_date' => 'required|date',
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
                'purchase_date.date' => "Format de date incorrect.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "Format de date incorrect.",
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
            // $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->save();

            ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->delete();

            $productsPurchaseOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $request->quantities[$key];
                $productPurchaseOrder->unit_price = $request->unit_prices[$key];
                $productPurchaseOrder->product_id = $product;
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productPurchaseOrder->provider_id = $request->provider;
                $productPurchaseOrder->save();

                array_push($productsPurchaseOrders, $productPurchaseOrder);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders],
                'success' => $success,
                'message' => $message,
            ], 200 | 400);
        } catch (Exception $e) {
            dd($e);
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200 | 400);
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
                'datas' => ['productsPurchaseOrders'=>$productsPurchaseOrders],
                'success' => $success,
                'message' => $message,
            ], 200 | 400);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200 | 400);
        }
    }
}
