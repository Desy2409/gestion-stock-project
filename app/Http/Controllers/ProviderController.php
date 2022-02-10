<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Address;
use App\Models\Provider;
use App\Models\Person;
use App\Models\ProviderRegister;
use App\Models\ProviderType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    use UtilityTrait;

    public function index($type)
    {
        $this->authorize('ROLE_PROVIDER_READ', Provider::class);
        switch ($type) {
            case 'Raffinerie':
                $idOfProviderTypes = ProviderType::where('type', '=', 'Raffinerie')->with('providers')->pluck('id')->toArray();
                break;

            case 'Unité de stockage':
                $idOfProviderTypes = ProviderType::where('type', '=', 'Unité de stockage')->with('providers')->pluck('id')->toArray();
                break;

            case 'Transport':
                $idOfProviderTypes = ProviderType::where('type', '=', 'Transport')->with('providers')->pluck('id')->toArray();
                break;

            case 'Autres fournisseurs':
                $idOfProviderTypes = ProviderType::where('type', '=', 'Autres fournisseurs')->with('providers')->pluck('id')->toArray();
                break;

            default:
                $providerTypes = [];
                break;
        }
        $providers = $idOfProviderTypes ? Provider::with('providerType')->with(['person.address'])->whereIn('provider_type_id', $idOfProviderTypes)->get() : null;

        $lastProviderRegister = ProviderRegister::latest()->first();

        $providerRegister = new ProviderRegister();
        if ($lastProviderRegister) {
            $providerRegister->code = $this->formateNPosition('FS', $lastProviderRegister->id + 1, 8);
        } else {
            $providerRegister->code = $this->formateNPosition('FS', 1, 8);
        }
        $providerRegister->save();

        return new JsonResponse([
            'datas' => ['providers' => $providers]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_PROVIDER_READ', Provider::class);
        $lastProviderRegister = ProviderRegister::latest()->first();
        if ($lastProviderRegister) {
            $code = $this->formateNPosition('FS', $lastProviderRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('FS', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function onProviderTypeSelect($type)
    {
        $providerTypes = ProviderType::where('type', '=', $type)->get();

        return new JsonResponse([
            'datas' => ['providerTypes' => $providerTypes]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_PROVIDER_CREATE', Provider::class);

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
            $validation = $this->validator('store', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $lastProvider = Provider::latest()->first();

                $provider = new Provider();
                if ($lastProvider) {
                    $provider->code = $this->formateNPosition('FS', $lastProvider->id + 1, 8);
                } else {
                    $provider->code = $this->formateNPosition('FS', 1, 8);
                }
                $provider->reference = $request->reference;
                $provider->settings = $request->settings;
                $provider->provider_type_id = $request->provider_type;
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

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'person' => $person,
                    'provider' => $provider,
                    'address' => $address,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_PROVIDER_READ', Provider::class);
        $provider = Provider::with('person.address')->where('id', $id)->first();
        return new JsonResponse(['provider' => $provider], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_PROVIDER_READ', Provider::class);
        $provider = Provider::with('person.address')->with('providerType')->where('id', $id)->first();
        return new JsonResponse(['provider' => $provider], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PROVIDER_UPDATE', Provider::class);
        $provider = Provider::findOrFail($id);
        $person = Person::where('personable_id', $provider->id)->where('personable_type', "App\Models\Provider")->first();
        $address = $person ? $person->address : null;

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
                $provider->reference = $request->reference;
                $provider->settings = $request->settings;
                $provider->provider_type_id = $request->provider_type;
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

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'person' => $person,
                    'provider' => $provider,
                    'address' => $address,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_PROVIDER_DELETE', Provider::class);
        $provider = Provider::findOrFail($id);
        $person = Person::where('personable_id', $provider->id)->where('personable_type', "App\Models\Provider")->first();
        try {
            $success = false;
            $message = "";
            if (
                empty($provider->orders) || sizeof($provider->orders) == 0 &&
                empty($provider->purchases) || sizeof($provider->purchases) == 0 &&
                empty($provider->removalOrders) || sizeof($provider->removalOrders) == 0 &&
                empty($provider->tanks) || sizeof($provider->tanks) == 0 &&
                empty($provider->trucks) || sizeof($provider->trucks) == 0
            ) {
                // dd('delete');
                $provider->delete();
                $person->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce fournisseur ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'provider' => $provider,
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

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'provider_type' => 'required',
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'reference' => 'required',
                    'email' => 'required|email',
                    'phone_number' => 'required',
                ],
                [
                    'provider_type.required' => "Le choix du type de fournisseur est obligatoire.",
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'reference.required' => "La reference est obligatoire.",
                    'email.required' => "L'adresse email est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'provider_type' => 'required',
                    'rccm_number' => 'required',
                    'cc_number' => 'required',
                    'social_reason' => 'required',
                    'reference' => 'required',
                    'email' => 'required|email',
                    'phone_number' => 'required',
                ],
                [
                    'provider_type.required' => "Le choix du type de fournisseur est obligatoire.",
                    'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                    'cc_number.required' => "Le numéro CC est obligatoire.",
                    'social_reason.required' => "La raison sociale est obligatoire.",
                    'reference.required' => "La reference est obligatoire.",
                    'email.email' => "L'adresse email est incorrecte.",
                    'phone_number.required' => "Le numéro de téléphone est obligatoire.",
                ]
            );
        }
    }
}
