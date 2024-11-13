<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $role = Role::where('name', RoleEnum::COSTUMER->value)->first();
        $roleID = $role ? $role->id : null;
        
        return [
            'role_id'  => ['required', 'integer', 'in:' . $roleID],
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
    }
}
