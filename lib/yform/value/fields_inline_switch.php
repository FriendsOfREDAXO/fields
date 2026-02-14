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
        if ($this->needsOutput() && $this->isViewable()) {
            if (!$this->isEditable()) {
                $this->params['form_output'][$this->getId()] = $this->parse('value.showvalue.tpl.php');
            } else {
                $value = (int) $this->getValue();
                $name = $this->getFieldName();
                $label = $this->getLabel();
                $id = $this->getFieldId();
                
                $activeClass = ($value == 1) ? 'fields-switch-active' : '';

                // We replicate the YForm Bootstrap structure manually or use a custom template mechanism.
                // To keep it simple and self-contained, we build the HTML here using the standard YForm layout classes.
                
                $html = '
                <div class="checkbox" id="'.$id.'">
                    <label>
                        <input type="hidden" name="'.$name.'" value="'.$value.'">
                        <div class="fields-inline-switch fields-form-switch '.$activeClass.'" 
                             data-value="'.$value.'"
                             onclick="var v = this.getAttribute(\'data-value\'); var n = (v==1?0:1); this.setAttribute(\'data-value\', n); this.classList.toggle(\'fields-switch-active\'); this.parentNode.querySelector(\'input\').value = n;">
                            <div class="fields-switch-slider"></div>
                        </div>
                        '.$label.'
                    </label>
                </div>';

                // Notice handling
                if ($this->getElement('notice') != '') {
                    $html .= '<p class="help-block">' . rex_i18n::translate($this->getElement('notice'), false) . '</p>';
                }

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
