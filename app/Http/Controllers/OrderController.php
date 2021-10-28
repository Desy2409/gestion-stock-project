<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('order_date')->get();
        $clients = Client::with('person')->get();
        $products = Product::orderBy('wording')->get();

        return new JsonResponse([
            'datas' => ['orders' => $orders, 'clients' => $clients, 'products' => $products]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'sale_point'=>'required',
                'client' => 'required',
                'reference' => 'required|unique:orders',
                'purchase_date' => 'required|date',
                'delivery_date' => 'required|date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'sale_point.required'=>"Le choix du point de vente est obligatoire.",
                'client.required' => "Le choix du client est obligatoire.",
                'reference.required' => "La référence de la commande est obligatoire.",
                'reference.unique' => "Ce bon de commande existe déjà.",
                'purchase_date.required' => "La date de la commande est obligatoire.",
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
            $order = new Order();
            $order->reference = $request->reference;
            $order->purchase_date   = $request->purchase_date;
            $order->delivery_date   = $request->delivery_date;
            $order->total_amount = $request->total_amount;
            $order->observation = $request->observation;
            $order->client_id = $request->client;
            $order->save();

            $productsOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productOrder = new ProductOrder();
                $productOrder->quantity = $request->quantities[$key];
                $productOrder->unit_price = $request->unit_prices[$key];
                $productOrder->product_id = $product;
                $productOrder->order_id = $order->id;
                $productOrder->save();

                array_push($productsOrders, $productOrder);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
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
        $order = Order::findOrFail($id);
        $productsOrders = $order ? $order->productOrders : null; //ProductOrder::where('order_id', $order->id)->get();

        return new JsonResponse([
            'order' => $order,
            'datas' => ['productsOrders' => $productsOrders]
        ], 200);
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $clients = Client::with('person')->get();
        $products = Product::orderBy('wording')->get();
        $productsOrders = $order ? $order->productsOrders : null;

        return new JsonResponse([
            'order' => $order,
            'datas' => ['clients' => $clients, 'products' => $products, 'productsOrders' => $productsOrders]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $this->validate(
            $request,
            [
                'sale_point'=>'required',
                'client' => 'required',
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
                'sale_point.required'=>"Le choix du point de vente est obligatoire.",
                'client.required' => "Le choix du client est obligatoire.",
                'reference.required' => "La référence de la commande est obligatoire.",
                'purchase_date.required' => "La date de la commande est obligatoire.",
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
            // $order = new Order();
            $order->reference = $request->reference;
            $order->purchase_date   = $request->purchase_date;
            $order->delivery_date   = $request->delivery_date;
            $order->total_amount = $request->total_amount;
            $order->observation = $request->observation;
            $order->client_id = $request->client;
            $order->save();

            ProductOrder::where('order_id', $order->id)->delete();

            $productsOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productOrder = new ProductOrder();
                $productOrder->quantity = $request->quantities[$key];
                $productOrder->unit_price = $request->unit_prices[$key];
                $productOrder->product_id = $product;
                $productOrder->order_id = $order->id;
                // $productOrder->client_id = $request->client;
                $productOrder->save();

                array_push($productsOrders, $productOrder);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
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
        $order = Order::findOrFail($id);
        $productsOrders = $order ? $order->productsOrders : null;
        try {
            $order->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
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
