<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use App\Support\CatalogCache;

class SiteSettingsController extends Controller
{
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
                'primary_button_color' => '#000000',
                'primary_button_color_end' => '#000000',
                'primary_button_color_angle' => 135,
                'secondary_button_color' => '#FFFFFF',
                'secondary_button_color_end' => '#FFFFFF',
                'secondary_button_color_angle' => 135,
                'primary_button_text_color' => '#FFFFFF',
                'secondary_button_text_color' => '#000000',
                'primary_button_border_color' => '#000000',
                'secondary_button_border_color' => '#000000',
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
        // check logo if exists
        if ($request->hasfile('logo')) {

            // move | upload file on server
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $filename = 'logo-'.time().'.'.$extension;
            $file->move(uploadsDir('front'), $filename);

            // check if upload successfully
            if (file_exists(uploadsDir('front').$filename)
                && ! empty($request->previous_logo && file_exists(uploadsDir('front').$request->previous_logo))
            ) {
                unlink(uploadsDir('front').$request->previous_logo);
            }
        } else {
            $filename = $request->previous_logo;
        }

        // check small logo if exists
        if ($request->hasfile('small_logo')) {

            // move | upload file on server
            $file = $request->file('small_logo');
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $smlogo = 'small_logo-'.time().'.'.$extension;
            $file->move(uploadsDir('front'), $smlogo);

            // check if upload successfully
            if (file_exists(uploadsDir('front').$smlogo)
                && ! empty($request->previous_small_logo && file_exists(uploadsDir('front').$request->previous_small_logo))
            ) {
                unlink(uploadsDir('front').$request->previous_small_logo);
            }
        } else {
            $smlogo = $request->previous_small_logo;
        }

        // check favicon if exists
        if ($request->hasfile('favicon')) {

            // move | upload file on server
            $file = $request->file('favicon');
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $favicon = 'favicon-'.time().'.'.$extension;
            $file->move(uploadsDir('front'), $favicon);

            // check if upload successfully
            if (file_exists(uploadsDir('front').$favicon)
                && ! empty($request->previous_favicon && file_exists(uploadsDir('front').$request->previous_favicon))
            ) {
                unlink(uploadsDir('front').$request->previous_favicon);
            }
        } else {
            $favicon = $request->previous_favicon;
        }

        $data = $request->except([
            '_token',
            '_method',
            'previous_logo',
            'previous_logo_ar',
            'previous_favicon',
            'previous_small_logo',
            'logo',
            'logo_ar',
            'favicon',
            'small_logo',
        ]);

        $data['logo'] = $filename;
        // $data['logo_ar'] = $logo_ar;
        $data['favicon'] = $favicon;
        // $data['small_logo'] = $smlogo;

        SiteSetting::where('id', $id)->update($data);
        CatalogCache::flushSiteSettings();

        return redirect()
            ->route('admin.site-settings.index')
            ->with('success', __('general.site_settings_was_updated_successfully'));
    }
}
