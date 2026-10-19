<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class EventStoreRequest extends FormRequest
{
    public const MAX_TOTAL_MEDIA = 15;
    public const MAX_IMAGE_SIZE_BYTES = 30 * 1024 * 1024; // 30 MB
    public const MAX_VIDEO_SIZE_BYTES = 100 * 1024 * 1024; // 100 MB

    public function authorize(): bool
    {
        return $this->user()?->isVendor() || $this->user()?->isAdmin();
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tags' => ['nullable', 'string', 'max:500'],
            'audience' => ['required', 'array', 'min:1'],
            'audience.*' => ['required', 'string', Rule::in(Event::AUDIENCE_CATEGORIES)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'is_date_confirmed' => ['nullable', 'boolean'],
            'event_type' => ['nullable', 'in:free,paid'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'media' => ['nullable', 'array', 'max:' . self::MAX_TOTAL_MEDIA],
            'media.*' => [
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value instanceof UploadedFile) {
                        return;
                    }

                    $ext = strtolower((string) $value->getClientOriginalExtension());
                    $mime = strtolower((string) $value->getMimeType());
                    $size = $value->getSize();
                    $name = $value->getClientOriginalName();

                    $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif', 'heic', 'heif'];
                    $imageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif', 'image/heic', 'image/heif'];

                    $videoExts = ['mp4', 'mov', 'webm', 'm4v', 'mkv', 'avi'];
                    $videoMimes = ['video/mp4', 'video/quicktime', 'video/webm', 'video/x-m4v', 'video/x-matroska', 'video/avi', 'video/x-msvideo'];

                    $isImage = in_array($ext, $imageExts, true) || in_array($mime, $imageMimes, true) || str_starts_with($mime, 'image/');
                    $isVideo = in_array($ext, $videoExts, true) || in_array($mime, $videoMimes, true) || str_starts_with($mime, 'video/');

                    if (!$isImage && !$isVideo) {
                        $fail(__('The file ":name" must be a supported image (JPG, PNG, WEBP, AVIF, HEIC) or video (MP4, MOV, WEBM).', ['name' => $name]));
                        return;
                    }

                    if ($isImage && $size > self::MAX_IMAGE_SIZE_BYTES) {
                        $fail(__('The image ":name" may not be greater than 30MB.', ['name' => $name]));
                    }

                    if ($isVideo && $size > self::MAX_VIDEO_SIZE_BYTES) {
                        $fail(__('The video ":name" may not be greater than 100MB.', ['name' => $name]));
                    }
                },
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $newMedia = $this->file('media', []);
            $newCount = is_array($newMedia) ? count($newMedia) : 0;
            if ($newCount === 0) {
                return;
            }

            $event = $this->route('event');
            $existingCount = ($event instanceof Event) ? $event->media()->count() : 0;

            if (($existingCount + $newCount) > self::MAX_TOTAL_MEDIA) {
                $available = max(0, self::MAX_TOTAL_MEDIA - $existingCount);
                $validator->errors()->add(
                    'media',
                    __('You can upload at most :available more media files. Maximum :max photos and videos allowed per event.', [
                        'available' => $available,
                        'max' => self::MAX_TOTAL_MEDIA,
                    ])
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'audience.required' => __('Please select at least one target audience category.'),
            'audience.min' => __('Please select at least one target audience category.'),
            'audience.*.in' => __('The selected target audience is invalid. Allowed categories: Kids, Teens, Adults, All Ages.'),
            'media.max' => __('You cannot upload more than :max media files at once.', ['max' => self::MAX_TOTAL_MEDIA]),
        ];
    }
}
