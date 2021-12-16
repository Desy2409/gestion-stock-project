<?php

namespace App\Repositories;

use App\Models\Host;

class HostRepository extends Repository
{
    public function hostReport($code = false, $provider = false, $url = false, $host_name = false, $driver = false, $startDate = null, $endDate = null)
    {
        if (!$code && !$provider && !$url && !$host_name && !$driver && $startDate == null && $endDate == null) {
            $hosts = null;
        } else {
            $hosts = Host::where('id', '!=', null);
            if ($code) {
                array_push($this->columns, 'code');
            }
            if ($provider) {
                array_push($this->columns, 'provider');
            }
            if ($url) {
                array_push($this->columns, 'url');
            }
            if ($host_name) {
                array_push($this->columns, 'host_name');
            }
            if ($driver) {
                $hosts->with('driver');
            }
            if ($startDate && $endDate) {
                $hosts->whereBetween('created_at', [$startDate, $endDate]);
            }
            $hosts = $hosts->get($this->columns);
        }

        return $hosts;
    }
}
