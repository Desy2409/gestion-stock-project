<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductSale;
use App\Models\Sale;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $purchaseCoupons = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->orderBy('sale_date')->get();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $clients = Client::with('person')->get();

        return new JsonResponse([
            'datas' => ['purchaseCoupons' => $purchaseCoupons, 'clients' => $clients, 'products' => $products]
        ], 200);
    }

    public function indexFromOrder($id)
    {
        $orders = Order::with('client')->with('productOrders')->orderBy('sale_date')->get();
        $purchaseCoupons = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->orderBy('sale_date')->get();
        $idOfProducts = ProductOrder::where('order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['purchaseCoupons' => $purchaseCoupons,  'orders' => $orders, 'products' => $products]
        ], 200);
    }

    public function showProductOfOrder($id)
    {
        $idOfProducts = ProductOrder::where('order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['products' => $products]
        ], 200);
    }

    public function store(Request $request)
    {
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'client' => 'required',
                'reference' => 'required|unique:purchase_coupons',
                'sale_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:sale_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'client.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Cette vente existe déjà.",
                'sale_date.required' => "La date du bon est obligatoire.",
                'sale_date.date' => "La date de la vente est incorrecte.",
                'sale_date.date_format' => "La date de la vente doit être sous le format : AAAA-MM-JJ.",
                'sale_date.date_equals' => "La date de la vente ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date de la vente.",
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
            $purchaseCoupon = new Sale();
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->sale_date   = $request->sale_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->client_id = $request->client;
            $purchaseCoupon->save();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->purchase_coupon_id = $purchaseCoupon->id;
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
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

    public function storeFromOrder(Request $request)
    {
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'order' => 'required',
                'reference' => 'required|unique:purchase_coupons',
                'sale_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:sale_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'order.required' => "Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Cette vente existe déjà.",
                'sale_date.required' => "La date du bon est obligatoire.",
                'sale_date.date' => "La date de la vente est incorrecte.",
                'sale_date.date_format' => "La date de la vente doit être sous le format : AAAA-MM-JJ.",
                'sale_date.date_equals' => "La date de la vente ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date de la vente.",
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
            $order = Order::findOrFail($request->order);

            $purchaseCoupon = new Sale();
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->sale_date   = $request->sale_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->order_id = $order->id;
            $purchaseCoupon->client_id = $order->client->id;
            $purchaseCoupon->save();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->purchase_coupon_id = $purchaseCoupon->id;
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
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
        $purchaseCoupon = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $productSales = $purchaseCoupon ? $purchaseCoupon->productSales : null; //ProductSale::where('order_id', $purchaseCoupon->id)->get();

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['productSales' => $productSales]
        ], 200);
    }

    public function edit($id)
    {
        $purchaseCoupon = Sale::with('client')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $clients = Client::with('person')->get();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $productSales = $purchaseCoupon ? $purchaseCoupon->productSales : null;

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['clients' => $clients, 'productSales' => $productSales, 'products' => $products]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $purchaseCoupon = Sale::findOrFail($id);
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'client' => 'required',
                'reference' => 'required',
                'sale_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:sale_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'client.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'sale_date.required' => "La date de la vente est obligatoire.",
                'sale_date.date' => "La date de la vente est incorrecte.",
                'sale_date.date_format' => "La date de la vente doit être sous le format : AAAA-MM-JJ.",
                'sale_date.date_equals' => "La date de la vente ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date de la vente.",
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
            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->sale_date   = $request->sale_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->client_id = $request->client;
            $purchaseCoupon->save();

            ProductSale::where('purchase_coupon_id', $purchaseCoupon->id)->delete();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->purchase_coupon_id = $purchaseCoupon->id;
                $productSale->unity_id = $request->unities[$key];
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
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

    public function editFromOrder($id)
    {
        $orders = Order::with('client')->with('productOrders')->orderBy('sale_date')->get();
        $purchaseCoupon = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $idOfProducts = ProductOrder::where('order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->whereIn('id', $idOfProducts)->get();
        $productSales = $purchaseCoupon ? $purchaseCoupon->productSales : null;

        return new JsonResponse([
            'purchaseCoupon' => $purchaseCoupon,
            'datas' => ['productSales' => $productSales, 'orders' => $orders, 'products' => $products]
        ], 200);
    }

    public function updateFromOrder(Request $request, $id)
    {
        $purchaseCoupon = Sale::findOrFail($id);
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'order' => 'required',
                'reference' => 'required',
                'sale_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:sale_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'order.required' => "Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'sale_date.required' => "La date du bon est obligatoire.",
                'sale_date.date' => "La date de la vente est incorrecte.",
                'sale_date.date_format' => "La date de la vente doit être sous le format : AAAA-MM-JJ.",
                'sale_date.date_equals' => "La date de la vente ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date de la vente.",
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
            $order = Order::findOrFail($request->order);

            $purchaseCoupon->reference = $request->reference;
            $purchaseCoupon->sale_date   = $request->sale_date;
            $purchaseCoupon->delivery_date   = $request->delivery_date;
            $purchaseCoupon->total_amount = $request->total_amount;
            $purchaseCoupon->observation = $request->observation;
            $purchaseCoupon->order_id = $order->id;
            $purchaseCoupon->client_id = $order->client->id;
            $purchaseCoupon->save();

            ProductSale::where('purchase_coupon_id', $purchaseCoupon->id)->delete();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->purchase_coupon_id = $purchaseCoupon->id;
                $productSale->unity_id = $request->unities[$key];
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
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
        $purchaseCoupon = Sale::findOrFail($id);
        $productSales = $purchaseCoupon ? $purchaseCoupon->productSales : null;
        try {
            $purchaseCoupon->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'purchaseCoupon' => $purchaseCoupon,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
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
