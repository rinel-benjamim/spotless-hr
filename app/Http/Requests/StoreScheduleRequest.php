<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->employee?->isManager();
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required_without:generate_month', 'date'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'is_working_day' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'generate_month' => ['sometimes', 'boolean'],
            'year' => ['required_with:generate_month', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required_with:generate_month', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'O funcionário é obrigatório.',
            'date.required_without' => 'A data é obrigatória.',
            'shift_id.exists' => 'Turno não encontrado.',
        ];
    }
}
