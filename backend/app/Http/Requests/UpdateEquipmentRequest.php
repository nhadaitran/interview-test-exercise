<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admin can update equipment
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $equipmentId = $this->route('equipment');

        return [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', Rule::unique('equipment', 'serial_number')->ignore($equipmentId), 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
