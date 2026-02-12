<?php

/**
 * YForm Value: Conditional Field Group
 *
 * Blendet nachfolgende Felder abhängig von einem Quellfeld ein/aus.
 * Rein clientseitig via JavaScript.
 *
 * @package fields
 */
class rex_yform_value_fields_conditional extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        // Dieses Feld speichert keinen eigenen Wert.
        // Es steuert nur die Sichtbarkeit anderer Felder via JS.

        if ($this->needsOutput() && $this->isViewable()) {
            $sourceField = $this->getElement('source_field');
            $operator = $this->getElement('operator') ?: '=';
            $compareValue = $this->getElement('compare_value') ?: '';
            $targetFields = $this->getElement('target_fields') ?: '';
            $action = $this->getElement('action') ?: 'show';

            // Target Fields als Array
            $targets = array_map('trim', explode(',', $targetFields));

            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_conditional.tpl.php',
                [
                    'sourceField' => $sourceField,
                    'operator' => $operator,
                    'compareValue' => $compareValue,
                    'targetFields' => $targets,
                    'action' => $action,
                ],
            );
        }
    }

    public function getDescription(): string
    {
        return 'fields_conditional|name|source_field|operator|compare_value|target_fields|action';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_conditional',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'source_field' => ['type' => 'text', 'label' => rex_i18n::msg('fields_conditional_source')],
                'operator' => [
                    'type' => 'choice',
                    'label' => rex_i18n::msg('fields_conditional_operator'),
                    'choices' => '==gleich (=),!=ungleich (!=),>größer (>),<kleiner (<),contains=enthält (contains),empty=leer (empty),!empty=nicht leer (!empty)',
                    'default' => '=',
                ],
                'compare_value' => ['type' => 'text', 'label' => rex_i18n::msg('fields_conditional_value')],
                'target_fields' => ['type' => 'text', 'label' => 'Zielfelder (kommagetrennt) oder CSS-Selector (.class, #id)', 'notice' => 'Feldnamen oder CSS-Selektoren (z.B. .my-class) angeben.'],
                'action' => [
                    'type' => 'choice',
                    'label' => rex_i18n::msg('fields_conditional_action'),
                    'choices' => 'show,hide',
                    'default' => 'show',
                ],
            ],
            'description' => rex_i18n::msg('fields_conditional_description'),
            'db_type' => ['none'],
            'famous' => false,
        ];
    }
}
