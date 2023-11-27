<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required'],
            'phone' => ['required'],
            'role_id' => ['required'],
            'password' => ['nullable'],
            'gender_id' => ['required'],
            'is_status' => ['required'],
            'image' => ['nullable'],
            'is_ready' => ['nullable'],
            'position' => ['nullable'],
            'number_vehicle' => ['nullable'],
            'email_verified_at' => ['nullable'],
            'tranpostation_type' => ['nullable'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            "errors" => $validator->getMessageBag()
        ], 400));
    }
}
