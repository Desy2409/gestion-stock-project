<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Type;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::with('roles')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['types' => $types]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:types|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce type existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $type = new Type();
            $type->code = Str::random(10);
            $type->wording = $request->wording;
            $type->description = $request->description;
            $type->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'type' => $type,
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

    public function update(Request $request, $id)
    {

        $type = Type::findOrFail($id);
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

        $existingTypes = Type::where('wording', $request->wording)->get();
        if (!empty($existingTypes) && sizeof($existingTypes) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingType' => $existingTypes[0],
                'message' => "Le type " . $existingTypes[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $type->wording = $request->wording;
            $type->description = $request->description;
            $type->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'type' => $type,
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
        $type = Type::findOrFail($id);
        try {
            $type->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'type' => $type,
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
