<?php

namespace App\Http\Controllers;

use App\Http\Traits\StockTrait;
use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\ClientDeliveryNote;
use App\Models\Institution;
use App\Models\Product;
use App\Models\ProductClientDeliveryNote;
use App\Models\ProductPurchaseOrder;
use App\Models\ProductSale;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderRegister;
use App\Models\Sale;
use App\Models\SalePoint;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderProcessingController extends Controller
{
    use UtilityTrait;
    use StockTrait;

    public function index()
    {
        $this->authorize('ROLE_PURCHASE_ORDER_READ', PurchaseOrder::class);
        $purchaseOrders = PurchaseOrder::with('client')->with('salePoint')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
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

    public function purchaseOrderProcessing(Request $request, $id)
    {
        $this->authorize('ROLE_ORDER_CREATE', Order::class);

        $instituion = Institution::findOrFail($id);

        $this->purchaseOrderProcessingValidation($request, $instituion);

        switch ($instituion->settings) {
            case 'PURCHASE_ORDER_SALE_CLIENT_DELIVERY_NOTE':
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
                    $purchaseOrder->sale_point_id = $request->salePoint;
                    $purchaseOrder->save();

                    $lastSale = Sale::latest()->first();

                    $sale = new Sale();
                    if ($lastSale) {
                        $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                    } else {
                        $sale->code = $this->formateNPosition('VT', 1, 8);
                    }
                    $sale->reference = $request->reference;
                    $sale->sale_date   = $request->sale_date;
                    $sale->total_amount = $purchaseOrder->total_amount;
                    $sale->amount_gross = $purchaseOrder->amount_gross;
                    $sale->ht_amount = $request->ht_amount;
                    $sale->discount = $request->discount;
                    $sale->amount_token = $request->amount_token;
                    $sale->tva = $request->tva;
                    $sale->observation = $request->observation;
                    $sale->order_id = $purchaseOrder->id;
                    $sale->client_id = $purchaseOrder->client->id;
                    $sale->sale_point_id = $purchaseOrder->salePoint->id;
                    $sale->save();

                    $lastClientDeliveryNote = ClientDeliveryNote::latest()->first();

                    $clientDeliveryNote = new ClientDeliveryNote();
                    if ($lastClientDeliveryNote) {
                        $clientDeliveryNote->code = $this->formateNPosition('BL', $lastClientDeliveryNote->id + 1, 8);
                    } else {
                        $clientDeliveryNote->code = $this->formateNPosition('BL', 1, 8);
                    }
                    $clientDeliveryNote->reference = $request->reference;
                    $clientDeliveryNote->delivery_date   = $request->delivery_date;
                    $clientDeliveryNote->total_amount = $request->total_amount;
                    $clientDeliveryNote->observation = $request->observation;
                    $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
                    $clientDeliveryNote->sale_id = $sale->id;
                    $clientDeliveryNote->save();

                    $productsPurchaseOrders = [];

                    foreach ($request->productPurchaseOrders as $key => $productPurchaseOrderLine) {
                        $productPurchaseOrder = new ProductPurchaseOrder();
                        $productPurchaseOrder->quantity = $productPurchaseOrderLine['quantity'];
                        $productPurchaseOrder->unit_price = $productPurchaseOrderLine['unit_price'];
                        $productPurchaseOrder->product_id = $productPurchaseOrderLine['product']['id'];
                        $productPurchaseOrder->unity_id = $productPurchaseOrderLine['unity']['id'];
                        $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                        $productPurchaseOrder->save();

                        $productSale = new ProductSale();
                        $productSale->quantity = $productPurchaseOrderLine['quantity'];
                        $productSale->unit_price = $productPurchaseOrderLine['unit_price'];
                        $productSale->product_id = $productPurchaseOrderLine['product']['id'];
                        $productSale->unity_id = $productPurchaseOrderLine['unity']['id'];
                        $productSale->sale_id = $sale->id;
                        $productSale->save();

                        $productClientDeliveryNote = new ProductClientDeliveryNote();
                        $productClientDeliveryNote->quantity = $productPurchaseOrderLine['quantity'];
                        $productClientDeliveryNote->product_id = $productPurchaseOrderLine['product']['id'];
                        $productClientDeliveryNote->unity_id = $productPurchaseOrderLine['unity']['id'];
                        $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
                        $productClientDeliveryNote->save();

                        array_push($productsPurchaseOrders, $productPurchaseOrder);
                    }

                    $this->decrement($clientDeliveryNote);

                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'purchaseOrder' => $purchaseOrder,
                        'success' => $success,
                        'message' => $message,
                        // 'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders],
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
                break;
            case 'PURCHASE_ORDER_SALE':
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
                    $purchaseOrder->sale_point_id = $request->salePoint;
                    $purchaseOrder->save();

                    $lastSale = Sale::latest()->first();

                    $sale = new Sale();
                    if ($lastSale) {
                        $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                    } else {
                        $sale->code = $this->formateNPosition('VT', 1, 8);
                    }
                    $sale->reference = $request->reference;
                    $sale->sale_date   = $request->sale_date;
                    $sale->total_amount = $purchaseOrder->total_amount;
                    $sale->amount_gross = $purchaseOrder->amount_gross;
                    $sale->ht_amount = $request->ht_amount;
                    $sale->discount = $request->discount;
                    $sale->amount_token = $request->amount_token;
                    $sale->tva = $request->tva;
                    $sale->observation = $request->observation;
                    $sale->order_id = $purchaseOrder->id;
                    $sale->client_id = $purchaseOrder->client->id;
                    $sale->sale_point_id = $purchaseOrder->salePoint->id;
                    $sale->save();

                    $productsPurchaseOrders = [];

                    foreach ($request->productPurchaseOrders as $key => $productPurchaseOrderLine) {
                        $productPurchaseOrder = new ProductPurchaseOrder();
                        $productPurchaseOrder->quantity = $productPurchaseOrderLine['quantity'];
                        $productPurchaseOrder->unit_price = $productPurchaseOrderLine['unit_price'];
                        $productPurchaseOrder->product_id = $productPurchaseOrderLine['product']['id'];
                        $productPurchaseOrder->unity_id = $productPurchaseOrderLine['unity']['id'];
                        $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                        $productPurchaseOrder->save();

                        $productSale = new ProductSale();
                        $productSale->quantity = $productPurchaseOrderLine['quantity'];
                        $productSale->unit_price = $productPurchaseOrderLine['unit_price'];
                        $productSale->product_id = $productPurchaseOrderLine['product']['id'];
                        $productSale->unity_id = $productPurchaseOrderLine['unity']['id'];
                        $productSale->sale_id = $sale->id;
                        $productSale->save();

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
                break;
            case 'SALE_CLIENT_DELIVERY_NOTE':
                $lastSale = Sale::latest()->first();

                $sale = new Sale();
                if ($lastSale) {
                    $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                } else {
                    $sale->code = $this->formateNPosition('VT', 1, 8);
                }
                $sale->reference = $request->reference;
                $sale->sale_date   = $request->sale_date;
                $sale->total_amount = $request->total_amount;
                $sale->amount_gross = $request->amount_gross;
                $sale->ht_amount = $request->ht_amount;
                $sale->discount = $request->discount;
                $sale->amount_token = $request->amount_token;
                $sale->tva = $request->tva;
                $sale->observation = $request->observation;
                $sale->client_id = $request->client;
                $sale->sale_point_id = $request->SalePoint;
                $sale->save();

                $lastClientDeliveryNote = ClientDeliveryNote::latest()->first();

                $clientDeliveryNote = new ClientDeliveryNote();
                if ($lastClientDeliveryNote) {
                    $clientDeliveryNote->code = $this->formateNPosition('BL', $lastClientDeliveryNote->id + 1, 8);
                } else {
                    $clientDeliveryNote->code = $this->formateNPosition('BL', 1, 8);
                }
                $clientDeliveryNote->reference = $request->reference;
                $clientDeliveryNote->delivery_date   = $request->delivery_date;
                $clientDeliveryNote->total_amount = $request->total_amount;
                $clientDeliveryNote->observation = $request->observation;
                $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
                $clientDeliveryNote->sale_id = $sale->id;
                $clientDeliveryNote->save();

                $productSales = [];
                foreach ($request->saleProducts as $key => $productSaleLine) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $productSaleLine['quantity'];
                    $productSale->unit_price = $productSaleLine['unit_price'];
                    $productSale->product_id = $productSaleLine['product']['id'];
                    $productSale->unity_id = $productSaleLine['unity']['id'];
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    $productClientDeliveryNote = new ProductClientDeliveryNote();
                    $productClientDeliveryNote->quantity = $productSaleLine['quantity'];
                    $productClientDeliveryNote->product_id = $productSaleLine['product']['id'];
                    $productClientDeliveryNote->unity_id = $productSaleLine['unity']['id'];
                    $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
                    $productClientDeliveryNote->save();

                    array_push($productSales, $productSale);
                }

                $this->decrement($clientDeliveryNote);
                
                break;

            default:
                # code...
                break;
        }
    }


    public function purchaseOrderProcessingValidation(Request $request, $instituion)
    {
        switch ($instituion->settings) {
            case 'PURCHASE_ORDER_SALE_CLIENT_DELIVERY_NOTE':
                return Validator::make(
                    $request,
                    [
                        'salePoint' => 'required',
                        'client' => 'required',
                        'reference' => 'required|unique:purchase_orders',
                        'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                        'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                        'total_amount' => 'required',
                        'observation' => 'max:255',
                        'productPurchaseOrders' => 'required',
                    ],
                    [
                        'salePoint.required' => "Le choix du point de vente est obligatoire.",
                        'client.required' => "Le choix du client est obligatoire.",
                        'reference.required' => "La référence de la commande est obligatoire.",
                        'reference.unique' => "Cette commande existe déjà.",
                        'purchase_date.required' => "La date de la commande est obligatoire.",
                        'purchase_date.date' => "La date de la commande est incorrecte.",
                        'purchase_date.before' => "La date de la commande doit être antérieure ou égale à aujourd'hui.",
                        'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                        'delivery_date.date' => "La date de livraison est incorrecte.",
                        'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                        'total_amount.required' => "Le montant total est obligatoire.",
                        'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                        'productPurchaseOrders.required' => "Vous devez ajouter au moins un produit au panier.",
                    ]
                );
                break;
            case 'PURCHASE_ORDER_SALE':
                return Validator::make(
                    $request,
                    [
                        'sale_point' => 'required',
                        'provider' => 'required',
                        'reference' => 'required',
                        'order_date' => 'required|date|before:today', //|date_format:Ymd
                        'delivery_date' => 'required|date|after:order_date', //|date_format:Ymd
                        'total_amount' => 'required',
                        'observation' => 'max:255',
                        'productOrders' => 'required',
                    ],
                    [
                        'sale_point.required' => "Le choix du point de vente est obligatoire.",
                        'provider.required' => "Le choix du fournisseur est obligatoire.",
                        'reference.required' => "La référence du bon est obligatoire.",
                        'order_date.required' => "La date du bon est obligatoire.",
                        'order_date.date' => "La date du bon de commande est incorrecte.",
                        'order_date.before' => "La date du bon de commande doit être antérieure ou égale à aujourd'hui.",
                        'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                        'delivery_date.date' => "La date de livraison est incorrecte.",
                        'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
                        'total_amount.required' => "Le montant total est obligatoire.",
                        'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                        'productOrders.required' => "Vous devez ajouter au moins un produit au panier.",
                    ]
                );
                break;
            case 'SALE_CLIENT_DELIVERY_NOTE':
                return Validator::make(
                    $request,
                    [
                        'purchase_order' => 'required',
                        'reference' => 'required|unique:sales',
                        'sale_date' => 'required|date|before:today', //|date_format:Ymd
                        'observation' => 'max:255',
                        'saleProducts' => 'required',
                    ],
                    [
                        'purchase_order.required' => "Le choix d'un bon de commande est obligatoire.",
                        'reference.required' => "La référence du bon est obligatoire.",
                        'reference.unique' => "Cette vente existe déjà.",
                        'sale_date.required' => "La date du bon est obligatoire.",
                        'sale_date.date' => "La date de la vente est incorrecte.",
                        'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                        'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                        'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    ]
                );
                break;

            default:
                # code...
                break;
        }
    }
}
