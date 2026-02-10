<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isVendor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.day' => ['nullable', 'string'],
            'opening_hours.*.open' => ['nullable', 'string'],
            'opening_hours.*.close' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
