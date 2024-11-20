<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
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
        return [
            'recipient_user_id' => 'nullable', // Permite que seja null
            'post_id' => 'nullable', // Permite que seja null
            'taxes' => 'nullable', // Permite que seja null
            'amount' => 'required', // Este é obrigatório
            'provider' => 'required', // Este é obrigatório
            'transaction_type' => 'required', // Este é obrigatório
            'billing_address' => 'nullable|min:3|max:255', // Permite null, mas se preenchido, deve ter entre 3 e 255 caracteres
            'first_name' => 'required|min:1|max:255', // Permite null
            'last_name' => 'required|min:1|max:255', // Permite null
            'country' => 'required|min:1|max:255', // Permite null
            'state' => 'nullable|min:1|max:255', // Permite null
            'postcode' => 'nullable|min:1|max:255', // Permite null
            'city' => 'nullable|min:1|max:255', // Permite null
            'manual_payment_files' => 'nullable', // Permite null
            'manual_payment_description' => 'nullable', // Permite null
        ];
    }
}
