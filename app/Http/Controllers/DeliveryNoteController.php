<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteRegister;
use App\Models\Product;
use App\Models\ProductDeliveryNote;
use App\Models\ProductPurchaseCoupon;
use App\Models\ProductPurchaseOrder;
use App\Models\PurchaseCoupon;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $deliveryNotes = DeliveryNote::with('purchaseCoupon')->with('productDeliveryNotes')->orderBy('code')->orderBy('purchase_date')->get();
        $purchaseOrders = PurchaseOrder::with('provider')->with('purchaseOrder')->orderBy('code')->orderBy('purchase_date')->get();

        $lastDeliveryNoteRegister = DeliveryNoteRegister::latest()->first();

        $deliveryNoteRegister = new DeliveryNoteRegister();
        if ($lastDeliveryNoteRegister) {
            $deliveryNoteRegister->code = $this->formateNPosition('BL', $lastDeliveryNoteRegister->id + 1, 8);
        } else {
            $deliveryNoteRegister->code = $this->formateNPosition('BL', 1, 8);
        }
        $deliveryNoteRegister->save();

        return new JsonResponse([
            'datas' => ['deliveryNotes' => $deliveryNotes, 'purchaseOrders' => $purchaseOrders]
        ], 200);
    }


    public function showNextCode()
    {
        $lastDeliveryNoteRegister = DeliveryNoteRegister::latest()->first();
        if ($lastDeliveryNoteRegister) {
            $code = $this->formateNPosition('BL', $lastDeliveryNoteRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BL', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function productFromPurchaseOrder($id)
    {
        $idOfProducts = ProductPurchaseOrder::where('purchase_order_id', $id)->pluck('product_id')->toArray();
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
                'purchase_order' => 'required',
                'reference' => 'required|unique:delivery_notes',
                'purchase_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'products_of_delivery_note' => 'required',
                'quantities' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'purchase_order.required' => "Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon de livraison existe déjà.",
                'purchase_date.required' => "La date du bon de livraison  est obligatoire.",
                'purchase_date.date' => "La date du bon de livraison est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon de livraison doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'products_of_delivery_note.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {

            $purschaseCoupon = PurchaseCoupon::where('purchase_order_id',$request->purchase_order)->first();

            $lastDeliveryNote = DeliveryNote::latest()->first();

            $deliveryNote = new DeliveryNote();
            if ($lastDeliveryNote) {
                $deliveryNote->code = $this->formateNPosition('BL', $lastDeliveryNote->id + 1, 8);
            } else {
                $deliveryNote->code = $this->formateNPosition('BL', 1, 8);
            }
            $deliveryNote->reference = $request->reference;
            $deliveryNote->purchase_date   = $request->purchase_date;
            $deliveryNote->delivery_date   = $request->delivery_date;
            $deliveryNote->total_amount = $request->total_amount;
            $deliveryNote->observation = $request->observation;
            $deliveryNote->place_of_delivery = $request->place_of_delivery;
            $deliveryNote->purchase_coupon_id = $purschaseCoupon->id;
            $deliveryNote->save();

            $productDeliveryNotes = [];
            foreach ($request->products_of_delivery_note as $key => $product) {
                $productDeliveryNote = new ProductDeliveryNote();
                $productDeliveryNote->quantity = $request->quantities[$key];
                $productDeliveryNote->product_id = $product;
                $productDeliveryNote->purchase_order_id = $deliveryNote->id;
                $productDeliveryNote->unity_id = $request->unities[$key];
                $productDeliveryNote->save();

                array_push($productDeliveryNotes, $productDeliveryNote);
            }

            $savedProductDeliveryNotes = ProductDeliveryNote::where('purchase_coupon_id', $deliveryNote->id)->get();
            if (empty($savedProductDeliveryNotes) || sizeof($savedProductDeliveryNotes) == 0) {
                $deliveryNote->delete();
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

    public function update(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        $this->validate(
            $request,
            [
                'purchase_order' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'products_of_delivery_note' => 'required',
                'quantities' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'purchase_order.required' => "Le choix d'un bon de commande est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon de livraison est incorrecte.",
                'purchase_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon de livraison doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'products_of_delivery_note.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            $purschaseCoupon = PurchaseCoupon::where('purchase_order_id',$request->purchase_order)->first();

            $deliveryNote->reference = $request->reference;
            $deliveryNote->purchase_date   = $request->purchase_date;
            $deliveryNote->delivery_date   = $request->delivery_date;
            $deliveryNote->total_amount = $request->total_amount;
            $deliveryNote->observation = $request->observation;
            $deliveryNote->place_of_delivery = $request->place_of_delivery;
            $deliveryNote->purchase_coupon_id = $purschaseCoupon->id;
            $deliveryNote->save();

            ProductDeliveryNote::where('purchase_order_id', $deliveryNote->id)->delete();

            $productDeliveryNotes = [];
            foreach ($request->products_of_delivery_note as $key => $product) {
                $productDeliveryNote = new ProductDeliveryNote();
                $productDeliveryNote->quantity = $request->quantities[$key];
                $productDeliveryNote->product_id = $product;
                $productDeliveryNote->purchase_order_id = $deliveryNote->id;
                $productDeliveryNote->unity_id = $request->unities[$key];
                $productDeliveryNote->save();

                array_push($productDeliveryNotes, $productDeliveryNote);
            }

            $savedProductDeliveryNotes = ProductDeliveryNote::where('purchase_coupon_id', $deliveryNote->id)->get();
            if (empty($savedProductDeliveryNotes) || sizeof($savedProductDeliveryNotes) == 0) {
                $deliveryNote->delete();
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
