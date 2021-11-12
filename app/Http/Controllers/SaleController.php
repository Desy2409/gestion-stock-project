<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductSale;
use App\Models\Sale;
use App\Models\SaleRegister;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $sales = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->orderBy('sale_date')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $clients = Client::with('person')->get();

        $lastSaleRegister = SaleRegister::latest()->first();

        $saleRegister = new SaleRegister();
        if ($lastSaleRegister) {
            $saleRegister->code = $this->formateNPosition('VT', $lastSaleRegister->id + 1, 8);
        } else {
            $saleRegister->code = $this->formateNPosition('VT', 1, 8);
        }
        $saleRegister->save();

        return new JsonResponse([
            'datas' => ['sales' => $sales, 'clients' => $clients, 'products' => $products]
        ], 200);
    }

    public function showNextCode()
    {
        $lastSaleRegister = SaleRegister::latest()->first();
        if ($lastSaleRegister) {
            $code = $this->formateNPosition('VT', $lastSaleRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('VT', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function indexFromOrder($id)
    {
        $orders = Order::with('client')->with('productOrders')->orderBy('sale_date')->get();
        $sales = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->orderBy('sale_date')->get();
        $idOfProducts = ProductOrder::where('order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['sales' => $sales,  'orders' => $orders, 'products' => $products]
        ], 200);
    }

    public function showProductOfOrder($id)
    {
        $idOfProducts = ProductOrder::where('order_id', $id)->pluck('product_id')->toArray();
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
                'client' => 'required',
                'reference' => 'required|unique:sales',
                'sale_date' => 'required|date|date_format:d-m-Y|before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:sale_date',
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
                'sale_date.date_format' => "La date de la vente doit être sous le format : JJ/MM/AAAA.",
                'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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
            $lastSale = Sale::latest()->first();

            $sale = new Sale();
            if ($lastSale) {
                $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
            } else {
                $sale->code = $this->formateNPosition('VT', 1, 8);
            }
            $sale->reference = $request->reference;
            $sale->sale_date   = $request->sale_date;
            $sale->delivery_date   = $request->delivery_date;
            $sale->total_amount = $request->total_amount;
            $sale->observation = $request->observation;
            $sale->client_id = $request->client;
            $sale->save();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->sale_id = $sale->id;
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'sale' => $sale,
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
        $this->validate(
            $request,
            [
                'order' => 'required',
                'reference' => 'required|unique:sales',
                'sale_date' => 'required|date|date_format:d-m-Y|before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:sale_date',
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
                'sale_date.date_format' => "La date de la vente doit être sous le format : JJ/MM/AAAA.",
                'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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

            $lastSale = Sale::latest()->first();

            $sale = new Sale();
            if ($lastSale) {
                $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
            } else {
                $sale->code = $this->formateNPosition('VT', 1, 8);
            }
            $sale->reference = $request->reference;
            $sale->sale_date   = $request->sale_date;
            $sale->delivery_date   = $request->delivery_date;
            $sale->total_amount = $request->total_amount;
            $sale->observation = $request->observation;
            $sale->order_id = $order->id;
            $sale->client_id = $order->client->id;
            $sale->save();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->order_id = $order->id;
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'sale' => $sale,
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
        $sale = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $productSales = $sale ? $sale->productSales : null; //ProductSale::where('order_id', $order->id)->get();

        return new JsonResponse([
            'sale' => $sale,
            'datas' => ['productSales' => $productSales]
        ], 200);
    }

    public function edit($id)
    {
        $sale = Sale::with('client')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $clients = Client::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $productSales = $sale ? $sale->productSales : null;

        return new JsonResponse([
            'sale' => $sale,
            'datas' => ['clients' => $clients, 'productSales' => $productSales, 'products' => $products]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $this->validate(
            $request,
            [
                'client' => 'required',
                'reference' => 'required',
                'sale_date' => 'required|date|date_format:d-m-Y|before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:sale_date',
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
                'sale_date.date_format' => "La date de la vente doit être sous le format : JJ/MM/AAAA.",
                'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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
            $sale->reference = $request->reference;
            $sale->sale_date   = $request->sale_date;
            $sale->delivery_date   = $request->delivery_date;
            $sale->total_amount = $request->total_amount;
            $sale->observation = $request->observation;
            $sale->client_id = $request->client;
            $sale->save();

            ProductSale::where('sale_id', $sale->id)->delete();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->sale_id = $sale->id;
                $productSale->unity_id = $request->unities[$key];
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'sale' => $sale,
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
        $sale = Sale::with('client')->with('order')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $idOfProducts = ProductOrder::where('order_id', $id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        $productSales = $sale ? $sale->productSales : null;

        return new JsonResponse([
            'sale' => $sale,
            'datas' => ['productSales' => $productSales, 'orders' => $orders, 'products' => $products]
        ], 200);
    }

    public function updateFromOrder(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $this->validate(
            $request,
            [
                'order' => 'required',
                'reference' => 'required',
                'sale_date' => 'required|date|date_format:d-m-Y|before:today',
                'delivery_date' => 'required|date|date_format:d-m-Y|after:sale_date',
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
                'sale_date.date_format' => "La date de la vente doit être sous le format : JJ/MM/AAAA.",
                'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : JJ/MM/AAAA.",
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

            $sale->reference = $request->reference;
            $sale->sale_date   = $request->sale_date;
            $sale->delivery_date   = $request->delivery_date;
            $sale->total_amount = $request->total_amount;
            $sale->observation = $request->observation;
            $sale->order_id = $order->id;
            $sale->client_id = $order->client->id;
            $sale->save();

            ProductSale::where('order_id', $order->id)->delete();

            $productSales = [];
            foreach ($request->ordered_product as $key => $product) {
                $productSale = new ProductSale();
                $productSale->quantity = $request->quantities[$key];
                $productSale->unit_price = $request->unit_prices[$key];
                $productSale->product_id = $product;
                $productSale->order_id = $order->id;
                $productSale->unity_id = $request->unities[$key];
                $productSale->save();

                array_push($productSales, $productSale);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'sale' => $sale,
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
        $sale = Sale::findOrFail($id);
        $productSales = $sale ? $sale->productSales : null;
        try {
            $sale->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'sale' => $sale,
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
