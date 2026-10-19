<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class SearchEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('audience')) {
            $raw = $this->input('audience');
            if (is_string($raw)) {
                $raw = preg_split('/[,\\n]+/', $raw) ?: [];
            }
            if (is_array($raw)) {
                $normalized = [];
                foreach ($raw as $item) {
                    $item = trim((string) $item);
                    if ($item === '') {
                        continue;
                    }
                    $canonical = Event::normalizeAudienceCategory($item);
                    $normalized[] = $canonical ?? $item;
                }
                $this->merge(['audience' => array_values(array_unique($normalized))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'place' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'audience' => ['nullable', 'array'],
            'audience.*' => ['nullable', 'string'],
            'event_type' => ['nullable', 'in:free,paid'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'city' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:date_asc,date_desc,distance,price_asc,price_desc,featured'],
        ];
    }
}
