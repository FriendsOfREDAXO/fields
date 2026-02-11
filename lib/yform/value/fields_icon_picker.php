<?php

/**
 * YForm Value: Icon Picker
 *
 * Icon-Auswahl aus Font Awesome und/oder UIkit Icon-Sets.
 * Speichert die Icon-Klasse als String (z.B. "fa fa-heart" oder "uk-icon-heart").
 *
 * @package fields
 */
class rex_yform_value_fields_icon_picker extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        $value = $this->getValue();

        $this->setValue($value);

        $iconSets = rex_addon::get('fields')->getConfig('icon_sets', 'fontawesome,uikit');
        $enabledSets = array_map('trim', explode(',', $iconSets));

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_icon_picker.tpl.php',
                [
                    'enabledSets' => $enabledSets,
                ],
            );
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    public function getDescription(): string
    {
        return 'fields_icon_picker|name|label|';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_icon_picker',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_icon_picker_description'),
            'db_type' => ['varchar(191)'],
            'famous' => false,
        ];
    }
}
