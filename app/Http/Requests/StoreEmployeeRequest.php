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
        return $this->user()->isAdmin() || $this->user()->employee?->isManager();
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(EmployeeRole::class)],
            'contract_type' => ['required', Rule::enum(ContractType::class)],
            'shift_id' => ['required', 'exists:shifts,id'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'deduction_per_absence' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(EmployeeStatus::class)],
            'user_id' => ['nullable', 'exists:users,id'],
            'password' => ['nullable', 'string', 'min:8'],
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
