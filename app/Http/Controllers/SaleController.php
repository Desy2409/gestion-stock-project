<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Mail\SaleValidationMail;
use App\Models\Client;
use App\Models\ClientDeliveryNote;
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
use App\Repositories\SaleRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SaleController extends Controller
{
    use UtilityTrait;

    public $saleRepository;
    public function __construct(SaleRepository $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    public function saleOnPurchaseOrder()
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $sales = Sale::with('client')->with('purchaseOrder')->with('clientDeliveryNotes')->with('productSales')->where('purchase_order_id', '!=', null)->orderBy('code')->orderBy('sale_date')->get();
        $lastSaleRegister = SaleRegister::latest()->first();

        $saleRegister = new SaleRegister();
        if ($lastSaleRegister) {
            $saleRegister->code = $this->formateNPosition('VT', $lastSaleRegister->id + 1, 8);
        } else {
            $saleRegister->code = $this->formateNPosition('VT', 1, 8);
        }
        $saleRegister->save();

        $purchaseOrders = PurchaseOrder::with('client')->with('salePoint')->orderBy('code')->get();
        return new JsonResponse([
            'datas' => ['purchaseOrders' => $purchaseOrders, 'sales' => $sales]
        ]);
    }

    public function directSale()
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $sales = Sale::with('client')->with('clientDeliveryNotes')->with('productSales')->where('purchase_order_id', '=', null)->orderBy('code')->orderBy('sale_date')->get();
        $lastSaleRegister = SaleRegister::latest()->first();

        $saleRegister = new SaleRegister();
        if ($lastSaleRegister) {
            $saleRegister->code = $this->formateNPosition('VT', $lastSaleRegister->id + 1, 8);
        } else {
            $saleRegister->code = $this->formateNPosition('VT', 1, 8);
        }
        $saleRegister->save();

        $clients = Client::with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $products = Product::with('subCategory')->get();
        $unities = Unity::orderBy('wording')->get();

        return new JsonResponse([
            'datas' => ['clients' => $clients, 'salePoints' => $salePoints, 'products' => $products, 'sales' => $sales, 'unities' => $unities]
        ]);
    }

    public function datasFromPurchaseOrder($id)
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $client = $purchaseOrder ? $purchaseOrder->client : null;
        $salePoint = $purchaseOrder ? $purchaseOrder->salePoint : null;

        $productPurchaseOrders = ProductPurchaseOrder::with('product')->with('unity')->where('purchase_order_id', $purchaseOrder->id)->get();
        return new JsonResponse([
            'client' => $client, 'salePoint' => $salePoint, 'datas' => ['productPurchaseOrders' => $productPurchaseOrders]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_SALE_READ', Sale::class);
        $lastSaleRegister = SaleRegister::latest()->first();
        if ($lastSaleRegister) {
            $code = $this->formateNPosition('VT', $lastSaleRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('VT', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
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
        $sale = Sale::with('purchaseOrder')->with('client')->with('salePoint')->findOrFail($id);
        $productSales = ProductSale::where('sale_id',$sale->id)->with('product')->with('unity')->get();
        return new JsonResponse([
            'sale' => $sale,
            'datas' => [ 'productSales' => $productSales]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_SALE_CREATE', Sale::class);
        if ($request->saleType == "Vente directe") {
            $this->validate(
                $request,
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
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );
            // if (sizeof($request->saleProducts) != sizeof($request->quantities) || sizeof($request->saleProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
                $lastSale = Sale::latest()->first();

                $sale = new Sale();
                if ($lastSale) {
                    $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                } else {
                    $sale->code = $this->formateNPosition('VT', 1, 8);
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
                $sale->sale_point_id = $request->SalePoint;
                $sale->save();

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

                $productSales = [];
                foreach ($request->saleProducts as $key => $product) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $product["quantity"];
                    $productSale->unit_price = $product["unit_price"];
                    $productSale->unity_id = $product["unity"];
                    $productSale->product_id = $product["product"];
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    $productClientDeliveryNote = new ProductClientDeliveryNote();
                    $productClientDeliveryNote->quantity = $product["quantity"];
                    $productClientDeliveryNote->unity_id = $product["unity"];
                    $productClientDeliveryNote->product_id = $product["product"];
                    $productClientDeliveryNote->client_delivery_note_id = $clientDeliveryNote->id;
                    $productClientDeliveryNote->save();

                    array_push($productSales, $productSale);
                }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'sale' => $sale,
                    'clientDeliveryNote' => $clientDeliveryNote,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productSales' => $productSales],
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
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->saleProducts) != sizeof($request->quantities) || sizeof($request->saleProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
                $purchaseOrder = PurchaseOrder::findOrFail($request->purchaseOrder);

                $lastSale = Sale::latest()->first();

                $sale = new Sale();
                if ($lastSale) {
                    $sale->code = $this->formateNPosition('VT', $lastSale->id + 1, 8);
                } else {
                    $sale->code = $this->formateNPosition('VT', 1, 8);
                }
                $sale->reference = $request->reference;
                $sale->sale_date   = $request->sale_date;
                // $sale->delivery_date   = $request->delivery_date;
                $sale->total_amount = $purchaseOrder->total_amount;
                $sale->amount_gross = $purchaseOrder->total_amount;
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
                    $productSale->unity_id = $product["unity"]["id"];
                    $productSale->product_id = $product["product"]["id"];
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    array_push($productSales, $productSale);
                }

                // $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                // if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                //     $sale->delete();
                // }

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'sale' => $sale,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productSales' => $productSales],
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
        $this->authorize('ROLE_SALE_UPDATE', Sale::class);
        $sale = Sale::findOrFail($id);
        if ($request->saleType == "Vente directe") {
            $this->validate(
                $request,
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
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->saleProducts) != sizeof($request->quantities) || sizeof($request->saleProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
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
                    $productSale->unity_id = $product["unity"]["id"];
                    $productSale->product_id = $product["product"]["id"];
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

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'sale' => $sale,
                    // 'clientDeliveryNote' => $clientDeliveryNote,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productSales' => $productSales],
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
                    'reference.required' => "La référence du bon est obligatoire.",
                    'reference.unique' => "Cette vente existe déjà.",
                    'sale_date.required' => "La date du bon est obligatoire.",
                    'sale_date.date' => "La date de la vente est incorrecte.",
                    'sale_date.before' => "La date de la vente doit être antérieure ou égale à aujourd'hui.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'saleProducts.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantities.required' => "Les quantités sont obligatoires.",
                    // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                ]
            );

            // if (sizeof($request->saleProducts) != sizeof($request->quantities) || sizeof($request->saleProducts) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            // if (sizeof($request->products_of_purchase) != sizeof($request->quantities) || sizeof($request->products_of_purchase) != sizeof($request->unit_prices) || sizeof($request->unit_prices) != sizeof($request->quantities)) {
            //     $success = false;
            //     $message = "Un produit, une quantité ou un prix unitaire n'a pas été renseigné.";
            //     return new JsonResponse([
            //         'success' => $success,
            //         'message' => $message,
            //     ]);
            // }

            try {
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
                $sale->order_id = $purchaseOrder->id;
                $sale->client_id = $request->client;
                $sale->sale_point_id = $request->salePoint;
                $sale->save();

                $productSales = [];
                foreach ($request->saleProducts as $key => $product) {
                    $productSale = new ProductSale();
                    $productSale->quantity = $product["quantity"];
                    $productSale->unit_price = $product["unit_price"];
                    $productSale->unity_id = $product["unity"]["id"];
                    $productSale->product_id = $product["product"]["id"];
                    $productSale->sale_id = $sale->id;
                    $productSale->save();

                    array_push($productSales, $productSale);
                }

                // $savedProductSales = ProductSale::where('sale_id', $sale->id)->get();
                // if (empty($savedProductSales) || sizeof($savedProductSales) == 0) {
                //     $sale->delete();
                // }

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'sale' => $sale,
                    'success' => $success,
                    'message' => $message,
                    'datas' => ['productSales' => $productSales],
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
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cet achat ne peut être supprimé car il a servi dans des traitements.";
            }
            return new JsonResponse([
                'sale' => $sale,
                'success' => $success,
                'message' => $message,
                'datas' => ['productSales' => $productSales],
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

    public function validateSale($id)
    {
        $this->authorize('ROLE_SALE_VALIDATE', Sale::class);
        $sale = Sale::findOrFail($id);
        try {
            $sale->state = 'S';
            $sale->date_of_processing = date('Y-m-d', strtotime(now()));
            $sale->save();

            $success = true;
            $message = "Vente validée avec succès.";
            return new JsonResponse([
                'sale' => $sale,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la validation de la vente.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function rejectSale($id)
    {
        $this->authorize('ROLE_SALE_REJECT', Sale::class);
        $sale = Sale::findOrFail($id);
        try {
            $sale->state = 'A';
            $sale->date_of_processing = date('Y-m-d', strtotime(now()));
            $sale->save();

            $success = true;
            $message = "Vente annulée avec succès.";
            return new JsonResponse([
                'sale' => $sale,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'annulation de la vente.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function saleReports(Request $request)
    {
        try {
            $sales = $this->saleRepository->saleReport($request->code, $request->reference, $request->sale_date, $request->date_of_processing, $request->state, $request->total_amount, $request->tva, $request->amount_token, $request->discount, $request->amount_gross, $request->ht_amount, $request->purchase_order, $request->client, $request->sale_point, $request->observation, $request->start_sale_date, $request->end_sale_date, $request->start_delivery_date, $request->end_delivery_date, $request->start_processing_date, $request->end_processing_date);
            return new JsonResponse(['datas' => ['sales' => $sales]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
