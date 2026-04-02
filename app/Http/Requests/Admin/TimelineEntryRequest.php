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
        return [
            'type'        => ['required', 'string', 'in:education,experience'],
            'institution' => ['required', 'string', 'max:255'],
            'role'        => ['required', 'string', 'max:255'],
            'start_year'  => ['required', 'integer'],
            'end_year'    => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer'],
        ];
    }
}
