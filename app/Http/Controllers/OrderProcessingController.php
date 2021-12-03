<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\DeliveryNote;
use App\Models\Institution;
use App\Models\Order;
use App\Models\OrderRegister;
use App\Models\Product;
use App\Models\ProductDeliveryNote;
use App\Models\ProductOrder;
use App\Models\ProductPurchase;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\SalePoint;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderProcessingController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $this->authorize('ROLE_ORDER_READ', Order::class);
        $orders = Order::with('provider')->with('productOrders')->orderBy('order_date')->get();
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $unities = Unity::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();

        $lastOrderRegister = OrderRegister::latest()->first();

        $orderRegister = new OrderRegister();
        if ($lastOrderRegister) {
            $orderRegister->code = $this->formateNPosition('BC', $lastOrderRegister->id + 1, 8);
        } else {
            $orderRegister->code = $this->formateNPosition('BC', 1, 8);
        }
        $orderRegister->save();

        return new JsonResponse([
            'datas' => ['orders' => $orders, 'providers' => $providers, 'products' => $products, 'unities' => $unities, 'salePoints' => $salePoints]
        ], 200);
    }

    public function orderProcessing(Request $request, $id)
    {
        $this->authorize('ROLE_ORDER_CREATE', Order::class);

        $instituion = Institution::findOrFail($id);

        $this->orderProcessingValidation($request, $instituion);

        switch ($instituion->settings) {
            case 'ORDER_PURCHASE_DELIVERY_NOTE':
                try {

                    $lastOrder = Order::latest()->first();

                    $order = new Order();
                    if ($lastOrder) {
                        $order->code = $this->formateNPosition('BC', $lastOrder->id + 1, 8);
                    } else {
                        $order->code = $this->formateNPosition('BC', 1, 8);
                    }
                    $order->reference = $request->reference;
                    $order->order_date   = $request->order_date;
                    $order->delivery_date   = $request->delivery_date;
                    $order->total_amount = $request->total_amount;
                    $order->observation = $request->observation;
                    $order->provider_id = $request->provider;
                    $order->sale_point_id = $request->sale_point;
                    $order->save();

                    $lastPurchase = Purchase::latest()->first();

                    $purchase = new Purchase();
                    if ($lastPurchase) {
                        $purchase->code = $this->formateNPosition('BA', $lastPurchase->id + 1, 8);
                    } else {
                        $purchase->code = $this->formateNPosition('BA', 1, 8);
                    }
                    $purchase->reference = $request->reference;
                    $purchase->purchase_date   = $request->order_date;
                    $purchase->delivery_date   = $request->delivery_date;
                    $purchase->total_amount = $order->total_amount;
                    $purchase->amount_gross = $order->total_amount;
                    $purchase->ht_amount = $request->ht_amount;
                    $purchase->discount = $request->discount;
                    $purchase->amount_token = $request->amount_token;
                    $purchase->tva = $request->tva;
                    $purchase->observation = $request->observation;
                    $purchase->order_id = $order->id;
                    $purchase->provider_id = $order->provider->id;
                    $purchase->sale_point_id = $order->salePoint->id;
                    $purchase->save();

                    $lastDeliveryNote = DeliveryNote::latest()->first();

                    $deliveryNote = new DeliveryNote();
                    if ($lastDeliveryNote) {
                        $deliveryNote->code = $this->formateNPosition('BL', $lastDeliveryNote->id + 1, 8);
                    } else {
                        $deliveryNote->code = $this->formateNPosition('BL', 1, 8);
                    }
                    $deliveryNote->reference = $request->reference;
                    $deliveryNote->delivery_date   = $request->delivery_date;
                    $deliveryNote->total_amount = $request->total_amount;
                    $deliveryNote->observation = $request->observation;
                    $deliveryNote->place_of_delivery = $request->place_of_delivery;
                    $deliveryNote->purchase_id = $purchase->id;
                    $deliveryNote->save();


                    $productsOrders = [];
                    foreach ($request->productOrders as $key => $productOrderLine) {
                        $productOrder = new ProductOrder();
                        $productOrder->quantity = $productOrderLine['quantity'];
                        $productOrder->unit_price = $productOrderLine['unit_price'];
                        $productOrder->product_id = $productOrderLine['product'];;
                        $productOrder->order_id = $order->id;
                        $productOrder->unity_id = $productOrderLine['unity'];;
                        $productOrder->save();

                        $productPurchase = new ProductPurchase();
                        $productPurchase->quantity = $productOrderLine["quantity"];
                        $productPurchase->unit_price = $productOrderLine["unit_price"];
                        $productPurchase->unity_id = $productOrderLine["unity"];
                        $productPurchase->product_id = $productOrderLine;
                        $productPurchase->purchase_id = $purchase->id;
                        $productPurchase->save();

                        $productDeliveryNote = new ProductDeliveryNote();
                        $productDeliveryNote->quantity = $productOrderLine["quantity"];
                        $productDeliveryNote->unity_id = $productOrderLine["unity"];
                        $productDeliveryNote->product_id = $productOrderLine["product"];
                        $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                        $productDeliveryNote->save();

                        array_push($productsOrders, $productOrder);
                    }

                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'order' => $order,
                        'purchase' => $purchase,
                        'deliveryNote' => $deliveryNote,
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
                break;
            case 'ORDER_PURCHASE':
                try {

                    $lastOrder = Order::latest()->first();

                    $order = new Order();
                    if ($lastOrder) {
                        $order->code = $this->formateNPosition('BC', $lastOrder->id + 1, 8);
                    } else {
                        $order->code = $this->formateNPosition('BC', 1, 8);
                    }
                    $order->reference = $request->reference;
                    $order->order_date   = $request->order_date;
                    $order->delivery_date   = $request->delivery_date;
                    $order->total_amount = $request->total_amount;
                    $order->observation = $request->observation;
                    $order->provider_id = $request->provider;
                    $order->sale_point_id = $request->sale_point;
                    $order->save();

                    $lastPurchase = Purchase::latest()->first();

                    $purchase = new Purchase();
                    if ($lastPurchase) {
                        $purchase->code = $this->formateNPosition('BA', $lastPurchase->id + 1, 8);
                    } else {
                        $purchase->code = $this->formateNPosition('BA', 1, 8);
                    }
                    $purchase->reference = $request->reference;
                    $purchase->purchase_date   = $request->order_date;
                    $purchase->delivery_date   = $request->delivery_date;
                    $purchase->total_amount = $order->total_amount;
                    $purchase->amount_gross = $order->total_amount;
                    $purchase->ht_amount = $request->ht_amount;
                    $purchase->discount = $request->discount;
                    $purchase->amount_token = $request->amount_token;
                    $purchase->tva = $request->tva;
                    $purchase->observation = $request->observation;
                    $purchase->order_id = $order->id;
                    $purchase->provider_id = $order->provider->id;
                    $purchase->sale_point_id = $order->salePoint->id;
                    $purchase->save();

                    $productsOrders = [];
                    foreach ($request->productOrders as $key => $productOrderLine) {
                        $productOrder = new ProductOrder();
                        $productOrder->quantity = $productOrderLine['quantity'];
                        $productOrder->unit_price = $productOrderLine['unit_price'];
                        $productOrder->product_id = $productOrderLine['product'];;
                        $productOrder->order_id = $order->id;
                        $productOrder->unity_id = $productOrderLine['unity'];;
                        $productOrder->save();

                        $productPurchase = new ProductPurchase();
                        $productPurchase->quantity = $productOrderLine["quantity"];
                        $productPurchase->unit_price = $productOrderLine["unit_price"];
                        $productPurchase->unity_id = $productOrderLine["unity"];
                        $productPurchase->product_id = $productOrderLine;
                        $productPurchase->purchase_id = $purchase->id;
                        $productPurchase->save();

                        array_push($productsOrders, $productOrder);
                    }

                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'order' => $order,
                        'purchase' => $purchase,
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
                break;
            case 'PURCHASE_DELIVERY_NOTE':
                try {
                    $lastPurchase = Purchase::latest()->first();

                    $purchase = new Purchase();
                    if ($lastPurchase) {
                        $purchase->code = $this->formateNPosition('BA', $lastPurchase->id + 1, 8);
                    } else {
                        $purchase->code = $this->formateNPosition('BA', 1, 8);
                    }
                    $purchase->reference = $request->reference;
                    $purchase->purchase_date   = $request->purchase_date;
                    $purchase->delivery_date   = $request->delivery_date;
                    $purchase->total_amount = $request->total_amount;
                    $purchase->amount_gross = $request->amount_gross;
                    $purchase->ht_amount = $request->ht_amount;
                    $purchase->discount = $request->discount;
                    $purchase->amount_token = $request->amount_token;
                    $purchase->tva = $request->tva;
                    $purchase->observation = $request->observation;
                    $purchase->provider_id = $request->provider;
                    $purchase->sale_point_id = $request->salePoint;
                    $purchase->save();

                    $lastDeliveryNote = DeliveryNote::latest()->first();

                    $deliveryNote = new DeliveryNote();
                    if ($lastDeliveryNote) {
                        $deliveryNote->code = $this->formateNPosition('BL', $lastDeliveryNote->id + 1, 8);
                    } else {
                        $deliveryNote->code = $this->formateNPosition('BL', 1, 8);
                    }
                    $deliveryNote->reference = $request->reference;
                    $deliveryNote->delivery_date   = $request->delivery_date;
                    $deliveryNote->total_amount = $request->total_amount;
                    $deliveryNote->observation = $request->observation;
                    $deliveryNote->place_of_delivery = $request->place_of_delivery;
                    $deliveryNote->purchase_id = $purchase->id;
                    $deliveryNote->save();


                    $productsPurchases = [];
                    foreach ($request->productsPurchases as $key => $productPurchaseLine) {
                        $productPurchase = new ProductPurchase();
                        $productPurchase->quantity = $productPurchaseLine["quantity"];
                        $productPurchase->unit_price = $productPurchaseLine["unit_price"];
                        $productPurchase->unity_id = $productPurchaseLine["unity"];
                        $productPurchase->product_id = $productPurchaseLine;
                        $productPurchase->purchase_id = $purchase->id;
                        $productPurchase->save();

                        $productDeliveryNote = new ProductDeliveryNote();
                        $productDeliveryNote->quantity = $productPurchaseLine["quantity"];
                        $productDeliveryNote->unity_id = $productPurchaseLine["unity"];
                        $productDeliveryNote->product_id = $productPurchaseLine["product"];
                        $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                        $productDeliveryNote->save();

                        array_push($productsPurchases, $productPurchase);
                    }

                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'purchase' => $purchase,
                        'deliveryNote' => $deliveryNote,
                        'success' => $success,
                        'message' => $message,
                        'datas' => ['productsPurchases' => $productsPurchases],
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

            default:
                # code...
                break;
        }
    }


    public function orderProcessingValidation(Request $request, $instituion)
    {
        // $instituion = Institution::findOrFail($id);

        switch ($instituion->settings) {
            case 'ORDER_PURCHASE_DELIVERY_NOTE':
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
            case 'ORDER_PURCHASE':
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
            case 'PURCHASE_DELIVERY_NOTE':
                return Validator::make(
                    $request,
                    [
                        'order' => 'required',
                        'reference' => 'required|unique:purchases',
                        'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                        'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                        'observation' => 'max:255',
                        'purchaseProducts' => 'required',
                    ],
                    [
                        'order.required' => "Le choix d'une commande est obligatoire.",
                        'reference.required' => "La référence du bon est obligatoire.",
                        'reference.unique' => "Ce bon d'achat existe déjà.",
                        'purchase_date.required' => "La date du bon d'achat est obligatoire.",
                        'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                        'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                        'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                        'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                        'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                        'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                        'purchaseProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    ]
                );
                break;

            default:
                # code...
                break;
        }
    }
}
