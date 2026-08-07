<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdatePlatformSettingRequest;
use App\Models\SiteSetting;
use App\Support\CatalogCache;

class PlatformSettingsController extends Controller
{
    /**
     * @var list<string>
     */
    protected array $secretFields = [
        'paytabs_server_key',
        'paytabs_client_key',
        'tap_secret_key',
        'tap_public_key',
        'noon_app_key',
        'noon_app_secret',
        'amazon_ps_access_code',
        'amazon_ps_sha_request',
        'amazon_ps_sha_response',
    ];

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:platform_settings.view')->only(['index']);
        $this->middleware('permission:platform_settings.edit')->only(['update']);
    }

    public function index()
    {
        $records = SiteSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'site_title' => config('app.name', 'Admin'),
                'contact_email' => 'support@admin.com',
                'currency' => 'AED',
                'order_payment_mode' => 'later',
                'order_assignment_mode' => 'open',
                'order_source_retention_days' => 90,
                'order_delivery_retention_days' => 1095,
                'vendor_document_download_allowed' => false,
                'vendor_payout_schedule' => 'weekly',
                'platform_fee_percent' => 10,
                'platform_fee_fixed' => 0,
            ]
        );

        return view('admin.platform-settings', compact('records'));
    }

    public function update(UpdatePlatformSettingRequest $request, int $id)
    {
        $settings = SiteSetting::query()->findOrFail($id);
        $data = $request->validated();

        foreach ($this->secretFields as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];
            if ($value === null || $value === '') {
                unset($data[$field]);
            }
        }

        $settings->fill($data);
        $settings->save();

        CatalogCache::flushSiteSettings();

        return redirect()
            ->route('admin.platform-settings.index')
            ->with('success', __('general.platform_settings_was_updated_successfully'));
    }
}
