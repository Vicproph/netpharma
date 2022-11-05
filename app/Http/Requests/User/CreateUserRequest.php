<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends Request
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required','string', 'min:4'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8']
        ];
    }
}
