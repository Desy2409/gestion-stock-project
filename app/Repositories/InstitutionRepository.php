<?php

namespace App\Repositories;

use App\Models\Institution;

class InstitutionRepository extends Repository
{
    public function institutionReport($rccmNumber = false, $ccNumber = false, $socialReason = false, $email = false, $phoneNumber = false, $address = false, $bp = false, $settings = false, $startDate = null, $endDate = null, $institution = false)
    {
        if (!$rccmNumber && !$ccNumber && !$socialReason && !$email && !$phoneNumber && !$address && !$bp && !$settings && $startDate == null && $endDate == null) {
            $institutions = null;
        } else {
            $institutions = Institution::where('id', '!=', null);
            if ($rccmNumber) {
                array_push($this->columns, 'rccm_number');
            }
            if ($ccNumber) {
                array_push($this->columns, 'cc_number');
            }
            if ($socialReason) {
                array_push($this->columns, 'social_reason');
            }
            if ($email) {
                array_push($this->columns, 'email');
            }
            if ($phoneNumber) {
                array_push($this->columns, 'phone_number');
            }
            if ($address) {
                array_push($this->columns, 'address');
            }
            if ($bp) {
                array_push($this->columns, 'bp');
            }
            if ($settings) {
                array_push($this->columns, 'settings');
            }
            if ($startDate && $endDate) {
                $institutions->whereBetween('created_at', [$startDate, $endDate]);
            }

            $institutions = $institutions->get($this->columns);
        }

        return $institutions;
    }
}
