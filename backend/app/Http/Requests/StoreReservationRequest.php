<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'equipment_id' => ['required', 'exists:equipment,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];

        if ($this->user() && $this->user()->isAdmin()) {
            $rules['user_id'] = ['required', 'exists:users,id'];
        }

        return $rules;
    }
}
