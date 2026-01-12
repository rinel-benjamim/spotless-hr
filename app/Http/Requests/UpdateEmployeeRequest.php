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
            'employee_code' => ['sometimes', 'string', 'max:255', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', Rule::enum(EmployeeRole::class)],
            'contract_type' => ['sometimes', Rule::enum(ContractType::class)],
            'shift_id' => ['sometimes', 'exists:shifts,id'],
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
