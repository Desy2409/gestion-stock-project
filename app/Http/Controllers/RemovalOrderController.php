<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\ClientDeliveryNote;
use App\Models\Compartment;
use App\Models\Order;
use App\Models\ProductClientDeliveryNote;
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
use App\Repositories\RemovalOrderRepository;
use App\Repositories\TankRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RemovalOrderController extends Controller
{

    use UtilityTrait;

    private $customsRegimes = ["HT", "TTC"];

    public $purchaseOrderRepository;
    public $tankRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository, TankRepository $tankRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->tankRepository = $tankRepository;
    }

    public function index($voucherType)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $purchaseOrders = $this->purchaseOrderRepository->purchaseOrderBasedOnClientDeliveryNote();

        if ($voucherType == "Externe") {
            $removalOrders = RemovalOrder::where('voucher_type', '=', 'Externe')->get();
        }
        if ($voucherType == "Interne") {
            $removalOrders = RemovalOrder::where('voucher_type', '=', 'Interne')->get();
        }

        $idOfProviderTypeStorageUnits = ProviderType::where('type', "Unité de stockage")->pluck('id')->toArray();
        $idOfProviderTypeCarriers = ProviderType::where('type', "Transport")->pluck('id')->toArray();

        $removalOrders = RemovalOrder::orderBy('created_at', 'desc')->orderBy('voucher_date')->orderBy('reference')->get();
        $storageUnits = Provider::whereIn('provider_type_id', $idOfProviderTypeStorageUnits)->with('person')->get();
        $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();
        $compartments = Compartment::orderBy('reference')->get();
        $currentTourns = Tourn::where('state', '!=', 'C')->get();

        $lastRemovalOrderRegister = RemovalOrderRegister::latest()->first();

        $removalOrderRegister = new RemovalOrderRegister();
        if ($lastRemovalOrderRegister) {
            $removalOrderRegister->code = $this->formateNPosition(RemovalOrder::class, $lastRemovalOrderRegister->id + 1);
        } else {
            $removalOrderRegister->code = $this->formateNPosition(RemovalOrder::class, 1);
        }
        $removalOrderRegister->save();

        return new JsonResponse(['datas' => [
            'removalOrders' => $removalOrders, 'purchaseOrders' => $purchaseOrders, 'compartments' => $compartments,
            'storageUnits' => $storageUnits, 'carriers' => $carriers, 'customsRegimes' => $this->customsRegimes,
            'currentTourns' => $currentTourns,
        ]], 200);
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

    public function externalRemovalOrderDatasOnPurchaseOrderSelect($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $client = $purchaseOrder ? $purchaseOrder->client : null;
        $salePoint = $purchaseOrder ? $purchaseOrder->salePoint : null;

        return new JsonResponse([
            'client' => $client, 'salePoint' => $salePoint,
        ], 200);
    }

    public function internalRemovalOrderDatasOnTransferSelect($id)
    {
        $transfer = Transfer::findOrFail($id);
        $client = $transfer ? $transfer->client : null;
        // $salePoint = $transfer ? $transfer->salePoint : null;
        $transmitter = SalePoint::findOrFail($transfer->transmitter_id);
        $receiver = SalePoint::findOrFail($transfer->receiver_id);

        return new JsonResponse([
            'client' => $client, //'salePoint' => $salePoint,
            'transmitter' => $transmitter, 'receiver' => $receiver,
        ], 200);
    }

    public function trucksOfSelectedTransportProvider($id)
    {
        $trucks = Truck::where('provider_id', $id)->get();
        return new JsonResponse(['datas' => ['trucks' => $trucks]], 200);
    }

    public function tanksOfSelectedTruck($id)
    {
        $tanks = $this->tankRepository->tanksOfSelectedTruck($id);
        return new JsonResponse(['datas' => ['tanks' => $tanks]], 200);
    }

    public function loadAllClientDeliveryNotes()
    {
        $clientDeliveryNotes = ClientDeliveryNote::orderBy('created_at', 'desc')->with('productClientDeliveryNotes')->get();
        return new JsonResponse(['datas' => ['clientDeliveryNotes' => $clientDeliveryNotes]], 200);
    }

    public function loadAllTransfers()
    {
        $transfers = Transfer::orderBy('created_at', 'desc')->with('productTransfers')->get();
        return new JsonResponse(['datas' => ['transfers' => $transfers]], 200);
    }

    public function datasOnClientDeliveryNoteSelect($id)
    {
        $clientDeliveryNote = ClientDeliveryNote::with('sale')->findOrFail($id);
        $client = $clientDeliveryNote ? $clientDeliveryNote->sale->client : null;
        $salePoint = $clientDeliveryNote ? $clientDeliveryNote->sale->salePoint : null;
        $deliveryDate = $clientDeliveryNote ? $clientDeliveryNote->delivery_date : null;
        $purchaseOrder = $clientDeliveryNote ? $clientDeliveryNote->sale->purchaseOrder : null;
        // $productClientDeliveryNotes = $clientDeliveryNote ? $clientDeliveryNote->productClientDeliveryNotes : null;
        $productClientDeliveryNotes = $clientDeliveryNote ? ProductClientDeliveryNote::where('client_delivery_note_id', $clientDeliveryNote->id)->with('product')->with('unity')->get() : null;

        return new JsonResponse([
            'client' => $client, 'salePoint' => $salePoint,
            'deliveryDate' => $deliveryDate, 'purchaseOrder' => $purchaseOrder,
            'datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes],
        ], 200);
    }


    // public function onCarrierSelect($id)
    // {
    //     $provider = Provider::with('person')->with('trucks')->findOrFail($id);
    //     return new JsonResponse(['provider' => $provider], 200);
    // }

    public function show($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $removalOrder = RemovalOrder::with('tourn')->with('client')->findOrFail($id);
        return new JsonResponse([
            'removalOrder' => $removalOrder
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $removalOrder = RemovalOrder::with('tourn')->with('client')->findOrFail($id);
        return new JsonResponse([
            'removalOrder' => $removalOrder,
        ], 200);
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
                $removalOrder->wording = $request->wording;
                $removalOrder->description = $request->description;
                $removalOrder->voucher_date = $request->voucher_date;
                $removalOrder->delivery_date_wished = $request->delivery_date_wished;
                $removalOrder->place_of_delivery = $request->place_of_delivery;
                $removalOrder->voucher_type = $request->voucher_type;
                $removalOrder->customs_regime = $request->customs_regime;
                $removalOrder->storage_unit_id = $request->storage_unit;
                $removalOrder->carrier_id = $request->carrier;
                $removalOrder->transmitter_id = $request->transmitter;
                // $removalOrder->sale_point_id = $request->sale_point;
                $removalOrder->client_id = $request->client;
                // $removalOrder->stock_type_id = $request->stock_type;
                $removalOrder->save();

                $lastTourn = Tourn::latest()->first();

                $tourn = new Tourn();
                if ($lastTourn) {
                    $tourn->code = $this->formateNPosition(Tourn::class, $lastTourn->id + 1);
                } else {
                    $tourn->code = $this->formateNPosition(Tourn::class, 1);
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

                // $productsTourns = [];
                foreach ($request->productTourns as $key => $productTournLine) {
                    // dd($productTournLine);
                    $productTourn = new ProductTourn();
                    $productTourn->quantity = $productTournLine['quantity'];
                    $productTourn->product_id = $productTournLine['product_id'];
                    $productTourn->unity_id = $productTournLine['unity_id'];
                    $productTourn->compartment_id = $productTournLine['compartment_id'];
                    $productTourn->tourn_id = $tourn->id;
                    $productTourn->save();

                    // array_push($productsTourns, $productTourn);
                }

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'removalOrder' => $removalOrder,
                    'success' => true,
                    'message' => $message,
                    // 'datas' => ['productsTourns' => $productsTourns],
                ], 200);
            }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_CREATE', RemovalOrder::class);
        $removalOrder = RemovalOrder::findOrFail($id);
        $tourn = $removalOrder ? $removalOrder->tourn : null;

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
                $removalOrder->reference = $request->reference;
                $removalOrder->wording = $request->wording;
                $removalOrder->description = $request->description;
                $removalOrder->voucher_date = $request->voucher_date;
                $removalOrder->delivery_date_wished = $request->delivery_date_wished;
                $removalOrder->place_of_delivery = $request->place_of_delivery;
                $removalOrder->voucher_type = $request->voucher_type;
                $removalOrder->customs_regime = $request->customs_regime;
                $removalOrder->storage_unit_id = $request->storage_unit;
                $removalOrder->carrier_id = $request->carrier;
                $removalOrder->transmitter_id = $request->transmitter;
                // $removalOrder->sale_point_id = $request->sale_point;
                $removalOrder->client_id = $request->client;
                // $removalOrder->stock_type_id = $request->stock_type;
                $removalOrder->save();

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

                ProductTourn::where('tourn_id', $tourn->id)->delete();
                foreach ($request->productTourns as $key => $productTournLine) {
                    $productTourn = new ProductTourn();
                    $productTourn->quantity = $productTournLine['quantity'];
                    $productTourn->product_id = $productTournLine['product_id'];
                    $productTourn->unity_id = $productTournLine['unity_id'];
                    $productTourn->compartment_id = $productTournLine['compartment_id'];
                    $productTourn->tourn_id = $tourn->id;
                    $productTourn->save();
                }

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'removalOrder' => $removalOrder,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            dd($e);
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
            $success = false;
            $message = "";
            if (!$removalOrder->tourn) {
                $removalOrder->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                $message = "Ce bon à enlever ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'removalOrder' => $removalOrder,
                'success' => $success,
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
                    // 'client' => 'required',
                    // 'stock_type' => 'required',
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
                    // 'client' => "Le choix du client est obligatoire.",
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
                    // 'client' => 'required',
                    // 'stock_type' => 'required',
                    'reference' => 'required|unique:removal_orders',
                    'voucher_date' => 'required|date|date_equals:today', //|date_format:Ymd
                    'delivery_date_wished' => 'required|date|after:voucher_date', //|date_format:Ymd
                    'voucher_type' => 'required',
                    'customs_regime' => 'required',
                    'storage_unit' => 'required',
                    'carrier' => 'required',
                ],
                [
                    // 'stock_type' => "Le choix du client est obligatoire.",
                    // 'stock_type' => "Le choix du type de stock est obligatoire.",
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
