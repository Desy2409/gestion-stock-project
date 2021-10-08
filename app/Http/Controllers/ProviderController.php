<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $providers = Provider::orderBy('social_reason')->get();
        return [
            'providers' => $providers,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'email' => 'required|string|email',
                'phone' => 'required|min:8|max:25',//|regex:/(01)[0-9]{9}/',
                'address' => 'max:255',
            ],
            [
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
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

        try {

            $provider = new Provider();
            $provider->reference = 'FS000001';
            $provider->rccm_number = $request->rccm_number;
            $provider->cc_number = $request->cc_number;
            $provider->social_reason = $request->social_reason;
            $provider->address = $request->address;
            $provider->email = $request->email;
            $provider->bp = $request->bp;
            $provider->phone = $request->phone;
            $provider->save();

            return $provider;
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
        return Provider::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $provider = Provider::findOrFail($id);
        return [
            'provider' => $provider,
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
        $provider = Provider::findOrFail($id);
        $this->validate(
            $request,

            [
                'rccm_number' => 'required',
                'cc_number' => 'required',
                'social_reason' => 'required',
                'email' => 'required|string|email',
                'phone' => 'required|min:8|max:25',//|regex:/(01)[0-9]{9}/',
                'address' => 'max:255',
            ],
            [
                'rccm_number.required' => "Le numéro RRCM est obligatoire.",
                'cc_number.required' => "Le numéro CC est obligatoire.",
                'social_reason.required' => "La raison sociale est obligatoire.",
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

        try {
           $provider->rccm_number = $request->rccm_number;
            $provider->cc_number = $request->cc_number;
            $provider->social_reason = $request->social_reason;
            $provider->address = $request->address;
            $provider->email = $request->email;
            $provider->bp = $request->bp;
            $provider->phone = $request->phone;
            $provider->save();

            return $provider;
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
        $provider = Provider::findOrFail($id);
        try {
            $provider->delete();
            return $provider;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppression.");
        }
    }
}
