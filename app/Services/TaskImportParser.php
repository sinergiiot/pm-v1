<?php

namespace App\Services;

use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TaskImportParser
{
    /** Expected column labels (various forms) mapped to internal key */
    private const COLUMN_MAP = [
        'title' => ['judul', 'title'],
        'description' => ['deskripsi', 'description'],
        'status' => ['status (nama)', 'status', 'status nama'],
        'priority' => ['priority (nama)', 'priority', 'priority nama'],
        'epic' => ['epic (nama)', 'epic', 'epic nama'],
        'assignees' => ['assignees comma separated emails', 'assignee (nama dipisah koma)', 'assignee', 'assignees'],
        'start_date' => ['start date yyyy-mm-dd', 'start date', 'start_date'],
        'due_date' => ['due date yyyy-mm-dd', 'due date (y-m-d)', 'due date', 'due_date', 'due date (y-m-d)'],
    ];

    /**
     * Parse CSV or XLSX file and return rows as array of associative arrays.
     * Keys: title, epic, status, priority, description, due_date, assignees.
     *
     * @return array<int, array{title: string, epic: string, status: string, priority: string, description: string, due_date: string, assignees: string}>
     */
    public static function parse(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'xlsx' || $ext === 'xls') {
            return self::parseExcel($path);
        }

        return self::parseCsv($path);
    }

    private static function parseCsv(string $path): array
    {
        $reader = Reader::createFromPath($path);
        $reader->setHeaderOffset(0);
        $headers = $reader->getHeader();
        $keyMap = self::buildKeyMap($headers);
        $rows = [];
        foreach ($reader->getRecords() as $record) {
            $row = [];
            foreach ($keyMap as $key => $colIndex) {
                $headerLabel = $headers[$colIndex] ?? '';
                $row[$key] = isset($record[$headerLabel]) ? trim((string) $record[$headerLabel]) : '';
            }
            $rows[] = self::normalizeRow($row);
        }

        return $rows;
    }

    private static function parseExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $array = $sheet->toArray();
        if (empty($array)) {
            return [];
        }
        $headers = array_map('trim', array_map('strval', $array[0]));
        $keyMap = self::buildKeyMap($headers);
        $rows = [];
        for ($i = 1; $i < count($array); $i++) {
            $record = $array[$i];
            $row = [];
            foreach ($keyMap as $key => $colIndex) {
                $row[$key] = isset($record[$colIndex]) ? trim((string) $record[$colIndex]) : '';
            }
            $rows[] = self::normalizeRow($row);
        }

        return $rows;
    }

    /** @param array<int, string> $headers */
    private static function buildKeyMap(array $headers): array
    {
        $keyMap = [];
        foreach (self::COLUMN_MAP as $key => $labels) {
            foreach ($headers as $index => $header) {
                $normalized = strtolower(trim((string) $header));
                foreach ($labels as $label) {
                    if ($normalized === $label) {
                        $keyMap[$key] = $index;
                        break 2;
                    }
                }
            }
            $keyMap[$key] = $keyMap[$key] ?? 0;
        }

        return $keyMap;
    }

    private static function normalizeRow(array $row): array
    {
        return [
            'title' => $row['title'] ?? '',
            'description' => $row['description'] ?? '',
            'status' => $row['status'] ?? '',
            'priority' => $row['priority'] ?? '',
            'epic' => $row['epic'] ?? '',
            'assignees' => $row['assignees'] ?? '',
            'start_date' => $row['start_date'] ?? '',
            'due_date' => $row['due_date'] ?? '',
        ];
    }
}
