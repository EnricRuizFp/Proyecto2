<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => ['required', Rule::unique('users')->ignore($this->user->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'password' => 'nullable|min:8',
            'role_id' => 'required|array',
            'role_id.*' => 'exists:roles,id',
            'surname1' => 'required|string|max:255',
            'surname2' => 'nullable|string|max:255',
            'nationality' => 'required|string|in:africa,america,asia,europe,oceania',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'El nombre de usuario es requerido',
            'username.unique' => 'Este nombre de usuario ya está en uso',
            'email.required' => 'El correo electrónico es requerido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'email.email' => 'El formato del correo electrónico no es válido',
            'nationality.required' => 'La nacionalidad es requerida',
            'nationality.in' => 'La nacionalidad seleccionada no es válida',
            'role_id.required' => 'Debe seleccionar al menos un rol',
            'role_id.array' => 'Los roles deben ser un array',
            'role_id.*.exists' => 'Uno o más roles seleccionados no son válidos'
        ];
    }
}
