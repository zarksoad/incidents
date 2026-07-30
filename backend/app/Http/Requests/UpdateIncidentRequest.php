<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:baja,media,alta,critica',
            'status' => 'sometimes|required|in:abierto,en_progreso,cerrado,vencido',
            'assigned_id' => 'nullable|exists:users,id',
            'due_date' => 'sometimes|required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'priority.in' => 'La prioridad debe ser: baja, media, alta o critica.',
            'status.in' => 'El estado debe ser: abierto, en_progreso, cerrado o vencido.',
            'assigned_id.exists' => 'El usuario asignado no existe.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
        ];
    }
}
