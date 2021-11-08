<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index()
    {
        $folders = Folder::orderBy('name')->get();
        return new JsonResponse([
            'datas' => ['folders' => $folders]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'folder' => 'required|unique:folders'
            ],
            [
                'folder.required' => "Le nom du dossier est obligatoire.",
                'folder.unique' => "Ce dossier existe déjà.",
            ]
        );

        try {
            $folder = new Folder();
            $folder->name = $request->name;
            $folder->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'folder' => $folder,
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
        $folder = Folder::findOrfail($id);
        $this->validate(
            $request,
            [
                'folder' => 'required'
            ],
            [
                'folder.required' => "Le nom du dossier est obligatoire.",
            ]
        );

        $existingFolders=Folder::where('name',$request->name)->get();
        if (!empty($existingFolders)&& sizeof($existingFolders)>1) {
            $success = false;
            return new JsonResponse([
                'existingInstitution' => $existingFolders[0],
                'success' => $success,
                'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
            ], 400);
        }

        try {
            $folder->name = $request->name;
            $folder->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'folder' => $folder,
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
        $folder = Folder::findOrFail($id);
        try {
            $folder->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'folder' => $folder,
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
