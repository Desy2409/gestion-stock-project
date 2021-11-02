<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\ProductDeliveryNote;
use App\Models\ProductPurchaseCoupon;
use App\Models\PurchaseCoupon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function index()
    {
        $purchaseCoupons = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->orderBy('purchase_date')->get();
        $deliveryNotes = DeliveryNote::with('purchaseCoupon')->with('productDeliveryNotes')->orderBy('purchase_date')->get();

        return new JsonResponse([
            'datas' => ['deliveryNotes' => $deliveryNotes, 'purchaseCoupons' => $purchaseCoupons]
        ], 200);
    }

    public function showProductOfPurchaseCoupon($id)
    {
        $idOfProducts = ProductPurchaseCoupon::where('purchase_coupon_id', $id)->pluck('product_id')->toArray();
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
                'purchase_coupon'=>'required',
                'reference' => 'required|unique:delivery_notes',
                'purchase_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'purchase_coupon.required'=>"Le choix d'un bon de livraison est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon de livraison existe déjà.",
                'purchase_date.required' => "La date du bon de livraison  est obligatoire.",
                'purchase_date.date' => "La date du bon de livraison est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'purchase_date.date_equals' => "La date du bon de livraison ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
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
            $deliveryNote = new DeliveryNote();
            $deliveryNote->reference = $request->reference;
            $deliveryNote->purchase_date   = $request->purchase_date;
            $deliveryNote->delivery_date   = $request->delivery_date;
            $deliveryNote->total_amount = $request->total_amount;
            $deliveryNote->observation = $request->observation;
            $deliveryNote->place_of_delivery = $request->place_of_delivery;
            $deliveryNote->purchase_coupon_id = $request->purchase_coupon;
            $deliveryNote->save();

            $productDeliveryNotes = [];
            foreach ($request->ordered_product as $key => $product) {
                $productDeliveryNote = new ProductDeliveryNote();
                $productDeliveryNote->quantity = $request->quantities[$key];
                $productDeliveryNote->unit_price = $request->unit_prices[$key];
                $productDeliveryNote->product_id = $product;
                $productDeliveryNote->purchase_order_id = $deliveryNote->id;
                $productDeliveryNote->save();

                array_push($productDeliveryNotes, $productDeliveryNote);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'deliveryNote' => $deliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productDeliveryNotes' => $productDeliveryNotes],
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
        $deliveryNote = DeliveryNote::with('purchaseCoupon')->with('productDeliveryNotes')->findOrFail($id);
        $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null; //ProductDeliveryNote::where('purchase_order_id', $deliveryNote->id)->get();

        return new JsonResponse([
            'deliveryNote' => $deliveryNote,
            'datas' => ['productDeliveryNotes' => $productDeliveryNotes]
        ], 200);
    }

    public function edit($id)
    {
        $deliveryNote = DeliveryNote::with('purchaseCoupon')->with('productDeliveryNotes')->findOrFail($id);
        $purchaseCoupons = PurchaseCoupon::with('provider')->with('purchaseOrder')->with('deliveryNotes')->with('productPurchaseCoupons')->orderBy('purchase_date')->get();
        $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null;

        return new JsonResponse([
            'deliveryNote' => $deliveryNote,
            'datas' => ['deliveryNote' => $deliveryNote, 'productDeliveryNotes' => $productDeliveryNotes, 'purchaseCoupons' => $purchaseCoupons]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $currentDate = date('Y-m-d', strtotime(now()));
        $deliveryNote = DeliveryNote::findOrFail($id);
        $this->validate(
            $request,
            [
                'purchase_coupon'=>'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_date' => 'required|date|date_format:Y-m-d|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'purchase_coupon.required'=>"Le choix d'un bon de livraison est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon de livraison est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'purchase_date.date_equals' => "La date du bon de livraison ne peut être qu'aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
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
            $deliveryNote->reference = $request->reference;
            $deliveryNote->purchase_date   = $request->purchase_date;
            $deliveryNote->delivery_date   = $request->delivery_date;
            $deliveryNote->total_amount = $request->total_amount;
            $deliveryNote->observation = $request->observation;
            $deliveryNote->place_of_delivery = $request->place_of_delivery;
            $deliveryNote->purchase_coupon_id = $request->purchase_coupon;
            $deliveryNote->save();

            ProductDeliveryNote::where('purchase_order_id', $deliveryNote->id)->delete();

            $productDeliveryNotes = [];
            foreach ($request->ordered_product as $key => $product) {
                $productDeliveryNote = new ProductDeliveryNote();
                $productDeliveryNote->quantity = $request->quantities[$key];
                $productDeliveryNote->unit_price = $request->unit_prices[$key];
                $productDeliveryNote->product_id = $product;
                $productDeliveryNote->purchase_order_id = $deliveryNote->id;
                $productDeliveryNote->save();

                array_push($productDeliveryNotes, $productDeliveryNote);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'deliveryNote' => $deliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productDeliveryNotes' => $productDeliveryNotes],
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
        $deliveryNote = DeliveryNote::findOrFail($id);
        $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null;
        try {
            $deliveryNote->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'deliveryNote' => $deliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productDeliveryNotes' => $productDeliveryNotes],
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
