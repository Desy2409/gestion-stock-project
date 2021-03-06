<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileTrait;
use App\Http\Traits\ProcessingTrait;
use App\Http\Traits\StockTrait;
use App\Http\Traits\UtilityTrait;
use App\Mail\DeliveryNoteValidationMail;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteRegister;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductDeliveryNote;
use App\Models\ProductPurchase;
use App\Models\Purchase;
use App\Models\Stock;
use App\Repositories\DeliveryNoteRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DeliveryNoteController extends Controller
{
    use UtilityTrait;
    use StockTrait;
    use ProcessingTrait;
    use FileTrait;

    public $deliveryNoteRepository;

    public function __construct(DeliveryNoteRepository $deliveryNoteRepository)
    {
        $this->deliveryNoteRepository = $deliveryNoteRepository;
        $this->user = Auth::user();
    }

    public function index()
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', DeliveryNote::class);
        $deliveryNotes = DeliveryNote::orderBy('created_at','desc')->with('purchase')->with('productDeliveryNotes')->orderBy('code')->orderBy('delivery_date')->get();
        $purchasesBasedOnOrderId = Purchase::select('order_id')->distinct()->where('order_id', '!=', null)->pluck('order_id')->toArray();
        $orders = Order::whereIn('id', $purchasesBasedOnOrderId)->with('provider')->with('purchases')->orderBy('code')->orderBy('order_date')->get();
        // dd($orders->pluck('id')->toArray());
        // dd(App::getLocale());

        $lastDeliveryNoteRegister = DeliveryNoteRegister::latest()->first();

        $deliveryNoteRegister = new DeliveryNoteRegister();
        if ($lastDeliveryNoteRegister) {
            $deliveryNoteRegister->code = $this->formateNPosition(DeliveryNote::class, $lastDeliveryNoteRegister->id + 1);
        } else {
            $deliveryNoteRegister->code = $this->formateNPosition(DeliveryNote::class, 1);
        }
        $deliveryNoteRegister->save();

        return new JsonResponse([
            'datas' => ['deliveryNotes' => $deliveryNotes, 'orders' => $orders]
        ], 200);
    }


    public function showNextCode()
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', DeliveryNote::class);
        $lastDeliveryNoteRegister = DeliveryNoteRegister::latest()->first();
        if ($lastDeliveryNoteRegister) {
            $code = $this->formateNPosition(DeliveryNote::class, $lastDeliveryNoteRegister->id + 1);
        } else {
            $code = $this->formateNPosition(DeliveryNote::class, 1);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function datasOnSelectOrder($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', DeliveryNote::class);
        $order = Order::findOrFail($id);
        $purchase = Purchase::where('order_id', $order->id)->first();

        // dd($purchase->verifyQuantity);

        $productPurchases = ProductPurchase::with('product')->with('unity')->where('purchase_id', $purchase->id)->get();
        // $array = [];

        // foreach ($productPurchases as $key => $value) {
        //     array_push($array, $value::remainingQuantity());
        // }
        // dd($array);
        return new JsonResponse([
            'purchase' => $purchase, 'datas' => ['productPurchases' => $productPurchases]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_CREATE', DeliveryNote::class);
        try {
            $validation = $this->validator('store', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $purchase = Purchase::where('order_id', $request->order)->first();
                $purchaseProducts = $purchase ? $purchase->productPurchases : null;
                $oldDeliveryNotes = DeliveryNote::where('purchase_id', $purchase->id)->get();

                $lastDeliveryNote = DeliveryNote::latest()->first();

                $deliveryNote = new DeliveryNote();
                if ($lastDeliveryNote) {
                    $deliveryNote->code = $this->formateNPosition(DeliveryNote::class, $lastDeliveryNote->id + 1);
                } else {
                    $deliveryNote->code = $this->formateNPosition(DeliveryNote::class, 1);
                }
                $deliveryNote->reference = $request->reference;
                $deliveryNote->delivery_date   = $request->delivery_date;
                $deliveryNote->total_amount = $request->total_amount;
                $deliveryNote->observation = $request->observation;
                $deliveryNote->place_of_delivery = $request->place_of_delivery;
                $deliveryNote->purchase_id = $purchase->id;
                $deliveryNote->save();

                $productDeliveryNotes = [];
                foreach ($request->deliveryNoteProducts as $key => $product) {
                    // if (in_array($product["product"], $purchaseProducts->pluck('product_id')->toArray())) {

                    // }

                    $productDeliveryNote = new ProductDeliveryNote();
                    $productDeliveryNote->quantity = $product["quantity"];
                    $productDeliveryNote->unity_id = $product["unity"]["id"];
                    $productDeliveryNote->product_id = $product["product"]["id"];
                    $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                    $productDeliveryNote->save();

                    array_push($productDeliveryNotes, $productDeliveryNote);
                }

                // $savedProductDeliveryNotes = ProductDeliveryNote::where('purchase_id', $purchase->id)->get();
                // if (empty($savedProductDeliveryNotes) || sizeof($savedProductDeliveryNotes) == 0) {
                //     $deliveryNote->delete();
                // }

                $folder = Folder::findOrFail($request->folder);

                $check = $this->checkFileType($deliveryNote);
                if (!$check) {
                    $success = false;
                    $message = "Les formats de fichiers autoris??s sont : pdf,docx et xls";
                    return new JsonResponse(['success' => $success, 'message' => $message], 400);
                } else {
                    $this->storeFile($this->user, $deliveryNote, $folder, $request->upload_files);
                }

                $message = "Enregistrement effectu?? avec succ??s.";
                return new JsonResponse([
                    'deliveryNote' => $deliveryNote,
                    'success' => true,
                    'message' => $message,
                    'datas' => ['productDeliveryNotes' => $productDeliveryNotes],
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', DeliveryNote::class);
        $deliveryNote = DeliveryNote::with('purchase')->with('productDeliveryNotes')->findOrFail($id);
        $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null; //ProductDeliveryNote::where('purchase_id', $purchase->id)->get();

        $email = 'tes@mailinator.com';
        Mail::to($email)->send(new DeliveryNoteValidationMail($deliveryNote, $productDeliveryNotes));


        return new JsonResponse([
            'deliveryNote' => $deliveryNote,
            'datas' => ['productDeliveryNotes' => $productDeliveryNotes]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', Sale::class);
        $deliveryNote = DeliveryNote::with('purchase')->findOrFail($id);
        $productDeliveryNotes = ProductDeliveryNote::where('delivery_note_id', $deliveryNote->id)->with('product')->with('unity')->get();
        return new JsonResponse([
            'deliveryNote' => $deliveryNote,
            'datas' => ['productDeliveryNotes' => $productDeliveryNotes]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_UPDATE', DeliveryNote::class);
        $deliveryNote = DeliveryNote::findOrFail($id);

        try {
            $validation = $this->validator('update', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $purchase = Purchase::where('order_id', $request->order)->first();

                $deliveryNote->reference = $request->reference;
                $deliveryNote->delivery_date = $request->delivery_date;
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
                    $productDeliveryNote->unity_id =  $product["unity"]["id"];
                    $productDeliveryNote->product_id = $product["product"]["id"];
                    $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                    $productDeliveryNote->save();

                    array_push($productDeliveryNotes, $productDeliveryNote);
                }

                // $savedProductDeliveryNotes = ProductDeliveryNote::where('purchase_id', $purchase->id)->get();
                // if (empty($savedProductDeliveryNotes) || sizeof($savedProductDeliveryNotes) == 0) {
                //     $deliveryNote->delete();
                // }

                $message = "Modification effectu??e avec succ??s.";
                return new JsonResponse([
                    'deliveryNote' => $deliveryNote,
                    'success' => true,
                    'message' => $message,
                    'datas' => ['productDeliveryNotes' => $productDeliveryNotes],
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_DELETE', DeliveryNote::class);
        $deliveryNote = DeliveryNote::findOrFail($id);
        $productDeliveryNotes = $deliveryNote ? $deliveryNote->productDeliveryNotes : null;
        try {
            $success = false;
            $message = "";
            if (empty($deliveryNote->productDeliveryNotes) || sizeof($deliveryNote->productDeliveryNotes) == 0) {
                // dd('delete');
                $deliveryNote->delete();
                $success = true;
                $message = "Suppression effectu??e avec succ??s.";
            } else {
                // dd('not delete');
                $message = "Cette livraison ne peut ??tre supprim??e car elle a servi dans des traitements.";
            }

            return new JsonResponse([
                'deliveryNote' => $deliveryNote,
                'success' => $success,
                'message' => $message,
                'datas' => ['productDeliveryNotes' => $productDeliveryNotes],
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function deliveryNoteProcessing($id, $action)
    {
        try {
            $this->processing(DeliveryNote::class, $id, $action);

            if ($action == 'validate') {
                $message = "Livraison valid??e avec succ??s.";
            }
            if ($action == 'reject') {
                $message = "Livraison rejet??e avec succ??s.";
            }
            return new JsonResponse([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            if ($action == 'validate') {
                $message = "Erreur survenue lors de la validation de la livraison.";
            }
            if ($action == 'reject') {
                $message = "Erreur survenue lors de l'annulation de la livraison.";
            }
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function returnOfMerchandises($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_REJECT', DeliveryNote::class);
        $deliveryNote = DeliveryNote::where('id', $id)->where('state', '=', 'S')->first();
        // dd($deliveryNote);
        try {

            $this->decrementByRetunringDeliveryNote($deliveryNote);

            $message = "Marchandises rendues avec succ??s.";
            return new JsonResponse([
                'deliveryNote' => $deliveryNote,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors du retour des marchandises.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function deliveryNoteReports(Request $request)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_PRINT', DeliveryNote::class);
        try {
            $deliveryNotes = $this->deliveryNoteRepository->deliveryNoteReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['deliveryNotes' => $deliveryNotes]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'purchase' => 'required',
                    'reference' => 'required|unique:delivery_notes',
                    // 'purchase_date' => 'required|date|date_format:Ymd|before:today',
                    'delivery_date' => 'required|date|before:today', //|after:',
                    'total_amount' => 'required',
                    'observation' => 'max:255',
                    'deliveryNoteProducts' => 'required',
                    // 'quantities' => 'required|min:0',
                    // 'unities' => 'required',
                ],
                [
                    'purchase.required' => "Le choix d'un bon de commande est obligatoire.",
                    'reference.required' => "La r??f??rence du bon est obligatoire.",
                    'reference.unique' => "Ce bon de livraison existe d??j??.",
                    'delivery_date.required' => "La date de livraison pr??vue est obligatoire.",
                    'delivery_date.date' => "La date de livraison est incorrecte.",
                    // 'delivery_date.after' => "La date de livraison doit ??tre ult??rieure ?? la date du bon de livraison.",
                    'delivery_date.before' => "La date de livraison doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                    'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas d??passer 255 caract??res.",
                    'deliveryNoteProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantit??s sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantit??s ne peut ??tre inf??rieur ?? 0.",
                    // 'unities.required' => "Veuillez d??finir des unit??s ?? tous les produits ajout??s.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'purchase' => 'required',
                    'reference' => 'required',
                    // 'purchase_date' => 'required|date|date_format:Ymd|before:today',
                    'delivery_date' => 'required|date|before:today',
                    'total_amount' => 'required',
                    'observation' => 'max:255',
                    'deliveryNoteProducts' => 'required',
                    // 'quantities' => 'required|min:0',
                    // 'unities' => 'required',
                ],
                [
                    'purchase.required' => "Le choix d'un bon de commande est obligatoire.",
                    'reference.required' => "La r??f??rence du bon est obligatoire.",
                    'purchase_date.required' => "La date du bon est obligatoire.",
                    // 'purchase_date.date' => "La date du bon de livraison est incorrecte.",
                    // 'purchase_date.date_format' => "La date livraison doit ??tre sous le format : Ann??e Mois Jour.",
                    // 'purchase_date.before' => "La date du bon de livraison doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                    'delivery_date.required' => "La date de livraison pr??vue est obligatoire.",
                    'delivery_date.date' => "La date de livraison est incorrecte.",
                    'delivery_date.before' => "La date de livraison doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                    // 'delivery_date.after' => "La date livraison doit ??tre ult??rieure ?? la date du bon de livraison.",
                    'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas d??passer 255 caract??res.",
                    'deliveryNoteProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantit??s sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantit??s ne peut ??tre inf??rieur ?? 0.",
                    // 'unities.required' => "Veuillez d??finir des unit??s ?? tous les produits ajout??s.",
                ]
            );
        }
    }
}
