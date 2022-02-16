<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\DeliveryPoint;
use App\Models\Destination;
use App\Models\Institution;
use App\Models\SalePoint;
use App\Repositories\DeliveryPointRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryPointController extends Controller
{
    use UtilityTrait;

    public $deliveryPointRepository;

    public function __construct(DeliveryPointRepository $deliveryPointRepository)
    {
        $this->deliveryPointRepository = $deliveryPointRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_DELIVERY_POINT_READ', DeliveryPoint::class);
        $deliveryPoints = DeliveryPoint::orderBy('created_at', 'desc')->with('institution')->orderBy('wording')->get();
        $destinations = Destination::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $clients = Client::with('person')->get();
        return new JsonResponse([
            'datas' => ['deliveryPoints' => $deliveryPoints, 'salePoints' => $salePoints, 'clients' => $clients, 'destinations' => $destinations]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_DELIVERY_POINT_CREATE', DeliveryPoint::class);
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
                $deliveryPoint = new DeliveryPoint();
                $deliveryPoint->code = $request->code;
                $deliveryPoint->wording = $request->wording;
                $deliveryPoint->latitude = $request->latitude;
                $deliveryPoint->longitude = $request->longitude;
                $deliveryPoint->description = $request->description;
                $deliveryPoint->institution_id = $request->institution;
                $deliveryPoint->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'deliveryPoint' => $deliveryPoint,
                    'success' => true,
                    'message' => $message,
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

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_DELIVERY_POINT_UPDATE', DeliveryPoint::class);
        $deliveryPoint = DeliveryPoint::findOrFail($id);

        $existingDeliveryPointsOnCode = DeliveryPoint::where('wording', $request->wording)->get();
        if (!empty($existingDeliveryPointsOnCode) && sizeof($existingDeliveryPointsOnCode) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingDeliveryPoint' => $existingDeliveryPointsOnCode[0],
                'message' => "Le lieu de livraison portant le code " . $existingDeliveryPointsOnCode[0]->wording . " existe déjà"
            ], 200);
        }

        $existingDeliveryPointsOnWording = DeliveryPoint::where('wording', $request->wording)->get();
        if (!empty($existingDeliveryPointsOnWording) && sizeof($existingDeliveryPointsOnWording) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingDeliveryPoint' => $existingDeliveryPointsOnWording[0],
                'message' => "Le lieu de livraison " . $existingDeliveryPointsOnWording[0]->wording . " existe déjà"
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
                $deliveryPoint->code = $request->code;
                $deliveryPoint->wording = $request->wording;
                $deliveryPoint->latitude = $request->latitude;
                $deliveryPoint->longitude = $request->longitude;
                $deliveryPoint->description = $request->description;
                $deliveryPoint->institution_id = $request->institution;
                $deliveryPoint->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'deliveryPoint' => $deliveryPoint,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_DELIVERY_POINT_DELETE', DeliveryPoint::class);
        $deliveryPoint = DeliveryPoint::findOrFail($id);
        try {
            $deliveryPoint->delete();
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'deliveryPoint' => $deliveryPoint,
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

    public function show($id)
    {
        $this->authorize('ROLE_DELIVERY_POINT_READ', DeliveryPoint::class);
        $deliveryPoint = DeliveryPoint::findOrFail($id);
        return new JsonResponse([
            'deliveryPoint' => $deliveryPoint
        ], 200);
    }

    public function deliveryPointReports(Request $request)
    {
        $this->authorize('ROLE_DELIVERY_POINT_PRINT', DeliveryPoint::class);
        try {
            $deliveryPoints = $this->deliveryPointRepository->deliveryPointReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['deliveryPoints' => $deliveryPoints]], 200);
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
                    'code' => 'required|unique:delivery_points',
                    'wording' => 'required|unique:delivery_points|max:150',
                    'description' => 'max:255',
                    'latitude' => 'integer',
                    'longitude' => 'integer',
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'code.unique' => "Ce code existe déjà.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Ce lieu de livraison existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'latitude.integer' => "La latitude doit être un nombre.",
                    'longitude.integer' => "La longitude doit être un nombre.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'code' => 'required',
                    'wording' => 'required|max:150',
                    'description' => 'max:255',
                    'latitude' => 'integer',
                    'longitude' => 'integer',
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Ce lieu de livraison existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'latitude.integer' => "La latitude doit être un nombre.",
                    'longitude.integer' => "La longitude doit être un nombre.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères.",
                ]
            );
        }
    }
}
