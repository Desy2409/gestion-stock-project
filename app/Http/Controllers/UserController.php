<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Page;
use App\Models\PageOperation;
use App\Models\PasswordHistory;
use App\Models\Person;
use App\Models\SalePoint;
use App\Models\User;
use App\Models\UserType;
use App\Repositories\UserRepository;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // public $user;

    // public function __construct($user)
    // {
    //     // dd(Auth::user());
    //     $user = Auth::user();
    //     $this->user = $user;
    // }

    public $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_USER_READ', User::class);
        $users = User::orderBy('created_at', 'desc')->orderBy('last_name')->orderBy('first_name')->get();
        $userTypes = UserType::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();

        $pages = Page::with('pageOperations')->get();
        $operations = Operation::all();
        // $pageOperations = PageOperation::with('page')->with('operation')->get();

        return new JsonResponse([
            'datas' => ['users' => $users, 'salePoints' => $salePoints, 'userTypes' => $userTypes, 'pages' => $pages, 'operations' => $operations]
        ], 200);
    }

    
    public function pageOperationIdsOnUserTypeSelect($id)
    {
        // $this->authorize('ROLE_USER_READ', User::class);
        $userType = UserType::findOrFail($id);
        $pageOperationIds = $userType->roles ? $this->pageOperationIdsAccordingToUserTypeRoles($userType) : null;

        return new JsonResponse([
            'datas' => ['page_operation_ids' => $pageOperationIds]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_USER_CREATE', User::class);

        $existingUser = Person::where('last_name', $request->last_name)->where('first_name', $request->first_name)->get();
        if (!empty($existingUser) && sizeof($existingUser) >= 1) {
            return new JsonResponse([
                'success' => false,
                'existingUser' => $existingUser[0],
                'message' => "Cet utilisateur existe d??j??"
            ], 200);
        }

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
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->last_name = $request->last_name;
                $user->first_name = $request->first_name;
                $user->date_of_birth = $request->date_of_birth;
                $user->place_of_birth = $request->place_of_birth;
                $user->settings = [
                    'authorized_sale_points' => $request->sale_points
                ];
                $user->user_type_id = $request->user_type;
                $roles = [];
                if (!empty($request->page_operation_ids) && sizeof($request->page_operation_ids) > 0) {
                    foreach ($request->page_operation_ids as $key => $pageOperationId) {
                        $pageOperation = PageOperation::where('id', $pageOperationId)->first();
                        array_push($roles, $pageOperation->code);
                    }
                }
                $user->roles = $roles;
                $user->save();

                $passwordHistory = new PasswordHistory();
                $passwordHistory->user_id = $user->id;
                $passwordHistory->password = Hash::make($user->password);
                $passwordHistory->date = new DateTime();
                $passwordHistory->save();

                $message = "Enregistrement effectu?? avec succ??s.";
                return new JsonResponse([
                    'success' => true,
                    'message' => $message,
                    'user' => $user,
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

    public function edit($id)
    {
        $this->authorize('ROLE_USER_UPDATE', User::class);
        $user = User::findOrFail($id);
        $pageOperationIds = $user->roles ? $this->pageOperationIdsAccordingToUserTypeRoles($user) : null;

        return new JsonResponse([
            'user' => $user,
            'datas' => ['page_operation_ids' => $pageOperationIds]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_USER_UPDATE', User::class);
        $user = User::findOrFail($id);

        $existingUser = Person::where('last_name', $request->last_name)->where('first_name', $request->first_name)->get();
        if (!empty($existingUser) && sizeof($existingUser) >= 1) {
            return new JsonResponse([
                'success' => false,
                'existingUser' => $existingUser[0],
                'message' => "Cet utilisateur existe d??j??"
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
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->last_name = $request->last_name;
                $user->first_name = $request->first_name;
                $user->date_of_birth = $request->date_of_birth;
                $user->place_of_birth = $request->place_of_birth;
                $user->settings = [
                    'authorized_sale_points' => $request->sale_points
                ];
                $user->user_type_id = $request->user_type;
                $roles = [];
                if (!empty($request->page_operation_ids) && sizeof($request->page_operation_ids) > 0) {
                    foreach ($request->page_operation_ids as $key => $pageOperationId) {
                        $pageOperation = PageOperation::where('id', $pageOperationId)->first();
                        array_push($roles, $pageOperation->code);
                    }
                }
                $user->roles = $roles;
                $user->save();

                $passwordHistory = PasswordHistory::where('user_id', $user->id)->latest()->first();
                if ($passwordHistory) {
                    if ($user->password == $passwordHistory->password) {
                        $message = "Le nouveau mot de passe doit ??tre diff??rent de l'ancien.";
                        return new JsonResponse([
                            'success' => false,
                            'message' => $message,
                        ], 200);
                    } else {
                        $passwordHistory = new PasswordHistory();
                        $passwordHistory->user_id = $user->id;
                        $passwordHistory->password = Hash::make($user->password);
                        $passwordHistory->date = new DateTime();
                        $passwordHistory->save();
                    }
                } else {
                    $passwordHistory = new PasswordHistory();
                    $passwordHistory->user_id = $user->id;
                    $passwordHistory->password = Hash::make($user->password);
                    $passwordHistory->date = new DateTime();
                    $passwordHistory->save();
                }

                $message = "Modification effectu??e avec succ??s.";
                return new JsonResponse([
                    'success' => true,
                    'message' => $message,
                    'user' => $user,
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_USER_DELETE', User::class);
        $user = User::findOrFail($id);
        try {
            $user->delete();
            $message = "Suppression effectu??e avec succ??s.";
            return new JsonResponse([
                'user' => $user,
                'success' => true,
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

    public function userTypeConfiguration(Request $request, $id)
    {
        $this->authorize('ROLE_USER_CREATE', User::class);
        $user = User::findOrFail($id);
        $userType = UserType::findOrFail($request->user_type);

        try {
            $user->user_type_id = $userType->id;
            $user->roles = $userType->roles;
            $user->save();

            $message = "Type d'utilisateur attribu?? avec succ??s.";
            return new JsonResponse([
                'user' => $user,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'attribution d'un type ?? l'utilisateur.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function rolesConfiguration(Request $request, $id)
    {
        $this->authorize('ROLE_USER_CREATE', User::class);
        $user = User::findOrFail($id);

        try {
            $roles = array_merge($user->roles, $request->checkedRoles);

            $user->roles = $roles;
            $user->save();

            $message = "R??le(s) affect??(s) avec succ??s.";
            return new JsonResponse([
                'user' => $user,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'affectation des r??les ?? l'utilisateur.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function salePointsConfiguration(Request $request, $id)
    {
        // dd('user',Auth::user()->sale_points->toArray());
        // dd($this->user);

        $user = Auth::user();

        $userSalePoints = SalePoint::whereIn('id', $user->sale_points)->get();
        // dd('userSalePoints', $userSalePoints);


        $this->authorize('ROLE_USER_CREATE', User::class);
        $user = User::findOrFail($id);

        try {
            $user->sale_points = $request->sale_points;
            $user->save();

            $message = "Point(s) de vente affect??(s) avec succ??s.";
            return new JsonResponse([
                'user' => $user,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de l'affectation des points de vente ?? l'utilisateur.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function userReports(Request $request)
    {
        $this->authorize('ROLE_USER_PRINT', User::class);
        try {
            $users = $this->userRepository->userReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['users' => $users]], 200);
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
                    'user_type' => 'required',
                    'email' => 'required|email',
                    'password' => 'required|min:8',
                    'last_name' => 'required|max:30',
                    'first_name' => 'required|max:50',
                    'date_of_birth' => 'required|date|before:today', //|date_format:Ymd
                    'place_of_birth' => 'required|max:100',
                ],
                [
                    'user_type.required' => "Le type d'utilisateur est obligatoire.",
                    'email.required' => "Le champ email est obligatoire.",
                    'email.email' => "Le champ email est incorrect.",
                    'password.required' => "Le champ mot de passe est obligatoire.",
                    'password.min' => "Le champ mot de passe doit contenir au moins 8 caract??res.",
                    'last_name.required' => "e champ nom est obligatoire.",
                    'last_name.max' => "Le champ nom ne doit pas d??passer 30 caract??res.",
                    'first_name.required' => "Le champ pr??nom(s) est obligatoire.",
                    'first_name.max' => "Le champ pr??nom(s) ne doit pas d??passer 50 caract??res.",
                    'date_of_birth.required' => "Le champ date de naissance est obligatoire.",
                    'date_of_birth.date' => "Le champ date de naissance doit ??tre une date valide.",
                    // 'date_of_birth.date_format' => "Le champ date de naissance doit ??tre sous le format : Ann??e Mois Jour.",
                    'date_of_birth.before' => "Le champ date de naissance doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'user_type' => 'required',
                    'email' => 'required|email',
                    'password' => 'required|min:8',
                    'last_name' => 'required|max:30',
                    'first_name' => 'required|max:50',
                    'date_of_birth' => 'required|date|before:today', //|date_format:Ymd
                    'place_of_birth' => 'required|max:100',
                ],
                [
                    'user_type.required' => "Le type d'utilisateur est obligatoire.",
                    'email.required' => "Le champ email est obligatoire.",
                    'email.email' => "Le champ email est incorrect.",
                    'password.required' => "Le champ mot de passe est obligatoire.",
                    'password.min' => "Le champ mot de passe doit contenir au moins 8 caract??res.",
                    'last_name.required' => "e champ nom est obligatoire.",
                    'last_name.max' => "Le champ nom ne doit pas d??passer 30 caract??res.",
                    'first_name.required' => "Le champ pr??nom(s) est obligatoire.",
                    'first_name.max' => "Le champ pr??nom(s) ne doit pas d??passer 50 caract??res.",
                    'date_of_birth.required' => "Le champ date de naissance est obligatoire.",
                    'date_of_birth.date' => "Le champ date de naissance doit ??tre une date valide.",
                    // 'date_of_birth.date_format' => "Le champ date de naissance doit ??tre sous le format : Ann??e Mois Jour.",
                    'date_of_birth.before' => "Le champ date de naissance doit ??tre ant??rieure ou ??gale ?? aujourd'hui.",
                ]
            );
        }
    }

    protected function pageOperationIdsAccordingToUserRoles(User $user)
    {
        $page_operation_ids = [];
        foreach ($user->roles as $key => $role) {
            $pageOperationId = PageOperation::where('code', $role)->pluck('id')->first();
            array_push($page_operation_ids, $pageOperationId);
        }

        return $page_operation_ids;
    }

    
    protected function pageOperationIdsAccordingToUserTypeRoles(UserType $userType)
    {
        $page_operation_ids = [];
        foreach ($userType->roles as $key => $role) {
            $pageOperationId = PageOperation::where('code', $role)->pluck('id')->first();
            array_push($page_operation_ids, $pageOperationId);
        }

        return $page_operation_ids;
    }
}
