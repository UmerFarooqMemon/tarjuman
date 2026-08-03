<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreDocumentTypeRequest;
use App\Http\Requests\Admin\UpdateDocumentTypeRequest;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('document_types');
        $this->middleware('permission:document_types.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $documentTypes = DocumentType::with('translations')
            ->ordered()
            ->get();

        $crudLocales = crudLocales();

        return view('admin.document-types.index', compact('documentTypes', 'crudLocales'));
    }

    public function store(StoreDocumentTypeRequest $request)
    {
        $documentType = DocumentType::create([
            'sort_order' => (int) (DocumentType::query()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        $this->syncTranslations($documentType, $request->input('translations', []));

        return redirect()
            ->route('admin.document-types.index')
            ->with('success', __('general.document_type_has_been_created_successfully'));
    }

    public function update(UpdateDocumentTypeRequest $request, DocumentType $documentType)
    {
        $this->syncTranslations($documentType, $request->input('translations', []));

        return redirect()
            ->route('admin.document-types.index')
            ->with('success', __('general.document_type_has_been_updated_successfully'));
    }

    public function destroy(DocumentType $documentType)
    {
        $documentType->delete();

        return redirect()
            ->route('admin.document-types.index')
            ->with('success', __('general.document_type_has_been_deleted_successfully'));
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

        $documentType = DocumentType::find($request->id);

        if (! $documentType) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $documentType->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(DocumentType $documentType, array $translations): void
    {
        foreach (crudLocaleCodes() as $locale) {
            $documentType->translateOrNew($locale)->fill([
                'name' => data_get($translations, "{$locale}.name"),
            ]);
        }

        $documentType->save();
    }
}
