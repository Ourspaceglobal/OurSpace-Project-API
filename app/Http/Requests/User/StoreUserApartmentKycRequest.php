<?php

namespace App\Http\Requests\User;

use App\Models\SystemApartmentKyc;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreUserApartmentKycRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $validationData = collect();

        $this->validateSystemApartmentKycs($validationData);
        $this->validateCustomApartmentKycs($validationData);

        return $validationData->collapse()->toArray();
    }

    /**
     * Validate the system apartment KYCs.
     *
     * @param \Illuminate\Support\Collection $validationData
     * @return void
     */
    protected function validateSystemApartmentKycs(&$validationData)
    {
        $systemApartmentKycs = SystemApartmentKyc::required()->with('datatype:id,name,rule')->get();
        $systemApartmentKycsCount = $systemApartmentKycs->count();
        $selectedSystemApartmentKycs = $this->system_apartment_kycs;

        if ($systemApartmentKycsCount) {
            if (!$selectedSystemApartmentKycs) {
                throw ValidationException::withMessages([
                    'system_apartment_kycs' => 'The following system apartment KYCs are required: '
                        . $systemApartmentKycs->implode('name', ', ')
                ]);
            }

            if (!is_array($selectedSystemApartmentKycs)) {
                throw ValidationException::withMessages([
                    'system_apartment_kycs' => 'Selected system apartment KYCs must be an array'
                ]);
            }

            if (
                $systemApartmentKycsCount < count($selectedSystemApartmentKycs)
                || $systemApartmentKycsCount < count($selectedSystemApartmentKycs)
            ) {
                throw ValidationException::withMessages([
                    'system_apartment_kycs' =>
                        'All the system apartment KYCs must be provided. ' . $systemApartmentKycs->implode('name', ', ')
                ]);
            }

            foreach ($selectedSystemApartmentKycs as $systemApartmentKycId => $entry) {
                $systemApartmentKyc = $systemApartmentKycs->firstWhere('id', $systemApartmentKycId);

                if (is_null($systemApartmentKyc)) {
                    throw ValidationException::withMessages([
                        "system_apartment_kycs.{$systemApartmentKycId}" => 'System apartment KYC not acceptable!',
                    ]);
                }

                $rules = explode('|', $systemApartmentKyc->datatype->rule);

                $validationData->push([
                    "system_apartment_kycs.{$systemApartmentKycId}" => $rules
                ]);
            }
        } else {
            $this->system_apartment_kycs = [];
        }
    }

    /**
     * Validate the custom apartment KYCs.
     *
     * @param \Illuminate\Support\Collection $validationData
     * @return void
     */
    protected function validateCustomApartmentKycs(&$validationData)
    {
        $customApartmentKycs = $this->apartment->customApartmentKycs()->with('datatype:id,name,rule')->get();
        $customApartmentKycsCount = $customApartmentKycs->count();
        $selectedCustomApartmentKycs = $this->custom_apartment_kycs;

        if ($customApartmentKycsCount) {
            if (!$selectedCustomApartmentKycs) {
                throw ValidationException::withMessages([
                    'custom_apartment_kycs' => 'The following custom apartment KYCs are required: '
                        . $customApartmentKycs->implode('name', ', ')
                ]);
            }

            if (!is_array($selectedCustomApartmentKycs)) {
                throw ValidationException::withMessages([
                    'custom_apartment_kycs' => 'Selected custom apartment KYCs must be an array'
                ]);
            }

            if (
                $customApartmentKycsCount < count($selectedCustomApartmentKycs)
                || $customApartmentKycsCount < count($selectedCustomApartmentKycs)
            ) {
                throw ValidationException::withMessages([
                    'custom_apartment_kycs' =>
                        'All the custom apartment KYCs must be provided. ' . $customApartmentKycs->implode('name', ', ')
                ]);
            }

            foreach ($selectedCustomApartmentKycs as $customApartmentKycId => $entry) {
                $customApartmentKyc = $customApartmentKycs->firstWhere('id', $customApartmentKycId);

                if (is_null($customApartmentKyc)) {
                    throw ValidationException::withMessages([
                        "custom_apartment_kycs.{$customApartmentKyc}" => 'Custom apartment KYC not acceptable!',
                    ]);
                }

                $rules = explode('|', $customApartmentKyc->datatype->rule);

                $validationData->push([
                    "custom_apartment_kycs.{$customApartmentKycId}" => $rules
                ]);
            }
        } else {
            $this->custom_apartment_kycs = [];
        }
    }
}
