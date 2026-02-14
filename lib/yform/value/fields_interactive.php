<?php

/**
 * YForm Interactive Value (Tabs, Accordions)
 *
 * Ermöglicht die Gruppierung von Feldern in Tabs, Accordions oder einfache Container
 * via JS-DOM-Manipulation.
 */
class rex_yform_value_fields_interactive extends rex_yform_value_abstract
{

    public function enterObject()
    {
        if (!$this->needsOutput()) {
            return;
        }

        $type = $this->getElement('type'); // tab, accordion, fieldset, end_group
        $label = $this->getElement('label');
        $group = $this->getElement('group_id');
        $active = $this->getElement('active');
        $class = $this->getElement('class');

        // Allow partial definition (e.g. just 'fieldset|Label')
        if (!$type) {
            $type = 'fieldset';
        }
        
        // Marker Output
        // We use a DIV with data attributes. JS will pick this up.
        // ID is crucial for uniquely identifying this marker in list.
        $this->params['form_output'][$this->getId()] = sprintf(
            '<div class="fields-interactive-marker" id="%s" data-type="%s" data-label="%s" data-group="%s" data-active="%s" data-class="%s" style="display:none;"></div>',
            $this->getId(),
            $type,
            rex_escape($label),
            rex_escape($group),
            $active,
            rex_escape($class)
        );
    }

    public function getDescription(): string
    {
        return 'fields_interactive|label|type(tab/accordion/fieldset/end_group)|[group_id]|active(0/1)|css_class';
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_interactive',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'type' => [
                    'type' => 'choice',
                    'label' => 'Typ',
                    'choices' => [
                        'tab' => 'Tab (in einer Gruppe)',
                        'accordion' => 'Akkordeon (in einer Gruppe)',
                        'fieldset' => 'Fieldset (Einzelne Gruppe)',
                        'end_group' => 'Gruppe beenden (Ende der Tabs/Inhalte)',
                    ],
                    'default' => 'tab'
                ],
                'group_id' => [
                    'type' => 'text', 
                    'label' => 'Gruppen-ID', 
                    'notice' => 'Pflicht für Tabs/Accordions. Identische ID gruppiert Elemente.'
                ],
                'active' => [
                    'type' => 'checkbox', 
                    'label' => 'Aktiv / Geöffnet beim Start', 
                    'default' => 0
                ],
                'class' => [
                    'type' => 'text',
                    'label' => 'CSS Klasse',
                    'notice' => 'z.B. benutzerdefinierte Klasse für Container'
                ],
            ],
            'description' => 'Gruppiert Felder in Tabs, Akkordeons oder Fieldsets (JS-basiert)',
            'db_type' => ['none'],
            'is_searchable' => false,
            'is_hiddeninlist' => true,
        ];
    }
}
