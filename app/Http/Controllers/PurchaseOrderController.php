<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileTrait;
use App\Http\Traits\ProcessingTrait;
use App\Http\Traits\UtilityTrait;
use App\Mail\PurchaseOrderValidationMail;
use App\Models\Client;
use App\Models\Folder;
use App\Models\Product;
use App\Models\ProductPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderRegister;
use App\Models\SalePoint;
use App\Models\Unity;
use App\Repositories\PurchaseOrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PurchaseOrderController extends Controller
{

    use UtilityTrait;
    use ProcessingTrait;
    use FileTrait;

    public $purchaseOrderRepository;
    protected $prefix;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->user = Auth::user();
        $this->prefix = PurchaseOrder::$code;
    }

    public function index()
    {
        $this->authorize('ROLE_PURCHASE_ORDER_READ', PurchaseOrder::class);
        // $purchaseOrders = PurchaseOrder::with('client')->with('salePoint')->with('productPurchaseOrders')->orderBy('purchase_date')->get();
        $purchaseOrders = PurchaseOrder::orderBy('purchase_date')->get();
        $clients = Client::with('person')->get();
        $products = Product::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $unities = Unity::orderBy('wording')->get();

        $lastPurchaseOrderRegister = PurchaseOrderRegister::latest()->first();

        $purchaseOrderRegister = new PurchaseOrderRegister();
        if ($lastPurchaseOrderRegister) {
            $purchaseOrderRegister->code = $this->formateNPosition($this->prefix, $lastPurchaseOrderRegister->id + 1, 8);
        } else {
            $purchaseOrderRegister->code = $this->formateNPosition($this->prefix, 1, 8);
        }
        $purchaseOrderRegister->save();

        return new JsonResponse([
            'datas' => ['purchaseOrders' => $purchaseOrders, 'clients' => $clients, 'products' => $products, 'salePoints' => $salePoints, 'unities' => $unities]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_PURCHASE_ORDER_READ', PurchaseOrder::class);
        $lastPurchaseOrderRegister = PurchaseOrderRegister::latest()->first();
        if ($lastPurchaseOrderRegister) {
            $code = $this->formateNPosition($this->prefix, $lastPurchaseOrderRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition($this->prefix,  1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_PURCHASE_ORDER_CREATE', PurchaseOrder::class);
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
                'productPurchaseOrders' => 'required',
                // 'quantity' => 'required|min:0',
                // 'unit_price' => 'required|min:0',
                // 'unity' => 'required',
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
                'productPurchaseOrders.required' => "Vous devez ajouter au moins un produit au panier.",
                // 'quantity.required' => "Les quantités sont obligatoires.",
                // 'quantity.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unit_price.required' => "Les prix unitaires sont obligatoires.",
                // 'unit_price.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                // 'unity.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        // if (sizeof($request->productPurchaseOrders) != sizeof($request->quantity) || sizeof($request->productPurchaseOrders) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantity)) {
        //     $success = false;
        //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
        //     return new JsonResponse([
        //         'success' => $success,
        //         'message' => $message,
        //     ]);
        // }

        try {
            $lastPurchaseOrder = PurchaseOrder::latest()->first();

            $purchaseOrder = new PurchaseOrder();
            if ($lastPurchaseOrder) {
                $purchaseOrder->code = $this->formateNPosition($this->prefix, $lastPurchaseOrder->id + 1, 8);
            } else {
                $purchaseOrder->code = $this->formateNPosition($this->prefix, 1, 8);
            }
            $purchaseOrder->reference = $request->reference;
            $purchaseOrder->purchase_date = $request->purchase_date;
            $purchaseOrder->delivery_date = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->client_id = $request->client;
            $purchaseOrder->sale_point_id = $request->sale_point;
            $purchaseOrder->save();

            // dd($purchaseOrder);

            $productsPurchaseOrders = [];

            foreach ($request->productPurchaseOrders as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $product["quantity"];
                $productPurchaseOrder->unit_price = $product["unit_price"];
                $productPurchaseOrder->product_id = $product["product_id"];
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productPurchaseOrder->unity_id = $product["unity_id"];
                $productPurchaseOrder->save();

                array_push($productsPurchaseOrders, $productPurchaseOrder);
            }

            // $savedProductPurchaseOrders = ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->get();
            // if (empty($savedProductPurchaseOrders) || sizeof($savedProductPurchaseOrders) == 0) {
            //     $purchaseOrder->delete();
            // }

            $folder = Folder::findOrFail($request->folder);

            $check = $this->checkFileType($purchaseOrder);
            if (!$check) {
                $success = false;
                $message = "Les formats de fichiers autorisés sont : pdf,docx et xls";
                return new JsonResponse(['success' => $success, 'message' => $message], 400);
            } else {
                $this->storeFile($this->user, $purchaseOrder, $folder, $request->upload_files);
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
            // dd($e);
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
        $this->authorize('ROLE_PURCHASE_ORDER_READ', PurchaseOrder::class);
        $purchaseOrder = PurchaseOrder::with('client')->with('salePoint')->with('productPurchaseOrders')->findOrFail($id);
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productPurchaseOrders : null; //ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->get();

        $email = 'tes@mailinator.com';
        Mail::to($email)->send(new PurchaseOrderValidationMail($purchaseOrder, $productsPurchaseOrders));


        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_PURCHASE_ORDER_READ', PurchaseOrder::class);
        $purchaseOrder = PurchaseOrder::with('productPurchaseOrders')->findOrFail($id);
        // $clients = Client::with('person')->get();
        // $products = Product::with('subCategory')->orderBy('wording')->get();
        // $productsPurchaseOrders = ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->with('product')->with('unity')->get();

        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder,
            // 'datas' => ['clients' => $clients, 'products' => $products, 'productsPurchaseOrders' => $productsPurchaseOrders]
            // 'datas' => ['productsPurchaseOrders' => $productsPurchaseOrders]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PURCHASE_ORDER_UPDATE', PurchaseOrder::class);
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
                'productPurchaseOrders' => 'required',
                // 'quantity' => 'required|min:0',
                // 'unit_price' => 'required|min:0',
                // 'unity' => 'required',
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
                'productPurchaseOrders.required' => "Vous devez ajouter au moins un produit au panier.",
                // 'quantity.required' => "Les quantités sont obligatoires.",
                // 'quantity.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unit_price.required' => "Les prix unitaires sont obligatoires.",
                // 'unit_price.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
            ]
        );

        // if (sizeof($request->productPurchaseOrders) != sizeof($request->quantity) || sizeof($request->productPurchaseOrders) != sizeof($request->unit_price) || sizeof($request->unit_price) != sizeof($request->quantity)) {
        //     $success = false;
        //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
        //     return new JsonResponse([
        //         'success' => $success,
        //         'message' => $message,
        //     ]);
        // }

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
            foreach ($request->productPurchaseOrders as $key => $product) {
                $productPurchaseOrder = new ProductPurchaseOrder();
                $productPurchaseOrder->quantity = $product['quantity'];
                $productPurchaseOrder->unit_price = $product['unit_price'];
                $productPurchaseOrder->unity_id = $product["unity_id"];
                $productPurchaseOrder->product_id = $product['product_id'];
                $productPurchaseOrder->purchase_order_id = $purchaseOrder->id;
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
        $this->authorize('ROLE_PURCHASE_ORDER_DELETE', PurchaseOrder::class);
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productsPurchaseOrders = $purchaseOrder ? $purchaseOrder->productsPurchaseOrders : null;
        try {
            $success = false;
            $message = "";
            if (
                empty($productsPurchaseOrders) || sizeof($productsPurchaseOrders) == 0 &&
                empty($purchaseOrder->sales) || sizeof($purchaseOrder->sales) == 0
            ) {
                // dd('delete');
                $purchaseOrder->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette commande ne peut être supprimée car elle a servi dans des traitements.";
            }

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


    public function purchaseOrderProcessing($id, $action)
    {
        try {
            $this->processing(PurchaseOrder::class, $id, $action);

            $success = true;
            if ($action == 'validate') {
                $message = "Commande validée avec succès.";
            }
            if ($action == 'reject') {
                $message = "Commande rejetée avec succès.";
            }
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            if ($action == 'validate') {
                $message = "Erreur survenue lors de la validation de la commande.";
            }
            if ($action == 'reject') {
                $message = "Erreur survenue lors de l'annulation de la commande.";
            }
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function purchaseOrderReports(Request $request)
    {
        $this->authorize('ROLE_PURCHASE_ORDER_PRINT', PurchaseOrder::class);
        try {
            $purchaseOrders = $this->purchaseOrderRepository->purchaseOrderReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['purchaseOrders' => $purchaseOrders]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
