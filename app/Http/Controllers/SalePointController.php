<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\SalePoint;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalePointController extends Controller
{
    public function index()
    {
        $salesPoints = SalePoint::orderBy('social_reason')->get();
        $institutions = Institution::orderBy('social_reason')->get();
        return new JsonResponse([
            'datas'=>['salesPoints' => $salesPoints,'institutions' => $institutions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'institution' => 'required',
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'reference' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'address' => 'required',
            ],
            [
                'institution.required' => "Le choix de l'institution est obligatoire.",
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
                'reference.required' => "La reference est obligatoire.",
                'email.required' => "L'adresse email est obligatoire.",
                'email.email' => "L'adresse email est incorrecte.",
                'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                'address.required' => "L'adresse est obligatoire.",
            ],
        );

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
            $salePoint = new SalePoint();
            $salePoint->rccm_number = $request->rccm_number;
            $salePoint->cc_number = $request->cc_number;
            $salePoint->social_reason = $request->social_reason;
            $salePoint->address = $request->address;
            $salePoint->email = $request->email;
            $salePoint->phone_number = $request->phone_number;
            $salePoint->institution_id = $request->institution;
            $salePoint->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'salePoint' => $salePoint,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
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
        $salePoint = SalePoint::findOrFail($id);
        return new JsonResponse([
            'salePoint' => $salePoint
        ], 200);
    }

    public function edit($id)
    {
        $salePoint = SalePoint::findOrFail($id);
        return new JsonResponse([
            'salePoint' => $salePoint,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $salePoint = SalePoint::findOrFail($id);
        $this->validate(
            $request,
            [
                'institution' => 'required',
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'reference' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'address' => 'required',
            ],
            [
                'institution.required' => "Le choix de l'institution est obligatoire.",
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
                'reference.required' => "La reference est obligatoire.",
                'email.required' => "L'adresse email est obligatoire.",
                'email.email' => "L'adresse email est incorrecte.",
                'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                'address.required' => "L'adresse est obligatoire.",
            ],
        );

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
            $salePoint->rccm_number = $request->rccm_number;
            $salePoint->cc_number = $request->cc_number;
            $salePoint->social_reason = $request->social_reason;
            $salePoint->address = $request->address;
            $salePoint->email = $request->email;
            $salePoint->phone_number = $request->phone_number;
            $salePoint->institution_id = $request->institution;
            $salePoint->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'salePoint' => $salePoint,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
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
        $salePoint = SalePoint::findOrFail($id);
        try {
            $salePoint->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'salePoint' => $salePoint,
                'success' => $success,
                'message' => $message,
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
}
