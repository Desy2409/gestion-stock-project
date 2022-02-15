<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Page;
use App\Models\PageOperation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Repositories\UserTypeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserTypeController extends Controller
{

    public $userTypeRepository;

    public function __construct(UserTypeRepository $userTypeRepository)
    {
        $this->userTypeRepository = $userTypeRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_USER_TYPE_READ', UserType::class);
        $pages = Page::all();
        $operations = Operation::all();
        // $pageOperations = PageOperation::with('page')->with('operation')->get();
        $userTypes = UserType::orderBy('created_at','desc')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['pages' => $pages, 'operations' => $operations, 'userTypes' => $userTypes]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_USER_TYPE_CREATE', UserType::class);

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
                // Liste des opérations et pages opérations sélectionnées
            $checkedRoles = [];
            // $checkedRoles = json_decode();
            // foreach ($request->roles as $key => $role) {
            //     $checkedRole = Role::where('operation_id', '=', $role->operation_id)->where('page_operation_id', '=', $role->page_operation->id)->first();
            //     array_push($checkedRoles, $checkedRole->code);
            // }

            $userType = new UserType();
            $userType->code = strtoupper(str_replace(' ', '_', $request->code));
            $userType->wording = $request->wording;
            $userType->description = $request->description;
            $userType->roles = $request->roles;
            $userType->save();

            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'userType' => $userType,
                'success' => true,
                'message' => $message,
            ], 200);
            }

            
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_USER_TYPE_UPDATE', UserType::class);
        $userType = UserType::findOrFail($id);

        $existingUserTypesOnCode = UserType::where('code', $request->code)->get();
        if (!empty($existingUserTypesOnCode) && sizeof($existingUserTypesOnCode) > 1) {
            return new JsonResponse([
                'success' => false,
                'existingUserType' => $existingUserTypesOnCode[0],
                'message' => "Le code " . $existingUserTypesOnCode[0]->wording . " existe déjà"
            ], 200);
        }

        $existingUserTypesOnWording = UserType::where('wording', $request->wording)->get();
        if (!empty($existingUserTypesOnWording) && sizeof($existingUserTypesOnWording) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingUserType' => $existingUserTypesOnWording[0],
                'message' => "Le libellé " . $existingUserTypesOnWording[0]->wording . " existe déjà"
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
                $userType->code = strtoupper(str_replace(' ', '_', $request->code));
                $userType->wording = $request->wording;
                $userType->description = $request->description;
                $userType->roles = $request->roles;
                $userType->save();

                $usersOfThisType = User::where('user_type_id', $userType)->get();
                if (!empty($usersOfThisType) && sizeof($usersOfThisType) > 0) {
                    foreach ($usersOfThisType as $key => $user) {
                        $user->roles = $request->roles;
                        $user->save();
                    }
                }

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'userType' => $userType,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_USER_TYPE_DELETE', UserType::class);
        $userType = UserType::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($userType->users) || sizeof($userType->users) == 0) {
                // dd('delete');
                $userType->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette citerne ne peut être supprimée car elle a servi dans des traitements.";
            }
            return new JsonResponse([
                'userType' => $userType,
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

    public function edit($id)
    {
        $this->authorize('ROLE_USER_TYPE_READ', UserType::class);
        $userType = UserType::findOrFail($id);
        // $pageOperations = PageOperation::whereIn('role', $userType->role)->get();

        // $pages = Page::all();
        // $operations = Operation::all();

        return new JsonResponse([
            'userType' => $userType, //'pageOperations' => $pageOperations
        ], 200);
    }

    public function show($id)
    {
        $this->authorize('ROLE_USER_TYPE_READ', UserType::class);
        $userType = UserType::findOrFail($id);

        return new JsonResponse([
            'userType' => $userType
        ], 200);
    }

    public function userTypeReports(Request $request)
    {
        $this->authorize('ROLE_USER_TYPE_PRINT', UserType::class);
        try {
            $userTypes = $this->userTypeRepository->userTypeReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['userTypes' => $userTypes]], 200);
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
                    'code' => 'required|unique:user_types',
                    'wording' => 'required|unique:user_types|max:150',
                    'description' => 'max:255',
                    'roles' => 'required',
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'code.unique' => "Ce code existe déjà.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Ce type existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères.",
                    'roles' => "Le choix d'au moins un rôle est obligatoire.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'code' => 'required',
                    'wording' => 'required|max:150',
                    'description' => 'max:255',
                    'roles' => 'required',
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères.",
                    'roles' => "Le choix d'au moins un rôle est obligatoire.",
                ]
            );
        }
    }
}
