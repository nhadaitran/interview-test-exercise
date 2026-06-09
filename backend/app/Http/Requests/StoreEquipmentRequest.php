<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admin can store equipment
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'unique:equipment,serial_number', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
