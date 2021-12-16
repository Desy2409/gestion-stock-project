<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Repositories\InstitutionRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public $institutionRepository;

    public function __construct(InstitutionRepository $institutionRepository)
    {
        $this->institutionRepository = $institutionRepository;
    }
    public function index()
    {
        $this->authorize('ROLE_INSTITUTION_READ', Institution::class);
        $institutions = Institution::with('salesPoints')->orderBy('social_reason')->get();
        return new JsonResponse([
            'datas' => ['institutions' => $institutions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_INSTITUTION_CREATE', Institution::class);
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
        $this->authorize('ROLE_INSTITUTION_READ', Institution::class);
        $institution = Institution::with('salesPoints')->findOrFail($id);
        return new JsonResponse([
            'institution' => $institution
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_INSTITUTION_READ', Institution::class);
        $institution = Institution::with('salesPoints')->findOrFail($id);
        return new JsonResponse([
            'institution' => $institution,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_INSTITUTION_UPDATE', Institution::class);
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
        if (!empty($existingInstitutions) && sizeof($existingInstitutions) > 1) {
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
        $this->authorize('ROLE_INSTITUTION_DELETE', Institution::class);
        $institution = Institution::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($institution->salesPoints) || sizeof($institution->salesPoints) == 0 && empty($institution->deliveryPoints) || sizeof($institution->deliveryPoints) == 0) {
                // dd('delete');
                $institution->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette institution ne peut être supprimée car elle a servi dans des traitements.";
            }

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

    public function institutionReports(Request $request)
    {
        try {
            $institutions = $this->institutionRepository->institutionReport($request->rccm_number, $request->cc_number, $request->social_reason, $request->email, $request->phone_number, $request->address, $request->bp, $request->settings, $request->start_date, $request->end_date);
            return new JsonResponse(['datas' => ['institutions' => $institutions]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
