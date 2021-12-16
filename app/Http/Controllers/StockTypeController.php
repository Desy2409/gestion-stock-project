<?php

namespace App\Http\Controllers;

use App\Models\StockType;
use App\Repositories\StockTypeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $this->validate(
            $request,
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

        try {
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
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    // Mise à jour d'un type de stock
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_STOCK_TYPE_UPDATE', StockType::class);
        $stockType = StockType::findOrFail($id);
        $this->validate(
            $request,
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

        try {
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
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
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
        try {
            $stockTypes = $this->stockTypeRepository->reportIncludeCode(StockType::class, $request->code, $request->wording, $request->description, $request->start_date, $request->end_date);
            return new JsonResponse(['datas' => ['stockTypes' => $stockTypes]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
