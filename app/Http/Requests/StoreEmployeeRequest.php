<?php

namespace App\Http\Requests;

use App\ContractType;
use App\EmployeeRole;
use App\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:255', 'unique:employees,employee_code'],
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(EmployeeRole::class)],
            'contract_type' => ['required', Rule::enum(ContractType::class)],
            'shift_id' => ['required', 'exists:shifts,id'],
            'status' => ['sometimes', Rule::enum(EmployeeStatus::class)],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.required' => 'O código do funcionário é obrigatório.',
            'employee_code.unique' => 'Este código de funcionário já está em uso.',
            'full_name.required' => 'O nome completo é obrigatório.',
            'role.required' => 'O cargo é obrigatório.',
            'contract_type.required' => 'O tipo de contrato é obrigatório.',
            'shift_id.required' => 'O turno é obrigatório.',
            'shift_id.exists' => 'O turno selecionado é inválido.',
        ];
    }
}
