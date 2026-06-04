<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use App\Repositories\DocumentShareRepository;
use App\Repositories\UserRepository;

class ShareService
{
    public function __construct(
        protected DocumentShareRepository $shareRepository,
        protected UserRepository $userRepository,
    ) {}

    public function share(Document $document, User $owner, int $userId, string $permission = 'edit'): DocumentShare
    {
        if (! $document->isOwnedBy($owner)) {
            abort(403, 'Only the owner can share this document.');
        }

        if ($userId === $owner->id) {
            abort(422, 'You cannot share a document with yourself.');
        }

        $share = $this->shareRepository->upsert($document->id, $userId, $permission);
        $share->setRelation('user', $this->userRepository->getOneById($userId));

        return $share;
    }

    public function revoke(Document $document, User $owner, int $userId): void
    {
        if (! $document->isOwnedBy($owner)) {
            abort(403, 'Only the owner can manage sharing.');
        }

        $this->shareRepository->deleteForDocumentAndUser($document->id, $userId);
    }
}
