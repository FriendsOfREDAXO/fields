<?php

namespace FriendsOfRedaxo\Fields;

use rex;
use rex_api_function;
use rex_request;
use rex_response;
use rex_sql;
use rex_string;

/**
 * API: Tagging Vorschläge
 *
 * Liefert Tag-Vorschläge aus einer Quelltabelle/-spalte als JSON.
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
        $query = trim((string) rex_request::request('q', 'string', ''));
        $limit = (int) rex_request::request('limit', 'int', 30);
        if ($limit < 1) {
            $limit = 30;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        if ($table === '' || $field === '') {
            rex_response::setStatus(rex_response::HTTP_BAD_REQUEST);
            rex_response::sendJson(['success' => false, 'message' => 'table and field are required']);
            exit;
        }

        $columns = rex_sql::showColumns($table);
        if ($columns === []) {
            rex_response::sendJson(['success' => true, 'tags' => []]);
            exit;
        }

        $fieldExists = false;
        foreach ($columns as $column) {
            if (($column['name'] ?? '') === $field) {
                $fieldExists = true;
                break;
            }
        }

        if (!$fieldExists) {
            rex_response::sendJson(['success' => true, 'tags' => []]);
            exit;
        }

        $sql = rex_sql::factory();
        $rows = $sql->getArray(
            'SELECT DISTINCT ' . $sql->escapeIdentifier($field) . ' AS tag_values FROM ' . $sql->escapeIdentifier($table) . ' WHERE ' . $sql->escapeIdentifier($field) . ' IS NOT NULL AND ' . $sql->escapeIdentifier($field) . ' <> "" LIMIT 500',
        );

        $tags = [];
        foreach ($rows as $row) {
            $raw = (string) ($row['tag_values'] ?? '');
            if ($raw === '') {
                continue;
            }

            $parts = preg_split('/[,;]+/', $raw);
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $tag = trim((string) $part);
                if ($tag === '') {
                    continue;
                }

                $normalized = rex_string::normalize($tag, '-', '_');
                if ($normalized === '') {
                    continue;
                }

                if ($query !== '' && strpos($normalized, rex_string::normalize($query, '-', '_')) === false) {
                    continue;
                }

                $tags[$normalized] = $normalized;
            }
        }

        $result = array_slice(array_values($tags), 0, $limit);

        rex_response::sendJson([
            'success' => true,
            'tags' => $result,
        ]);
        exit;
    }
}
