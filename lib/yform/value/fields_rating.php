<?php

/**
 * YForm Value: Star Rating
 *
 * Einfaches Bewertungssystem (Sterne)
 *
 * @package fields
 */
class rex_yform_value_fields_rating extends rex_yform_value_abstract
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
                    'value.fields_rating.tpl.php',
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
        return 'fields_rating|name|label|default|[max_stars]';
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_rating',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'default' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_default')],
                'max_stars' => ['type' => 'integer', 'label' => 'Anzahl Sterne (Default 5)', 'default' => 5],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => 'Ein grafisches Bewertungsfeld (Star Rating)',
            'db_type' => ['int'],
            'famous' => false,
        ];
    }
}
