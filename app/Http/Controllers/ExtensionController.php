<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExtensionController extends Controller
{
    public function index()
    {
        $extensions = Extension::orderBy('extension')->get();
        return new JsonResponse([
            'datas' => ['extensions' => $extensions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'extension' => 'required|unique:extensions'
            ],
            [
                'extension.required' => "L'extension est obligatoire.",
                'extension.unique' => "Cette extension existe déjà.",
            ]
        );

        try {
            $extension = new Extension();
            $extension->code = Str::lower('.' . $request->extension);
            $extension->extension = strtoupper($request->extension);
            $extension->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'extension' => $extension,
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
        $extension = Extension::findOrfail($id);
        $this->validate(
            $request,
            [
                'extension' => 'required|unique:extensions'
            ],
            [
                'extension.required' => "L'extension est obligatoire.",
                'extension.unique' => "Cette extension existe déjà.",
            ]
        );

        try {
            $extension->extension = strtoupper($request->extension);
            $extension->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'extension' => $extension,
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
        $extension = Extension::findOrFail($id);
        try {
            $extension->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'extension' => $extension,
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
