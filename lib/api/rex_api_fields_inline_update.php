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
    protected $published = true; // Publicly accessible via index.php?rex-api-call=...

    public function execute()
    {
        // 1. CSRF Protection
        $token = rex_request('_csrf_token', 'string', '');
        if (!rex_csrf_token::factory('fields_inline_edit')->isValid($token)) {
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
        // YForm Table Permissions format: "yform[table_name]" or "admin[]"
        if (!rex::getUser()->isAdmin() && !rex::getUser()->hasPerm('yform[table:' . $table . ']')) {
             header('HTTP/1.0 403 Forbidden');
             echo json_encode(['success' => false, 'message' => 'Permission denied for table ' . $table]);
             exit;
        }

        // 3. Update Data
        $dataSet = \rex_yform_manager_dataset::get($id, $table);
        
        if (!$dataSet) {
             rex_response::cleanOutputBuffers();
             rex_response::sendJson(['success' => false, 'message' => 'Dataset not found']);
             exit;
        }

        $dataSet->setValue($field, $value);
        
        if ($dataSet->save()) {
             $newValue = $dataSet->getValue($field);
             $formattedValue = $newValue;

             // Try to format if it's a number field
             $yformTable = \rex_yform_manager_table::get($table);
             if ($yformTable) {
                 $fieldDef = $yformTable->getValueField($field);
                 if ($fieldDef && ($fieldDef->getTypeName() == 'fields_inline_number' || $fieldDef->getTypeName() == 'number')) {
                     // Get Scale
                     $scale = $fieldDef->getElement('scale');
                     if (is_numeric($scale)) {
                         $formattedValue = number_format((float)$newValue, (int)$scale, ',', '.');
                     }
                 }
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
