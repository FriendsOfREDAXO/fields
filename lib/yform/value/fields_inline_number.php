<?php

/**
 * YForm Fields Inline Number Value
 *
 * Ermöglicht das direkte Bearbeiten von Zahlen-Feldern in der Listenansicht.
 * Im Formular verhält es sich wie ein normales Number-Feld.
 *
 * @package fields
 */
class rex_yform_value_fields_inline_number extends rex_yform_value_abstract
{
    public function enterObject()
    {
        // 1. Formular-Ansicht
        if ($this->needsOutput() && $this->isViewable()) {
            if (!$this->isEditable()) {
                $this->params['form_output'][$this->getId()] = $this->parse('value.showvalue.tpl.php');
            } else {
                $attributes = [
                    'class' => 'form-control',
                    'name'  => $this->getFieldName(),
                    'id'    => $this->getFieldId(),
                    'type'  => 'number',
                    'step'  => $this->getElement('step') ?: 'any',
                    'min'   => $this->getElement('min'),
                    'max'   => $this->getElement('max'),
                ];

                $attributes = $this->getAttributeElements($attributes, ['placeholder', 'autocomplete', 'pattern', 'required', 'disabled', 'readonly']);

                $this->params['form_output'][$this->getId()] = $this->parse('value.text.tpl.php', [
                    'type' => 'number',
                    'prepend' => $this->getElement('prefix'),
                    'append' => $this->getElement('suffix'),
                ]);
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
            'name' => 'fields_inline_number',
            'values' => [
                'name' => ['type' => 'name',    'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_label')],
                'min' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_number_min')],
                'max' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_number_max')],
                'step' => ['type' => 'text',   'label' => 'Step (z.B. 0.01)', 'default' => 'any'],
                'prefix' => ['type' => 'text', 'label' => rex_i18n::msg('fields_inline_number_prefix'), 'notice' => rex_i18n::msg('fields_inline_number_prefix_notice')],
                'suffix' => ['type' => 'text', 'label' => rex_i18n::msg('fields_inline_number_suffix'), 'notice' => rex_i18n::msg('fields_inline_number_suffix_notice')],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_inline_number_description'),
            'db_type' => ['int', 'float', 'double', 'decimal', 'varchar'],
        ];
    }

    public static function getListValue($params)
    {
        $value = $params['subject'];
        $list = $params['list'];
        $field = $params['params']['field']; 

        $id = $list->getValue('id');
        
        $table = '';
        $listParams = $list->getParams();
        if (isset($listParams['table_name'])) {
            $table = $listParams['table_name'];
        } 
        if (empty($table)) {
           $table = rex_request('table_name', 'string');
        }

        $fieldName = $field['name'];
        $prefix = isset($field['prefix']) ? $field['prefix'] : '';
        $suffix = isset($field['suffix']) ? $field['suffix'] : '';
        
        // CSRF Token
        $token = rex_csrf_token::factory('fields_inline_edit')->getValue();
        
        // Display Logic
        $displayValue = rex_escape($value);
        if ($displayValue === '') {
             $displayValue = '&nbsp;<i class="rex-icon fa-pencil" style="opacity:0.3"></i>&nbsp;';
        } else {
             // Wrap value with prefix/suffix for display
             if ($prefix) {
                 $displayValue = '<span class="fields-inline-prefix">'.rex_escape($prefix).'</span> ' . $displayValue;
             }
             if ($suffix) {
                 $displayValue = $displayValue . ' <span class="fields-inline-suffix">'.rex_escape($suffix).'</span>';
             }
        }

        return sprintf(
            '<div class="fields-inline-edit" 
                  data-table="%s" 
                  data-field="%s" 
                  data-id="%s" 
                  data-type="number"
                  data-token="%s"
                  data-raw-value="%s"
                  data-prefix="%s"
                  data-suffix="%s"
                  title="%s">
                <div class="fields-inline-view">%s</div>
                <div class="fields-inline-input" style="display:none;"></div>
             </div>',
            rex_escape($table),
            rex_escape($fieldName),
            (int) $id,
            $token,
            rex_escape($value),
            rex_escape($prefix),
            rex_escape($suffix),
            rex_i18n::msg('edit'),
            $displayValue
        );
    }
}
