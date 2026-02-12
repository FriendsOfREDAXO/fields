<?php

/**
 * YForm Value: Accessible Table
 *
 * Ein barrierefreier Tabelleneditor mit Header-Support und Caption.
 *
 * @package fields
 */
class rex_yform_value_fields_table extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        if ($this->needsOutput() && $this->isViewable()) {
            if (!$this->isEditable()) {
                $this->params['form_output'][$this->getId()] = $this->parse(
                    'value.view.tpl.php',
                    ['title' => $this->getLabel(), 'value' => $this->getValue()],
                );
            } else {
                $this->params['form_output'][$this->getId()] = $this->parse(
                    'value.fields_table.tpl.php',
                    [],
                );
            }
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    public function getDescription(): string
    {
        return 'fields_table|name|label|notice';
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_table',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => 'Ein barrierefreier Tabelleneditor',
            'db_type' => ['mediumtext'],
            'famous' => false,
        ];
    }
}
