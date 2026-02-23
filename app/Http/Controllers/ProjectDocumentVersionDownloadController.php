<?php

namespace App\Http\Controllers;

use App\Models\ProjectDocumentVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentVersionDownloadController extends Controller
{
    public function __invoke(ProjectDocumentVersion $version): StreamedResponse
    {
        $document = $version->document;
        $project = $document->project;

        $user = Auth::user();
        if (! $user?->hasRole('super_admin') && ! $project->users()->where('users.id', $user?->id)->exists()) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($version->path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $version->path,
            $version->file_name,
            ['Content-Type' => 'application/octet-stream']
        );
    }
}
