<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductPurchaseOrder;
use App\Models\ProductSale;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SalePoint;
use App\Models\SaleRegister;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index($saleType)
    {
        $sales = Sale::with('client')->with('purchaseOrder')->with('deliveryNotes')->with('productSales')->orderBy('code')->orderBy('sale_date')->get();
        $lastSaleRegister = SaleRegister::latest()->first();

        $saleRegister = new SaleRegister();
        if ($lastSaleRegister) {
            $saleRegister->code = $this->formateNPosition('BA', $lastSaleRegister->id + 1, 8);
        } else {
            $saleRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $saleRegister->save();

        if ($saleType == "Vente directe") {
            $clients = Client::with('person')->get();
            $salePoints = SalePoint::orderBy('social_reason')->get();
            $products = Product::with('subCategory')->get();

            return new JsonResponse([
                'datas' => ['clients' => $clients, 'salePoints' => $salePoints, 'products' => $products, 'sales' => $sales]
            ]);
        } else {
            $purchaseOrders = PurchaseOrder::with('client')->with('salePoint')->orderBy('code')->get();
            return new JsonResponse([
                'datas' => ['purchaseOrders' => $purchaseOrders, 'sales' => $sales]
            ]);
        }
    }

    public function productFromPurchaseOrder($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $order->id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['products' => $products]
        ], 200);
    }

    public function showNextCode()
    {
        $lastSaleRegister = SaleRegister::latest()->first();
        if ($lastSaleRegister) {
            $code = $this->formateNPosition('BA', $lastSaleRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BA', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function show($id)
    {
        $sale = Sale::with('client')->with('purchaseOrder')->with('deliveryNotes')->with('productSales')->findOrFail($id);
        $productSales = $sale ? $sale->productSales : null; //ProductPurchase::where('order_id', $sale->id)->get();

        return new JsonResponse([
            'sale' => $sale,
            'datas' => ['productSales' => $productSales]
        ], 200);
    }

    public function store(Request $request, $saleType)
    {
        if ($saleType == "Vente directe") {
            $this->validate(
                $request,
                [
                    'reference' => 'required|unique:sales',
                    'sale_date' => 'required|date|before:today', //|date_format:Ymd
                    'observation' => 'max:255',
                    'products_of_sale' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                    'unities' => 'required',
                ],
                [
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_sale.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );
            if (sizeof($request->products_of_sale) != sizeof($request->quantities) || sizeof($request->products_of_sale) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
                $success = false;
                $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ]);
            }

            try {
                $totalAmount = 0;

                $lastSale = Sale::latest()->first();

                $sale = new Sale();
                if ($lastSale) {
                    $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                } else {
                    $sale->code = $this->formateNPosition('VT', 1, 8);
                }
                $sale->reference = $request->reference;
                $sale->sale_date   = $request->sale_date;
                foreach ($request->unit_prices as $key => $unitPrice) {
                    $totalAmount += $unitPrice;
                }
                $sale->amount_gross = $totalAmount;
                $sale->ht_amount = $totalAmount;
                $sale->discount = $request->discount;
                $sale->amount_token = $request->amount_token;
                $sale->observation = $request->observation;
                $sale->client_id = $request->client;
                $sale->save();

                $productSales = [];
                foreach ($request->products_of_sale as $key => $product) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $request->quantities[$key];
                    $productSale->unit_price = $request->unit_prices[$key];
                    $productSale->unity_id = $request->unities[$key];
                    $productSale->product_id = $product;
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    array_push($productSales, $productSale);
                }

                $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                    $sale->delete();
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
                // dd($e);
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
                    'purchase_order' => 'required',
                    'reference' => 'required|unique:sales',
                    'sale_date' => 'required|date|before:today',//|date_format:Ymd
                    'observation' => 'max:255',
                    'products_of_sale' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                    'unities' => 'required',
                ],
                [
                    'purchase_order.required' => "Le choix d'un bon de commande est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_sale.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            if (sizeof($request->products_of_sale) != sizeof($request->quantities) || sizeof($request->products_of_sale) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
                $success = false;
                $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ]);
            }

            try {
                $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order);

                $lastSale = Sale::latest()->first();

                $sale = new Sale();
                if ($lastSale) {
                    $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                } else {
                    $sale->code = $this->formateNPosition('VT', 1, 8);
                }
                $sale->reference = $request->reference;
                $sale->sale_date   = $request->sale_date;
                // $sale->delivery_date   = $request->delivery_date;
                $sale->amount_gross = $purchaseOrder->total_amount;
                $sale->ht_amount = $purchaseOrder->total_amount;
                $sale->discount = $request->discount;
                $sale->amount_token = $request->amount_token;
                $sale->observation = $request->observation;
                $sale->order_id = $purchaseOrder->id;
                $sale->client_id = $purchaseOrder->client->id;
                $sale->sale_point_id = $purchaseOrder->salePoint->id;
                $sale->save();

                $productSales = [];
                foreach ($request->products_of_sale as $key => $product) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $request->quantities[$key];
                    $productSale->unit_price = $request->unit_prices[$key];
                    $productSale->unity_id = $request->unities[$key];
                    $productSale->product_id = $product;
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    array_push($productSales, $productSale);
                }

                $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                    $sale->delete();
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
    }




    public function update(Request $request, $id, $saleType)
    {
        $sale = Sale::findOrFail($id);
        if ($saleType == "Vente directe") {
            $this->validate(
                $request,
                [
                    'reference' => 'required|unique:sales',
                    'sale_date' => 'required|date|before:today', //|date_format:Ymd
                    'observation' => 'max:255',
                    'products_of_sale' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                    'unities' => 'required',
                ],
                [
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_sale.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            if (sizeof($request->products_of_sale) != sizeof($request->quantities) || sizeof($request->products_of_sale) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
                $success = false;
                $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ]);
            }

            try {
                $totalAmount = 0;

                $sale->reference = $request->reference;
                $sale->sale_date   = $request->sale_date;
                foreach ($request->unit_prices as $key => $unitPrice) {
                    $totalAmount += $unitPrice;
                }
                $sale->amount_gross = $totalAmount;
                $sale->ht_amount = $totalAmount;
                $sale->discount = $request->discount;
                $sale->amount_token = $request->amount_token;
                $sale->observation = $request->observation;
                $sale->client_id = $request->client;
                $sale->save();

                ProductSale::where('sale_id', $sale->id)->delete();

                $productSales = [];
                foreach ($request->products_of_sale as $key => $product) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $request->quantities[$key];
                    $productSale->unit_price = $request->unit_prices[$key];
                    $productSale->unity_id = $request->unities[$key];
                    $productSale->product_id = $product;
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    array_push($productSales, $productSale);
                }

                $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                    $sale->delete();
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
                    'purchase_order' => 'required',
                    'reference' => 'required|unique:sales',
                    'sale_date' => 'required|date|before:today', //|date_format:Ymd
                    'observation' => 'max:255',
                    'products_of_sale' => 'required',
                    'quantities' => 'required|min:0',
                    'unit_prices' => 'required|min:0',
                    'unities' => 'required',
                ],
                [
                    'purchase_order.required'=>"Le choix d'un bon de commande est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'products_of_sale.required' => "Vous devez ajouter au moins un produit au panier.",
                    'quantities.required' => "Les quantités sont obligatoires.",
                    'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );
            if (sizeof($request->products_of_sale) != sizeof($request->quantities) || sizeof($request->products_of_sale) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
                $success = false;
                $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ]);
            }

            if (sizeof($request->products_of_purchase) != sizeof($request->quantities) || sizeof($request->products_of_purchase) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
                $success = false;
                $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ]);
            }

            try {
                $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order);

                $sale->reference = $request->reference;
                $sale->sale_date   = $request->sale_date;
                $sale->amount_gross = $purchaseOrder->total_amount;
                $sale->ht_amount = $purchaseOrder->total_amount;
                $sale->discount = $request->discount;
                $sale->amount_token = $request->amount_token;
                $sale->observation = $request->observation;
                $sale->order_id = $purchaseOrder->id;
                $sale->client_id = $purchaseOrder->client->id;
                $sale->sale_point_id = $purchaseOrder->salePoint->id;
                $sale->save();

                $productSales = [];
                foreach ($request->products_of_sale as $key => $product) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $request->quantities[$key];
                    $productSale->unit_price = $request->unit_prices[$key];
                    $productSale->unity_id = $request->unities[$key];
                    $productSale->product_id = $product;
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    array_push($productSales, $productSale);
                }

                $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                    $sale->delete();
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
