<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCurrencyRequest;
use App\Http\Requests\Admin\UpdateCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class CurrenciesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('currencies');
        $this->middleware('permission:currencies.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $currencies = Currency::with('translations')
            ->ordered()
            ->get();

        $crudLocales = crudLocales();

        return view('admin.currencies.index', compact('currencies', 'crudLocales'));
    }

    public function store(StoreCurrencyRequest $request)
    {
        $code = strtoupper($request->code);

        $currency = Currency::create([
            'code' => $code,
            'decimals' => (int) $request->input('decimals', 2),
            'sort_order' => (int) (Currency::query()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        if ($request->hasFile('icon_file')) {
            $currency->update([
                'icon' => $this->storeIconFile($request->file('icon_file'), $code),
            ]);
        }

        $this->syncTranslations($currency, $request->input('translations', []));

        return redirect()
            ->route('admin.currencies.index')
            ->with('success', __('general.currency_has_been_created_successfully'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $code = strtoupper($request->code);

        $currency->update([
            'code' => $code,
            'decimals' => (int) $request->input('decimals', 2),
        ]);

        if ($request->hasFile('icon_file')) {
            $currency->update([
                'icon' => $this->storeIconFile($request->file('icon_file'), $code, $currency->icon),
            ]);
        } elseif ($currency->wasChanged('code') && $currency->icon) {
            // Keep filename aligned with code when renaming without a new upload.
            $currency->update([
                'icon' => $this->renameIconFile($currency->icon, $code),
            ]);
        }

        $this->syncTranslations($currency, $request->input('translations', []));

        return redirect()
            ->route('admin.currencies.index')
            ->with('success', __('general.currency_has_been_updated_successfully'));
    }

    public function destroy(Currency $currency)
    {
        if ($currency->isInUse()) {
            return redirect()
                ->route('admin.currencies.index')
                ->with('error', __('general.currency_in_use_cannot_delete'));
        }

        $currency->delete();

        return redirect()
            ->route('admin.currencies.index')
            ->with('success', __('general.currency_has_been_deleted_successfully'));
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

        $currency = Currency::find($request->id);

        if (! $currency) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        if (! (bool) $request->status && $currency->isPlatformCurrency()) {
            return response()->json([
                'error' => 1,
                'message' => __('general.currency_is_platform_currency_cannot_deactivate'),
                'data' => [],
            ]);
        }

        $currency->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(Currency $currency, array $translations): void
    {
        foreach (crudLocaleCodes() as $locale) {
            $currency->translateOrNew($locale)->fill([
                'name' => data_get($translations, "{$locale}.name"),
            ]);
        }

        $currency->save();
    }

    protected function storeIconFile(UploadedFile $file, string $code, ?string $previous = null): string
    {
        $directory = public_path('assets/img/currencies');
        File::ensureDirectoryExists($directory);

        $filename = strtolower($code).'.svg';
        $file->move($directory, $filename);

        if ($previous && $previous !== $filename) {
            $previousPath = $directory.DIRECTORY_SEPARATOR.$previous;
            if (is_file($previousPath)) {
                @unlink($previousPath);
            }
        }

        return $filename;
    }

    protected function renameIconFile(string $previous, string $code): string
    {
        $directory = public_path('assets/img/currencies');
        $filename = strtolower($code).'.svg';
        $previousPath = $directory.DIRECTORY_SEPARATOR.$previous;
        $nextPath = $directory.DIRECTORY_SEPARATOR.$filename;

        if ($previous === $filename || ! is_file($previousPath)) {
            return $previous;
        }

        File::ensureDirectoryExists($directory);
        @rename($previousPath, $nextPath);

        return is_file($nextPath) ? $filename : $previous;
    }
}
