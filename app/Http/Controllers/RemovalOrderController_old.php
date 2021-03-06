<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\ClientDeliveryNote;
use App\Models\Order;
use App\Models\ProductTourn;
use App\Models\RemovalOrder;
use App\Models\RemovalOrderRegister;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SalePoint;
use App\Models\StockType;
use App\Models\Tank;
use App\Models\Tourn;
use App\Models\TournRegister;
use App\Models\Transfer;
use App\Models\Truck;
use App\Repositories\PurchaseOrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RemovalOrderController_old extends Controller
{

    use UtilityTrait;

    private $voucherTypes = ["Externe", "Interne"];
    private $customsRegimes = ["HT", "TTC"];

    public $purchaseOrderRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    public function index()
    {

        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $purchaseOrders = $this->purchaseOrderRepository->purchaseOrderBasedOnClientDeliveryNote();

        $idOfProviderTypeStorageUnits = ProviderType::where('type', "Unité de stockage")->pluck('id')->toArray();
        $idOfProviderTypeCarriers = ProviderType::where('type', "Transport")->pluck('id')->toArray();

        $removalOrders = RemovalOrder::orderBy('created_at', 'desc')->orderBy('voucher_date')->orderBy('reference')->get();
        $storageUnits = Provider::whereIn('provider_type_id', $idOfProviderTypeStorageUnits)->with('person')->get();
        $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $stockTypes = StockType::orderBy('wording')->get();
        $clients = Client::with('person.address')->get();
        // $transfers = Transfer::orderBy('code')->get();

        $lastRemovalOrderRegister = RemovalOrderRegister::latest()->first();

        $removalOrderRegister = new RemovalOrderRegister();
        if ($lastRemovalOrderRegister) {
            $removalOrderRegister->code = $this->formateNPosition(RemovalOrder::class, $lastRemovalOrderRegister->id + 1);
        } else {
            $removalOrderRegister->code = $this->formateNPosition(RemovalOrder::class, 1);
        }
        $removalOrderRegister->save();

        return new JsonResponse([
            'datas' => [
                'purchaseOrders' => $purchaseOrders,
                'removalOrders' => $removalOrders, 'voucherTypes' => $this->voucherTypes,
                'storageUnits' => $storageUnits, 'carriers' => $carriers,
                'customsRegimes' => $this->customsRegimes, 'salePoints' => $salePoints,
                // 'stockTypes' => $stockTypes,
                'clients' => $clients,
                // 'transfers' => $transfers
            ]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $lastRemovalOrderRegister = RemovalOrderRegister::latest()->first();
        if ($lastRemovalOrderRegister) {
            $code = $this->formateNPosition(RemovalOrder::class, $lastRemovalOrderRegister->id + 1);
        } else {
            $code = $this->formateNPosition(RemovalOrder::class, 1);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function salePointsFromTransfer($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $transfer = Transfer::findOrFail($id);
        $transmitter = SalePoint::findOrFail($transfer->transmitter_id);
        $receiver = SalePoint::findOrFail($transfer->receiver_id);

        return new JsonResponse(['transmitter' => $transmitter, 'receiver' => $receiver]);
    }

    public function datasOnPurchaseOrderSelect($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseDate = $purchaseOrder->purchase_date;
        $productClientDeliveryNotes = $this->purchaseOrderRepository->purchaseOrderDeliveredProducts($purchaseOrder);
        return new JsonResponse(['purchaseDate' => $purchaseDate, 'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes]], 200);
    }

    public function onClientSelect($id)
    {
        $client = Client::findOrFail($id);
        return new JsonResponse(['exemption_reference' => $client->exemption_reference], 200);
    }

    public function onCarrierSelect($id)
    {
        $provider = Provider::with('person')->with('trucks')->findOrFail($id);
        return new JsonResponse(['provider' => $provider], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_CREATE', RemovalOrder::class);

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
                $lastRemovalOrder = RemovalOrder::latest()->first();

                $removalOrder = new RemovalOrder();
                if ($lastRemovalOrder) {
                    $removalOrder->code = $this->formateNPosition(RemovalOrder::class, $lastRemovalOrder->id + 1);
                } else {
                    $removalOrder->code = $this->formateNPosition(RemovalOrder::class, 1);
                }
                $removalOrder->reference = $request->reference;
                $removalOrder->voucher_date = $request->voucher_date;
                $removalOrder->delivery_date_wished = $request->delivery_date_wished;
                $removalOrder->place_of_delivery = $request->place_of_delivery;
                $removalOrder->voucher_type = $request->voucher_type;
                $removalOrder->customs_regime = $request->customs_regime;
                $removalOrder->storage_unit_id = $request->storage_unit;
                $removalOrder->carrier_id = $request->carrier;
                $removalOrder->transmitter_id = $request->transmitter;
                $removalOrder->receiver_id = $request->receiver;
                $removalOrder->client_id = $request->client;
                $removalOrder->stock_type_id = $request->stock_type;
                $removalOrder->save();

                $lastTourn = Tourn::latest()->first();

                $tourn = new Tourn();
                if ($lastTourn) {
                    $tourn->code = $this->formateNPosition('TO', $lastTourn->id + 1);
                } else {
                    $tourn->code = $this->formateNPosition('TO', 1);
                }

                $clientDeliveryNotes = [];
                array_push($clientDeliveryNotes, $request->client_delivery_note);

                $tourn->reference = $request->reference_tourn;
                $tourn->date_of_edition = $request->date_of_edition;
                $tourn->removal_order_id = $removalOrder->id;
                $tourn->truck_id = $request->truck;
                $tourn->tank_id = $request->tank;
                $tourn->destination_id = $request->destination;
                $tourn->client_delivery_notes = $clientDeliveryNotes;
                $tourn->save();

                $productsTourns = [];
                foreach ($request->productTourns as $key => $productTournLine) {
                    // dd($productTournLine);
                    $productTourn = new ProductTourn();
                    $productTourn->quantity = $productTournLine['quantity'];
                    $productTourn->product_id = $productTournLine['product_id'];
                    $productTourn->tourn_id = $tourn->id;
                    $productTourn->unity_id = $productTournLine['unity_id'];
                    $productTourn->save();

                    array_push($productsTourns, $productTourn);
                }

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'removalOrder' => $removalOrder,
                    'success' => true,
                    'message' => $message,
                    'datas' => ['productsTourns' => $productsTourns],
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $removalOrder = RemovalOrder::with('salePoint')->findOrFail($id);
        return new JsonResponse([
            'removalOrder' => $removalOrder
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $removalOrder = RemovalOrder::with('salePoint')->findOrFail($id);
        return new JsonResponse([
            'removalOrder' => $removalOrder,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_UPDATE', RemovalOrder::class);
        $removalOrder = RemovalOrder::findOrFail($id);

        $existingRemovalOrders = RemovalOrder::where('reference', $request->reference)->get();
        if (!empty($existingRemovalOrders) && sizeof($existingRemovalOrders) > 1) {
            return new JsonResponse([
                'existingRemovalOrder' => $existingRemovalOrders[0],
                'success' => false,
                'message' => "Le bon à enlever portant la référence " . $existingRemovalOrders[0]->reference . " existe déjà."
            ], 200);
        }

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
                $removalOrder->reference = $request->reference;
                $removalOrder->voucher_date = $request->voucher_date;
                $removalOrder->delivery_date_wished = $request->delivery_date_wished;
                $removalOrder->place_of_delivery = $request->place_of_delivery;
                $removalOrder->voucher_type = $request->voucher_type;
                $removalOrder->customs_regime = $request->customs_regime;
                $removalOrder->storage_unit_id = $request->storage_unit;
                $removalOrder->carrier_id = $request->carrier;
                $removalOrder->transmitter_id = $request->transmitter;
                $removalOrder->receiver_id = $request->receiver;
                $removalOrder->client_id = $request->client;
                $removalOrder->stock_type_id = $request->stock_type;
                $removalOrder->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'removalOrder' => $removalOrder,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_DELETE', RemovalOrder::class);
        $removalOrder = RemovalOrder::findOrFail($id);
        try {
            $removalOrder->delete();

            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'removalOrder' => $removalOrder,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'client' => 'required',
                    'stock_type' => 'required',
                    'reference' => 'required|unique:removal_orders',
                    // 'reference_tourn' => 'required|unique:tourns',
                    'voucher_date' => 'required|date', //|date_format:Ymd
                    'delivery_date_wished' => 'required|date|after:voucher_date', //|date_format:Ymd
                    'voucher_type' => 'required',
                    'customs_regime' => 'required',
                    'storage_unit' => 'required',
                    'carrier' => 'required',
                ],
                [
                    'client' => "Le choix du client est obligatoire.",
                    'stock_type' => "Le choix du type de stock est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette référence existe déjà.",
                    'reference_tourn.required' => "La référence de la tournée est obligatoire.",
                    'reference_tourn.unique' => "Cette référence de tournée existe déjà.",
                    'voucher_date.required' => "La date du bon à enlever est obligatoire.",
                    'voucher_date.date' => "La date du bon à enlever est incorrecte.",
                    // 'voucher_date.date_format' => "La date du bon à enlever doit être sous le format : Année Mois Jour.",
                    'voucher_date.date_equals' => "La date du bon à enlever ne peut être qu'aujourd'hui.",
                    'delivery_date_wished.required' => "La date de livraison souhaitée prévue est obligatoire.",
                    'delivery_date_wished.date' => "La date de livraison souhaitée est incorrecte.",
                    // 'delivery_date_wished.date_format' => "La date de livraison souhaitée doit être sous le format : Année Mois Jour.",
                    'delivery_date_wished.after' => "La date de livraison souhaitée doit être ultérieure à la date du bon à enlever.",
                    'voucher_type.required' => "Le type de bon est obligatoire.",
                    'customs_regime.required' => "Le régime douanier est obligatoire.",
                    'storage_unit.required' => "L'unité de stockage est obligatoire.",
                    'carrier.required' => "Le transporteur est obligatoire.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'client' => 'required',
                    'stock_type' => 'required',
                    'reference' => 'required|unique:removal_orders',
                    'voucher_date' => 'required|date|date_format:Ymd|date_equals:today',
                    'delivery_date_wished' => 'required|date|date_format:Ymd|after:voucher_date',
                    'voucher_type' => 'required',
                    'customs_regime' => 'required',
                    'storage_unit' => 'required',
                    'carrier' => 'required',
                ],
                [
                    'stock_type' => "Le choix du client est obligatoire.",
                    'stock_type' => "Le choix du type de stock est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette référence existe déjà.",
                    'voucher_date.required' => "La date du bon à enlever est obligatoire.",
                    'voucher_date.date' => "La date du bon à enlever est incorrecte.",
                    'voucher_date.date_format' => "La date du bon à enlever doit être sous le format : Année Mois Jour.",
                    'voucher_date.date_equals' => "La date du bon à enlever ne peut être qu'aujourd'hui.",
                    'delivery_date_wished.required' => "La date de livraison souhaitée prévue est obligatoire.",
                    'delivery_date_wished.date' => "La date de livraison souhaitée est incorrecte.",
                    'delivery_date_wished.date_format' => "La date de livraison souhaitée doit être sous le format : Année Mois Jour.",
                    'delivery_date_wished.after' => "La date de livraison souhaitée doit être ultérieure à la date du bon à enlever.",
                    'voucher_type.required' => "Le type de bon est obligatoire.",
                    'customs_regime.required' => "Le régime douanier est obligatoire.",
                    'storage_unit.required' => "L'unité de stockage est obligatoire.",
                    'carrier.required' => "Le transporteur est obligatoire.",
                ]
            );
        }
    }
}
