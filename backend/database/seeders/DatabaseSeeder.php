<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alice = User::updateOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alice Owner', 'password' => Hash::make('password')],
        );

        $bob = User::updateOrCreate(
            ['email' => 'bob@example.com'],
            ['name' => 'Bob Collaborator', 'password' => Hash::make('password')],
        );

        $carol = User::updateOrCreate(
            ['email' => 'carol@example.com'],
            ['name' => 'Carol Viewer', 'password' => Hash::make('password')],
        );

        $welcome = Document::updateOrCreate(
            ['user_id' => $alice->id, 'title' => 'Welcome to CollabDocs'],
            [
                'content' => '<h1>Welcome to CollabDocs</h1><p>This is a <strong>sample document</strong> owned by Alice. Try logging in as Bob to see shared documents, or share this doc from the editor.</p><ul><li>Bold, italic, underline formatting</li><li>Headings and lists</li><li>File import from .txt or .md</li></ul>',
            ],
        );

        DocumentShare::updateOrCreate(
            ['document_id' => $welcome->id, 'user_id' => $bob->id],
            ['permission' => 'edit'],
        );

        Document::updateOrCreate(
            ['user_id' => $bob->id, 'title' => 'Bob\'s Private Notes'],
            [
                'content' => '<p>Only Bob can see this unless it is shared.</p>',
            ],
        );
    }
}
