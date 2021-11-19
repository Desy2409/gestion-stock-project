<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderRegister;
use App\Models\SalePoint;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PurchaseOrderController extends Controller
{

    use UtilityTrait;

    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('provider')->with('salePoint')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
        $clients = Client::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $unities = Unity::orderBy('wording')->get();

        $lastPurchaseOrderRegister = PurchaseOrderRegister::latest()->first();

        $purchaseOrderRegister = new PurchaseOrderRegister();
        if ($lastPurchaseOrderRegister) {
            $purchaseOrderRegister->code = $this->formateNPosition('BC', $lastPurchaseOrderRegister->id + 1, 8);
        } else {
            $purchaseOrderRegister->code = $this->formateNPosition('BC', 1, 8);
        }
        $purchaseOrderRegister->save();

        return new JsonResponse([
            'datas' => ['purchaseOrders' => $purchaseOrders, 'clients' => $clients, 'products' => $products, 'salePoints' => $salePoints, 'unities' => $unities]
        ], 200);
    }

    public function showNextCode()
    {
        $lastPurchaseOrderRegister = PurchaseOrderRegister::latest()->first();
        if ($lastPurchaseOrderRegister) {
            $code = $this->formateNPosition('BC', $lastPurchaseOrderRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BC',  1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'client' => 'required',
                'reference' => 'required|unique:purchase_orders',
                'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                'total_amount' => 'required',
                'observation' => 'max:255',
                'products_of_purchase_order' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'sale_point.required' => "Le choix du point de vente est obligatoire.",
                'client.required' => "Le choix du client est obligatoire.",
                'reference.required' => "La référence de la commande est obligatoire.",
                'reference.unique' => "Cette commande existe déjà.",
                'purchase_date.required' => "La date de la commande est obligatoire.",
                'purchase_date.date' => "La date de la commande est incorrecte.",
                // 'purchase_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date de la commande doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                // 'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'products_of_purchase_order.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        if (sizeof($request->products_of_purchase_order) != sizeof($request->quantities) || sizeof($request->products_of_purchase_order) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            $success = false;
            $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ]);
        }

        try {
            $lastPurchaseOrder = PurchaseOrder::latest()->first();

            $purchaseOrder = new PurchaseOrder();
            if ($lastPurchaseOrder) {
                $purchaseOrder->code = $this->formateNPosition('BC', $lastPurchaseOrder->id + 1, 8);
            } else {
                $purchaseOrder->code = $this->formateNPosition('BC', 1, 8);
            }
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->client_id = $request->client;
            $purchaseOrder->sale_point_id = $request->sale_point;
            $purchaseOrder->save();

            $productsPurchaseOrders = [];

            foreach ($request->products_of_purchase_order as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $request->quantities[$key];
                $productPurchaseOrder->unit_price = $request->unit_prices[$key];
                $productPurchaseOrder->product_id = $product;
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productPurchaseOrder->unity_id = $request->unities[$key];
                $productPurchaseOrder->save();

                array_push($productsPurchaseOrders, $productPurchaseOrder);
            }

            $savedProductPurchaseOrders = ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->get();
            if (empty($savedProductPurchaseOrders) || sizeof($savedProductPurchaseOrders) == 0) {
                $purchaseOrder->delete();
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
        $purchaseOrder = PurchaseOrder::with('provider')->with('salePoint')->with('productPurchaseOrders')->findOrFail($id);
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productPurchaseOrders : null; //ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->get();

        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200);
    }

    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('provider')->with('salePoint')->with('productPurchaseOrders')->findOrFail($id);
        $clients = Client::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productsPurchaseOrders : null;

        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['clients' => $clients, 'products' => $products, 'productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $this->validate(
            $request,
            [
                'sale_point' => 'required',
                'client' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                'total_amount' => 'required',
                'observation' => 'max:255',
                'products_of_purchase_order' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'sale_point.required' => "Le choix du point de vente est obligatoire.",
                'client.required' => "Le choix du client est obligatoire.",
                'reference.required' => "La référence de la commande est obligatoire.",
                'purchase_date.required' => "La date de la commande est obligatoire.",
                'purchase_date.date' => "La date de la commande est incorrecte.",
                // 'purchase_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date de la commande doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                // 'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'products_of_purchase_order.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        if (sizeof($request->products_of_purchase_order) != sizeof($request->quantities) || sizeof($request->products_of_purchase_order) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            $success = false;
            $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ]);
        }

        try {
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->client_id = $request->client;
            $purchaseOrder->save();

            ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->delete();

            $productsPurchaseOrders = [];
            foreach ($request->products_of_purchase_order as $key => $product) {
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


    public function validateOrder($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        try {
            $purchaseOrder->state = 'S';
            $purchaseOrder->date_of_processing = date('Y-m-d', strtotime(now()));
            $purchaseOrder->save();

            $success = true;
            $message = "Demande de transfert validée avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la validation de la demande de transfert.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function cancelOrder($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        try {
            $purchaseOrder->state = 'A';
            $purchaseOrder->date_of_processing = date('Y-m-d', strtotime(now()));
            $purchaseOrder->save();

            $success = true;
            $message = "Commande annulée avec succès.";
            return new JsonResponse([
                'purchaseOrder' => $purchaseOrder,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'annulation de la commande.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
