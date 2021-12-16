<?php

namespace App\Http\Controllers;

use App\Models\ProviderType;
use App\Repositories\ProviderTypeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderTypeController extends Controller
{
    public $providerTypeRepository;

    public function __construct(ProviderTypeRepository $providerTypeRepository)
    {
        $this->providerTypeRepository = $providerTypeRepository;
    }

    private $types = ["Raffinerie", "Unité de stockage", "Transport", "Autre fournisseur"];

    public function index()
    {
        $this->authorize('ROLE_PROVIDER_TYPE_READ', ProviderType::class);
        $listTypes = $this->types;
        $providerTypes = ProviderType::with('providers')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['listTypes' => $listTypes, 'providerTypes' => $providerTypes]
        ], 200);
    }

    // Enregistrement d'une nouvelle sous-catégorie
    public function store(Request $request)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_CREATE', ProviderType::class);
        $this->validate(
            $request,
            [
                'type' => 'required',
                'reference' => 'required|unique:provider_types',
                'wording' => 'required|unique:provider_types|max:150',
                'description' => 'max:255'
            ],
            [
                'type.required' => "Le type du type de fournisseur est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette réference a déjà été attribuée déjà.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce type de fournisseur existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $providerType = new ProviderType();
            $providerType->reference = $request->reference;
            $providerType->wording = $request->wording;
            $providerType->description = $request->description;
            $providerType->type = $request->type;
            $providerType->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'providerType' => $providerType,
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

    // Mise à jour d'une sous-catégorie
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_UPDATE', ProviderType::class);
        $providerType = ProviderType::findOrFail($id);
        $this->validate(
            $request,
            [
                'type' => 'required',
                'reference' => 'required',
                'wording' => 'required|max:150',
                'description' => 'max:255'
            ],
            [
                'type.required' => "Le type du type de fournisseur est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $providerType->reference = $request->reference;
            $providerType->wording = $request->wording;
            $providerType->description = $request->description;
            $providerType->type = $request->type;
            $providerType->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'providerType' => $providerType,
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

    // Suppression d'une sous-catégorie
    public function destroy($id)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_DELETE', ProviderType::class);
        $providerType = ProviderType::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($providerType->providers) || sizeof($providerType->providers) == 0) {
                // dd('delete');
                $providerType->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            }else{
                // dd('not delete');
                $message = "Ce type de forunisseur ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'providerType' => $providerType,
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

    public function show($id)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_READ', ProviderType::class);
        $providerType = ProviderType::findOrFail($id);
        return new JsonResponse([
            'providerType' => $providerType
        ], 200);
    }

    public function providerTypeReports(Request $request)
    {
        try {
            $providerTypes = $this->providerTypeRepository->providerTypeReport($request->reference, $request->wording, $request->description, $request->type, $request->start_date, $request->end_date);
            return new JsonResponse(['datas' => ['providerTypes' => $providerTypes]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
