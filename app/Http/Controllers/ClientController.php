<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\JuridicPersonality;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = Client::orderBy('social_reason')->orderBy('last_name')->orderBy('first_name')->get();
        return [
            'clients' => $clients,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $juridicPersonalities = JuridicPersonality::orderBy('wording')->get();
        return [
            'juridicPersonalities' => $juridicPersonalities,
        ];
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
                'jurdic_personality' => 'required',
                'email' => 'required|string|email',
                'phone' => 'required|min:8|max:25|regex:/(01)[0-9]{9}/',
                'address' => 'max:255',
            ],
            [
                'jurdic_personality.required' => "La personnalité juridique est obligatoire.",
                'email.required' => "L'email est obligatoire.",
                'email.string' => "L'email doit être une chaîne de caractères.",
                'email.email' => "Le format d'email est incorrect.",
                'phone.required' => "Le numéro de téléphone est obligatoire.",
                'phone.min' => "Le numéro de téléphone ne peut être inférieur à 25 caractères.",
                'phone.max' => "Le numéro de téléphone ne doit pas dépasser 25 caractères.",
                'phone.regex' => "Le numéro de téléphone est incorrect.",
                'address.max' => "L'adresse ne doit pas dépasser 255 caractères.",
            ],
        );

        if ($request->jurdic_personality != null) {
            switch ($request->jurdic_personality) {
                case 1:
                    $this->validate(
                        $request,

                        [
                            'last_name' => 'required|max:50|regex:/^[a-zA-Z]+$/i',
                            'first_name' => 'required|max:50|regex:/^[a-zA-Z]+$/i',
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

            return Client::create($request->all());

            // Session::flash('success', "Enregistrement effectué avec succès.");
            // return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de l'enregistrement.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Client::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $this->validate(
            $request,

            [
                'jurdic_personality' => 'required',
                'email' => 'required|string|email',
                'phone' => 'required|min:8|max:25|regex:/(01)[0-9]{9}/',
                'address' => 'max:255',
            ],
            [
                'jurdic_personality.required' => "La personnalité juridique est obligatoire.",
                'email.required' => "L'email est obligatoire.",
                'email.string' => "L'email doit être une chaîne de caractères.",
                'email.email' => "Le format d'email est incorrect.",
                'phone.required' => "Le numéro de téléphone est obligatoire.",
                'phone.min' => "Le numéro de téléphone ne peut être inférieur à 25 caractères.",
                'phone.max' => "Le numéro de téléphone ne doit pas dépasser 25 caractères.",
                'phone.regex' => "Le numéro de téléphone est incorrect.",
                'address.max' => "L'adresse ne doit pas dépasser 255 caractères.",
            ],
        );

        if ($request->jurdic_personality != null) {
            switch ($request->jurdic_personality) {
                case 1:
                    $this->validate(
                        $request,

                        [
                            'last_name' => 'required|max:50|regex:/^[a-zA-Z]+$/i',
                            'first_name' => 'required|max:50|regex:/^[a-zA-Z]+$/i',
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

            $client->update($request->all());
            return $client;

            // Session::flash('success', "Enregistrement effectué avec succès.");
            // return back();
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
        try {
            return Client::destroy($id);
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppresion.");
        }
    }
}
