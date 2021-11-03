<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Address;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $corporations = Client::with(['person.addresses'])->whereHas('person', function ($q) {
            $q->where('person_type', '=', 'Personne morale');
        })->get();

        $personPhysic = Client::with(['person.addresses'])->whereHas('person', function ($q) {
            $q->where('person_type', '=', 'Personne physique');
        })->get();
        return new JsonResponse([
            'datas' => ['corporations' => $corporations, 'personPhysic' => $personPhysic]
        ], 200);
    }

    public function store(Request $request)
    {
        if ($request->person_type == "Personne physique") {
            $this->validate(
                $request,
                [
                    'last_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
                    'first_name' => 'required|max:100|string', //regex:/^[a-zA-Zé]+$/i',
                    'reference' => 'required',
                    'email' => 'email',
                    'phone_number' => 'required',
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
                ],
            );
        } elseif ($request->person_type == "Personne morale") {
            $this->validate(
                $request,
                [
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'reference' => 'required',
                    'email' => 'email',
                    'phone_number' => 'required',
                ],
                [
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'reference.required' => "La reference est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                ],
            );
            $existingMoralPersons = Person::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
            if (!empty($existingMoralPersons) && sizeof($existingMoralPersons) > 1) {
                $success = false;
                return new JsonResponse([
                    'existingMoralPerson' => $existingMoralPersons[0],
                    'success' => $success,
                    'message' => "Le client " . $existingMoralPersons[0]->social_reason . " existe déjà."
                ], 400);
            }
        } else {
            $this->validate(
                $request,
                [
                    'person_type' => 'required'
                ],
                [
                    'person_type.required' => "Le type de personne est obligatoire."
                ]
            );
        }

        try {
            $clients = Client::all();
            $client = new Client();
            $client->code = $this->formateNPosition('CL', sizeof($clients) + 1, 8);
            $client->reference = $request->reference;
            $client->settings = $request->settings;
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

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'person' => $person,
                'client' => $client,
                'address' => $address,
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

    public function show($id)
    {
        $client = Client::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['client' => $client], 200);
    }

    public function edit($id)
    {
        $client = Client::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['client' => $client], 200);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $person = Person::where('personable_id', $client->id)->where('personable_type', "App\Models\Client")->first();
        $address = $person ? $person->address : null;

        if ($request->person_type == "Personne physique") {
            $this->validate(
                $request,
                [
                    'last_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
                    'first_name' => 'required|max:100|string', //regex:/^[a-zA-Zé]+$/i',
                    'reference' => 'required',
                    'email' => 'email',
                    'phone_number' => 'required',
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
                ],
            );
        } elseif ($request->person_type == "Personne morale") {
            $this->validate(
                $request,
                [
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'reference' => 'required',
                    'email' => 'email',
                    'phone_number' => 'required',
                ],
                [
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'reference.required' => "La reference est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                ],
            );
            $existingMoralPersons = Person::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->get();
            if (!empty($existingMoralPersons) && sizeof($existingMoralPersons) > 1) {
                $success = false;
                return new JsonResponse([
                    'existingMoralPerson' => $existingMoralPersons[0],
                    'success' => $success,
                    'message' => "Le client " . $existingMoralPersons[0]->social_reason . " existe déjà."
                ], 400);
            }
        } else {
            $this->validate(
                $request,
                [
                    'person_type' => 'required'
                ],
                [
                    'person_type.required' => "Le type de personne est obligatoire."
                ]
            );
        }

        try {
            $client->reference = $request->reference;
            $client->settings = $request->settings;
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

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'person' => $person,
                'client' => $client,
                'address' => $address,
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
        $client = Client::findOrFail($id);
        $person = Person::where('personable_id', $client->id)->where('personable_type', "App\Models\Client")->first();
        try {
            $client->delete();
            $person->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'client' => $client,
                'person' => $person,
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
