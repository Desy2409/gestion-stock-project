<?php

namespace App\Http\Controllers;

use App\Models\ProviderType;
use App\Repositories\ProviderTypeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderTypeController extends Controller
{
    public $providerTypeRepository;
    private $types = ["Raffinerie", "Unité de stockage", "Transport", "Autres fournisseurs"];

    public function __construct(ProviderTypeRepository $providerTypeRepository)
    {
        $this->providerTypeRepository = $providerTypeRepository;
    }


    public function index($type)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_READ', ProviderType::class);

        switch ($type) {
            case 'Raffinerie':
                $providerTypes = ProviderType::where('type', '=', 'Raffinerie')->with('providers')->orderBy('wording')->get();
                break;

            case 'Unité de stockage':
                $providerTypes = ProviderType::where('type', '=', 'Unité de stockage')->with('providers')->orderBy('wording')->get();
                break;

            case 'Transport':
                $providerTypes = ProviderType::where('type', '=', 'Transport')->with('providers')->orderBy('wording')->get();
                break;

            case 'Autres fournisseurs':
                $providerTypes = ProviderType::where('type', '=', 'Autres fournisseurs')->with('providers')->orderBy('wording')->get();
                break;

            default:
                $providerTypes = [];
                break;
        }
        return new JsonResponse([
            'datas' => ['listTypes' => $this->types, 'providerTypes' => $providerTypes]
        ], 200);
    }

    // Enregistrement d'une nouvelle sous-catégorie
    public function store(Request $request)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_CREATE', ProviderType::class);

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
                $providerType = new ProviderType();
                $providerType->reference = $request->reference;
                $providerType->wording = $request->wording;
                $providerType->description = $request->description;
                $providerType->type = $request->type;
                $providerType->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'providerType' => $providerType,
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

    // Mise à jour d'une sous-catégorie
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PROVIDER_TYPE_UPDATE', ProviderType::class);
        $providerType = ProviderType::findOrFail($id);

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
                $providerType->reference = $request->reference;
                $providerType->wording = $request->wording;
                $providerType->description = $request->description;
                $providerType->type = $request->type;
                $providerType->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'providerType' => $providerType,
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
            } else {
                // dd('not delete');
                $message = "Ce type de forunisseur ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'providerType' => $providerType,
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
        $this->authorize('ROLE_PROVIDER_TYPE_PRINT', ProviderType::class);
        try {
            $providerTypes = $this->providerTypeRepository->providerTypeReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['providerTypes' => $providerTypes]], 200);
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
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
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
        }
    }
}
