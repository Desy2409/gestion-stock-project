<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function index()
    {
        $this->authorize('ROLE_FOLDER_READ', Folder::class);
        $folders = Folder::with('parent')->with('children')->orderBy('name')->get();
        // $folders = Folder::orderBy('name')->get();
        return new JsonResponse([
            'datas' => ['folders' => $folders]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_FOLDER_CREATE', Folder::class);
        $this->validate(
            $request,
            [
                'affiliation' => 'required',
                'name' => 'required'
            ],
            [
                'affiliation.required' => "Vous devez choisir une affiliation.",
                'name.required' => "Le nom du dossier est obligatoire.",
            ]
        );

        if ($request->affiliation == 'parent') {
            $existingFolders = Folder::where('folder_id', null)->where('name', $request->name)->get();
            if (!empty($existingFolders) && sizeof($existingFolders) > 1) {
                $success = false;
                return new JsonResponse([
                    'existingFolder' => $existingFolders[0],
                    'success' => $success,
                    'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
                ], 400);
            }
            try {
                $folder = new Folder();
                $folder->affiliation = $request->affiliation;
                $folder->name = $request->name;
                $folder->path = $request->name;
                $folder->save();

                Storage::makeDirectory($folder->path);
            } catch (Exception $e) {
                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'folder' => $folder,
                    'success' => $success,
                    'message' => $message,
                ], 200);
            }
        } else {
            $this->validate(
                $request,
                [
                    'folder' => 'required',
                ],
                [
                    'folder.required' => "Le choix du dossier parent est obligatoire.",
                ]
            );

            $existingFolders = Folder::where('folder_id', $request->folder)->where('name', $request->name)->get();
            // dd($existingFolders);
            if (!empty($existingFolders) && sizeof($existingFolders) >= 1) {
                // dd('1');
                $success = false;
                return new JsonResponse([
                    'existingFolder' => $existingFolders[0],
                    'success' => $success,
                    'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
                ], 400);
            }else{
                // dd('2');
            }

            try {
                $parentFolder = new Folder();
                if ($request->folder) {
                    $parentFolder = Folder::findOrFail($request->folder);
                }
                $folder = new Folder();
                $folder->affiliation = $request->affiliation;
                if ($request->folder) {
                    $folder->folder_id = $request->folder;
                }
                $folder->name = $request->name;
                if ($parentFolder) {
                    $folder->path = $parentFolder->name . '/' . $request->name;
                } else {
                    $folder->path = $request->name;
                }
                $folder->save();

                Storage::makeDirectory($folder->path);
            } catch (Exception $e) {
                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'folder' => $folder,
                    'success' => $success,
                    'message' => $message,
                ], 200);
            }
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_FOLDER_UPDATE', Folder::class);
        $folder = Folder::findOrfail($id);
        $parentFolder = $folder ? $folder->parent : null;
        $this->validate(
            $request,
            [
                'affiliation' => 'required',
                'name' => 'required'
            ],
            [
                'affiliation.required' => "Vous devez choisir une affiliation.",
                'name.required' => "Le nom du dossier est obligatoire.",
            ]
        );

        if ($folder->affiliation == 'parent') {
            if ($request->affiliation == 'parent') {
                $existingFolders = Folder::where('folder_id', null)->where('name', $request->name)->get();
                if (!empty($existingFolders) && sizeof($existingFolders) > 1) {
                    $success = false;
                    return new JsonResponse([
                        'existingFolder' => $existingFolders[0],
                        'success' => $success,
                        'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
                    ], 400);
                }
                try {
                    $folder->affiliation = $request->affiliation;
                    $folder->name = $request->name;
                    $folder->path = $request->name;
                    $folder->save();

                    Storage::makeDirectory($folder->path);
                } catch (Exception $e) {
                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'folder' => $folder,
                        'success' => $success,
                        'message' => $message,
                    ], 200);
                }
            } else {
                $this->validate(
                    $request,
                    [
                        'folder' => 'required',
                    ],
                    [
                        'folder.required' => "Le choix du dossier parent est obligatoire.",
                    ]
                );

                $existingFolders = Folder::where('folder_id', $request->folder)->where('name', $request->name)->get();
                if (!empty($existingFolders) && sizeof($existingFolders) > 1) {
                    $success = false;
                    return new JsonResponse([
                        'existingFolder' => $existingFolders[0],
                        'success' => $success,
                        'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
                    ], 400);
                }

                try {
                    $parentFolder = new Folder();
                    if ($request->folder) {
                        $parentFolder = Folder::findOrFail($request->folder);
                    }
                    $folder->affiliation = $request->affiliation;
                    if ($request->folder) {
                        $folder->folder_id = $request->folder;
                    }
                    $folder->name = $request->name;
                    if ($parentFolder) {
                        $folder->path = $parentFolder->name . '/' . $request->name;
                    } else {
                        $folder->path = $request->name;
                    }
                    $folder->save();

                    Storage::move($folder->name, $parentFolder->name . '/' . $folder->name);
                    // Storage::makeDirectory($folder->path);
                } catch (Exception $e) {
                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'folder' => $folder,
                        'success' => $success,
                        'message' => $message,
                    ], 200);
                }
            }
        } else {
            if ($request->affiliation == 'child') {
                $this->validate(
                    $request,
                    [
                        'folder' => 'required',
                    ],
                    [
                        'folder.required' => "Le choix du dossier parent est obligatoire.",
                    ]
                );

                $existingFolders = Folder::where('folder_id', $request->folder)->where('name', $request->name)->get();
                if (!empty($existingFolders) && sizeof($existingFolders) > 1) {
                    $success = false;
                    return new JsonResponse([
                        'existingFolder' => $existingFolders[0],
                        'success' => $success,
                        'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
                    ], 400);
                }

                try {
                    $parentFolder = new Folder();
                    if ($request->folder) {
                        $parentFolder = Folder::findOrFail($request->folder);
                    }

                    $folder->affiliation = $request->affiliation;
                    if ($request->folder) {
                        $folder->folder_id = $request->folder;
                    }
                    $folder->name = $request->name;
                    if ($parentFolder) {
                        $folder->path = $parentFolder->name . '/' . $request->name;
                    } else {
                        $folder->path = $request->name;
                    }
                    $folder->save();

                    if ($parentFolder->id != $folder->folder_id) {
                        Storage::move($folder->name, $parentFolder->name . '/' . $folder->name);
                    }
                    // Storage::makeDirectory($folder->path);
                } catch (Exception $e) {
                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'folder' => $folder,
                        'success' => $success,
                        'message' => $message,
                    ], 200);
                }
            } else {
                $existingFolders = Folder::where('folder_id', null)->where('name', $request->name)->get();
                if (!empty($existingFolders) && sizeof($existingFolders) > 1) {
                    $success = false;
                    return new JsonResponse([
                        'existingFolder' => $existingFolders[0],
                        'success' => $success,
                        'message' => "Le dossier " . $existingFolders[0]->name . " existe déjà."
                    ], 400);
                }
                try {
                    $folder->affiliation = $request->affiliation;
                    $folder->name = $request->name;
                    $folder->path = $request->name;
                    $folder->save();

                    Storage::move($parentFolder->name . '/' . $folder->name, $folder->name);
                } catch (Exception $e) {
                    $success = true;
                    $message = "Enregistrement effectué avec succès.";
                    return new JsonResponse([
                        'folder' => $folder,
                        'success' => $success,
                        'message' => $message,
                    ], 200);
                }
            }
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_FOLDER_DELETE', Folder::class);
        $folder = Folder::findOrFail($id);
        // dd($folder->name);
        try {
                $success = false;
                $message = "";
                if (empty($folder->children) || sizeof($folder->children) == 0) {
                    // dd('delete');
                    $folder->delete();
                    Storage::deleteDirectory($folder->path);
                    $success = true;
                    $message = "Suppression effectuée avec succès.";
                }else{
                    // dd('not delete');
                    $message = "Ce dossier ne peut être supprimé car il a servi dans des traitements.";
                }
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

    public function show($id)
    {
        $this->authorize('ROLE_FOLDER_READ', Folder::class);
        $folder = Folder::findOrFail($id);
        return new JsonResponse([
            'folder' => $folder
        ], 200);
    }
}
