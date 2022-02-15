<?php

namespace App\Http\Controllers;

use App\Models\Taxe;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxeController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $this->authorize('ROLE_TAXE_READ', Taxe::class);
        $taxes = Taxe::orderBy('created_at','desc')->orderBy('wording')->get();

        return new JsonResponse(['datas' => ['taxes' => $taxes]], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_TAXE_CREATE', Taxe::class);

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
                $taxe = new Taxe();
                $taxe->reference = $request->reference;
                $taxe->wording = $request->wording;
                $taxe->value_type = $request->value_type;
                $taxe->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'taxe' => $taxe,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TAXE_UPDATE', Taxe::class);
        $taxe = Taxe::findOrFail($id);

        $existingTaxes = Taxe::where('reference', $request->reference)->get();
        if (!empty($existingTaxes) && sizeof($existingTaxes) > 1) {
            return new JsonResponse([
                'existingTaxe' => $existingTaxes[0],
                'success' => false,
                'message' => "Cette taxe existe déjà."
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
                $taxe->reference = $request->reference;
                $taxe->wording = $request->wording;
                $taxe->value_type = $request->value_type;
                $taxe->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'taxe' => $taxe,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_TAXE_DELETE', Taxe::class);
        $taxe = Taxe::findOrFail($id);
        try {
            $taxe->delete();

            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'success' => true,
                'message' => $message
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_TAXE_READ', Taxe::class);
        $taxe = Taxe::findOrFail($id);
        return new JsonResponse([
            'taxe' => $taxe
        ], 200);
    }

    public function taxeReports()
    {
        $this->authorize('ROLE_TAXE_PRINT', Taxe::class);
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'reference' => 'required|unique:taxes',
                    'wording' => 'required|unique:taxes',
                    'value_type' => 'required',
                ],
                [
                    'reference.required' => "La référence est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'value_type.required' => "Le ype de valeur est obligatoire.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'reference' => 'required',
                    'wording' => 'required',
                    'value_type' => 'required',
                ],
                [
                    'reference.required' => "La référence est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'value_type.required' => "Le ype de valeur est obligatoire.",
                ]
            );
        }
    }
}
