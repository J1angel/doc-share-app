<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentShareResource;
use App\Models\Document;
use App\Models\User;
use App\Services\ShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function __construct(protected ShareService $shareService) {}

    public function store(Request $request, Document $document): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permission' => ['sometimes', 'in:view,edit'],
        ]);

        $share = $this->shareService->share(
            $document,
            $request->user(),
            (int) $data['user_id'],
            $data['permission'] ?? 'edit',
        );

        return response()->json([
            'message' => 'Document shared successfully.',
            'share' => (new DocumentShareResource($share))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Document $document, User $user): JsonResponse
    {
        $this->shareService->revoke($document, $request->user(), $user->id);

        return response()->json(['message' => 'Share removed']);
    }
}
