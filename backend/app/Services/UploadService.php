<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\User;
use App\Repositories\DocumentAttachmentRepository;
use App\Repositories\DocumentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    private const ALLOWED_IMPORT_EXTENSIONS = ['txt', 'md', 'markdown'];

    private const ALLOWED_ATTACHMENT_EXTENSIONS = ['txt', 'md', 'markdown', 'pdf', 'png', 'jpg', 'jpeg'];

    public function __construct(
        protected DocumentRepository $documentRepository,
        protected DocumentAttachmentRepository $attachmentRepository,
    ) {}

    public function validateImportExtension(string $extension): ?string
    {
        if (! in_array($extension, self::ALLOWED_IMPORT_EXTENSIONS, true)) {
            return 'Unsupported file type. Supported import types: .txt, .md';
        }

        return null;
    }

    public function validateAttachmentExtension(string $extension): ?string
    {
        if (! in_array($extension, self::ALLOWED_ATTACHMENT_EXTENSIONS, true)) {
            return 'Unsupported attachment type. Allowed: .txt, .md, .pdf, .png, .jpg, .jpeg';
        }

        return null;
    }

    public function importAsNew(User $user, UploadedFile $file, ?string $title): Document
    {
        $html = $this->plainTextToHtml(file_get_contents($file->getRealPath()));

        return $this->documentRepository->persist([
            'user_id' => $user->id,
            'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'content' => $html,
        ]);
    }

    public function importIntoExisting(Document $document, UploadedFile $file, string $mode = 'append'): Document
    {
        $html = $this->plainTextToHtml(file_get_contents($file->getRealPath()));

        $document->content = $mode === 'replace'
            ? $html
            : $document->content.$html;

        $document->save();

        return $document;
    }

    public function attach(Document $document, UploadedFile $file): DocumentAttachment
    {
        $path = $file->store('attachments/'.$document->id, 'local');

        return $this->attachmentRepository->persist([
            'document_id' => $document->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function downloadPath(DocumentAttachment $attachment): ?string
    {
        if (! Storage::disk('local')->exists($attachment->path)) {
            return null;
        }

        return $attachment->path;
    }

    public function plainTextToHtml(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $paragraphs = preg_split("/\r\n|\r|\n/", $escaped) ?: [];
        $blocks = array_map(
            fn (string $line) => $line === '' ? '<p><br></p>' : '<p>'.$line.'</p>',
            $paragraphs,
        );

        return implode('', $blocks);
    }
}
