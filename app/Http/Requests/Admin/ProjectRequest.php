<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $thumbnailRules = ['image', 'mimes:jpeg,png,webp', 'max:2048'];

        if ($this->isMethod('POST')) {
            array_unshift($thumbnailRules, 'required');
        } else {
            array_unshift($thumbnailRules, 'nullable');
        }

        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'tech_stack'  => ['nullable', 'array'],
            'tech_stack.*' => ['string'],
            'demo_url'    => ['nullable', 'url', 'max:500'],
            'repo_url'    => ['nullable', 'url', 'max:500'],
            'is_published' => ['boolean'],
            'sort_order'  => ['nullable', 'integer'],
            'thumbnail'   => $thumbnailRules,
        ];
    }
}
