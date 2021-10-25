<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\JuridicPersonality;
use App\Models\Product;
use App\Models\Provider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Person;
use App\Http\Traits\UtilityTrait;

class ClientController extends Controller
{
    // use UtilityTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $clients = Client::orderBy('social_reason')->orderBy('last_name')->orderBy('first_name')->get();
        // return $clients;
        $clients = Client::with('person')->get();
        return $clients;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $juridicPersonalities = JuridicPersonality::orderBy('wording')->get();
        // return [
        //     'juridicPersonalities' => $juridicPersonalities,
        // ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,

            [

                'personType' => 'required',
                'code' => 'required',
                'reference' => 'required',
                'settings' => 'required',
            ],
            [

                'personType.required' => "Le type est obligatoire.",
                'code.required' => "Le code est obligatoire.",
                'reference.required' => "La reference sociale est obligatoire.",
            ],
        );

        // if ($request->juridic_personality != null) {
        //     switch ($request->juridic_personality) {
        //         case 1:
        //             $this->validate(
        //                 $request,

        //                 [
        //                     #Il faudrait réfléchir à comment permettre de mettre un point (.) dans le nom ou le prénom
        //                     'last_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
        //                     'first_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
        //                 ],
        //                 [
        //                     'last_name.required' => "Le nom est obligatoire.",
        //                     'last_name.max' => "Le nom ne doit pas dépasser 50 caractères.",
        //                     'last_name.string' => "Le nom doit être une chaîne de caractères.",
        //                     'first_name.required' => "Le prénom est obligatoire.",
        //                     'first_name.max' => "Le prénom ne doit pas dépasser 50 caractères.",
        //                     'first_name.string' => "Le prénom doit être une chaîne de caractères.",
        //                 ],
        //             );
        //             break;

        //         case 2:
        //             $this->validate(
        //                 $request,
        //                 [
        //                     'rccm_number' => 'required',
        //                     'cc_number' => 'required',
        //                     'social_reason' => 'required',
        //                 ],
        //                 [
        //                     'rccm_number.required' => "Le numéro RRCM est obligatoire.",
        //                     'cc_number.required' => "Le numéro CC est obligatoire.",
        //                     'social_reason.required' => "La raison sociale est obligatoire.",
        //                 ],
        //             );
        //             break;

        //         default:
        //             # code...
        //             break;
        //     }
        // }

        try {
            $clients = Client::all();
            $client = new Client();
            // $client->code = $this->formateNPosition('CL',sizeof($clients),8);
            // $client->code = $request->code;
            // dd($client->code);
            // $client->code = "CL000001";
            $client->reference = $request->reference;
            $client->settings = $request->settings;
            // dd($client);
            $client->save();

            $person = new Person();
            $person->lastName = $request->lastName;
            $person->firstName = $request->firstName;
            $person->rccmNumber = $request->rccmNumber;
            $person->ccNumber = $request->ccNumber;
            $person->socialReason = $request->socialReason;
            $person->personType = $request->personType;
            $person->personable_code = $client->code;
            $person->personable_type = 'App\Models\Client';
            $person->save();

            return [$client, $person];
        } catch (Exception $e) {
            dd($e);
            Session::flash('danger', "Erreur survenue lors de l'enregistrement.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        $client = Client::where("code", $code);
        return $client;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($code)
    {
        $client = Client::where("code", $code);
        $juridicPersonalities = JuridicPersonality::orderBy('wording')->get();
        return [
            'juridicPersonalities' => $juridicPersonalities,
            'client' => $client,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $code)
    {
        $client = Client::where("code", $code);
        $this->validate(
            $request,

            [
                'juridic_personality' => 'required',
                'email' => 'required|string|email',
                'phone' => 'required|min:8|max:25', //|regex:/(01)[0-9]{9}/',
                'address' => 'max:255',
            ],
            [
                'juridic_personality.required' => "La personnalité juridique est obligatoire.",
                'email.required' => "L'email est obligatoire.",
                'email.string' => "L'email doit être une chaîne de caractères.",
                'email.email' => "Le format d'email est incorrect.",
                'phone.required' => "Le numéro de téléphone est obligatoire.",
                'phone.min' => "Le numéro de téléphone ne peut être inférieur à 25 caractères.",
                'phone.max' => "Le numéro de téléphone ne doit pas dépasser 25 caractères.",
                // 'phone.regex' => "Le numéro de téléphone est incorrect.",
                'address.max' => "L'adresse ne doit pas dépasser 255 caractères.",
            ],
        );

        if ($request->juridic_personality != null) {
            switch ($request->juridic_personality) {
                case 1:
                    $this->validate(
                        $request,

                        [
                            'last_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
                            'first_name' => 'required|max:50|string', //regex:/^[a-zA-Zé]+$/i',
                        ],
                        [
                            'last_name.required' => "Le nom est obligatoire.",
                            'last_name.max' => "Le nom ne doit pas dépasser 50 caractères.",
                            'last_name.regex' => "Le nom doit être une chaîne de caractères.",
                            'first_name.required' => "Le prénom est obligatoire.",
                            'first_name.max' => "Le prénom ne doit pas dépasser 50 caractères.",
                            'first_name.regex' => "Le prénom doit être une chaîne de caractères.",
                        ],
                    );
                    break;

                case 2:
                    $this->validate(
                        $request,

                        [
                            'rccm_number' => 'required',
                            'cc_number' => 'required',
                            'social_reason' => 'required',
                        ],
                        [
                            'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                            'cc_number.required' => "Le numéro CC est obligatoire.",
                            'social_reason.required' => "La raison sociale est obligatoire.",
                        ],
                    );
                    break;

                default:
                    # code...
                    break;
            }
        }

        try {
            // $client->update($request->all());
            $client->last_name = $request->last_name;
            $client->first_name = $request->first_name;
            $client->rccm_number = $request->rccm_number;
            $client->cc_number = $request->cc_number;
            $client->social_reason = $request->social_reason;
            $client->address = $request->address;
            $client->email = $request->email;
            $client->bp = $request->bp;
            $client->phone = $request->phone;
            $client->juridic_personality_id = $request->juridic_personality;

            $client->save();

            return $client;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la modification.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        try {
            $client->delete();
            return $client;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppression.");
        }
    }


    function completeZero($value, $length)
    {
        $valueString = $value;
        $valueLength = strlen($valueString);
        while ($valueLength < $length) {
            $valueString = '0' . $valueString;
            $valueLength = strlen($valueString);
        }

        return $valueString;
    }

    public function makeCodeEntity($entity, $field = 'reference')
    {
        $prefixe = '';
        $reference = null;

        switch ($entity) {
            case Client::class:
                $prefixe = 'CL';
                break;
            case Provider::class:
                $prefixe = 'FS';
                break;
            case Product::class:
                $prefixe = '';
                break;
        }

        $counter = count($entity::all());
        $counter++;
        $code = $this->completeZero($counter, 6);
        $reference = strtoupper($prefixe . $code);
        return $reference;
    }
}
