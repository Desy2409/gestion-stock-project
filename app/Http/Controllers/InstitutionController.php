<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index()
    {
        $institutions = Institution::with('salesPoints')->orderBy('social_reason')->get();
        return new JsonResponse([
            'datas' => ['institutions' => $institutions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'address' => 'required',
                'bp' => 'required',
            ],
            [
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
                'email.required' => "L'adresse email est obligatoire.",
                'email.email' => "L'adresse email est incorrecte.",
                'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                'address.required' => "L'adresse est obligatoire.",
                'bp.required' => "La boîte postale est obligatoire",
            ],
        );

        $existingInstitution = Institution::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->first();
        if ($existingInstitution) {
            $success = false;
            return new JsonResponse([
                'existingInstitution' => $existingInstitution,
                'success' => $success,
                'message' => "L'institution " . $existingInstitution->social_reason . " existe déjà."
            ], 200);
        }

        try {
            $institution = new Institution();
            $institution->rccm_number = $request->rccm_number;
            $institution->cc_number = $request->cc_number;
            $institution->social_reason = $request->social_reason;
            $institution->address = $request->address;
            $institution->email = $request->email;
            $institution->bp = $request->bp;
            $institution->phone_number = $request->phone_number;
            $institution->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'institution' => $institution,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $institution = Institution::with('salesPoints')->findOrFail($id);
        return new JsonResponse([
            'institution' => $institution
        ], 200);
    }

    public function edit($id)
    {
        $institution = Institution::with('salesPoints')->findOrFail($id);
        return new JsonResponse([
            'institution' => $institution,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $institution = Institution::findOrFail($id);
        $this->validate(
            $request,
            [
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'address' => 'required',
                'bp' => 'required',
            ],
            [
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
                'email.required' => "L'adresse email est obligatoire.",
                'email.email' => "L'adresse email est incorrecte.",
                'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                'address.required' => "L'adresse est obligatoire.",
                'bp.required' => "La boîte postale est obligatoire",
            ],
        );

        $existingInstitutions = Institution::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
        if (!empty($existingInstitutions)&&sizeof($existingInstitutions)>1) {
            $success = false;
            return new JsonResponse([
                'existingInstitution' => $existingInstitutions[0],
                'success' => $success,
                'message' => "L'institution " . $existingInstitutions[0]->social_reason . " existe déjà."
            ], 200);
        }

        try {
            $institution->rccm_number = $request->rccm_number;
            $institution->cc_number = $request->cc_number;
            $institution->social_reason = $request->social_reason;
            $institution->address = $request->address;
            $institution->email = $request->email;
            $institution->bp = $request->bp;
            $institution->phone_number = $request->phone_number;
            $institution->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'institution' => $institution,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $institution = Institution::findOrFail($id);
        try {
            $institution->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'institution' => $institution,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }
}
