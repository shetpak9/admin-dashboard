<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisableUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('admin_authenticated') === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:200'],
        ];
    }
}
