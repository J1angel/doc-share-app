<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Repositories\DocumentRepository;

class DocumentService extends BaseService
{
    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listForUser(User $user): array
    {
        return [
            'owned' => $this->repository->getOwnedByUserId($user->id),
            'shared' => $this->repository->getSharedWithUserId($user->id),
        ];
    }

    public function storeForUser(User $user, array $data): Document
    {
        $document = $this->repository->persist([
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'] ?? '<p></p>',
        ]);

        return $document->load('sharedWith');
    }

    public function loadForShow(Document $document): Document
    {
        return $document->load(['owner:id,name,email', 'sharedWith:id,name,email', 'attachments']);
    }

    public function updateDocument(Document $document, array $data): Document
    {
        return $this->repository->persist($data, $document)->load('sharedWith');
    }

    public function rename(Document $document, string $title): Document
    {
        return $this->repository->persist(['title' => $title], $document);
    }

    public function deleteIfOwner(Document $document, User $user): void
    {
        if (! $document->isOwnedBy($user)) {
            abort(403, 'Only the owner can delete this document.');
        }

        $this->repository->delete($document);
    }
}
