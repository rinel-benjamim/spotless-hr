<?php

namespace App\Http\Requests;

use App\ContractType;
use App\EmployeeRole;
use App\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', Rule::enum(EmployeeRole::class)],
            'contract_type' => ['sometimes', Rule::enum(ContractType::class)],
            'shift_id' => ['sometimes', 'exists:shifts,id'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'deduction_per_absence' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(EmployeeStatus::class)],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.unique' => 'Este código de funcionário já está em uso.',
            'full_name.required' => 'O nome completo é obrigatório.',
            'shift_id.exists' => 'O turno selecionado é inválido.',
        ];
    }
}
