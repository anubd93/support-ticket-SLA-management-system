<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreImportRequestV1;
use App\Services\V1\ImportServiceV1;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportControllerV1 extends Controller
{
    public function __construct(private readonly ImportServiceV1 $importServiceV1) {}

    public function index(): View
    {
        return view('imports.index', [
            'imports' => $this->importServiceV1->getImports(),
        ]);
    }

    public function create(): View
    {
        return view('imports.create');
    }

    public function store(StoreImportRequestV1 $request): JsonResponse
    {
        try {
            $import = $this->importServiceV1->start($request->file('file'), $request->user());

            return response()->json([
                'message' => 'File uploaded. The import is running in the background.',
                'redirect' => route('imports.show', $import->id),
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): View
    {
        $import = $this->importServiceV1->findImport($id);
        abort_if(! $import, Response::HTTP_NOT_FOUND);

        return view('imports.show', [
            'import' => $import,
            'failedRows' => $this->importServiceV1->getErrors($id),
        ]);
    }

    public function template(): StreamedResponse
    {
        return $this->importServiceV1->template();
    }
}
