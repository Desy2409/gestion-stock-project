<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Repository;

class ClientRepository extends Repository
{
    public function clientReport($code = false, $reference = false, $lastName = false, $firstName = false, $socialReason = false, $rccmNumber = false, $ccNumber = false, $personType = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$reference && !$lastName && !$firstName && !$socialReason && !$rccmNumber && !$ccNumber && !$personType && $startDate == null && $endDate == null) {
            $clients=null;
        } else {
            $clients = Client::with('person');
        }

        return $clients;
    }
}
