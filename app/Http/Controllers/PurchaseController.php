<?php

namespace App\Http\Controllers;

use App\Http\Traits\ProcessingTrait;
use App\Http\Traits\UtilityTrait;
use App\Mail\PurchaseValidationMail;
use App\Models\DeliveryNote;
use App\Models\Folder;
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
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PurchaseController extends Controller
{
    use UtilityTrait;
    use ProcessingTrait;

    private $directPurchase = "Achat direct";
    private $purchaseOnOrder = "Achat sur commande";

    public $purchaseRepository;
    public function __construct(PurchaseRepository $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
        $this->user = Auth::user();
    }

    public function purchaseOnOrder()
    {
        $purchases = Purchase::with('deliveryNotes')->where('order_id', '!=', null)->orderBy('code')->orderBy('purchase_date')->get();

        $lastPurchaseRegister = PurchaseRegister::latest()->first();

        $purchaseRegister = new PurchaseRegister();
        if ($lastPurchaseRegister) {
            $purchaseRegister->code = $this->formateNPosition('BA', $lastPurchaseRegister->id + 1, 8);
        } else {
            $purchaseRegister->code = $this->formateNPosition('BA', 1, 8);
        }
        $purchaseRegister->save();

        $orders = Order::orderBy('code')->get();
        return new JsonResponse([
            'datas' => ['orders' => $orders, 'purchases' => $purchases]
        ]);
    }

    public function directPurchase()
    {
        $purchases = Purchase::with('deliveryNotes')->where('order_id', '=', null)->orderBy('code')->orderBy('purchase_date')->get();

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
        $products = Product::orderBy('wording')->get();
        $unities = Unity::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['providers' => $providers, 'salePoints' => $salePoints, 'products' => $products, 'purchases' => $purchases, 'unities' => $unities]
        ]);
    }

    public function datasFromOrder($id)
    {
        $this->authorize('ROLE_PURCHASE_READ', Purchase::class);
        $order = Order::with('provider')->with('salePoint')->findOrFail($id);
        $productOrders = ProductOrder::where('order_id', $order->id)->with('product')->with('unity')->get();
        // dd($productOrders);
        // dd($order);
        return new JsonResponse([
            'order' => $order,
            'product_orders' => $productOrders
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

        $email = 'tes@mailinator.com';
        Mail::to($email)->send(new PurchaseValidationMail($purchase, $productPurchases));


        return new JsonResponse([
            'purchase' => $purchase,
            'datas' => ['productPurchases' => $productPurchases]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_PURCHASE_READ', Purchase::class);
        $purchase = Purchase::with('productPurchases')->findOrFail($id);
        $productPurchases = ProductPurchase::where('purchase_id', $purchase->id)->with('product')->with('unity')->get();
        return new JsonResponse([
            'purchase' => $purchase,
            'product_purchases' => $productPurchases
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_PURCHASE_CREATE', Purchase::class);
        if ($request->purchaseType == "Achat direct") {
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
                $purchase->sale_point_id = $request->sale_point;
                $purchase->save();

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

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity_id"];
                    $productPurchase->product_id = $product["product_id"];
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    $productDeliveryNote = new ProductDeliveryNote();
                    $productDeliveryNote->quantity = $product["quantity"];
                    $productDeliveryNote->unity_id = $product["unity_id"];
                    $productDeliveryNote->product_id = $product["product_id"];
                    $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                    $productDeliveryNote->save();

                    array_push($productPurchases, $productPurchase);
                }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    'deliveryNote' => $deliveryNote,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
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
                $purchase->total_amount = $request->total_amount;
                $purchase->amount_gross = $request->amount_gross;
                $purchase->ht_amount = $request->ht_amount;
                $purchase->discount = $request->discount;
                $purchase->amount_token = $request->amount_token;
                $purchase->tva = $request->tva;
                $purchase->observation = $request->observation;
                $purchase->order_id = $order->id;
                $purchase->provider_id = $request->provider;
                $purchase->sale_point_id = $request->sale_point;
                $purchase->save();

                $productPurchases = [];
                $i = 0;
                // dd($request->purchaseProducts);
                foreach ($request->purchaseProducts as $key => $product) {
                    // dd($product[1]["unit_price"]);
                    // dd($product);
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity_id"];
                    $productPurchase->product_id = $product["product_id"];
                    // $productPurchase->quantity = $product[$i]["quantity"];
                    // $productPurchase->unit_price = $product[$i]["unit_price"];
                    // $productPurchase->unity_id = $product[$i]["unity"];
                    // $productPurchase->product_id = $product[$i]["product"];
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();
                    $i++;

                    array_push($productPurchases, $productPurchase);
                }

                // dd($productPurchases);

                // $savedProductPurchases = ProductPurchase::where('purchase_id', $purchase->id)->get();
                // if (empty($savedProductPurchases) || sizeof($savedProductPurchases) == 0) {
                //     $purchase->delete();
                // }

                $folder = Folder::findOrFail($request->folder);

                $check = $this->checkFileType($purchase);
                if (!$check) {
                    $success = false;
                    $message = "Les formats de fichiers autorisés sont : pdf, docx et xls";
                    return new JsonResponse(['success' => $success, 'message' => $message], 400);
                } else {
                    $this->storeFile($this->user, $purchase, $folder, $request->upload_files);
                }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
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
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PURCHASE_UPDATE', Purchase::class);
        $purchase = Purchase::findOrFail($id);
        if ($request->purchaseType == "Achat direct") {
            $this->validate(
                $request,
                [
                    'sale_point' => 'required',
                    'provider' => 'required',
                    'reference' => 'required',
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
                $purchase->sale_point_id = $request->sale_point;
                $purchase->save();

                // $deliveryNote = $purchase ? $purchase->deliveryNote : null;
                // // if ($deliveryNote) {
                // $deliveryNote->reference = $request->reference;
                // $deliveryNote->delivery_date   = $request->delivery_date;
                // $deliveryNote->total_amount = $request->total_amount;
                // $deliveryNote->observation = $request->observation;
                // $deliveryNote->place_of_delivery = $request->place_of_delivery;
                // $deliveryNote->purchase_id = $purchase->id;
                // $deliveryNote->save();

                ProductPurchase::where('purchase_id', $purchase->id)->delete();

                // ProductDeliveryNote::where('delivery_note_id', $deliveryNote->id)->delete();

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity_id"];
                    $productPurchase->product_id = $product["product_id"];
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    // $productDeliveryNote = new ProductDeliveryNote();
                    // $productDeliveryNote->quantity = $product["quantity"];
                    // $productDeliveryNote->unity_id =  $product["unity"]["id"];
                    // $productDeliveryNote->product_id = $product["product"]["id"];
                    // $productDeliveryNote->delivery_note_id = $deliveryNote->id;
                    // $productDeliveryNote->save();

                    array_push($productPurchases, $productPurchase);
                }

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'purchase' => $purchase,
                    // 'deliveryNote' => $deliveryNote,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
                ], 200);
            } catch (Exception $e) {
                // dd($e);
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
                    'reference' => 'required',
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
                    // 'reference.unique' => "Ce bon d'achat existe déjà.",
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
                $purchase->total_amount = $request->total_amount;
                $purchase->amount_gross = $request->amount_gross;
                $purchase->ht_amount = $request->ht_amount;
                $purchase->discount = $request->discount;
                $purchase->amount_token = $request->amount_token;
                $purchase->tva = $request->tva;
                $purchase->observation = $request->observation;
                $purchase->order_id = $order->id;
                $purchase->provider_id = $request->provider;
                $purchase->sale_point_id = $request->sale_point;
                $purchase->save();

                ProductPurchase::where('purchase_id', $purchase->id)->delete();

                $productPurchases = [];
                foreach ($request->purchaseProducts as $key => $product) {
                    $productPurchase = new ProductPurchase();
                    $productPurchase->quantity = $product["quantity"];
                    $productPurchase->unit_price = $product["unit_price"];
                    $productPurchase->unity_id = $product["unity_id"];
                    $productPurchase->product_id = $product["product_id"];
                    $productPurchase->purchase_id = $purchase->id;
                    $productPurchase->save();

                    array_push($productPurchases, $productPurchase);
                }

                $savedProductPurchases = ProductPurchase::where('purchase_id', $purchase->id)->get();
                if (empty($savedProductPurchases) || sizeof($savedProductPurchases) == 0) {
                    $purchase->delete();
                }

                $success = true;
                $message = 'Modification effectuée avec succès.';
                return new JsonResponse([
                    'purchase' => $purchase,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productPurchases' => $productPurchases],
                ], 200);
            } catch (Exception $e) {
                // dd($e);
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

    public function purchaseProcessing($id, $action)
    {
        try {
            $this->processing(Purchase::class, $id, $action);

            $success = true;
            if ($action == 'validate') {
                $message = "Bon d'achat validé avec succès.";
            }
            if ($action == 'reject') {
                $message = "Bon d'achat rejeté avec succès.";
            }
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            if ($action == 'validate') {
                $message = "Erreur survenue lors de la validation du bon d'achat.";
            }
            if ($action == 'reject') {
                $message = "Erreur survenue lors de l'annulation du bon d'achat.";
            }
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function purchaseReports(Request $request)
    {
        $this->authorize('ROLE_PURCHASE_PRINT', Purchase::class);
        try {
            $purchases = $this->purchaseRepository->purchaseReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['purchases' => $purchases]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
