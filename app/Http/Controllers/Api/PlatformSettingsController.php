<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    use ResolvesApiLocale;

    /**
     * Public platform / website settings for the frontend app.
     */
    public function show(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);
        $settings = siteSettings() ?? new SiteSetting;

        return response()->json([
            'data' => [
                'contact_email' => $this->nullableString($settings->contact_email),
                'contact_phone' => $this->nullableString($settings->contact_phone),
                'address' => [
                    'en' => $this->nullableString($settings->address),
                    'ar' => $this->nullableString($settings->address_ar),
                ],
                'copyright' => $this->nullableString($settings->copyright),
                'social' => [
                    'instagram' => $this->nullableString($settings->instagram),
                    'facebook' => $this->nullableString($settings->facebook),
                    'tiktok' => $this->nullableString($settings->tiktok),
                    'whatsapp' => $this->nullableString($settings->whatsapp),
                ],
                'logos' => [
                    'en' => $this->uploadUrl($settings->logo),
                    'ar' => $this->uploadUrl($settings->logo_ar),
                    'favicon' => $this->uploadUrl($settings->favicon),
                    'footer_en' => $this->uploadUrl($settings->footer_logo),
                    'footer_ar' => $this->uploadUrl($settings->footer_logo_ar),
                ],
                'accepted_by' => $this->galleryUrls($settings, 'accepted_by_images'),
                'certified_by' => $this->galleryUrls($settings, 'certified_by_images'),
                'regulated_by' => $this->galleryUrls($settings, 'regulated_by_images'),
                'branding' => $this->brandingPayload($settings),
            ],
            'locale' => $locale,
            'currency' => $this->currencyPayload(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function brandingPayload(SiteSetting $settings): array
    {
        return [
            'primary' => $this->gradientColor($settings, 'primary_color', '#000000'),
            'secondary' => $this->gradientColor($settings, 'secondary_color', '#FFFFFF'),
            'primary_button' => $this->gradientColor($settings, 'primary_button_color', '#227241'),
            'secondary_button' => $this->gradientColor($settings, 'secondary_button_color', '#FFFFFF'),
            'primary_button_text' => $settings->brandingSolid('primary_button_text_color', '#FFFFFF'),
            'secondary_button_text' => $settings->brandingSolid('secondary_button_text_color', '#000000'),
            'primary_button_border' => $settings->brandingSolid('primary_button_border_color', '#227241'),
            'secondary_button_border' => $settings->brandingSolid('secondary_button_border_color', '#000000'),
            'footer_bg' => $settings->brandingSolid('footer_bg_color', '#0F172A'),
            'footer_heading' => $settings->brandingSolid('footer_heading_color', '#FFFFFF'),
            'footer_link' => $settings->brandingSolid('footer_link_color', '#CBD5E1'),
            'footer_link_hover' => $settings->brandingSolid('footer_link_hover_color', '#FFFFFF'),
        ];
    }

    /**
     * @return array{start: string, end: string|null, angle: int, css: string}
     */
    protected function gradientColor(SiteSetting $settings, string $key, string $fallback): array
    {
        $start = $settings->brandingSolid($key, $fallback);
        $end = $settings->{$key.'_end'} ?? null;
        $end = is_string($end) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $end)
            ? $end
            : null;
        $angle = max(0, min(360, (int) ($settings->{$key.'_angle'} ?? 135)));

        return [
            'start' => $start,
            'end' => $end,
            'angle' => $angle,
            'css' => $settings->brandingBackground($key, $fallback),
        ];
    }

    protected function uploadUrl(?string $filename): ?string
    {
        if (! is_string($filename) || $filename === '') {
            return null;
        }

        $relative = uploadsDir('front').$filename;

        return file_exists(public_path($relative)) ? asset($relative) : null;
    }

    /**
     * @return list<string>
     */
    protected function galleryUrls(SiteSetting $settings, string $column): array
    {
        $urls = [];

        foreach ($settings->galleryFilenames($column) as $filename) {
            $url = $this->uploadUrl($filename);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
