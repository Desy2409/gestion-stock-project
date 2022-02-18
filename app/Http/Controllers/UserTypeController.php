<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Page;
use App\Models\PageOperation;
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
        $pages = Page::with('pageOperations')->get();
        $operations = Operation::all();
        // $pageOperations = PageOperation::with('page')->with('operation')->get();
        $userTypes = UserType::orderBy('created_at', 'desc')->orderBy('wording')->get();
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
                $userType = new UserType();
                $userType->code = strtoupper(str_replace(' ', '_', $request->code));
                $userType->wording = $request->wording;
                $userType->description = $request->description;
                $roles = [];
                if (!empty($request->page_operation_ids) && sizeof($request->page_operation_ids) > 0) {
                    foreach ($request->page_operation_ids as $key => $pageOperationId) {
                        $pageOperation = PageOperation::where('id', $pageOperationId)->first();
                        array_push($roles, $pageOperation->code);
                    }
                }
                $userType->roles = $roles;
                $userType->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'userType' => $userType,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            dd($e);
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
        // dd($userType);

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
            // dd($request->all());
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
            $roles = [];
            // dd($request->page_operations);
            if (!empty($request->page_operation_ids) && sizeof($request->page_operation_ids) > 0) {
                foreach ($request->page_operation_ids as $key => $pageOperationId) {
                    $pageOperation = PageOperation::where('id', $pageOperationId)->first();
                    array_push($roles, $pageOperation->code);
                }
            }
            $userType->roles = $roles;
            $userType->save();

            if ($request->update_user_roles) {
                // dd('update_user_roles');
                $this->userRoleAccordingToUserTypeRoles($userType);
            }

            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'userType' => $userType,
                'success' => true,
                'message' => $message,
            ], 200);
            }
        } catch (Exception $e) {
            dd($e);
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
                $message = "Ce type d'utilisateur ne peut être supprimée car il a servi dans des traitements.";
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
        

        return new JsonResponse([
            'userType' => $userType, 'page_operation_ids' => $this->pageOperationIdsAccordingToUserTypeRoles($userType)
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
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'code.unique' => "Ce code existe déjà.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Ce type existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères.",
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
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères.",
                ]
            );
        }
    }

    protected function userRoleAccordingToUserTypeRoles(UserType $userType)
    {
        $usersOfUserType = User::where('user_type_id', $userType->id)->get();
        if (!empty($usersOfUserType) && sizeof($usersOfUserType) > 0) {
            foreach ($usersOfUserType as $key => $user) {
                $user->roles = $userType->roles;
                $user->save();
            }
        }
    }

    protected function pageOperationIdsAccordingToUserTypeRoles(UserType $userType){
        $page_operation_ids=[];
        foreach ($userType->roles as $key => $role) {
            $pageOperationId = PageOperation::where('code',$role)->pluck('id')->toArray();
            array_push($page_operation_ids,$pageOperationId);
        }

        return $page_operation_ids;
    }
}
