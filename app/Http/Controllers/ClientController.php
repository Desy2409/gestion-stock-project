<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Address;
use App\Models\ClientRegister;
use App\Repositories\ClientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use UtilityTrait;

    public $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_CLIENT_READ', Client::class);
        $corporations = Client::orderBy('created_at','desc')->with(['person.addresses', 'person.address'])->whereHas('person', function ($q) {
            $q->where('person_type', '=', 'Personne morale');
        })->get();

        $personPhysic = Client::orderBy('created_at','desc')->with(['person.addresses', 'person.address'])->whereHas('person', function ($q) {
            $q->where('person_type', '=', 'Personne physique');
        })->get();

        $lastClientRegister = ClientRegister::latest()->first();

        $clientRegister = new ClientRegister();
        if ($lastClientRegister) {
            $clientRegister->code = $this->formateNPosition('CL', $lastClientRegister->id + 1, 8);
        } else {
            $clientRegister->code = $this->formateNPosition('CL', 1, 8);
        }
        $clientRegister->save();

        return new JsonResponse([
            'datas' => ['corporations' => $corporations, 'personPhysic' => $personPhysic]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_CLIENT_READ', Client::class);
        $lastClientRegister = ClientRegister::latest()->first();
        // dd($lastClientRegister);
        if ($lastClientRegister) {
            $code = $this->formateNPosition('CL', $lastClientRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('CL', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_CLIENT_CREATE', Client::class);

        if ($request->person_type == "Personne morale") {
            $existingMoralPersons = Person::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
            if (!empty($existingMoralPersons) && sizeof($existingMoralPersons) > 1) {
                return new JsonResponse([
                    'existingMoralPerson' => $existingMoralPersons[0],
                    'success' => false,
                    'message' => "Le client " . $existingMoralPersons[0]->social_reason . " existe déjà."
                ], 200);
            }
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
                $lastClient = Client::latest()->first();

                $client = new Client();
                if ($lastClient) {
                    $client->code = $this->formateNPosition('CL', $lastClient->id + 1, 8);
                } else {
                    $client->code = $this->formateNPosition('CL', 1, 8);
                }
                $client->reference = $request->reference;
                $client->settings = $request->settings;
                $client->exemption_reference = $request->exemption_reference;
                $client->limit_date_exemption = $request->limit_date_exemption;
                $client->save();

                $person = new Person();
                $person->last_name = $request->last_name;
                $person->first_name = $request->first_name;
                $person->rccm_number = $request->rccm_number;
                $person->cc_number = $request->cc_number;
                $person->social_reason = $request->social_reason;
                $person->person_type = $request->person_type;
                $person->personable_id = $client->id;
                $person->personable_type = "App\Models\Client";
                $person->save();

                $address = new Address();
                $address->address = $request->address;
                $address->email = $request->email;
                $address->phone_number = $request->phone_number;
                $address->bp = $request->bp;
                $address->person_id = $person->id;
                $address->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'person' => $person,
                    'client' => $client,
                    'address' => $address,
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

    public function show($id)
    {
        $this->authorize('ROLE_CLIENT_READ', Client::class);
        $client = Client::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['client' => $client], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_CLIENT_UPDATE', Client::class);
        $client = Client::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['client' => $client], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_CLIENT_UPDATE', Client::class);
        $client = Client::findOrFail($id);
        $person = Person::where('personable_id', $client->id)->where('personable_type', "App\Models\Client")->first();
        $address = $person ? $person->address : null;


        $existingMoralPersons = Person::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
        if (!empty($existingMoralPersons) && sizeof($existingMoralPersons) > 1) {
            $success = false;
            return new JsonResponse([
                'existingMoralPerson' => $existingMoralPersons[0],
                'success' => $success,
                'message' => "Le client " . $existingMoralPersons[0]->social_reason . " existe déjà."
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
                $client->reference = $request->reference;
                $client->settings = $request->settings;
                $client->exemption_reference = $request->exemption_reference;
                $client->limit_date_exemption = $request->limit_date_exemption;
                $client->save();

                $person->last_name = $request->last_name;
                $person->first_name = $request->first_name;
                $person->rccm_number = $request->rccm_number;
                $person->cc_number = $request->cc_number;
                $person->social_reason = $request->social_reason;
                $person->person_type = $request->person_type;
                $person->save();

                if ($address->address != $request->address || $address->email != $request->email || $address->phone_number != $request->phone_number || $address->bp != $request->bp) {
                    $address = new Address();
                    $address->address = $request->address;
                    $address->email = $request->email;
                    $address->phone_number = $request->phone_number;
                    $address->bp = $request->bp;
                    $address->person_id = $person->id;
                    $address->save();
                }

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'person' => $person,
                    'client' => $client,
                    'address' => $address,
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
        $this->authorize('ROLE_CLIENT_DELETE', Client::class);
        $client = Client::findOrFail($id);
        $person = Person::where('personable_id', $client->id)->where('personable_type', "App\Models\Client")->first();
        try {
            $success = false;
            $message = "";
            if (
                empty($client->purchaseOrders) || sizeof($client->purchaseOrders) == 0 &&
                empty($client->sales) || sizeof($client->sales) == 0 &&
                empty($client->removalOrders) || sizeof($client->removalOrders) == 0
            ) {
                // dd('delete');
                $client->delete();
                $person->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce client ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'client' => $client,
                'person' => $person,
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

    public function clientReports(Request $request)
    {
        try {
            $clients = $this->clientRepository->clientReport($request->code, $request->reference, $request->last_name, $request->first_name, $request->social_reason, $request->rccm_number, $request->cc_number, $request->person_type, $request->settings, $request->start_date, $request->end_date);
            return new JsonResponse(['datas' => ['clients' => $clients]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            if ($data['person_type'] == 'Personne physique') {
                return Validator::make(
                    $data,
                    [
                        'last_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
                        'first_name' => 'required|max:100|string', //regex:/^[a-zA-Zé]+$/i',
                        'reference' => 'required',
                        'email' => 'email',
                        'phone_number' => 'required',
                        'exemption_reference' => 'required',
                        'limit_date_exemption' => 'required|date|after:yesterday'
                    ],
                    [
                        'last_name.required' => "Le nom est obligatoire.",
                        'last_name.max' => "Le nom ne doit pas dépasser 50 caractères.",
                        'last_name.string' => "Le nom doit être une chaîne de caractères.",
                        'first_name.required' => "Le prénom est obligatoire.",
                        'first_name.max' => "Le prénom ne doit pas dépasser 100 caractères.",
                        'first_name.string' => "Le prénom doit être une chaîne de caractères.",
                        'reference.required' => "La reference est obligatoire.",
                        'email.email' => "L'adresse email est incorrecte.",
                        'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                        'exemption_reference.required' => "La référence d'exonération est obligatoire.",
                        'limit_date_exemption.required' => "La date limite d'exonération est obligatoire.",
                        'limit_date_exemption.date' => "La date limite d'exonération est incorrecte.",
                        'limit_date_exemption.date_format' => "La date limite d'exonération doit être sous le format : Année Mois Jour.",
                        'limit_date_exemption.after' => "La date limite d'exonération est déjà dépassée.",
                    ]
                );
            } elseif ($data['person_type'] == 'Personne morale') {
                return Validator::make(
                    $data,
                    [
                        'rccm_number' => 'required',
                        'cc_number' => 'required',
                        'social_reason' => 'required',
                        'reference' => 'required',
                        'email' => 'email',
                        'phone_number' => 'required',
                        'exemption_reference' => 'required',
                        'limit_date_exemption' => 'required|date|after:yesterday'
                    ],
                    [
                        'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                        'cc_number.required' => "Le numéro CC est obligatoire.",
                        'social_reason.required' => "La raison sociale est obligatoire.",
                        'reference.required' => "La reference est obligatoire.",
                        'email.email' => "L'adresse email est incorrecte.",
                        'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                        'exemption_reference.required' => "La référence d'exonération est obligatoire.",
                        'limit_date_exemption.required' => "La date limite d'exonération est obligatoire.",
                        'limit_date_exemption.date' => "La date limite d'exonération est incorrecte.",
                        'limit_date_exemption.date_format' => "La date limite d'exonération doit être sous le format : Année Mois Jour.",
                        'limit_date_exemption.after' => "La date limite d'exonération est déjà dépassée.",
                    ]
                );
            } else {
                return Validator::make(
                    $data,
                    [
                        'person_type' => 'required'
                    ],
                    [
                        'person_type.required' => "Le type de personne est obligatoire."
                    ]
                );
            }
        }
        if ($mode == 'update') {
            if ($data['person_type'] == 'Personne physique') {
                return Validator::make(
                    $data,
                    [
                        'last_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
                        'first_name' => 'required|max:100|string', //regex:/^[a-zA-Zé]+$/i',
                        'reference' => 'required',
                        'email' => 'email',
                        'phone_number' => 'required',
                        'exemption_reference' => 'required',
                        'limit_date_exemption' => 'required|date|after:yesterday',
                    ],
                    [
                        'last_name.required' => "Le nom est obligatoire.",
                        'last_name.max' => "Le nom ne doit pas dépasser 50 caractères.",
                        'last_name.string' => "Le nom doit être une chaîne de caractères.",
                        'first_name.required' => "Le prénom est obligatoire.",
                        'first_name.max' => "Le prénom ne doit pas dépasser 100 caractères.",
                        'first_name.string' => "Le prénom doit être une chaîne de caractères.",
                        'reference.required' => "La reference est obligatoire.",
                        'email.email' => "L'adresse email est incorrecte.",
                        'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                        'exemption_reference.required' => "La référence d'exonération est obligatoire.",
                        'limit_date_exemption.required' => "La date limite d'exonération est obligatoire.",
                        'limit_date_exemption.date' => "La date limite d'exonération est incorrecte.",
                        'limit_date_exemption.date_format' => "La date limite d'exonération doit être sous le format : Année Mois Jour.",
                        'limit_date_exemption.after' => "La date limite d'exonération est déjà dépassée.",
                    ]
                );
            } elseif ($data['person_type'] == 'Personne morale') {
                return Validator::make(
                    $data,
                    [
                        'rccm_number' => 'required',
                        'cc_number' => 'required',
                        'social_reason' => 'required',
                        'reference' => 'required',
                        'email' => 'email',
                        'phone_number' => 'required',
                        'exemption_reference' => 'required',
                        'limit_date_exemption' => 'required|date|after:yesterday'
                    ],
                    [
                        'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                        'cc_number.required' => "Le numéro CC est obligatoire.",
                        'social_reason.required' => "La raison sociale est obligatoire.",
                        'reference.required' => "La reference est obligatoire.",
                        'email.email' => "L'adresse email est incorrecte.",
                        'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                        'exemption_reference.required' => "La référence d'exonération est obligatoire.",
                        'limit_date_exemption.required' => "La date limite d'exonération est obligatoire.",
                        'limit_date_exemption.date' => "La date limite d'exonération est incorrecte.",
                        'limit_date_exemption.date_format' => "La date limite d'exonération doit être sous le format : Année Mois Jour.",
                        'limit_date_exemption.after' => "La date limite d'exonération est déjà dépassée.",
                    ]
                );
            } else {
                return Validator::make(
                    $data,
                    [
                        'person_type' => 'required'
                    ],
                    [
                        'person_type.required' => "Le type de personne est obligatoire."
                    ]
                );
            }
        }
    }
}
