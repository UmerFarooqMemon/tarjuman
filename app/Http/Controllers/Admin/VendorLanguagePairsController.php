<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreVendorLanguagePairRequest;
use App\Http\Requests\Admin\UpdateVendorLanguagePairRequest;
use App\Models\Language;
use App\Models\Vendor;
use App\Models\VendorLanguagePair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorLanguagePairsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:vendors.view')->only(['index']);
        $this->middleware('permission:vendors.edit')->only([
            'store', 'update', 'destroy', 'changeStatus',
        ]);
    }

    public function index(Vendor $vendor)
    {
        $pairs = $vendor->languagePairs()
            ->with(['sourceLanguage.translations', 'targetLanguage.translations', 'pricingRules'])
            ->orderByDesc('id')
            ->get();

        $pairLanguageIds = $pairs
            ->flatMap(fn (VendorLanguagePair $pair) => [
                (int) $pair->source_language_id,
                (int) $pair->target_language_id,
            ])
            ->unique()
            ->all();

        $languages = Language::cachedAll()
            ->filter(fn (Language $language) => $language->is_active || in_array((int) $language->id, $pairLanguageIds, true))
            ->values();

        $existingPairKeys = $pairs
            ->map(fn (VendorLanguagePair $pair) => $pair->source_language_id.':'.$pair->target_language_id)
            ->values()
            ->all();

        return view('admin.vendors.language-pairs.index', compact(
            'vendor',
            'pairs',
            'languages',
            'existingPairKeys'
        ));
    }

    public function store(StoreVendorLanguagePairRequest $request, Vendor $vendor)
    {
        $pairs = collect($request->input('pairs', []))
            ->map(fn (array $pair) => [
                'source_language_id' => (int) $pair['source_language_id'],
                'target_language_id' => (int) $pair['target_language_id'],
            ])
            ->unique(fn (array $pair) => $pair['source_language_id'].':'.$pair['target_language_id'])
            ->values();

        $created = 0;

        DB::transaction(function () use ($vendor, $pairs, &$created) {
            foreach ($pairs as $pair) {
                $model = $vendor->languagePairs()->firstOrCreate(
                    [
                        'source_language_id' => $pair['source_language_id'],
                        'target_language_id' => $pair['target_language_id'],
                    ],
                    ['is_active' => true]
                );

                if ($model->wasRecentlyCreated) {
                    $created++;
                }
            }
        });

        if ($created === 0) {
            return redirect()
                ->route('admin.vendors.language-pairs.index', $vendor)
                ->with('error', __('general.language_pair_already_exists'));
        }

        return redirect()
            ->route('admin.vendors.language-pairs.index', $vendor)
            ->with('success', trans_choice('general.language_pairs_created_successfully', $created, ['count' => $created]));
    }

    public function update(UpdateVendorLanguagePairRequest $request, Vendor $vendor, VendorLanguagePair $languagePair)
    {
        $this->ensurePairBelongsToVendor($vendor, $languagePair);

        $languagePair->update([
            'source_language_id' => $request->source_language_id,
            'target_language_id' => $request->target_language_id,
        ]);

        return redirect()
            ->route('admin.vendors.language-pairs.index', $vendor)
            ->with('success', __('general.language_pair_has_been_updated_successfully'));
    }

    public function destroy(Vendor $vendor, VendorLanguagePair $languagePair)
    {
        $this->ensurePairBelongsToVendor($vendor, $languagePair);

        $languagePair->delete();

        return redirect()
            ->route('admin.vendors.language-pairs.index', $vendor)
            ->with('success', __('general.language_pair_has_been_deleted_successfully'));
    }

    public function changeStatus(Request $request, Vendor $vendor)
    {
        if (! isset($request->id, $request->status)) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $pair = VendorLanguagePair::where('vendor_id', $vendor->id)->find($request->id);

        if (! $pair) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $pair->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    protected function ensurePairBelongsToVendor(Vendor $vendor, VendorLanguagePair $languagePair): void
    {
        abort_unless($languagePair->vendor_id === $vendor->id, 404);
    }
}
