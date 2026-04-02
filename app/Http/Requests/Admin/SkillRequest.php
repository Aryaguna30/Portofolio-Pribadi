<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'icon_class' => ['nullable', 'string', 'max:100'],
            'category'   => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
