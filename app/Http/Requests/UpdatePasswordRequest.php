<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            if ($user && $this->filled('current_password')
                && ! Hash::check($this->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', 'Password lama tidak sesuai.');
            }

            if ($user && $this->filled('password')
                && Hash::check($this->input('password'), $user->password)) {
                $validator->errors()->add('password', 'Password baru tidak boleh sama dengan password lama.');
            }
        });
    }
}
