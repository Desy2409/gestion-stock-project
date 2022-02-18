<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Institution;
use App\Models\Product;
use App\Models\TableSetting;
use App\Repositories\InstitutionRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstitutionController extends Controller
{
    use UtilityTrait;

    public $institutionRepository;
    // public $tablesConcernBySettingCodeMinLength = [
    //     "App\Models\Poduct", "App\Models\Client", "App\Models\Provider",
    //     "App\Models\Order", "App\Models\Purchase", "App\Models\DeliveryNote", "App\Models\PurchaseOrder",
    //     "App\Models\Sale", "App\Models\ClientDeliveryNote", "App\Models\TransferDemand", //"App\Models\Transfer",
    //     "App\Models\RemovalOrder", "App\Models\Tourn"
    // ];

    // public $tablesConcernBySettingValidationNumberAndLevel = [
    //     "App\Models\Order", "App\Models\Purchase", "App\Models\DeliveryNote", "App\Models\PurchaseOrder",
    //     "App\Models\Sale", "App\Models\ClientDeliveryNote", "App\Models\TransferDemand", //"App\Models\Transfer",
    //     "App\Models\RemovalOrder", "App\Models\Tourn"
    // ];

    public $tablesConcernBySetting = [
        Product::class, Client::class, Provider::class,
        Order::class, Purchase::class, DeliveryNote::class, PurchaseOrder::class,
        Sale::class, ClientDeliveryNote::class, TransferDemand::class, //Transfer::class,
        RemovalOrder::class, Tourn::class
    ];

    public function __construct(InstitutionRepository $institutionRepository)
    {
        $this->institutionRepository = $institutionRepository;
        // $this->tablesConcernbySetting = $tablesConcernbySetting;
        // $this->tablesConcernBySettingValidationNumberAndLevel = $tablesConcernBySettingValidationNumberAndLevel;
    }

    public function index()
    {
        $this->authorize('ROLE_INSTITUTION_READ', Institution::class);
        $institutions = Institution::orderBy('created_at', 'desc')->with('salesPoints')->orderBy('social_reason')->get();
        return new JsonResponse([
            'datas' => ['institutions' => $institutions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_INSTITUTION_CREATE', Institution::class);

        // $existingInstitution = Institution::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->first();
        // if ($existingInstitution) {
        //     $success = false;
        //     return new JsonResponse([
        //         'existingInstitution' => $existingInstitution,
        //         'success' => $success,
        //         'message' => "L'institution " . $existingInstitution->social_reason . " existe déjà."
        //     ], 200);
        // }

        try {
            // $validation = $this->validator('store', $request->all());

            // if ($validation->fails()) {
            //     $messages = $validation->errors()->all();
            //     $messages = implode('<br/>', $messages);
            //     return new JsonResponse([
            //         'success' => false,
            //         'message' => $messages,
            //     ], 200);
            // } else {
            $institution = new Institution();
            $institution->rccm_number = $request->rccm_number;
            $institution->cc_number = $request->cc_number;
            $institution->social_reason = $request->social_reason;
            $institution->address = $request->address;
            $institution->email = $request->email;
            $institution->bp = $request->bp;
            $institution->phone_number = $request->phone_number;
            $institution->settings = [
                'blocking_number_of_attempt' => $request->blocking_number_of_attempt,
                'principal_currency' => $request->principal_currency,
                'principal_unit_of_measure' => $request->principal_unit_of_measure,
                'from_order_to_delivery' => $request->from_order_to_delivery,
                'password_complexity' => [
                    'minuscule' => $request->minuscule,
                    'majuscule' => $request->majuscule,
                    'special_characters' => $request->special_characters,
                    'min_length' => $request->min_length,
                    'new_password_diffrent_from_old' => $request->new_password_diffrent_from_old,
                ],
                'taxes' => $request->taxes
            ];

            $institution->save();

            $this->saveTableSetting($request);

            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'institution' => $institution,
                'success' => true,
                'message' => $message,
            ], 200);
            // }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_INSTITUTION_READ', Institution::class);
        $institution = Institution::findOrFail($id);
        return new JsonResponse([
            'institution' => $institution
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_INSTITUTION_READ', Institution::class);
        $institution = Institution::findOrFail($id);
        return new JsonResponse([
            'institution' => $institution,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_INSTITUTION_UPDATE', Institution::class);
        $institution = Institution::findOrFail($id);

        $existingInstitutions = Institution::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
        if (!empty($existingInstitutions) && sizeof($existingInstitutions) > 1) {
            $success = false;
            return new JsonResponse([
                'existingInstitution' => $existingInstitutions[0],
                'success' => $success,
                'message' => "L'institution " . $existingInstitutions[0]->social_reason . " existe déjà."
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
                $institution->rccm_number = $request->rccm_number;
                $institution->cc_number = $request->cc_number;
                $institution->social_reason = $request->social_reason;
                $institution->address = $request->address;
                $institution->email = $request->email;
                $institution->bp = $request->bp;
                $institution->phone_number = $request->phone_number;
                $institution->settings = [
                    'blocking_number_of_attempt' => $request->blocking_number_of_attempt,
                    'principal_currency' => $request->principal_currency,
                    'principal_unit_of_measure' => $request->principal_unit_of_measure,
                    'from_order_to_delivery' => $request->from_order_to_delivery,
                    'password_complexity' => [
                        'minuscule' => $request->minuscule,
                        'majuscule' => $request->majuscule,
                        'special_characters' => $request->special_characters,
                        'min_length' => $request->min_length,
                        'new_password_diffrent_from_old' => $request->new_password_diffrent_from_old,
                    ],
                    'taxes' => $request->taxes
                ];
                $institution->save();

                $this->saveTableSetting($request);

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'institution' => $institution,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_INSTITUTION_DELETE', Institution::class);
        $institution = Institution::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($institution->salesPoints) || sizeof($institution->salesPoints) == 0 && empty($institution->deliveryPoints) || sizeof($institution->deliveryPoints) == 0) {
                // dd('delete');
                $institution->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette institution ne peut être supprimée car elle a servi dans des traitements.";
            }

            return new JsonResponse([
                'institution' => $institution,
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

    public function institutionReports(Request $request)
    {
        $this->authorize('ROLE_INSTITUTION_PRINT', Institution::class);

        try {
            $institutions = $this->institutionRepository->institutionReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['institutions' => $institutions]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }

    public function institutionSettings($id)
    {
        // dd('in controller');
        return new JsonResponse(['datas' => ['institution_settings' => $this->getInstitutionSettings($id)]], 200);
    }

    public function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'email' => 'required|email',
                    'phone_number' => 'required',
                    'address' => 'required',
                    'bp' => 'required',
                ],
                [
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'email.required' => "L'adresse email est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                    'address.required' => "L'adresse est obligatoire.",
                    'bp.required' => "La boîte postale est obligatoire",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'email' => 'required|email',
                    'phone_number' => 'required',
                    'address' => 'required',
                    'bp' => 'required',
                ],
                [
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'email.required' => "L'adresse email est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                    'address.required' => "L'adresse est obligatoire.",
                    'bp.required' => "La boîte postale est obligatoire",
                ]
            );
        }
    }
}
