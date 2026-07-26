<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreVendorRequest;
use App\Http\Requests\Admin\UpdateVendorRequest;
use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('vendors');
        $this->middleware('permission:vendors.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $vendors = Vendor::with(['owner', 'translations'])
            ->orderByDesc('id')
            ->get();

        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(StoreVendorRequest $request)
    {
        DB::transaction(function () use ($request) {
            $admin = auth('admin')->user();
            $legalNameEn = $request->input('translations.en.legal_name');

            $vendor = Vendor::create([
                'slug' => $this->uniqueSlug($legalNameEn),
                'trn' => $request->trn,
                'trade_license_no' => $request->trade_license_no,
                'trade_license_expiry' => $request->trade_license_expiry,
                'moj_registration_no' => $request->moj_registration_no,
                'email' => $request->email,
                'phone' => $request->phone,
                'logo' => $this->storeLogo($request),
                'is_active' => true,
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => $admin?->id,
            ]);

            $this->syncTranslations($vendor, $request->input('translations', []));

            VendorUser::create([
                'vendor_id' => $vendor->id,
                'first_name' => $request->input('owner.first_name'),
                'last_name' => $request->input('owner.last_name'),
                'phone' => $request->input('owner.phone'),
                'email' => $request->input('owner.email'),
                'password' => bcrypt($request->input('owner.password')),
                'is_active' => true,
                'is_owner' => true,
            ]);
        });

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', __('general.vendor_has_been_created_successfully'));
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['owner', 'translations', 'approvedBy']);

        return view('admin.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load(['owner', 'translations']);

        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        DB::transaction(function () use ($request, $vendor) {
            $data = $request->only([
                'trn',
                'trade_license_no',
                'trade_license_expiry',
                'moj_registration_no',
                'email',
                'phone',
            ]);

            if ($request->hasFile('logo')) {
                $data['logo'] = $this->storeLogo($request, $request->previous_logo);
            }

            $vendor->update($data);
            $this->syncTranslations($vendor, $request->input('translations', []));

            $owner = VendorUser::where('vendor_id', $vendor->id)
                ->where('id', $request->input('owner.id'))
                ->firstOrFail();

            $ownerData = [
                'first_name' => $request->input('owner.first_name'),
                'last_name' => $request->input('owner.last_name'),
                'phone' => $request->input('owner.phone'),
                'email' => $request->input('owner.email'),
            ];

            if ($request->filled('owner.password')) {
                $ownerData['password'] = bcrypt($request->input('owner.password'));
            }

            $owner->update($ownerData);
        });

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', __('general.vendor_has_been_updated_successfully'));
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->logo && file_exists(uploadsDir('vendors').$vendor->logo)) {
            unlink(uploadsDir('vendors').$vendor->logo);
        }

        $vendor->delete();

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', __('general.vendor_has_been_deleted_successfully'));
    }

    public function changeStatus(Request $request)
    {
        if (! isset($request->id, $request->status)) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $vendor = Vendor::find($request->id);

        if (! $vendor) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $vendor->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(Vendor $vendor, array $translations): void
    {
        foreach (['en', 'ar'] as $locale) {
            $vendor->translateOrNew($locale)->fill([
                'legal_name' => data_get($translations, "{$locale}.legal_name"),
                'business_name' => data_get($translations, "{$locale}.business_name"),
                'address' => data_get($translations, "{$locale}.address"),
            ]);
        }

        $vendor->save();
    }

    protected function storeLogo(StoreVendorRequest|UpdateVendorRequest $request, ?string $previousLogo = null): ?string
    {
        if (! $request->hasFile('logo')) {
            return $previousLogo;
        }

        $file = $request->file('logo');
        $filename = 'vendor-logo-'.time().'.'.$file->getClientOriginalExtension();
        $file->move(uploadsDir('vendors'), $filename);

        if ($previousLogo && file_exists(uploadsDir('vendors').$previousLogo)) {
            unlink(uploadsDir('vendors').$previousLogo);
        }

        return $filename;
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'vendor';
        $slug = $base;
        $counter = 1;

        while (
            Vendor::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
