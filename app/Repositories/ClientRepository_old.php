<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Person;
use App\Repositories\Repository;

class ClientRepository_old extends Repository
{
    public function clientReport($code = false, $reference = false, $lastName = false, $firstName = false, $socialReason = false, $rccmNumber = false, $ccNumber = false, $personType = false, $settings = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$reference && !$lastName && !$firstName && !$socialReason && !$rccmNumber && !$ccNumber && !$personType && !$settings && $startDate == null && $endDate == null) {
            $clients = null;
        } else {
            // $clients = Client::where('id','!=',null)->with('person');
            // $clients = Client::whereHas('person', function ($q) use ($lastName)  {
            $clients = Client::whereHas('person', function ($q) use ($lastName)  {
                if ($lastName) {
                    array_push($this->columns, 'person.last_name');
                }
                // $q->where('person_type', '=', 'Personne morale');
            });

            



            // $clients = Person::whereHas('personable', function ($q) use ($lastName) {
            //     if ($lastName) {
            //         array_push($this->columns, 'last_name');
            //     }
            //     // $q->where('person_type', '=', 'Personne morale');
            // });

            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($reference) {
                array_push($this->columns, 'reference');
            }
            // if ($lastName) {
            //     array_push($this->columns, 'person.last_name');
            // }
            // if ($firstName) {
            //     array_push($this->columns, 'first_name');
            // }
            // if ($socialReason) {
            //     array_push($this->columns, 'social_reason');
            // }
            // if ($rccmNumber) {
            //     array_push($this->columns, 'rccm_number');
            // }
            // if ($ccNumber) {
            //     array_push($this->columns, 'cc_number');
            // }
            // if ($personType) {
            //     array_push($this->columns, 'person_type');
            // }
            if ($settings) {
                array_push($this->columns, 'settings');
            }
            if ($startDate && $endDate) {
                $clients->whereBetween('created_at', [$startDate, $endDate]);
            }
            $clients = $clients->get($this->columns);
        }

        return $clients;
    }
}
