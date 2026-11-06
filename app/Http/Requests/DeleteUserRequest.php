<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('admin_authenticated') === true;
    }

    public function rules(): array
    {
        return [
            'confirmation_uid' => ['required', 'string', 'same:uid'],
        ];
    }
}
