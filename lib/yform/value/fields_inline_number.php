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
        if ('' == $this->getValue() && !$this->params['send']) {
            $this->setValue($this->getElement('default'));
        }

        if ('' === $this->getValue()) {
            $this->setValue(null);
        } else {
            $this->setValue($this->getValue());
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }

        if (!$this->needsOutput() || !$this->isViewable()) {
            return;
        }

        if (!$this->isEditable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                ['value.number-view.tpl.php', 'value.integer-view.tpl.php', 'value.view.tpl.php'],
                ['prepend' => $this->getElement('unit')],
            );
        } else {
            $type = 'text';
            if ('input:number' == $this->getElement('widget')) {
                $type = 'number';
            }
            $this->params['form_output'][$this->getId()] = $this->parse(
                ['value.number.tpl.php', 'value.integer.tpl.php', 'value.text.tpl.php'],
                ['prepend' => $this->getElement('unit'), 'type' => $type],
            );
        }
    }

    public function getDescription(): string
    {
        return 'fields_inline_number|name|label|precision|scale|defaultwert|[no_db]|[unit]|[notice]|[attributes]';
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_inline_number',
            'values' => [
                'name' => ['type' => 'name',    'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_label')],
                'precision' => ['type' => 'integer', 'label' => rex_i18n::msg('yform_values_number_precision'), 'default' => '10'],
                'scale' => ['type' => 'integer', 'label' => rex_i18n::msg('yform_values_number_scale'), 'default' => '2'],
                'default' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_number_default')],
                'no_db' => ['type' => 'no_db',   'label' => rex_i18n::msg('yform_values_defaults_table'),  'default' => 0],
                'widget' => ['type' => 'choice', 'label' => rex_i18n::msg('yform_values_defaults_widgets'), 'choices' => ['input:text' => 'input:text', 'input:number' => 'input:number'], 'default' => 'input:text'],
                'unit' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_unit')],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
                'attributes' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_attributes'), 'notice' => rex_i18n::msg('yform_values_defaults_attributes_notice')],
            ],
            'validates' => [
                ['type' => ['name' => 'precision', 'type' => 'integer', 'message' => rex_i18n::msg('yform_values_number_error_precision', '1', '65'), 'not_required' => false]],
                ['type' => ['name' => 'scale', 'type' => 'integer', 'message' => rex_i18n::msg('yform_values_number_error_scale', '0', '30'), 'not_required' => false]],
                ['compare' => ['name' => 'scale', 'name2' => 'precision', 'message' => rex_i18n::msg('yform_values_number_error_compare'), 'compare_type' => '>']],
                ['intfromto' => ['name' => 'precision', 'from' => '1', 'to' => '65', 'message' => rex_i18n::msg('yform_values_number_error_precision', '1', '65')]],
                ['intfromto' => ['name' => 'scale', 'from' => '0', 'to' => '30', 'message' => rex_i18n::msg('yform_values_number_error_scale', '0', '30')]],
            ],
            'description' => rex_i18n::msg('fields_inline_number_description'),
            'db_type' => ['DECIMAL({precision},{scale})'],
            'hooks' => [
                'preCreate' => static function (rex_yform_manager_field $field, $db_type) {
                    $db_type = str_replace('{precision}', (string) ($field->getElement('precision') ?? 6), $db_type);
                    $db_type = str_replace('{scale}', (string) ($field->getElement('scale') ?? 2), $db_type);
                    return $db_type;
                },
            ],
            'db_null' => true,
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
        // Unit from Definition (original 'unit' param)
        $unit = isset($field['unit']) ? $field['unit'] : '';

        // Formatting based on scale/precision if available
        $formattedValue = rex_escape($value);
        if ($value !== '' && $value !== null && isset($field['scale']) && is_numeric($field['scale'])) {
            $formattedValue = number_format((float)$value, (int)$field['scale'], ',', '.');
        }

        // CSRF Token
        $token = rex_csrf_token::factory('fields_inline_edit')->getValue();
        
        $displayValue = $formattedValue;
        if ((string)$value === '') {
             $displayValue = '&nbsp;<i class="rex-icon fa-pencil" style="opacity:0.3"></i>&nbsp;';
        } else {
             if ($unit) {
                 $displayValue = $displayValue . ' ' . '<span class="text-muted">'.rex_escape($unit).'</span>';
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
                  title="%s">
                <div class="fields-inline-view">%s</div>
                <div class="fields-inline-input" style="display:none;"></div>
             </div>',
            rex_escape($table),
            rex_escape($fieldName),
            (int) $id,
            $token,
            rex_escape($value),
            rex_i18n::msg('edit'),
            $displayValue
        );
    }
}
