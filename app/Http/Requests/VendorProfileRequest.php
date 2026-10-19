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
            'instagram' => ['nullable', 'url'],
            'facebook' => ['nullable', 'url'],
            'twitter' => ['nullable', 'url'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.day' => ['required_with:opening_hours', 'string'],
            'opening_hours.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.closed' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'media' => ['nullable', 'array', 'max:15'],
            'media.*' => [
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
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

                    if ($isImage && $size > 30 * 1024 * 1024) {
                        $fail(__('The image ":name" may not be greater than 30MB.', ['name' => $name]));
                    }

                    if ($isVideo && $size > 100 * 1024 * 1024) {
                        $fail(__('The video ":name" may not be greater than 100MB.', ['name' => $name]));
                    }
                },
            ],
        ];
    }
}
