<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_share_document_with_another_user(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Team Plan',
            'content' => '<p>Draft</p>',
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->postJson("/api/documents/{$document->id}/share", [
                'user_id' => $collaborator->id,
                'permission' => 'edit',
            ]);

        $response->assertOk()
            ->assertJsonPath('share.user.id', $collaborator->id);

        $this->assertDatabaseHas('document_shares', [
            'document_id' => $document->id,
            'user_id' => $collaborator->id,
            'permission' => 'edit',
        ]);
    }

    public function test_shared_user_sees_document_in_shared_list(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Shared Doc',
            'content' => '<p>Hello</p>',
        ]);

        $document->shares()->create([
            'user_id' => $collaborator->id,
            'permission' => 'edit',
        ]);

        $response = $this->actingAs($collaborator, 'sanctum')
            ->getJson('/api/documents');

        $response->assertOk();
        $sharedIds = collect($response->json('shared'))->pluck('id');

        $this->assertTrue($sharedIds->contains($document->id));
    }

    public function test_non_owner_cannot_delete_document(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Protected',
            'content' => '<p>Content</p>',
        ]);

        $document->shares()->create([
            'user_id' => $collaborator->id,
            'permission' => 'edit',
        ]);

        $this->actingAs($collaborator, 'sanctum')
            ->deleteJson("/api/documents/{$document->id}")
            ->assertForbidden();
    }
}
