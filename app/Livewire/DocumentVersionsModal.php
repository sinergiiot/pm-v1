<?php

namespace App\Livewire;

use App\Models\ProjectDocument;
use App\Models\ProjectDocumentVersion;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DocumentVersionsModal extends Component
{
    public int $documentId;

    public ?int $revisionVersionId = null;

    public string $revisionNotes = '';

    public function mount(int $documentId): void
    {
        $this->documentId = $documentId;
    }

    public function getDocument(): ?ProjectDocument
    {
        return ProjectDocument::find($this->documentId);
    }

    public function getVersions()
    {
        $doc = $this->getDocument();
        if (! $doc) {
            return collect();
        }

        return $doc->versions()->with(['uploader', 'reviewer'])->orderByDesc('version')->get();
    }

    public function approveVersion(int $versionId): void
    {
        $version = ProjectDocumentVersion::find($versionId);
        if (! $version || $version->project_document_id != $this->documentId) {
            return;
        }
        $version->update([
            'approval_status' => ProjectDocumentVersion::APPROVAL_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
            'revision_notes' => null,
        ]);
        Notification::make()->title('Versi disetujui (Done)')->success()->send();
    }

    public function openRevisionForm(int $versionId): void
    {
        $version = ProjectDocumentVersion::find($versionId);
        if (! $version || $version->project_document_id != $this->documentId) {
            return;
        }
        $this->revisionVersionId = $versionId;
        $this->revisionNotes = $version->revision_notes ?? '';
    }

    public function cancelRevisionForm(): void
    {
        $this->revisionVersionId = null;
        $this->revisionNotes = '';
    }

    public function submitRevision(): void
    {
        $this->validate([
            'revisionNotes' => 'required|string|min:1',
        ], [
            'revisionNotes.required' => 'Detail revisi wajib diisi.',
        ]);

        $version = ProjectDocumentVersion::find($this->revisionVersionId);
        if (! $version || $version->project_document_id != $this->documentId) {
            return;
        }
        $version->update([
            'approval_status' => ProjectDocumentVersion::APPROVAL_REVISION_REQUESTED,
            'revision_notes' => $this->revisionNotes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);
        Notification::make()->title('Revisi dicatat')->success()->send();
        $this->cancelRevisionForm();
    }

    public function render()
    {
        return view('livewire.document-versions-modal', [
            'document' => $this->getDocument(),
            'versions' => $this->getVersions(),
        ]);
    }
}
