<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Address;
use App\Models\Provider;
use App\Models\Person;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProviderController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $providers = Provider::with(['person.addresses'])->get();
        return new JsonResponse([
            'datas' => ['providers' => $providers]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'reference' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
            ],
            [
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
                'reference.required' => "La reference est obligatoire.",
                'email.required' => "L'adresse email est obligatoire.",
                'email.email' => "L'adresse email est incorrecte.",
                'phone_number.required' => "Le numéro de téléphone est obligatoire.",
            ],
        );

        $existingMoralPerson = Person::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->first();
        if ($existingMoralPerson) {
            $success = false;
            return new JsonResponse([
                'existingMoralPerson' => $existingMoralPerson,
                'success' => $success,
                'message' => "Le provider " . $existingMoralPerson->social_reason . " existe déjà."
            ], 400);
        }

        try {
            $providers = Provider::all();
            $provider = new Provider();
            $provider->code = $this->formateNPosition('FS', sizeof($providers) + 1, 8);
            $provider->reference = $request->reference;
            $provider->settings = $request->settings;
            $provider->save();

            $person = new Person();
            $person->rccm_number = $request->rccm_number;
            $person->cc_number = $request->cc_number;
            $person->social_reason = $request->social_reason;
            $person->person_type = "Personne morale";
            $person->personable_id = $provider->id;
            $person->personable_type = "App\Models\Provider";
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
                'provider' => $provider,
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
        $provider = Provider::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['provider' => $provider], 200);
    }

    public function edit($id)
    {
        $provider = Provider::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['provider' => $provider], 200);
    }

    public function update(Request $request, $id)
    {
        $provider = Provider::findOrFail($id);
        $person = Person::where('personable_id', $provider->id)->where('personable_type', "App\Models\Provider")->first();
        $address = $person ? $person->address : null;

        dd($address);
        $this->validate(
            $request,
            [
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'reference' => 'required',
                'email' => 'required|email',
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

        $existingMoralPerson = Person::where('rccm_number', $request->rccm_number)->where('cc_number', $request->cc_number)->first();
        if ($existingMoralPerson) {
            $success = false;
            return new JsonResponse([
                'existingMoralPerson' => $existingMoralPerson,
                'success' => $success,
                'message' => "Le provider " . $existingMoralPerson->social_reason . " existe déjà."
            ], 400);
        }

        try {
            $provider->reference = $request->reference;
            $provider->settings = $request->settings;
            $provider->save();

            $person->rccm_number = $request->rccm_number;
            $person->cc_number = $request->cc_number;
            $person->social_reason = $request->social_reason;
            $person->save();

            if ($address) {
                if ($address->address != $request->address || $address->email != $request->email || $address->phone_number != $request->phone_number || $address->bp != $request->bp) {
                    $address = new Address();
                    $address->address = $request->address;
                    $address->email = $request->email;
                    $address->phone_number = $request->phone_number;
                    $address->bp = $request->bp;
                    $address->person_id = $person->id;
                    $address->save();
                }
            } else {
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
                'provider' => $provider,
                'address' => $address,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            dd($e);
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
        $provider = Provider::findOrFail($id);
        $person = Person::where('personable_id', $provider->id)->where('personable_type', "App\Models\Provider")->first();
        try {
            $provider->delete();
            $person->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'provider' => $provider,
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
