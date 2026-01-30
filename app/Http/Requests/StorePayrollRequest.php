<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->employee?->isManager();
    }

    public function rules(): array
    {
        $rules = [
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'generate_all' => ['sometimes', 'boolean'],
        ];
        
        // Se não for para gerar para todos, employee_id é obrigatório
        if (!$this->boolean('generate_all')) {
            $rules['employee_id'] = ['required', 'exists:employees,id'];
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'employee_id.required_without' => 'Selecione um funcionário ou gere para todos.',
            'employee_id.exists' => 'Funcionário não encontrado.',
            'year.required' => 'O ano é obrigatório.',
            'month.required' => 'O mês é obrigatório.',
        ];
    }
}
