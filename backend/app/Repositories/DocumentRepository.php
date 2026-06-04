<?php

namespace App\Repositories;

use App\Models\Document;

class DocumentRepository extends BaseRepository{

    public $model = Document::class;

    public function getOwnedByUserId(int $userId)
    {
        return $this->query()
            ->where('user_id', $userId)
            ->with(['sharedWith:id,name,email', 'attachments'])
            ->latest('updated_at')
            ->get();
    }

    public function getSharedWithUserId(int $userId)
    {
        return $this->query()
            ->whereHas('shares', fn ($q) => $q->where('user_id', $userId))
            ->with(['owner:id,name,email', 'attachments'])
            ->latest('updated_at')
            ->get();
    }

    public function findWithRelations(int $id, array $relations)
    {
        return $this->include($relations)->getOneById($id);
    }
}
