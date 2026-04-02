<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TimelineEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = now()->year;

        return [
            'type'        => ['required', 'string', 'in:education,experience'],
            'institution' => ['required', 'string', 'max:255'],
            'role'        => ['required', 'string', 'max:255'],
            'start_year'  => ['required', 'integer', 'min:1900', 'max:' . ($currentYear + 1)],
            'end_year'    => ['nullable', 'integer', 'min:1900', 'max:' . ($currentYear + 10), 'gte:start_year'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
