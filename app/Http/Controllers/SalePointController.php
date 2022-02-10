<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\SalePoint;
use App\Repositories\SalePointRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalePointController extends Controller
{
    public $salePointRepository;

    public function __construct(SalePointRepository $salePointRepository)
    {
        $this->salePointRepository = $salePointRepository;
    }
    public function index()
    {
        $this->authorize('ROLE_SALE_POINT_READ', SalePoint::class);
        // $salesPoints = SalePoint::with('institution')->with('transfersDemands')->with('transfers')->with('orders')->with('sales')->with('clientDeliveryNotes')->orderBy('social_reason')->get();
        $salesPoints = SalePoint::with('institution')->orderBy('social_reason')->get();
        $institutions = Institution::orderBy('social_reason')->get();
        return new JsonResponse([
            'datas' => ['salesPoints' => $salesPoints, 'institutions' => $institutions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_SALE_POINT_CREATE', SalePoint::class);
        $errors = $this->validator('store', $request->all());

        $existingSalePoint = SalePoint::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->first();
        if ($existingSalePoint) {
            $success = false;
            return new JsonResponse([
                'existingInstitution' => $existingSalePoint,
                'success' => $success,
                'message' => "Le point de vente " . $existingSalePoint->social_reason . " existe déjà."
            ], 400);
        }

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
                $salePoint = new SalePoint();
                $salePoint->rccm_number = $request->rccm_number;
                $salePoint->cc_number = $request->cc_number;
                $salePoint->social_reason = $request->social_reason;
                $salePoint->address = $request->address;
                $salePoint->email = $request->email;
                $salePoint->bp = $request->bp;
                $salePoint->phone_number = $request->phone_number;
                $salePoint->institution_id = $request->institution;
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
                'errors' => $errors,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_SALE_POINT_READ', SalePoint::class);
        // $salePoint = SalePoint::with('institution')->with('transfersDemands')->with('transfers')->with('orders')->with('sales')->with('clientDeliveryNotes')->findOrFail($id);
        $salePoint = SalePoint::with('institution')->orderBy('social_reason')->findOrFail($id);
        return new JsonResponse([
            'salePoint' => $salePoint
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_SALE_POINT_READ', SalePoint::class);
        // $salePoint = SalePoint::with('institution')->with('transfersDemands')->with('transfers')->with('orders')->with('sales')->with('clientDeliveryNotes')->findOrFail($id);
        $salePoint = SalePoint::with('institution')->orderBy('social_reason')->findOrFail($id);
        return new JsonResponse([
            'salePoint' => $salePoint,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_SALE_POINT_UPDATE', SalePoint::class);
        $salePoint = SalePoint::findOrFail($id);
        $errors = $this->validator('update', $request->all());

        $existingSalePoints = SalePoint::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
        if (!empty($existingSalePoints) && sizeof($existingSalePoints) > 1) {
            return new JsonResponse([
                'existingInstitution' => $existingSalePoints[0],
                'success' => false,
                'message' => "Le point de vente " . $existingSalePoints[0]->social_reason . " existe déjà."
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
                $salePoint->rccm_number = $request->rccm_number;
                $salePoint->cc_number = $request->cc_number;
                $salePoint->social_reason = $request->social_reason;
                $salePoint->address = $request->address;
                $salePoint->email = $request->email;
                $salePoint->bp = $request->bp;
                $salePoint->phone_number = $request->phone_number;
                $salePoint->institution_id = $request->institution;
                $salePoint->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'salePoint' => $salePoint,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_SALE_POINT_DELTE', SalePoint::class);
        $salePoint = SalePoint::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (
                empty($salePoint->orders) || sizeof($salePoint->orders) == 0 &&
                empty($salePoint->purchases) || sizeof($salePoint->purchases) == 0 &&
                empty($salePoint->purchaseOrders) || sizeof($salePoint->purchaseOrders) == 0 &&
                empty($salePoint->sales) || sizeof($salePoint->sales) == 0 &&
                empty($salePoint->removalOrders) || sizeof($salePoint->removalOrders) == 0 &&
                empty($salePoint->transfersDemands) || sizeof($salePoint->transfersDemands) == 0 &&
                empty($salePoint->transfers) || sizeof($salePoint->transfers) == 0
            ) {
                // dd('delete');
                $salePoint->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce point de vente ne peut être supprimé car il a servi dans des traitements.";
            }
            return new JsonResponse([
                'salePoint' => $salePoint,
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

    public function salePointReports(Request $request)
    {
        $this->authorize('ROLE_SALE_POINT_PRINT', SalePoint::class);
        try {
            $salePoints = $this->salePointRepository->oneJoinReport(SalePoint::class, 'sale_points', 'institutions',  'salP', 'inst', 'institution_id', $request->child_selected_fields, $request->parent_selected_fields);
            return new JsonResponse(['datas' => ['salePoints' => $salePoints]], 200);
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
                    'institution' => 'required',
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'email' => 'required|email',
                    'phone_number' => 'required',
                    'address' => 'required',
                    'bp' => 'required',
                ],
                [
                    'institution.required' => "Le choix de l'institution est obligatoire.",
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'email.required' => "L'adresse email est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                    'address.required' => "L'adresse est obligatoire.",
                    'bp.required' => "La boîte postale est obligatoire",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'institution' => 'required',
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'email' => 'required|email',
                    'phone_number' => 'required',
                    'address' => 'required',
                    'bp' => 'required',
                ],
                [
                    'institution.required' => "Le choix de l'institution est obligatoire.",
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'email.required' => "L'adresse email est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                    'address.required' => "L'adresse est obligatoire.",
                    'bp.required' => "La boîte postale est obligatoire",
                ]
            );
        }
    }
}
