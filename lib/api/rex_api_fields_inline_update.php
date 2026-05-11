<?php

namespace FriendsOfRedaxo\Fields;

use rex;
use rex_api_function;
use rex_csrf_token;
use rex_request;
use rex_response;

/**
 * API: Inline Update
 *
 * Handles AJAX updates from the fields_inline list view editor.
 */
class rex_api_fields_inline_update extends rex_api_function
{
    protected $published = false; // Backend-only: requires logged-in REDAXO user

    public function execute()
    {
        // 1. CSRF Protection
           if (!rex_csrf_token::factory('fields_inline_edit')->isValid()) {
             rex_response::cleanOutputBuffers();
             rex_response::sendJson(['success' => false, 'message' => 'CSRF Token invalid']);
             exit;
        }

        // 2. Permission Check
        if (!rex::getUser()) {
             header('HTTP/1.0 403 Forbidden');
             echo json_encode(['success' => false, 'message' => 'Not logged in']);
             exit;
        }

        $table = rex_request('table', 'string');
        $field = rex_request('field', 'string');
        $id = rex_request('id', 'int');
        $value = rex_request('value', 'string');

        // Check if User has permission to edit this table content
        // YForm Table Permissions format: "yform[tablename]" (full table name incl. prefix)
        if (!rex::getUser()->isAdmin() && !rex::getUser()->hasPerm('yform[' . $table . ']')) {
            rex_response::cleanOutputBuffers();
            rex_response::sendJson(['success' => false, 'message' => 'Permission denied for table ' . $table]);
            exit;
        }

        // 3. Update Data
        $dataSet = \rex_yform_manager_dataset::get($id, $table);
        
        if (!$dataSet) {
             rex_response::cleanOutputBuffers();
             rex_response::sendJson(['success' => false, 'message' => 'Dataset not found']);
             exit;
        }

        $yformTable = \rex_yform_manager_table::get($table);
        $fieldDef = $yformTable?->getValueField($field);
        if (!$fieldDef) {
            rex_response::cleanOutputBuffers();
            rex_response::sendJson(['success' => false, 'message' => 'Felddefinition nicht gefunden']);
            exit;
        }

        $fieldType = (string) $fieldDef->getTypeName();
        $currentValue = (string) $dataSet->getValue($field);

        if ('fields_inline_select' === $fieldType) {
            if (!class_exists('rex_yform_value_fields_inline_select')) {
                $valueClassFile = \rex_path::addon('fields', 'lib/yform/value/fields_inline_select.php');
                if (is_file($valueClassFile)) {
                    require_once $valueClassFile;
                }
            }

            if (\rex_yform_value_fields_inline_select::isValueLocked($currentValue, (string) $fieldDef->getElement('lock_values'))) {
                rex_response::cleanOutputBuffers();
                rex_response::sendJson(['success' => false, 'message' => \rex_i18n::msg('fields_inline_select_locked')]);
                exit;
            }

            $allowedChoices = \rex_yform_value_fields_inline_select::resolveChoices(
                (string) $fieldDef->getElement('choices'),
                (string) $fieldDef->getElement('query'),
            );

            if ([] !== $allowedChoices && !array_key_exists($value, $allowedChoices)) {
                rex_response::cleanOutputBuffers();
                rex_response::sendJson(['success' => false, 'message' => \rex_i18n::msg('fields_inline_select_invalid_value')]);
                exit;
            }
        }

        $dataSet->setValue($field, $value);
        
        if ($dataSet->save()) {
             $newValue = $dataSet->getValue($field);
             $formattedValue = $newValue;

             // Try to format if it's a number field
             if ('fields_inline_number' === $fieldType || 'number' === $fieldType) {
                 $scale = $fieldDef->getElement('scale');
                 if (is_numeric($scale)) {
                     $formattedValue = number_format((float)$newValue, (int)$scale, ',', '.');
                 }
             }

             if ('fields_inline_select' === $fieldType) {
                 $resolvedChoices = \rex_yform_value_fields_inline_select::resolveChoices(
                     (string) $fieldDef->getElement('choices'),
                     (string) $fieldDef->getElement('query'),
                 );
                 $formattedValue = $resolvedChoices[(string) $newValue] ?? (string) $newValue;
             }

             rex_response::cleanOutputBuffers();
             rex_response::sendJson([
                 'success' => true, 
                 'id' => $id, 
                 'value' => $newValue,
                 'formatted' => $formattedValue
             ]);
             exit;
        } else {
             rex_response::cleanOutputBuffers();
             rex_response::sendJson(['success' => false, 'message' => implode("\n", $dataSet->getMessages())]);
             exit;
        }
    }
}
