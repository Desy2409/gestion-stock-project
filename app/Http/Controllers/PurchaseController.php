<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductOrder;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseRegister;
use App\Models\Order;
use App\Models\SalePoint;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    use UtilityTrait;

    public function purchaseOnOrder()
    {
        $purchases = Purchase::with('provider')->with('order')->with('deliveryNotes')->with('productPurchases')->orderBy('code')->orderBy('purchase_date')->get();

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
        $purchases = Purchase::with('provider')->with('deliveryNotes')->with('productPurchases')->where('order_id', null)->orderBy('code')->orderBy('purchase_date')->get();

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

        return new JsonResponse([
            'datas' => ['providers' => $providers, 'salePoints' => $salePoints, 'products' => $products, 'purchases' => $purchases]
        ]);
    }

    public function productFromOrder($id)
    {
        $order = Order::findOrFail($id);
        $idOfProducts = ProductOrder::where('order_id', $order->id)->pluck('product_id')->toArray();
        $products = Product::with('subCategory')->whereIn('id', $idOfProducts)->get();
        return new JsonResponse([
            'datas' => ['products' => $products]
        ], 200);
    }

    public function showNextCode()
    {
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
        $purchase = Purchase::with('provider')->with('order')->with('deliveryNotes')->with('productPurchases')->findOrFail($id);
        $productPurchases = $purchase ? $purchase->productPurchases : null; //ProductPurchase::where('order_id', $purchase->id)->get();

        return new JsonResponse([
            'purchase' => $purchase,
            'datas' => ['productPurchases' => $productPurchases]
        ], 200);
    }

    public function store(Request $request)
    {
        if ($request->purchase_type == "Achat direct") {
            $this->validate(
                $request,
                [
                    'sale_point' => 'required',
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
                    'sale_point.required' => "Le choix du point de vente est obligatoire.",
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
                $purchase->sale_point_id = $request->sale_point;
                $purchase->save();

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $request->purchaseProducts["quantity"];
                    $productPurchase->unit_price = $request->purchaseProducts["unit_price"];
                    $productPurchase->unity_id = $request->purchaseProducts["unity"];
                    $productPurchase->product_id = $product;
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
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
        } else {
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
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $request->purchaseProducts["quantity"];
                    $productPurchase->unit_price = $request->purchaseProducts["unit_price"];
                    $productPurchase->unity_id = $request->purchaseProducts["unity"];
                    $productPurchase->product_id = $product;
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
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
        }
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($request->purchase_type == "Achat direct") {
            $this->validate(
                $request,
                [
                    'sale_point' => 'required',
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
                    'sale_point.required' => "Le choix du point de vente est obligatoire.",
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
                    $productPurchase->quantity = $request->purchaseProducts["quantity"];
                    $productPurchase->unit_price = $request->purchaseProducts["unit_price"];
                    $productPurchase->unity_id = $request->purchaseProducts["unity"];
                    $productPurchase->product_id = $product;
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
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
        } else {
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
                    $productPurchase->quantity = $request->purchaseProducts["quantity"];
                    $productPurchase->unit_price = $request->purchaseProducts["unit_price"];
                    $productPurchase->unity_id = $request->purchaseProducts["unity"];
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
        $purchase = Purchase::findOrFail($id);
        $productPurchases = $purchase ? $purchase->productPurchases : null;
        try {
            $purchase->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
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
}
