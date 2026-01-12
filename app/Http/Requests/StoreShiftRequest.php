<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do turno é obrigatório.',
            'start_time.required' => 'A hora de início é obrigatória.',
            'start_time.date_format' => 'Formato de hora inválido.',
            'end_time.required' => 'A hora de fim é obrigatória.',
            'end_time.after' => 'A hora de fim deve ser posterior à hora de início.',
        ];
    }
}
