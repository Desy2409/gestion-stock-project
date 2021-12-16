<?php

namespace App\Http\Controllers;

use App\Http\Traits\StockTrait;
use App\Http\Traits\UtilityTrait;
use App\Mail\ClientDeliveryNoteValidationMail;
use App\Models\ClientDeliveryNote;
use App\Models\ClientDeliveryNoteRegister;
use App\Models\Product;
use App\Models\ProductClientDeliveryNote;
use App\Models\ProductSale;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Stock;
use App\Repositories\ClientDeliveryNoteRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientDeliveryNoteController extends Controller
{
    use UtilityTrait;
    use StockTrait;

    public $clientDeliveryNoteRepository;

    public function __construct(ClientDeliveryNoteRepository $clientDeliveryNoteRepository)
    {
        $this->clientDeliveryNoteRepository = $clientDeliveryNoteRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_READ', ClientDeliveryNote::class);
        // $sales = Sale::with('provider')->with('purchaseOrder')->with('clientDeliveryNotes')->with('productSales')->get();
        $clientDeliveryNotes = ClientDeliveryNote::with('sale')->with('productClientDeliveryNotes')->orderBy('delivery_date')->get();
        $purchasesBasedOnPurchaseOrderId = Sale::select('purchase_order_id')->distinct()->where('purchase_order_id', '!=', null)->pluck('purchase_order_id')->toArray();
        $purchaseOrders = PurchaseOrder::whereIn('id', $purchasesBasedOnPurchaseOrderId)->with('client')->with('sales')->orderBy('code')->orderBy('purchase_date')->get();

        $lastClientDeliveryNoteRegister = ClientDeliveryNoteRegister::latest()->first();

        $clientDeliveryNoteRegister = new ClientDeliveryNoteRegister();
        if ($lastClientDeliveryNoteRegister) {
            $clientDeliveryNoteRegister->code = $this->formateNPosition('BL', $lastClientDeliveryNoteRegister->id + 1, 8);
        } else {
            $clientDeliveryNoteRegister->code = $this->formateNPosition('BL', 1, 8);
        }
        $clientDeliveryNoteRegister->save();

        return new JsonResponse([
            'datas' => ['clientDeliveryNotes' => $clientDeliveryNotes, 'purchaseOrders' => $purchaseOrders]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_READ', ClientDeliveryNote::class);
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

    public function datasOnSelectPurchaseOrder($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', DeliveryNote::class);
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $sale = Sale::where('purchase_order_id', $purchaseOrder->id)->first();

        $productSales = ProductSale::with('product')->with('unity')->where('sale_id', $sale->id)->get();
        return new JsonResponse([
            'sale' => $sale, 'datas' => ['productSales' => $productSales]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_CREATE', ClientDeliveryNote::class);
        $this->validate(
            $request,
            [
                'sale' => 'required',
                'reference' => 'required|unique:client_delivery_notes',
                'delivery_date' => 'required|date|before:today', //|date_format:Ymd
                'total_amount' => 'required',
                'observation' => 'max:255',
                'clientDeliveryNoteProducts' => 'required',
                // 'quantities' => 'required|min:0',
                // 'unities' => 'required',
            ],
            [
                'sale.required' => "Le choix d'un bon de vente est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'reference.unique' => "Ce bon de livraison existe déjà.",
                'delivery_date.required' => "La date de livraison effective est obligatoire.",
                'delivery_date.before' => "La date du bon de livraison doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                // 'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'clientDeliveryNoteProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                // 'quantities.required' => "Les quantités sont obligatoires.",
                // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            $sale = Sale::where('purchase_order_id', $request->purchase_order)->first();
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

            $productClientDeliveryNotes = [];
            foreach ($request->clientDeliveryNoteProducts as $key => $product) {
                $productClientDeliveryNote = new ProductClientDeliveryNote();
                $productClientDeliveryNote->quantity = $product["quantity"];
                $productClientDeliveryNote->unity_id = $product["unity"]["id"];
                $productClientDeliveryNote->product_id = $product["product"]["id"];
                $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
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
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_READ', ClientDeliveryNote::class);
        $clientDeliveryNote = ClientDeliveryNote::with('sale')->with('productClientDeliveryNotes')->findOrFail($id);
        $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null; //ProductClientDeliveryNote::where('purchase_order_id', $clientDeliveryNote->id)->get();

        $email = 'tes@mailinator.com';
        Mail::to($email)->send(new ClientDeliveryNoteValidationMail($clientDeliveryNote, $productClientDeliveryNotes));

        return new JsonResponse([
            'clientDeliveryNote' => $clientDeliveryNote,
            'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_READ', Sale::class);
        $clientDeliveryNote = ClientDeliveryNote::with('sale')->with('salePoint')->findOrFail($id);
        $productClientDeliveryNotes = ProductClientDeliveryNote::where('client_delivery_note_id', $clientDeliveryNote->id)->with('product')->with('unity')->get();
        return new JsonResponse([
            'clientDeliveryNote' => $clientDeliveryNote,
            'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_UPDATE', ClientDeliveryNote::class);
        $clientDeliveryNote = ClientDeliveryNote::findOrFail($id);
        $this->validate(
            $request,
            [
                'sale' => 'required',
                'reference' => 'required',
                'delivery_date' => 'required|date|before:today', //|date_format:Ymd
                'total_amount' => 'required',
                'observation' => 'max:255',
                'clientDeliveryNoteProducts' => 'required',
                // 'quantities' => 'required|min:0',
                // 'unities' => 'required',
            ],
            [
                'sale.required' => "Le choix d'un bon de vente est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'delivery_date.required' => "La date de livraison effective est obligatoire.",
                'delivery_date.date' => "La date du bon de livraison est incorrecte.",
                // 'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.before' => "La date du bon de livraison doit être antérieure ou égale à aujourd'hui.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'clientDeliveryNoteProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                // 'quantities.required' => "Les quantités sont obligatoires.",
                // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        try {
            $sale = Sale::where('purchase_order_id', $request->purchase_order)->first();

            $clientDeliveryNote->reference = $request->reference;
            $clientDeliveryNote->delivery_date   = $request->delivery_date;
            $clientDeliveryNote->total_amount = $request->total_amount;
            $clientDeliveryNote->observation = $request->observation;
            $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
            $clientDeliveryNote->sale_id = $sale->id;
            $clientDeliveryNote->save();

            ProductClientDeliveryNote::where('client_delivery_note_id', $clientDeliveryNote->id)->delete();

            $productClientDeliveryNotes = [];
            foreach ($request->clientDeliveryNoteProducts as $key => $product) {
                $productClientDeliveryNote = new ProductClientDeliveryNote();
                $productClientDeliveryNote->quantity = $product["quantity"];
                $productClientDeliveryNote->unity_id = $product["unity"];
                $productClientDeliveryNote->product_id = $product["product"];
                $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
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
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_DELETE', ClientDeliveryNote::class);
        $clientDeliveryNote = ClientDeliveryNote::findOrFail($id);
        $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null;
        try {
            $success = false;
            $message = "";
            if (empty($clientDeliveryNote->productClientDeliveryNotes) || sizeof($clientDeliveryNote->productClientDeliveryNotes) == 0) {
                // dd('delete');
                $clientDeliveryNote->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette livraison ne peut être supprimée car elle a servi dans des traitements.";
            }

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

    public function validateClientDeliveryNote($id)
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_VALIDATE', ClientDeliveryNote::class);
        $clientDeliveryNote = ClientDeliveryNote::findOrFail($id);
        try {
            $clientDeliveryNote->state = 'S';
            $clientDeliveryNote->date_of_processing = date('Y-m-d', strtotime(now()));
            $clientDeliveryNote->save();

            $this->decrement($clientDeliveryNote);

            $success = true;
            $message = "Bon de livraison validé avec succès.";
            return new JsonResponse([
                'clientDeliveryNote' => $clientDeliveryNote,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la validation du bon de livraison.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function rejectClientDeliveryNote($id)
    {
        $this->authorize('ROLE_CLIENT_DELIVERY_NOTE_REJECT', ClientDeliveryNote::class);
        $clientDeliveryNote = ClientDeliveryNote::findOrFail($id);
        try {
            $clientDeliveryNote->state = 'A';
            $clientDeliveryNote->date_of_processing = date('Y-m-d', strtotime(now()));
            $clientDeliveryNote->save();

            $success = true;
            $message = "Bon de livraison annulé avec succès.";
            return new JsonResponse([
                'clientDeliveryNote' => $clientDeliveryNote,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'annulation du bon de livraison.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function returnOfMerchandises($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_REJECT', ClientDeliveryNote::class);
        $clientDeliveryNote = ClientDeliveryNote::where('id', $id)->where('state', '=', 'S')->first();
        // dd($clientDeliveryNote);
        try {

            $this->decrementByRetunringClientDeliveryNote($clientDeliveryNote);

            $success = true;
            $message = "Marchandises rendues avec succès.";
            return new JsonResponse([
                'clientDeliveryNote' => $clientDeliveryNote,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            dd($e);
            $success = false;
            $message = "Erreur survenue lors du retour des marchandises.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function clientDeliveryNoteReports(Request $request)
    {
        try {
            $clientDeliveryNotes = $this->clientDeliveryNoteRepository->clientDeliveryNoteReport($request->code, $request->reference, $request->delivery_date, $request->date_of_processing, $request->total_amount, $request->state, $request->observation, $request->sale, $request->tourn, $request->start_delivery_date, $request->end_delivery_date, $request->start_processing_date, $request->end_processing_date);
            return new JsonResponse(['datas' => ['clientDeliveryNotes' => $clientDeliveryNotes]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
