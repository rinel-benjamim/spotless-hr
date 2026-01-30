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
        $rules = [
            'employee_ids' => ['required', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'is_working_day' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'generate_month' => ['sometimes', 'boolean'],
        ];
        
        // Se for para gerar mês completo, year e month são obrigatórios
        if ($this->boolean('generate_month')) {
            $rules['year'] = ['required', 'integer', 'min:2020', 'max:2100'];
            $rules['month'] = ['required', 'integer', 'min:1', 'max:12'];
        } else {
            $rules['date'] = ['required', 'date'];
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Pelo menos um funcionário é obrigatório.',
            'date.required_without' => 'A data é obrigatória.',
            'shift_id.exists' => 'Turno não encontrado.',
        ];
    }
}
