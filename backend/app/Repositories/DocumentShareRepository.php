<?php

namespace App\Repositories;

use App\Models\DocumentShare;

class DocumentShareRepository extends BaseRepository
{
    public $model = DocumentShare::class;

    public function upsert(int $documentId, int $userId, string $permission): DocumentShare
    {
        return $this->model::updateOrCreate(
            [
                'document_id' => $documentId,
                'user_id' => $userId,
            ],
            ['permission' => $permission],
        );
    }

    public function deleteForDocumentAndUser(int $documentId, int $userId): int
    {
        return $this->query()
            ->where('document_id', $documentId)
            ->where('user_id', $userId)
            ->delete();
    }
}
