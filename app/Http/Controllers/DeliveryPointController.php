<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\DeliveryPoint;
use App\Models\Destination;
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
        $destintations = Destination::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $clients = Client::with('person')->get();
        return new JsonResponse([
            'datas' => ['deliveryPoints' => $deliveryPoints, 'salePoints' => $salePoints, 'clients' => $clients, 'destintations' => $destintations]
        ], 200);
    }

    public function configClientDeliveryPoint(Request $request)
    {
        $this->authorize('ROLE_DELIVERY_POINT_CREATE', DeliveryPoint::class);
        $client = Client::findOrFail($request->client);

        try {
            $validation = $this->validator($request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {

                $client->delivery_points = $request->delivery_points;
                $client->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'client' => $client,
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

    public function configSalePointDeliveryPoint(Request $request)
    {
        $this->authorize('ROLE_DELIVERY_POINT_CREATE', DeliveryPoint::class);
        $salePoint = SalePoint::findOrFail($request->sale_point);

        try {
            $validation = $this->validator($request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {

                $salePoint->delivery_points = $request->delivery_points;
                $salePoint->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'salePoint' => $salePoint,
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

    // public function update(Request $request, $id)
    // {
    //     $this->authorize('ROLE_DELIVERY_POINT_UPDATE', DeliveryPoint::class);
    //     $deliveryPoint = DeliveryPoint::findOrFail($id);

    //     $existingDeliveryPointsOnCode = DeliveryPoint::where('wording', $request->wording)->get();
    //     if (!empty($existingDeliveryPointsOnCode) && sizeof($existingDeliveryPointsOnCode) >= 1) {
    //         $success = false;
    //         return new JsonResponse([
    //             'success' => $success,
    //             'existingDeliveryPoint' => $existingDeliveryPointsOnCode[0],
    //             'message' => "Le lieu de livraison portant le code " . $existingDeliveryPointsOnCode[0]->wording . " existe déjà"
    //         ], 200);
    //     }

    //     $existingDeliveryPointsOnWording = DeliveryPoint::where('wording', $request->wording)->get();
    //     if (!empty($existingDeliveryPointsOnWording) && sizeof($existingDeliveryPointsOnWording) >= 1) {
    //         $success = false;
    //         return new JsonResponse([
    //             'success' => $success,
    //             'existingDeliveryPoint' => $existingDeliveryPointsOnWording[0],
    //             'message' => "Le lieu de livraison " . $existingDeliveryPointsOnWording[0]->wording . " existe déjà"
    //         ], 200);
    //     }

    //     try {
    //         $validation = $this->validator($request->all());

    //         if ($validation->fails()) {
    //             $messages = $validation->errors()->all();
    //             $messages = implode('<br/>', $messages);
    //             return new JsonResponse([
    //                 'success' => false,
    //                 'message' => $messages,
    //             ], 200);
    //         } else {
    //             $deliveryPoint->code = $request->code;
    //             $deliveryPoint->wording = $request->wording;
    //             $deliveryPoint->latitude = $request->latitude;
    //             $deliveryPoint->longitude = $request->longitude;
    //             $deliveryPoint->description = $request->description;
    //             $deliveryPoint->institution_id = $request->institution;
    //             $deliveryPoint->save();

    //             $message = "Enregistrement effectué avec succès.";
    //             return new JsonResponse([
    //                 'deliveryPoint' => $deliveryPoint,
    //                 'success' => true,
    //                 'message' => $message,
    //             ], 200);
    //         }
    //     } catch (Exception $e) {
    //         $success = false;
    //         $message = "Erreur survenue lors de l'enregistrement.";
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => $message,
    //         ], 200);
    //     }
    // }

    // public function destroy($id)
    // {
    //     $this->authorize('ROLE_DELIVERY_POINT_DELETE', DeliveryPoint::class);
    //     $deliveryPoint = DeliveryPoint::findOrFail($id);
    //     try {
    //         $deliveryPoint->delete();
    //         $message = "Suppression effectuée avec succès.";
    //         return new JsonResponse([
    //             'deliveryPoint' => $deliveryPoint,
    //             'success' => true,
    //             'message' => $message,
    //         ], 200);
    //     } catch (Exception $e) {
    //         $message = "Erreur survenue lors de la suppression.";
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => $message,
    //         ], 200);
    //     }
    // }

    // public function show($id)
    // {
    //     $this->authorize('ROLE_DELIVERY_POINT_READ', DeliveryPoint::class);
    //     $deliveryPoint = DeliveryPoint::findOrFail($id);
    //     return new JsonResponse([
    //         'deliveryPoint' => $deliveryPoint
    //     ], 200);
    // }

    // public function deliveryPointReports(Request $request)
    // {
    //     $this->authorize('ROLE_DELIVERY_POINT_PRINT', DeliveryPoint::class);
    //     try {
    //         $deliveryPoints = $this->deliveryPointRepository->deliveryPointReport($request->selected_default_fields);
    //         return new JsonResponse(['datas' => ['deliveryPoints' => $deliveryPoints]], 200);
    //     } catch (Exception $e) {
    //         dd($e);
    //     }
    // }

    protected function validator($data)
    {
        return Validator::make(
            $data,
            [
                'delivery_points' => 'required'
            ],
            [
                'delivery_points.required' => "Veuillez renseigner au moins un point de livraison.",
            ]
        );
    }
}
