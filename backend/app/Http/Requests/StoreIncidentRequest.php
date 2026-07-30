<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:baja,media,alta,critica',
            'status' => 'required|in:abierto,en_progreso,cerrado,vencido',
            'assigned_id' => 'nullable|exists:users,id',
            'due_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'priority.required' => 'La prioridad es obligatoria.',
            'priority.in' => 'La prioridad debe ser: baja, media, alta o critica.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser: abierto, en_progreso, cerrado o vencido.',
            'assigned_id.exists' => 'El usuario asignado no existe.',
            'due_date.required' => 'La fecha de vencimiento es obligatoria.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
        ];
    }
}
