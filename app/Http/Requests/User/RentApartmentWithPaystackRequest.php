<?php

namespace App\Http\Requests\User;

use App\Enums\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RentApartmentWithPaystackRequest extends FormRequest
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
        $user = $this->user();

        return [
            'pay_with_wallet' => [
                'nullable',
                'boolean',
            ],
            'payment_card_id' => [
                'nullable',
                Rule::exists('payment_cards', 'id')
                    ->where('payment_gateway', PaymentGateway::PAYSTACK)
                    ->where('user_type', $user->getMorphClass())
                    ->where('user_id', $user->id),
            ],
            'callbackUrl' => [
                Rule::requiredIf((bool) $this->payment_card_id === false && (bool) $this->pay_with_wallet === false),
                'url',
            ],
            'booking_start_date' => 'nullable|date|after:today',
        ];
    }
}
