<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use App\Support\CatalogCache;
use Illuminate\Http\UploadedFile;

class SiteSettingsController extends Controller
{
    /**
     * @var list<array{column: string, keep: string, uploads: string, prefix: string}>
     */
    protected array $galleries = [
        [
            'column' => 'accepted_by_images',
            'keep' => 'accepted_by_keep',
            'uploads' => 'accepted_by_uploads',
            'managed' => 'accepted_by_managed',
            'prefix' => 'accepted-by-',
        ],
        [
            'column' => 'certified_by_images',
            'keep' => 'certified_by_keep',
            'uploads' => 'certified_by_uploads',
            'managed' => 'certified_by_managed',
            'prefix' => 'certified-by-',
        ],
        [
            'column' => 'regulated_by_images',
            'keep' => 'regulated_by_keep',
            'uploads' => 'regulated_by_uploads',
            'managed' => 'regulated_by_managed',
            'prefix' => 'regulated-by-',
        ],
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:site_settings.view')->only(['index']);
        $this->middleware('permission:site_settings.edit')->only(['update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $records = SiteSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'site_title' => config('app.name', 'Admin'),
                'contact_email' => 'support@admin.com',
                'currency' => 'AED',
                'primary_color' => '#000000',
                'primary_color_end' => '#000000',
                'primary_color_angle' => 135,
                'secondary_color' => '#FFFFFF',
                'secondary_color_end' => '#FFFFFF',
                'secondary_color_angle' => 135,
                'primary_button_color' => '#227241',
                'primary_button_color_end' => '#227241',
                'primary_button_color_angle' => 135,
                'secondary_button_color' => '#CCCCCC',
                'secondary_button_color_end' => '#CCCCCC',
                'secondary_button_color_angle' => 135,
                'primary_button_text_color' => '#FFFFFF',
                'secondary_button_text_color' => '#666666',
                'primary_button_border_color' => '#227241',
                'secondary_button_border_color' => '#CCCCCC',
                'footer_bg_color' => '#000000',
                'footer_heading_color' => '#FFFFFF',
                'footer_link_color' => '#FFFFFF',
                'footer_link_hover_color' => '#CCCCCC',
                'accepted_by_images' => [],
                'certified_by_images' => [],
                'regulated_by_images' => [],
            ]
        );

        return view('admin.site-settings', compact('records'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(UpdateSiteSettingRequest $request, $id)
    {
        $settings = SiteSetting::query()->findOrFail($id);

        $filename = $this->storeBrandingUpload(
            $request,
            'logo',
            'previous_logo',
            'logo-'
        );

        $logoAr = $this->storeBrandingUpload(
            $request,
            'logo_ar',
            'previous_logo_ar',
            'logo-ar-'
        );

        $favicon = $this->storeBrandingUpload(
            $request,
            'favicon',
            'previous_favicon',
            'favicon-'
        );

        $footerLogo = $this->storeBrandingUpload(
            $request,
            'footer_logo',
            'previous_footer_logo',
            'footer-logo-'
        );

        $footerLogoAr = $this->storeBrandingUpload(
            $request,
            'footer_logo_ar',
            'previous_footer_logo_ar',
            'footer-logo-ar-'
        );

        $fontEn = $this->storeBrandingUpload(
            $request,
            'font_en',
            'previous_font_en',
            'font-en-'
        );

        $fontAr = $this->storeBrandingUpload(
            $request,
            'font_ar',
            'previous_font_ar',
            'font-ar-'
        );

        $data = $request->except([
            '_token',
            '_method',
            'previous_logo',
            'previous_logo_ar',
            'previous_favicon',
            'previous_footer_logo',
            'previous_footer_logo_ar',
            'previous_font_en',
            'previous_font_ar',
            'previous_small_logo',
            'logo',
            'logo_ar',
            'favicon',
            'footer_logo',
            'footer_logo_ar',
            'font_en',
            'font_ar',
            'small_logo',
            'accepted_by_keep',
            'accepted_by_uploads',
            'accepted_by_managed',
            'certified_by_keep',
            'certified_by_uploads',
            'certified_by_managed',
            'regulated_by_keep',
            'regulated_by_uploads',
            'regulated_by_managed',
        ]);

        $data['logo'] = $filename;
        $data['logo_ar'] = $logoAr;
        $data['favicon'] = $favicon;
        $data['footer_logo'] = $footerLogo;
        $data['footer_logo_ar'] = $footerLogoAr;
        $data['font_en'] = $fontEn;
        $data['font_ar'] = $fontAr;

        foreach ($this->galleries as $gallery) {
            $data[$gallery['column']] = $this->syncGallery(
                $request,
                $settings,
                $gallery['column'],
                $gallery['keep'],
                $gallery['uploads'],
                $gallery['managed'],
                $gallery['prefix']
            );
        }

        $settings->update($data);
        CatalogCache::flushSiteSettings();

        return redirect()
            ->route('admin.site-settings.index')
            ->with('success', __('general.site_settings_was_updated_successfully'));
    }

    /**
     * @return list<string>
     */
    protected function syncGallery(
        UpdateSiteSettingRequest $request,
        SiteSetting $settings,
        string $column,
        string $keepKey,
        string $uploadKey,
        string $managedKey,
        string $prefix,
    ): array {
        $existing = $settings->galleryFilenames($column);

        if (! $request->boolean($managedKey)) {
            return $existing;
        }

        $keepInput = $request->input($keepKey, []);
        if (! is_array($keepInput)) {
            $keepInput = [];
        }

        $keep = [];
        foreach ($keepInput as $file) {
            if (is_string($file) && $file !== '' && in_array($file, $existing, true) && ! in_array($file, $keep, true)) {
                $keep[] = $file;
            }
        }

        foreach (array_diff($existing, $keep) as $removed) {
            $path = uploadsDir('front').$removed;
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $uploaded = [];
        $files = $request->file($uploadKey, []);
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        foreach (array_values($files) as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $filename = $prefix.time().'-'.$index.'.'.$file->getClientOriginalExtension();
            $file->move(uploadsDir('front'), $filename);
            $uploaded[] = $filename;
        }

        return array_values(array_merge($keep, $uploaded));
    }

    protected function storeBrandingUpload(
        UpdateSiteSettingRequest $request,
        string $fileKey,
        string $previousKey,
        string $prefix,
    ): ?string {
        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            $filename = $prefix.time().'.'.$file->getClientOriginalExtension();
            $file->move(uploadsDir('front'), $filename);

            $previous = $request->input($previousKey);
            if (
                file_exists(uploadsDir('front').$filename)
                && is_string($previous)
                && $previous !== ''
                && file_exists(uploadsDir('front').$previous)
            ) {
                unlink(uploadsDir('front').$previous);
            }

            return $filename;
        }

        $previous = $request->input($previousKey);

        return is_string($previous) && $previous !== '' ? $previous : null;
    }
}
