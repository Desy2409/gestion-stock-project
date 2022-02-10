<?php

namespace App\Http\Controllers;

use App\Models\StockType;
use App\Repositories\StockTypeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StockTypeController extends Controller
{
    public $stockTypeRepository;

    public function __construct(StockTypeRepository $stockTypeRepository)
    {
        $this->stockTypeRepository = $stockTypeRepository;
    }
    public function index()
    {
        $this->authorize('ROLE_STOCK_TYPE_READ', StockType::class);
        $stockTypes = StockType::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['stockTypes' => $stockTypes]
        ], 200);
    }

    // Enregistrement d'un nouveau type de stock
    public function store(Request $request)
    {
        $this->authorize('ROLE_STOCK_TYPE_CREATE', StockType::class);

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
                $stockType = new StockType();
                $stockType->code = Str::random(10);
                $stockType->wording = $request->wording;
                $stockType->description = $request->description;
                $stockType->save();

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'stockType' => $stockType,
                    'success' => $success,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    // Mise à jour d'un type de stock
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_STOCK_TYPE_UPDATE', StockType::class);
        $stockType = StockType::findOrFail($id);

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
                $stockType->wording = $request->wording;
                $stockType->description = $request->description;
                $stockType->save();

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'stockType' => $stockType,
                    'success' => $success,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    // Suppression d'un type de stock
    public function destroy($id)
    {
        $this->authorize('ROLE_STOCK_TYPE_DELETE', StockType::class);
        $stockType = StockType::findOrFail($id);
        try {
            $stockType->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'stockType' => $stockType,
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
        $this->authorize('ROLE_STOCK_TYPE_READ', StockType::class);
        $stockType = StockType::findOrFail($id);
        return new JsonResponse([
            'stockType' => $stockType
        ], 200);
    }


    public function stockTypeReports(Request $request)
    {
        $this->authorize('ROLE_STOCK_TYPE_PRINT', StockType::class);
        try {
            $stockTypes = $this->stockTypeRepository->reportIncludeCode(StockType::class, $request->selected_default_fields);
            return new JsonResponse(['datas' => ['stockTypes' => $stockTypes]], 200);
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
                    'wording' => 'required|unique:stock_types|max:150',
                    'description' => 'max:255',
                ],
                [
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Cette unité existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'wording' => 'required|max:150',
                    'description' => 'max:255',
                ],
                [
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
    }
}
