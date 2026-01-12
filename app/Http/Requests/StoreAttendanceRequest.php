<?php

namespace App\Http\Requests;

use App\AttendanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', Rule::enum(AttendanceType::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'O funcionário é obrigatório.',
            'employee_id.exists' => 'Funcionário inválido.',
            'type.required' => 'O tipo de registo é obrigatório.',
        ];
    }
}
