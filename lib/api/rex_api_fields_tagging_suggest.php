<?php

namespace FriendsOfRedaxo\Fields;

use rex;
use rex_api_function;
use rex_request;
use rex_response;
use rex_sql;

/**
 * API: Tagging Vorschläge
 *
 * Liefert eindeutige Tag-Objekte {text, color} aus einer Quelltabelle/-spalte.
 * Versteht sowohl neues JSON-Format als auch Legacy-Kommalisten.
 */
class rex_api_fields_tagging_suggest extends rex_api_function
{
    protected $published = true;

    public function execute(): void
    {
        rex_response::cleanOutputBuffers();

        if (!rex::isBackend() || !rex::getUser()) {
            rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
            rex_response::sendJson(['success' => false, 'message' => 'Forbidden']);
            exit;
        }

        $table = trim((string) rex_request::request('table', 'string', ''));
        $field = trim((string) rex_request::request('field', 'string', ''));

        if ($table === '' || $field === '') {
            rex_response::setStatus(rex_response::HTTP_BAD_REQUEST);
            rex_response::sendJson(['success' => false, 'message' => 'table and field are required']);
            exit;
        }

        // Table / field existence check
        $columns = rex_sql::showColumns($table);
        if ($columns === []) {
            rex_response::sendJson(['success' => true, 'tags' => []]);
            exit;
        }
        $fieldExists = false;
        foreach ($columns as $col) {
            if (($col['name'] ?? '') === $field) {
                $fieldExists = true;
                break;
            }
        }
        if (!$fieldExists) {
            rex_response::sendJson(['success' => true, 'tags' => []]);
            exit;
        }

        $sql  = rex_sql::factory();
        $rows = $sql->getArray(
            'SELECT DISTINCT ' . $sql->escapeIdentifier($field) . ' AS v'
            . ' FROM ' . $sql->escapeIdentifier($table)
            . ' WHERE ' . $sql->escapeIdentifier($field) . ' IS NOT NULL'
            . ' AND ' . $sql->escapeIdentifier($field) . ' <> ""'
            . ' LIMIT 500',
        );

        // Collect: text → last used color
        $seen = [];
        foreach ($rows as $row) {
            $raw = trim((string) ($row['v'] ?? ''));
            if ($raw === '') {
                continue;
            }

            // Try JSON format first
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (!is_array($item) || !isset($item['text'])) {
                        continue;
                    }
                    $text  = trim((string) $item['text']);
                    $color = isset($item['color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', (string) $item['color'])
                        ? (string) $item['color']
                        : '#7f8c8d';
                    if ($text !== '') {
                        $seen[mb_strtolower($text)] = ['text' => $text, 'color' => $color];
                    }
                }
                continue;
            }

            // Legacy: comma-separated plain strings
            $parts = preg_split('/[,;]+/', $raw);
            if (!is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                $text = trim((string) $part);
                if ($text !== '') {
                    $seen[mb_strtolower($text)] = ['text' => $text, 'color' => '#7f8c8d'];
                }
            }
        }

        rex_response::sendJson([
            'success' => true,
            'tags'    => array_values($seen),
        ]);
        exit;
    }
}
