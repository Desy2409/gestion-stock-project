<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileTrait;
use App\Http\Traits\ProcessingTrait;
use App\Http\Traits\UtilityTrait;
use App\Mail\SaleValidationMail;
use App\Models\Category;
use App\Models\Client;
use App\Models\ClientDeliveryNote;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductClientDeliveryNote;
use App\Models\ProductOrder;
use App\Models\ProductPurchaseOrder;
use App\Models\ProductSale;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SalePoint;
use App\Models\SaleRegister;
use App\Models\Unity;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    use UtilityTrait;
    use ProcessingTrait;
    use FileTrait;

    public $saleRepository;
    public $productRepository;
    public function __construct(SaleRepository $saleRepository, ProductRepository $productRepository)
    {
        $this->saleRepository = $saleRepository;
        $this->productRepository = $productRepository;
        $this->user = Auth::user();
    }

    public function saleOnPurchaseOrder()
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        // $sales = Sale::with('client')->with('purchaseOrder')->with('clientDeliveryNotes')->with('productSales')->where('purchase_order_id', '!=', null)->orderBy('code')->orderBy('sale_date')->get();
        $sales = Sale::orderBy('created_at','desc')->with('clientDeliveryNotes')->where('purchase_order_id', '!=', null)->orderBy('code')->orderBy('sale_date')->get();
        $lastSaleRegister = SaleRegister::latest()->first();

        $saleRegister = new SaleRegister();
        if ($lastSaleRegister) {
            $saleRegister->code = $this->formateNPosition(Sale::class, $lastSaleRegister->id + 1);
        } else {
            $saleRegister->code = $this->formateNPosition(Sale::class, 1);
        }
        $saleRegister->save();

        $purchaseOrders = PurchaseOrder::orderBy('code')->get();
        return new JsonResponse([
            'datas' => ['purchaseOrders' => $purchaseOrders, 'sales' => $sales]
        ]);
    }

    public function directSale()
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        // $sales = Sale::with('client')->with('clientDeliveryNotes')->with('productSales')->where('purchase_order_id', '=', null)->orderBy('code')->orderBy('sale_date')->get();
        $sales = Sale::orderBy('created_at','desc')->with('clientDeliveryNotes')->where('purchase_order_id', '=', null)->orderBy('code')->orderBy('sale_date')->get();
        $lastSaleRegister = SaleRegister::latest()->first();

        $saleRegister = new SaleRegister();
        if ($lastSaleRegister) {
            $saleRegister->code = $this->formateNPosition(Sale::class, $lastSaleRegister->id + 1);
        } else {
            $saleRegister->code = $this->formateNPosition(Sale::class, 1);
        }
        $saleRegister->save();

        $clients = Client::with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $categories = Category::orderBy('wording')->get();
        // $products = Product::with('subCategory')->get();
        $unities = Unity::orderBy('wording')->get();

        return new JsonResponse([
            'datas' => ['clients' => $clients, 'salePoints' => $salePoints, 'categories' => $categories, 'sales' => $sales, 'unities' => $unities]
        ]);
    }

    public function datasFromPurchaseOrder($id)
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $purchaseOrder = PurchaseOrder::with('client')->with('salePoint')->findOrFail($id);
        // $client = $purchaseOrder ? $purchaseOrder->client : null;
        // $salePoint = $purchaseOrder ? $purchaseOrder->salePoint : null;

        $productPurchaseOrders = ProductPurchaseOrder::where('purchase_order_id', $purchaseOrder->id)->with('product')->with('unity')->get();
        return new JsonResponse([
            'purchaseOrder' => $purchaseOrder, 'datas' => ['productPurchaseOrders' => $productPurchaseOrders]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $lastSaleRegister = SaleRegister::latest()->first();
        if ($lastSaleRegister) {
            $code = $this->formateNPosition(Sale::class, $lastSaleRegister->id + 1);
        } else {
            $code = $this->formateNPosition(Sale::class, 1);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }
    
    public function productsOfSelectedCategory($id)
    {
        $category = Category::findOrFail($id);
        $products = $this->productRepository->productsOfCategory($category->id);

        return new JsonResponse(['datas' => ['products' => $products]], 200);
    }

    public function show($id)
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $sale = Sale::with('client')->with('purchaseOrder')->with('clientDeliveryNotes')->with('productSales')->findOrFail($id);
        $productSales = $sale ? $sale->productSales : null; //ProductPurchase::where('order_id', $sale->id)->get();

        $email = 'tes@mailinator.com';
        Mail::to($email)->send(new SaleValidationMail($sale, $productSales));

        return new JsonResponse([
            'sale' => $sale,
            'datas' => ['productSales' => $productSales]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $sale = Sale::with('purchaseOrder')->findOrFail($id);
        $productSales = ProductSale::where('sale_id', $sale->id)->with('product')->with('unity')->get();
        return new JsonResponse([
            'sale' => $sale,
            'datas' => ['productSales' => $productSales]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_SALE_CREATE', Sale::class);
        if ($request->saleType == "Vente directe") {

            try {
                $validation = $this->validator('store', $request->saleType, $request->all());

                if ($validation->fails()) {
                    $messages = $validation->errors()->all();
                    $messages = implode('<br/>', $messages);
                    return new JsonResponse([
                        'success' => false,
                        'message' => $messages,
                        //'message' => 'Des donnees sont invalides',
                    ], 200);
                } else {
                    $lastSale = Sale::latest()->first();

                    $sale = new Sale();
                    if ($lastSale) {
                        $sale->code = $this->formateNPosition(Sale::class, $lastSale->id + 1);
                    } else {
                        $sale->code = $this->formateNPosition(Sale::class, 1);
                    }
                    $sale->reference = $request->reference;
                    $sale->sale_date   = $request->sale_date;
                    $sale->total_amount = $request->total_amount;
                    $sale->amount_gross = $request->amount_gross;
                    $sale->ht_amount = $request->ht_amount;
                    $sale->discount = $request->discount;
                    $sale->amount_token = $request->amount_token;
                    $sale->tva = $request->tva;
                    $sale->observation = $request->observation;
                    $sale->client_id = $request->client;
                    $sale->sale_point_id = $request->sale_point;
                    $sale->save();

                    $lastClientDeliveryNote = ClientDeliveryNote::latest()->first();

                    $clientDeliveryNote = new ClientDeliveryNote();
                    if ($lastClientDeliveryNote) {
                        $clientDeliveryNote->code = $this->formateNPosition('BL', $lastClientDeliveryNote->id + 1);
                    } else {
                        $clientDeliveryNote->code = $this->formateNPosition('BL', 1);
                    }
                    $clientDeliveryNote->reference = $request->reference;
                    $clientDeliveryNote->delivery_date   = $request->delivery_date;
                    $clientDeliveryNote->total_amount = $request->total_amount;
                    $clientDeliveryNote->observation = $request->observation;
                    $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
                    $clientDeliveryNote->sale_id = $sale->id;
                    $clientDeliveryNote->save();

                    $productSales = [];
                    foreach ($request->saleProducts as $key => $product) {
                        $productSale = new ProductSale();
                        $productSale->quantity = $product["quantity"];
                        $productSale->unit_price = $product["unit_price"];
                        $productSale->unity_id = $product["unity_id"];
                        $productSale->product_id = $product["product_id"];
                        $productSale->sale_id = $sale->id;
                        $productSale->save();

                        $productClientDeliveryNote = new ProductClientDeliveryNote();
                        $productClientDeliveryNote->quantity = $product["quantity"];
                        $productClientDeliveryNote->unity_id = $product["unity_id"];
                        $productClientDeliveryNote->product_id = $product["product_id"];
                        $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
                        $productClientDeliveryNote->save();

                        array_push($productSales, $productSale);
                    }

                    $message = "Enregistrement effectu?? avec succ??s.";
                    return new JsonResponse([
                        'sale' => $sale,
                        'clientDeliveryNote' => $clientDeliveryNote,
                        'success' => true,
                        'message' => $message,
                        'datas' => ['productSales' => $productSales],
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
        } elseif ($request->saleType == "Vente sur commande") {

            try {
                $validation = $this->validator('store', $request->saleType, $request->all());

                if ($validation->fails()) {
                    $messages = $validation->errors()->all();
                    $messages = implode('<br/>', $messages);
                    return new JsonResponse([
                        'success' => false,
                        'message' => $messages,
                        //'message' => 'Des donnees sont invalides',
                    ], 200);
                } else {
                    $purchaseOrder = PurchaseOrder::findOrFail($request->purchaseOrder);

                    $lastSale = Sale::latest()->first();

                    $sale = new Sale();
                    if ($lastSale) {
                        $sale->code = $this->formateNPosition(Sale::class, $lastSale->id + 1);
                    } else {
                        $sale->code = $this->formateNPosition(Sale::class, 1);
                    }
                    $sale->reference = $request->reference;
                    $sale->sale_date   = $request->sale_date;
                    // $sale->delivery_date   = $request->delivery_date;
                    $sale->total_amount = $request->total_amount;
                    $sale->amount_gross = $request->amount_gross;
                    $sale->ht_amount = $request->ht_amount;
                    $sale->discount = $request->discount;
                    $sale->amount_token = $request->amount_token;
                    $sale->tva = $request->tva;
                    $sale->observation = $request->observation;
                    $sale->purchase_order_id = $purchaseOrder->id;
                    $sale->client_id = $purchaseOrder->client->id;
                    $sale->sale_point_id = $purchaseOrder->salePoint->id;
                    $sale->save();

                    $productSales = [];
                    foreach ($request->saleProducts as $key => $product) {
                        $productSale = new ProductSale();
                        $productSale->quantity = $product["quantity"];
                        $productSale->unit_price = $product["unit_price"];
                        $productSale->unity_id = $product["unity_id"];
                        $productSale->product_id = $product["product_id"];
                        $productSale->sale_id = $sale->id;
                        $productSale->save();

                        array_push($productSales, $productSale);
                    }

                    // $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                    // if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                    //     $sale->delete();
                    // }

                    $folder = Folder::findOrFail($request->folder);

                    $check = $this->checkFileType($sale);
                    if (!$check) {
                        $message = "Les formats de fichiers autoris??s sont : pdf,docx et xls";
                        return new JsonResponse(['success' => false, 'message' => $message], 400);
                    } else {
                        $this->storeFile($this->user, $sale, $folder, $request->upload_files);
                    }

                    $message = "Enregistrement effectu?? avec succ??s.";
                    return new JsonResponse([
                        'sale' => $sale,
                        'success' => true,
                        'message' => $message,
                        'datas' => ['productSales' => $productSales],
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
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_SALE_UPDATE', Sale::class);
        $sale = Sale::findOrFail($id);
        if ($request->saleType == "Vente directe") {

            try {
                $validation = $this->validator('update', $request->saleType, $request->all());

                if ($validation->fails()) {
                    $messages = $validation->errors()->all();
                    $messages = implode('<br/>', $messages);
                    return new JsonResponse([
                        'success' => false,
                        'message' => $messages,
                        //'message' => 'Des donnees sont invalides',
                    ], 200);
                } else {
                    $sale->reference = $request->reference;
                    $sale->sale_date   = $request->sale_date;
                    $sale->total_amount = $request->total_amount;
                    $sale->amount_gross = $request->amount_gross;
                    $sale->ht_amount = $request->ht_amount;
                    $sale->discount = $request->discount;
                    $sale->amount_token = $request->amount_token;
                    $sale->tva = $request->tva;
                    $sale->observation = $request->observation;
                    $sale->client_id = $request->client;
                    $sale->sale_point_id = $request->salePoint;
                    $sale->save();

                    // $clientDeliveryNote = $sale ? $sale->clientDeliveryNote : null;

                    // $clientDeliveryNote->reference = $request->reference;
                    // $clientDeliveryNote->delivery_date   = $request->delivery_date;
                    // $clientDeliveryNote->total_amount = $request->total_amount;
                    // $clientDeliveryNote->observation = $request->observation;
                    // $clientDeliveryNote->place_of_delivery = $request->place_of_delivery;
                    // $clientDeliveryNote->sale_id = $sale->id;
                    // $clientDeliveryNote->save();

                    ProductSale::where('sale_id', $sale->id)->delete();

                    // ProductClientDeliveryNote::where('client_delivery_note_id', $clientDeliveryNote->id)->delete();

                    $productSales = [];
                    foreach ($request->saleProducts as $key => $product) {
                        $productSale = new ProductSale();
                        $productSale->quantity = $product["quantity"];
                        $productSale->unit_price = $product["unit_price"];
                        $productSale->unity_id = $product["unity_id"];
                        $productSale->product_id = $product["product_id"];
                        $productSale->sale_id = $sale->id;
                        $productSale->save();

                        // $productClientDeliveryNote = new ProductClientDeliveryNote();
                        // $productClientDeliveryNote->quantity = $product["quantity"];
                        // $productClientDeliveryNote->unity_id = $product["unity"];
                        // $productClientDeliveryNote->product_id = $product["product"];
                        // $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
                        // $productClientDeliveryNote->save();

                        array_push($productSales, $productSale);
                    }

                    $message = "Modification effectu??e avec succ??s.";
                    return new JsonResponse([
                        'sale' => $sale,
                        // 'clientDeliveryNote' => $clientDeliveryNote,
                        'success' => true,
                        'message' => $message,
                        'datas' => ['productSales' => $productSales],
                    ], 200);
                }
            } catch (Exception $e) {
                $message = "Erreur survenue lors de la modification.";
                return new JsonResponse([
                    'success' => false,
                    'message' => $message,
                ], 200);
            }
        } elseif ($request->saleType == "Vente sur commande") {

            // if (sizeof($request->saleProducts) != sizeof($request->quantities) || sizeof($request->saleProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantit?? ou un prix unitaire n'a pas ??t?? renseign??.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
                $validation = $this->validator('update', $request->saleType, $request->all());

                if ($validation->fails()) {
                    $messages = $validation->errors()->all();
                    $messages = implode('<br/>', $messages);
                    return new JsonResponse([
                        'success' => false,
                        'message' => $messages,
                        //'message' => 'Des donnees sont invalides',
                    ], 200);
                } else {
                    $purchaseOrder = PurchaseOrder::findOrFail($request->purchaseOrder);

                    $sale->reference = $request->reference;
                    $sale->sale_date   = $request->sale_date;
                    $sale->total_amount = $request->total_amount;
                    $sale->amount_gross = $request->amount_gross;
                    $sale->ht_amount = $request->ht_amount;
                    $sale->discount = $request->discount;
                    $sale->amount_token = $request->amount_token;
                    $sale->tva = $request->tva;
                    $sale->observation = $request->observation;
                    $sale->purchase_order_id = $purchaseOrder->id;
                    $sale->client_id = $request->client;
                    $sale->sale_point_id = $request->salePoint;
                    $sale->save();

                    $productSales = [];
                    foreach ($request->saleProducts as $key => $product) {
                        $productSale = new ProductSale();
                        $productSale->quantity = $product["quantity"];
                        $productSale->unit_price = $product["unit_price"];
                        $productSale->unity_id = $product["unity_id"];
                        $productSale->product_id = $product["product_id"];
                        $productSale->sale_id = $sale->id;
                        $productSale->save();

                        array_push($productSales, $productSale);
                    }

                    // $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                    // if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                    //     $sale->delete();
                    // }

                    $message = "Modification effectu??e avec succ??s.";
                    return new JsonResponse([
                        'sale' => $sale,
                        'success' => true,
                        'message' => $message,
                        'datas' => ['productSales' => $productSales],
                    ], 200);
                }
            } catch (Exception $e) {
                $message = "Erreur survenue lors de la modification.";
                return new JsonResponse([
                    'success' => false,
                    'message' => $message,
                ], 400);
            }
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_SALE_DELETE', Sale::class);
        $sale = Sale::findOrFail($id);
        $productSales = $sale ? $sale->productSales : null;
        try {
            $success = false;
            $message = "";
            if (
                empty($productSales) || sizeof($productSales) == 0 &&
                empty($sale->clientDeliveryNotes) || sizeof($sale->clientDeliveryNotes) == 0
            ) {
                // dd('delete');
                $sale->delete();

                $success = true;
                $message = "Suppression effectu??e avec succ??s.";
            } else {
                // dd('not delete');
                $message = "Cet achat ne peut ??tre supprim?? car il a servi dans des traitements.";
            }
            return new JsonResponse([
                'sale' => $sale,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function saleProcessing($id, $action)
    {
        try {
            $this->processing(Sale::class, $id, $action);
            if ($action == 'validate') {
                $message = "Vente valid??e avec succ??s.";
            }
            if ($action == 'reject') {
                $message = "Vente rejet??e avec succ??s.";
            }
            return new JsonResponse([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            if ($action == 'validate') {
                $message = "Erreur survenue lors de la validation de la vente.";
            }
            if ($action == 'reject') {
                $message = "Erreur survenue lors de l'annulation de la vente.";
            }
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function saleReports(Request $request)
    {
        $this->authorize('ROLE_SALE_PRINT', Sale::class);
        try {
            $sales = $this->saleRepository->saleReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['sales' => $sales]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validator($mode, $saleType, $data)
    {
        if ($mode == 'store') {
            if ($saleType == 'Vente directe') {
                return Validator::make(
                    $data,
                    [
                        'reference' => 'required|unique:sales',
                        'sale_date' => 'required|date|before:today', //|date_format:Ymd
                        'observation' => 'max:255',
                        'saleProducts' => 'required',
                        // 'quantities' => 'required|min:0',
                        // 'unit_prices' => 'required|min:0',
                        // 'unities' => 'required',
                    ],
                    [
                        'reference.required' => "La r??f??rence du bon est obligatoire.",
                        'reference.unique' => "Cette vente existe d??j??.",
                        'sale_date.required' => "La date du bon est obligatoire.",
                        'sale_date.date' => "La date de la vente est incorrecte.",
                        'sale_date.before' => "La date de la vente doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                        'observation.max' => "L'observation ne doit pas d??passer 255 caract??res.",
                        'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                        // 'quantities.required' => "Les quantit??s sont obligatoires.",
                        // 'quantities.min' => "Aucune des quantit??s ne peut ??tre inf??rieur ?? 0.",
                        // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                        // 'unit_prices.min' => "Aucun des prix unitaires ne peut ??tre inf??rieur ?? 0.",
                        // 'unities.required' => "Veuillez d??finir des unit??s ?? tous les produits ajout??s.",
                    ]
                );
            } else {
                return Validator::make(
                    $data,
                    [
                        'purchaseOrder' => 'required',
                        'reference' => 'required|unique:sales',
                        'sale_date' => 'required|date|before:today', //|date_format:Ymd
                        'observation' => 'max:255',
                        'saleProducts' => 'required',
                        // 'quantities' => 'required|min:0',
                        // 'unit_prices' => 'required|min:0',
                        // 'unities' => 'required',
                    ],
                    [
                        'purchaseOrder.required' => "Le choix d'un bon de commande est obligatoire.",
                        'reference.required' => "La r??f??rence du bon est obligatoire.",
                        'reference.unique' => "Cette vente existe d??j??.",
                        'sale_date.required' => "La date du bon est obligatoire.",
                        'sale_date.date' => "La date de la vente est incorrecte.",
                        'sale_date.before' => "La date de la vente doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                        'observation.max' => "L'observation ne doit pas d??passer 255 caract??res.",
                        'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                        // 'quantities.required' => "Les quantit??s sont obligatoires.",
                        // 'quantities.min' => "Aucune des quantit??s ne peut ??tre inf??rieur ?? 0.",
                        // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                        // 'unit_prices.min' => "Aucun des prix unitaires ne peut ??tre inf??rieur ?? 0.",
                        // 'unities.required' => "Veuillez d??finir des unit??s ?? tous les produits ajout??s.",
                    ]
                );
            }
        }
        if ($mode == 'update') {
            if ($saleType == 'Vente directe') {
                return Validator::make(
                    $data,
                    [
                        'reference' => 'required',
                        'sale_date' => 'required|date|before:today', //|date_format:Ymd
                        'observation' => 'max:255',
                        'saleProducts' => 'required',
                        // 'quantities' => 'required|min:0',
                        // 'unit_prices' => 'required|min:0',
                        // 'unities' => 'required',
                    ],
                    [
                        'reference.required' => "La r??f??rence du bon est obligatoire.",
                        'reference.unique' => "Cette vente existe d??j??.",
                        'sale_date.required' => "La date du bon est obligatoire.",
                        'sale_date.date' => "La date de la vente est incorrecte.",
                        'sale_date.before' => "La date de la vente doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                        'observation.max' => "L'observation ne doit pas d??passer 255 caract??res.",
                        'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                        // 'quantities.required' => "Les quantit??s sont obligatoires.",
                        // 'quantities.min' => "Aucune des quantit??s ne peut ??tre inf??rieur ?? 0.",
                        // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                        // 'unit_prices.min' => "Aucun des prix unitaires ne peut ??tre inf??rieur ?? 0.",
                        // 'unities.required' => "Veuillez d??finir des unit??s ?? tous les produits ajout??s.",
                    ]
                );
            } else {
                return Validator::make(
                    $data,
                    [
                        'purchaseOrder' => 'required',
                        'reference' => 'required',
                        'sale_date' => 'required|date|before:today', //|date_format:Ymd
                        'observation' => 'max:255',
                        'saleProducts' => 'required',
                        // 'quantities' => 'required|min:0',
                        // 'unit_prices' => 'required|min:0',
                        // 'unities' => 'required',
                    ],
                    [
                        'purchaseOrder.required' => "Le choix d'un bon de commande est obligatoire.",
                        'reference.required' => "La r??f??rence du bon est obligatoire.",
                        'reference.unique' => "Cette vente existe d??j??.",
                        'sale_date.required' => "La date du bon est obligatoire.",
                        'sale_date.date' => "La date de la vente est incorrecte.",
                        'sale_date.before' => "La date de la vente doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                        'observation.max' => "L'observation ne doit pas d??passer 255 caract??res.",
                        'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                        // 'quantities.required' => "Les quantit??s sont obligatoires.",
                        // 'quantities.min' => "Aucune des quantit??s ne peut ??tre inf??rieur ?? 0.",
                        // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                        // 'unit_prices.min' => "Aucun des prix unitaires ne peut ??tre inf??rieur ?? 0.",
                        // 'unities.required' => "Veuillez d??finir des unit??s ?? tous les produits ajout??s.",
                    ]
                );
            }
        }
    }
}
