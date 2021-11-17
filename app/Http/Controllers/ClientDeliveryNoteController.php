<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\ClientDeliveryNote;
use App\Models\ClientDeliveryNoteRegister;
use App\Models\Product;
use App\Models\ProductClientDeliveryNote;
use App\Models\ProductSale;
use App\Models\Sale;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientDeliveryNoteController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $sales = Sale::with('provider')->with('purchaseOrder')->with('clientDeliveryNotes')->with('productSales')->get();
        $clientDeliveryNotes = ClientDeliveryNote::with('sale')->with('productClientDeliveryNotes')->orderBy('delivery_note_date')->get();

        $lastClientDeliveryNoteRegister = ClientDeliveryNoteRegister::latest()->first();

        $clientDeliveryNoteRegister = new ClientDeliveryNoteRegister();
        if ($lastClientDeliveryNoteRegister) {
            $clientDeliveryNoteRegister->code = $this->formateNPosition('BL', $lastClientDeliveryNoteRegister->id + 1, 8);
        } else {
            $clientDeliveryNoteRegister->code = $this->formateNPosition('BL', 1, 8);
        }
        $clientDeliveryNoteRegister->save();

        return new JsonResponse([
            'datas' => ['clientDeliveryNotes' => $clientDeliveryNotes, 'sales' => $sales]
        ], 200);
    }

    public function showNextCode()
    {
        $lastClientDeliveryNoteRegister = ClientDeliveryNoteRegister::latest()->first();
        if ($lastClientDeliveryNoteRegister) {
            $code = $this->formateNPosition('BL', $lastClientDeliveryNoteRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BL', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function showProductOfSale($id)
    {
        $idOfProducts = ProductSale::where('sale_id', $id)->pluck('product_id')->toArray();
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
                'sale' => 'required',
                'reference' => 'required|unique:client_delivery_notes',
                'delivery_note_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:delivery_note_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'sale.required' => "Le choix d'un bon de vente est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon de livraison existe déjà.",
                'delivery_note_date.required' => "La date du bon de livraison  est obligatoire.",
                'delivery_note_date.date' => "La date du bon de livraison est incorrecte.",
                'delivery_note_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_note_date.before' => "La date du bon de livraison doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            $lastClientDeliveryNote = ClientDeliveryNote::latest()->first();

            $clientDeliveryNote = new ClientDeliveryNote();
            if ($lastClientDeliveryNote) {
                $clientDeliveryNote->code = $this->formateNPosition('BL', $lastClientDeliveryNote->id + 1, 8);
            } else {
                $clientDeliveryNote->code = $this->formateNPosition('BL', 1, 8);
            }
            $clientDeliveryNote->reference = $request->reference;
            $clientDeliveryNote->delivery_note_date   = $request->delivery_note_date;
            $clientDeliveryNote->delivery_date   = $request->delivery_date;
            $clientDeliveryNote->total_amount = $request->total_amount;
            $clientDeliveryNote->observation = $request->observation;
            $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
            $clientDeliveryNote->sale_id = $request->sale;
            $clientDeliveryNote->save();

            $productClientDeliveryNotes = [];
            foreach ($request->ordered_product as $key => $product) {
                $productClientDeliveryNote = new ProductClientDeliveryNote();
                $productClientDeliveryNote->quantity = $request->quantities[$key];
                $productClientDeliveryNote->product_id = $product;
                $productClientDeliveryNote->purchase_order_id = $clientDeliveryNote->id;
                $productClientDeliveryNote->unity_id = $request->unities[$key];
                $productClientDeliveryNote->save();

                array_push($productClientDeliveryNotes, $productClientDeliveryNote);
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'clientDeliveryNote' => $clientDeliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes],
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
        $clientDeliveryNote = ClientDeliveryNote::with('sale')->with('productClientDeliveryNotes')->findOrFail($id);
        $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null; //ProductClientDeliveryNote::where('purchase_order_id', $clientDeliveryNote->id)->get();

        return new JsonResponse([
            'clientDeliveryNote' => $clientDeliveryNote,
            'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes]
        ], 200);
    }

    public function edit($id)
    {
        $clientDeliveryNote = ClientDeliveryNote::with('sale')->with('productClientDeliveryNotes')->findOrFail($id);
        $sales = Sale::with('provider')->with('purchaseOrder')->with('clientDeliveryNotes')->with('productSales')->orderBy('delivery_note_date')->get();
        $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null;

        return new JsonResponse([
            'clientDeliveryNote' => $clientDeliveryNote,
            'datas' => ['clientDeliveryNote' => $clientDeliveryNote, 'productClientDeliveryNotes' => $productClientDeliveryNotes, 'sales' => $sales]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $clientDeliveryNote = ClientDeliveryNote::findOrFail($id);
        $this->validate(
            $request,
            [
                'sale' => 'required',
                'reference' => 'required',
                'delivery_note_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:delivery_note_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unities' => 'required',
            ],
            [
                'sale.required' => "Le choix d'un bon de vente est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'delivery_note_date.required' => "La date du bon est obligatoire.",
                'delivery_note_date.date' => "La date du bon de livraison est incorrecte.",
                'delivery_note_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_note_date.before' => "La date du bon de livraison doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de livraison.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            $clientDeliveryNote->reference = $request->reference;
            $clientDeliveryNote->delivery_note_date   = $request->delivery_note_date;
            $clientDeliveryNote->delivery_date   = $request->delivery_date;
            $clientDeliveryNote->total_amount = $request->total_amount;
            $clientDeliveryNote->observation = $request->observation;
            $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
            $clientDeliveryNote->sale_id = $request->sale;
            $clientDeliveryNote->save();

            ProductClientDeliveryNote::where('purchase_order_id', $clientDeliveryNote->id)->delete();

            $productClientDeliveryNotes = [];
            foreach ($request->ordered_product as $key => $product) {
                $productClientDeliveryNote = new ProductClientDeliveryNote();
                $productClientDeliveryNote->quantity = $request->quantities[$key];
                $productClientDeliveryNote->product_id = $product;
                $productClientDeliveryNote->purchase_order_id = $clientDeliveryNote->id;
                $productClientDeliveryNote->unity_id = $request->unities[$key];
                $productClientDeliveryNote->save();

                array_push($productClientDeliveryNotes, $productClientDeliveryNote);
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'clientDeliveryNote' => $clientDeliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes],
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
        $clientDeliveryNote = ClientDeliveryNote::findOrFail($id);
        $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null;
        try {
            $clientDeliveryNote->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'clientDeliveryNote' => $clientDeliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes],
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
