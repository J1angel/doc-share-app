<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentAttachmentResource;
use App\Http\Resources\DocumentImportNewResource;
use App\Http\Resources\DocumentImportUpdateResource;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadController extends Controller
{
    public function __construct(protected UploadService $uploadService) {}

    public function importAsNew(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($message = $this->uploadService->validateImportExtension($extension)) {
            return response()->json(['message' => $message], 422);
        }

        $document = $this->uploadService->importAsNew(
            $request->user(),
            $file,
            $request->input('title'),
        );

        return response()->json([
            'message' => 'File imported as a new document.',
            'document' => (new DocumentImportNewResource($document))->resolve($request),
        ], 201);
    }

    public function importIntoExisting(Request $request, Document $document): JsonResponse
    {
        if (! $document->canEdit($request->user())) {
            abort(403, 'You do not have permission to edit this document.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'mode' => ['sometimes', 'in:append,replace'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($message = $this->uploadService->validateImportExtension($extension)) {
            return response()->json(['message' => $message], 422);
        }

        $document = $this->uploadService->importIntoExisting(
            $document,
            $file,
            $request->input('mode', 'append'),
        );

        return response()->json([
            'message' => 'File content imported into document.',
            'document' => (new DocumentImportUpdateResource($document))->resolve($request),
        ]);
    }

    public function attach(Request $request, Document $document): JsonResponse
    {
        if (! $document->canEdit($request->user())) {
            abort(403, 'You do not have permission to edit this document.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($message = $this->uploadService->validateAttachmentExtension($extension)) {
            return response()->json(['message' => $message], 422);
        }

        $attachment = $this->uploadService->attach($document, $file);

        return response()->json([
            'message' => 'Attachment uploaded.',
            'attachment' => (new DocumentAttachmentResource($attachment))->resolve($request),
        ], 201);
    }

    public function download(Request $request, Document $document, DocumentAttachment $attachment): StreamedResponse
    {
        if ($attachment->document_id !== $document->id) {
            abort(404);
        }

        if (! $document->isAccessibleBy($request->user())) {
            abort(403);
        }

        $path = $this->uploadService->downloadPath($attachment);

        if ($path === null) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($path, $attachment->original_name);
    }
}
