<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use League\Csv\Writer;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskImportTemplateController extends Controller
{
    public function __invoke(Request $request, Project $project): StreamedResponse
    {
        $headers = [
            'Title',
            'Description',
            'Status',
            'Priority',
            'Epic',
            'Assignees Comma Separated Emails',
            'Start Date YYYY-MM-DD',
            'Due Date YYYY-MM-DD',
        ];

        $exampleRow = [
            'Sample Ticket Title',
            'Sample description for the ticket',
            $project->taskStatuses()->orderBy('order')->first()?->name ?? 'To Do',
            $project->taskPriorities()->first()?->name ?? 'Low',
            $project->epics()->first()?->title ?? 'Epic 1',
            '',
            now()->format('Y-m-d'),
            now()->addDays(7)->format('Y-m-d'),
        ];

        return response()->streamDownload(function () use ($headers, $exampleRow): void {
            $csv = Writer::createFromFileObject(new SplTempFileObject);
            $csv->insertOne($headers);
            $csv->insertOne($exampleRow);
            echo "\xEF\xBB\xBF" . $csv->toString();
        }, 'template-import-task.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
