<?php

/**
 * YForm Fields Inline Value
 *
 * Ermöglicht das direkte Bearbeiten von Text/Textarea-Feldern in der Listenansicht.
 * Im Formular verhält es sich wie ein normales Text/Textarea-Feld.
 *
 * @package fields
 */
class rex_yform_value_fields_inline extends rex_yform_value_abstract
{
    public function enterObject()
    {
        // 1. Formular-Ansicht (Normales Verhalten)
        // Wir delegieren einfach an text oder textarea Logik, bzw. bauen es simpel nach.
        
        $type = $this->getElement('type'); // text, textarea
        if (!in_array($type, ['text', 'textarea'])) {
            $type = 'text';
        }

        if ($this->needsOutput() && $this->isViewable()) {
            
            // Standard Output für Formular (Add/Edit)
            if (!$this->isEditable()) {
                $this->params['form_output'][$this->getId()] = $this->parse('value.showvalue.tpl.php');
            } else {
                // Wir nutzen die Standard-Templates von YForm, wenn möglich, oder eigene simple.
                // Da wir "wrapper" Funktionalität wollen, müssen wir die properties setzen.
                
                $attributes = [
                    'class' => 'form-control',
                    'name'  => $this->getFieldName(),
                    'id'    => $this->getFieldId(),
                ];

                $attributes = $this->getAttributeElements($attributes, ['placeholder', 'autocomplete', 'pattern', 'required', 'disabled', 'readonly']);

                $this->params['form_output'][$this->getId()] = $this->parse('value.text.tpl.php', ['type' => $type]);
                
                // HINWEIS: value.text.tpl.php behandelt normalerweise input type="text/email/..."
                // Für textarea bräuchten wir value.textarea.tpl.php
                
                if ($type === 'textarea') {
                     $this->params['form_output'][$this->getId()] = $this->parse('value.textarea.tpl.php');
                } else {
                     // Type attribute für input
                     $this->params['form_output'][$this->getId()] = $this->parse('value.text.tpl.php', ['type' => 'text']);
                }
            }
        }

        // 2. Datenbank-Speicherung
        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_inline',
            'values' => [
                'name' => ['type' => 'name',    'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_label')],
                'type' => ['type' => 'choice', 'label' => 'Type', 'choices' => 'text,textarea', 'default' => 'text'],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => 'Inline Edit Field',
            'db_type' => ['text', 'mediumtext'],
        ];
    }

    /**
     * Angepasste Listenansicht für Inline-Editing
     * Muss static sein, da YForm dies für setColumnFormat erwartet.
     */
    public static function getListValue($params)
    {
        $value = (string) $params['subject']; // Ensure string
        $list = $params['list'];
        $field = $params['params']['field']; // Field definition

        $id = $list->getValue('id'); // Record ID
        
        // Try to get table name
        $table = '';
        // 1. Try from List params (YForm Manager usually sets this)
        $listParams = $list->getParams();
        if (isset($listParams['table_name'])) {
            $table = $listParams['table_name'];
        } 
        // 2. Try from Request as fallback
        if (empty($table)) {
           $table = rex_request('table_name', 'string');
        }

        $fieldName = $field['name'];
        // Use Type from field definition or default to text
        $type = 'text';
        if (isset($field['type_name'])) {
             // In field array, specific type attributes might be stored differently depending on YForm version
             // But we need the configured "type" (text/textarea) from our value definition?
             // Actually, 'type' in $field array is usually the YForm Type Name (e.g. 'fields_inline').
             // We need our internal setting 'type'.
             // $field is an array representation of the column row in yform_field table.
             $type = $field['type'] ?? 'text'; 
        }

        
        // CSRF Token für API Schutz
        $token = rex_csrf_token::factory('fields_inline_edit')->getValue();

        // HTML Output Wrapper
        
        $displayValue = rex_escape($value);
        if ($displayValue == '') {
            $displayValue = '&nbsp;<i class="rex-icon fa-pencil" style="opacity:0.3"></i>&nbsp;';
        } else {
            $displayValue = nl2br($displayValue);
        }

        return sprintf(
            '<div class="fields-inline-edit" 
                  data-table="%s" 
                  data-field="%s" 
                  data-id="%s" 
                  data-type="%s"
                  data-token="%s"
                  data-raw-value="%s"
                  title="%s">
                <div class="fields-inline-view">%s</div>
                <div class="fields-inline-input" style="display:none;"></div>
             </div>',
            rex_escape($table),
            rex_escape($fieldName),
            (int) $id,
            rex_escape($type),
            $token,
            rex_escape($value), // Store raw value
            rex_i18n::msg('edit'), // Tooltip
            $displayValue // Initial display value
        );
    }
}
