<?php

/**
 * YForm Fields Inline Switch Value
 *
 * Ermöglicht das direkte Bearbeiten von Checkbox-Feldern in der Listenansicht (Toggle).
 *
 * @package fields
 */
class rex_yform_value_fields_inline_switch extends rex_yform_value_abstract
{
    public function enterObject()
    {
        // Wert wie bei der YForm-Checkbox: 0/1, bei neuen Datensätzen der Standardwert
        if (1 == $this->params['send'] && 1 != $this->getValue()) {
            $this->setValue(0);
        } elseif ('' != $this->getValue()) {
            $this->setValue((1 != $this->getValue()) ? '0' : '1');
        } else {
            $this->setValue((string) (int) $this->getElement('default'));
        }

        if ($this->needsOutput() && $this->isViewable()) {
            if (!$this->isEditable()) {
                $this->params['form_output'][$this->getId()] = $this->parse('value.showvalue.tpl.php');
            } else {
                $value = (int) $this->getValue();

                // Echte Checkbox mit role="switch" (Tastatur, Screenreader, change-Event – z. B. für fields_conditional),
                // optisch als Schalter; das hidden-Feld davor liefert 0, wenn der Schalter aus ist.
                $attributes = [
                    'type' => 'checkbox',
                    'class' => 'fields-switch-input',
                    'role' => 'switch',
                    'id' => $this->getFieldId(),
                    'name' => $this->getFieldName(),
                    'value' => 1,
                ];
                if (1 === $value) {
                    $attributes['checked'] = 'checked';
                }
                $attributes = $this->getAttributeElements($attributes, ['required', 'disabled', 'autofocus']);

                $notices = [];
                if ('' != $this->getElement('notice')) {
                    $notices[] = rex_i18n::translate($this->getElement('notice'), false);
                }
                if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
                    $notices[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()], false) . '</span>';
                }

                $html = '<div class="' . trim('checkbox fields-form-switch-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass()) . '" id="' . $this->getHTMLId() . '">'
                    . '<label>'
                    . '<input type="hidden" name="' . $this->getFieldName() . '" value="0">'
                    . '<input ' . implode(' ', $attributes) . '>'
                    . '<span class="fields-inline-switch fields-form-switch" aria-hidden="true"><span class="fields-switch-slider"></span></span>'
                    . $this->getLabel()
                    . '</label>'
                    . ($notices ? '<p class="help-block small">' . implode('<br />', $notices) . '</p>' : '')
                    . '</div>';

                $this->params['form_output'][$this->getId()] = $html;
            }
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }
    
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_inline_switch',
            'values' => [
                'name' => ['type' => 'name',    'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_label')],
                'options' => ['type' => 'text',    'label' => 'Werte (0,1)'], // Hardcoded label to avoid missing translation key
                'default' => ['type' => 'choice', 'label' => rex_i18n::msg('yform_values_checkbox_default'), 'choices' => '0,1', 'default' => '0'],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => 'Inline Edit Switch (Checkbox)',
            'db_type' => ['tinyint(1)', 'int'],
        ];
    }

    public static function getListValue($params)
    {
        $value = (int) $params['subject'];
        // Ohne Liste (z. B. Datensatz-Historie von YForm: getListValue() nur mit Wert) nur lesbar anzeigen –
        // die YForm-Liste ist rex_yform_list (keine Unterklasse von rex_list), daher nur auf ein Objekt mit getValue() prüfen
        if (!isset($params['list']) || !is_object($params['list']) || !method_exists($params['list'], 'getValue')) {
            return 1 === (int) ($params['subject'] ?? $params['value'] ?? 0) ? rex_i18n::msg('yes') : rex_i18n::msg('no');
        }
        $list = $params['list'];
        $field = $params['params']['field']; 

        $id = $list->getValue('id');
        
        // Try to get table name
        $table = '';
        $listParams = $list->getParams();
        if (isset($listParams['table_name'])) {
            $table = $listParams['table_name'];
        } 
        if (empty($table)) {
           $table = rex_request('table_name', 'string');
        }

        $fieldName = $field['name'];
        
        // CSRF Token
        $token = rex_csrf_token::factory('fields_inline_edit')->getValue();
        
        $checked = ($value == 1) ? 'checked' : '';
        $activeClass = ($value == 1) ? 'fields-switch-active' : '';

        // Switch HTML Structure 
        // Inline styles only for dimensions/layout to prevent layout shift before CSS loads.
        // Colors and Positions are handled by CSS via classes to ensure Dark Mode and JS State updates work.
        return sprintf(
            '<div class="fields-inline-switch %s" 
                  data-table="%s" 
                  data-field="%s" 
                  data-id="%s" 
                  data-token="%s"
                  data-value="%s"
                  title="%s">
                <div class="fields-switch-slider"></div>
             </div>',
            $activeClass,
            rex_escape($table),
            rex_escape($fieldName),
            (int) $id,
            $token,
            $value,
            rex_i18n::msg('edit')
        );
    }
}
