<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(protected DocumentService $documentService) {}

    public function index(Request $request): JsonResponse
    {
        $lists = $this->documentService->listForUser($request->user());

        return response()->json([
            'owned' => DocumentResource::collection($lists['owned'])->resolve($request),
            'shared' => DocumentResource::collection($lists['shared'])->resolve($request),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $document = $this->documentService->storeForUser($request->user(), $data);

        return response()->json([
            'document' => (new DocumentResource($document))->resolve($request),
        ], 201);
    }

    public function show(Request $request, Document $document): JsonResponse
    {
        $this->authorizeAccess($request, $document);

        $document = $this->documentService->loadForShow($document);

        return response()->json([
            'document' => (new DocumentResource($document))->withContent()->resolve($request),
        ]);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $this->authorizeEdit($request, $document);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
        ]);

        $document = $this->documentService->updateDocument($document, $data);

        return response()->json([
            'document' => (new DocumentResource($document))->withContent()->resolve($request),
        ]);
    }

    public function rename(Request $request, Document $document): JsonResponse
    {
        $this->authorizeEdit($request, $document);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $document = $this->documentService->rename($document, $data['title']);

        return response()->json([
            'document' => (new DocumentResource($document))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        $this->documentService->deleteIfOwner($document, $request->user());

        return response()->json(['message' => 'Document deleted']);
    }

    private function authorizeAccess(Request $request, Document $document): void
    {
        if (! $document->isAccessibleBy($request->user())) {
            abort(403, 'You do not have access to this document.');
        }
    }

    private function authorizeEdit(Request $request, Document $document): void
    {
        if (! $document->canEdit($request->user())) {
            abort(403, 'You do not have permission to edit this document.');
        }
    }
}
