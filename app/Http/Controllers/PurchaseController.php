<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductOrder;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseRegister;
use App\Models\Order;
use App\Models\ProductDeliveryNote;
use App\Models\SalePoint;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    use UtilityTrait;

    private $directPurchase = "Achat direct";
    private $purchaseOnOrder = "Achat sur commande";

    public function purchaseOnOrder()
    {
        $purchases = Purchase::with('provider')->with('order')->with('deliveryNotes')->with('productPurchases')->where('order_id', '!=', null)->orderBy('code')->orderBy('purchase_date')->get();

        $lastPurchaseRegister = PurchaseRegister::latest()->first();

        $purchaseRegister = new PurchaseRegister();
        if ($lastPurchaseRegister) {
            $purchaseRegister->code = $this->formateNPosition('BA', $lastPurchaseRegister->id + 1, 8);
        } else {
            $purchaseRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $purchaseRegister->save();

        $orders = Order::with('provider')->with('salePoint')->orderBy('code')->get();
        return new JsonResponse([
            'datas' => ['orders' => $orders, 'purchases' => $purchases]
        ]);
    }

    public function directPurchase()
    {
        $purchases = Purchase::with('provider')->with('deliveryNotes')->with('productPurchases')->where('order_id', '=', null)->orderBy('code')->orderBy('purchase_date')->get();

        $lastPurchaseRegister = PurchaseRegister::latest()->first();

        $purchaseRegister = new PurchaseRegister();
        if ($lastPurchaseRegister) {
            $purchaseRegister->code = $this->formateNPosition('BA', $lastPurchaseRegister->id + 1, 8);
        } else {
            $purchaseRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $purchaseRegister->save();

        $providers = Provider::with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $products = Product::with('subCategory')->get();
        $unities = Unity::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['providers' => $providers, 'salePoints' => $salePoints, 'products' => $products, 'purchases' => $purchases, 'unities' => $unities]
        ]);
    }

    public function datasFromOrder($id)
    {
        $this->authorize('ROLE_PURCHASE_READ', Purchase::class);
        $order = Order::findOrFail($id);
        $provider = $order ? $order->provider : null;
        $salePoint = $order ? $order->salePoint : null;

        $productOrders = ProductOrder::where('order_id', $order->id)->with('product')->with('unity')->get();
        // dd($productOrders);
        // dd($order);
        return new JsonResponse([
            'provider' => $provider, 'salePoint' => $salePoint, 'datas' => ['productOrders' => $productOrders]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_PURCHASE_READ', Purchase::class);
        $lastPurchaseRegister = PurchaseRegister::latest()->first();
        if ($lastPurchaseRegister) {
            $code = $this->formateNPosition('BA', $lastPurchaseRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BA', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function show($id)
    {
        $this->authorize('ROLE_PURCHASE_READ', Purchase::class);
        $purchase = Purchase::with('provider')->with('order')->with('deliveryNotes')->with('productPurchases')->findOrFail($id);
        $productPurchases = $purchase ? $purchase->productPurchases : null; //ProductPurchase::where('order_id', $purchase->id)->get();

        return new JsonResponse([
            'purchase' => $purchase,
            'datas' => ['productPurchases' => $productPurchases]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_PURCHASE_CREATE', Purchase::class);
        if ($request->purchaseType == "Achat direct") {
            $this->validate(
                $request,
                [
                    'salePoint' => 'required',
                    'provider' => 'required',
                    'reference' => 'required|unique:purchases',
                    'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                    'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'purchaseProducts' => 'required',
                    // 'quantities' => 'required|min:0',
                    // 'unit_prices' => 'required|min:0',
                    // 'unities' => 'required'
                ],
                [
                    'salePoint.required' => "Le choix du point de vente est obligatoire.",
                    'provider.required' => "Le choix du fournisseur est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon d'achat est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    // 'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    // 'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'purchaseProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->purchaseProducts) != sizeof($request->quantities) || sizeof($request->purchaseProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
                // Enregistrement de l'achat direct avec les produits qui y sont liés
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

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity"];
                    $productPurchase->product_id = $product["product"];
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
                }

                // Enregistrement de la livraison affiliée à l'achat direct
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

                $productDeliveryNotes = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productDeliveryNote = new ProductDeliveryNote();
                    $productDeliveryNote->quantity = $product["quantity"];
                    $productDeliveryNote->unity_id = $product["unity"];
                    $productDeliveryNote->product_id = $product["product"];
                    $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                    $productDeliveryNote->save();

                    array_push($productDeliveryNotes, $productDeliveryNote);
                }

                // $savedProductPurchases = ProductPurchase::where('purchase_id', $purchase->id)->get();
                // if (empty($savedProductPurchases) || sizeof($savedProductPurchases) == 0) {
                //     $purchase->delete();
                // }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
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
        } elseif ($request->purchaseType == "Achat sur commande") {
            $this->validate(
                $request,
                [
                    'order' => 'required',
                    'reference' => 'required|unique:purchases',
                    'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                    'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'purchaseProducts' => 'required',
                    // 'quantities' => 'required|min:0',
                    // 'unit_prices' => 'required|min:0',
                    // 'unities' => 'required'
                ],
                [
                    'order.required' => "Le choix d'une commande est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon d'achat est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    // 'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    // 'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'purchaseProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->purchaseProducts) != sizeof($request->quantities) || sizeof($request->purchaseProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
                //dd($request->purchaseProducts);
                //dd(json_encode($request->all()));
                $order = Order::findOrFail($request->order);

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

                $productPurchases = [];
                $i=0;
                // dd($request->purchaseProducts);
                foreach ($request->purchaseProducts as $key => $product) {
                    // dd($product[1]["unit_price"]);
                    // dd($product);
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity"];
                    $productPurchase->product_id = $product["product"];
                    // $productPurchase->quantity = $product[$i]["quantity"];
                    // $productPurchase->unit_price = $product[$i]["unit_price"];
                    // $productPurchase->unity_id = $product[$i]["unity"];
                    // $productPurchase->product_id = $product[$i]["product"];
                    $productPurchase->purchase_id = $purchase->id;
                    // $productPurchase->save();
                    $i++;

                    array_push($productPurchases, $productPurchase);
                }

                // dd($productPurchases);

                // $savedProductPurchases = ProductPurchase::where('purchase_id', $purchase->id)->get();
                // if (empty($savedProductPurchases) || sizeof($savedProductPurchases) == 0) {
                //     $purchase->delete();
                // }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
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

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PURCHASE_UPDATE', Purchase::class);
        $purchase = Purchase::findOrFail($id);
        if ($request->purchaseType == "Achat direct") {
            $this->validate(
                $request,
                [
                    'salePoint' => 'required',
                    'provider' => 'required',
                    'reference' => 'required|unique:purchases',
                    'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                    'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'purchaseProducts' => 'required',
                    // 'quantities' => 'required|min:0',
                    // 'unit_prices' => 'required|min:0',
                    // 'unities' => 'required'
                ],
                [
                    'salePoint.required' => "Le choix du point de vente est obligatoire.",
                    'provider.required' => "Le choix du fournisseur est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon d'achat est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    // 'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    // 'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'purchaseProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->purchaseProducts) != sizeof($request->quantities) || sizeof($request->purchaseProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
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
                $purchase->sale_point_id = $request->salePoint->id;
                $purchase->save();

                ProductPurchase::where('purchase_id', $purchase->id)->delete();

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity"];
                    $productPurchase->product_id = $product;
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
                }

                $deliveryNote = $purchase ? $purchase->deliveryNote : null;
                if ($deliveryNote) {
                    $deliveryNote->reference = $request->reference;
                    $deliveryNote->delivery_date   = $request->delivery_date;
                    $deliveryNote->total_amount = $request->total_amount;
                    $deliveryNote->observation = $request->observation;
                    $deliveryNote->place_of_delivery = $request->place_of_delivery;
                    $deliveryNote->purchase_id = $purchase->id;
                    $deliveryNote->save();

                    ProductDeliveryNote::where('delivery_note_id', $deliveryNote->id)->delete();

                    $productDeliveryNotes = [];
                    foreach ($request->deliveryNoteProducts as $key => $product) {
                        $productDeliveryNote = new ProductDeliveryNote();
                        $productDeliveryNote->quantity = $product["quantity"];
                        $productDeliveryNote->unity_id = $product["unity"];
                        $productDeliveryNote->product_id = $product["product"];
                        $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                        $productDeliveryNote->save();

                        array_push($productDeliveryNotes, $productDeliveryNote);
                    }
                }

                // $savedProductPurchases = ProductPurchase::where('purchase_id', $purchase->id)->get();
                // if (empty($savedProductPurchases) || sizeof($savedProductPurchases) == 0) {
                //     $purchase->delete();
                // }

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
                ], 200);
            } catch (Exception $e) {
                $success = false;
                $message = "Erreur survenue lors de la modification.";
                return new JsonResponse([
                    'success' => $success,
                    'message' => $message,
                ], 400);
            }
        } elseif ($request->purchaseType == "Achat sur commande") {
            $this->validate(
                $request,
                [
                    'order' => 'required',
                    'reference' => 'required|unique:purchases',
                    'purchase_date' => 'required|date|before:today', //|date_format:Ymd
                    'delivery_date' => 'required|date|after:purchase_date', //|date_format:Ymd
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'purchaseProducts' => 'required',
                    // 'quantities' => 'required|min:0',
                    // 'unit_prices' => 'required|min:0',
                    // 'unities' => 'required'
                ],
                [
                    'order.required' => "Le choix d'une commande est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Ce bon d'achat existe déjà.",
                    'purchase_date.required' => "La date du bon d'achat est obligatoire.",
                    'purchase_date.date' => "La date du bon d'achat est incorrecte.",
                    // 'purchase_date.date_format' => "La du bon d'achat doit être sous le format : Année Mois Jour.",
                    'purchase_date.before' => "La date du bon d'achat doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison prévue est incorrecte.",
                    // 'delivery_date.date_format' => "La date de livraison prévue doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date de livraison prévue doit être ultérieure à la date du bon d'achat.",
                    // 'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'purchaseProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->purchaseProducts) != sizeof($request->quantities) || sizeof($request->purchaseProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
                $order = Order::findOrFail($request->order);

                $purchase->reference = $request->reference;
                $purchase->purchase_date   = $request->purchase_date;
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
                $purchase->sale_point_id = $order->sale_point->id;
                $purchase->save();

                ProductPurchase::where('purchase_id', $purchase->id)->delete();

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity"];
                    $productPurchase->product_id = $product;
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
                }

                $savedProductPurchases = ProductPurchase::where('purchase_id', $purchase->id)->get();
                if (empty($savedProductPurchases) || sizeof($savedProductPurchases) == 0) {
                    $purchase->delete();
                }

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
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
        $this->authorize('ROLE_PURCHASE_DELETE', Purchase::class);
        $purchase = Purchase::findOrFail($id);
        $productPurchases = $purchase ? $purchase->productPurchases : null;
        try {
            $success = false;
            $message = "";
            if (
                empty($productPurchases) || sizeof($productPurchases) == 0 &&
                empty($purchase->deliveryNotes) || sizeof($purchase->deliveryNotes) == 0 
            ) {
                // dd('delete');
                $purchase->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cet achat ne peut être supprimé car il a servi dans des traitements.";
            }
            
            return new JsonResponse([
                'purchase' => $purchase,
                'success' => $success,
                'message' => $message,
                'datas' => ['productPurchases' => $productPurchases],
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

    public function validatePurchase($id)
    {
        $this->authorize('ROLE_PURCHASE_VALIDATE', Purchase::class);
        $purchase = Purchase::findOrFail($id);
        try {
            $purchase->state = 'S';
            $purchase->date_of_processing = date('Y-m-d', strtotime(now()));
            $purchase->save();

            $success = true;
            $message = "Bon d'achat validé avec succès.";
            return new JsonResponse([
                'purchase' => $purchase,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la validation du bon d'achat.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function rejectPurchase($id)
    {
        $this->authorize('ROLE_PURCHASE_REJECT', Purchase::class);
        $purchase = Purchase::findOrFail($id);
        try {
            $purchase->state = 'A';
            $purchase->date_of_processing = date('Y-m-d', strtotime(now()));
            $purchase->save();

            $success = true;
            $message = "Bon d'achat annulé avec succès.";
            return new JsonResponse([
                'purchase' => $purchase,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'annulation du bon d'achat.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
