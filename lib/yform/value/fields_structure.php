<?php

/**
 * YForm Value: Structure (Grid/Flex Layout)
 *
 * Wraps subsequent fields in a CSS Grid/Flex container.
 *
 * @package fields
 */
class rex_yform_value_fields_structure extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        if (!$this->needsOutput()) {
            return;
        }

        $type = $this->getElement('type'); // start, end
        $layout = $this->getElement('layout'); // e.g. "1fr 1fr"
        $gap = $this->getElement('gap');
        $label = $this->getElement('label');

        // Defaults
        if (!$type) $type = 'start';
        
        $style = '';
        if ($type === 'start') {
            // Map common shorthands or keep raw
            // 1/3 2/3 is not valid grid-template-columns syntax, but 1fr 2fr is.
            // We can try to be smart or just expect CSS.
            // Let's pass it as custom property to JS or inline style.
            
            // Allow simplified "50-50" syntax?
            // User asked for "1/3 2/3". CSS Grid: "1fr 2fr".
            
            $style = 'display: grid; grid-template-columns: ' . rex_escape($this->normalizeLayout($layout)) . ';';
            if ($gap) {
                $style .= ' gap: ' . rex_escape($gap) . ';';
            }
        }
        
        // Marker Output
        $this->params['form_output'][$this->getId()] = sprintf(
            '<div class="fields-structure-marker" data-id="%s" data-type="%s" data-style="%s" style="display:none;"></div>',
            $this->getId(),
            $type,
            $style
        );
    }

    private function normalizeLayout($layout)
    {
        if (empty($layout)) return '1fr';
        
        // Convert "1/2 1/2" to "1fr 1fr"?
        // Convert "1/3 2/3" to "1fr 2fr"?
        // Simple heuristic: if contains slash, replace with fr logic?
        // Actually, "1fr 2fr" is standard. Let's provide that as default / hint.
        
        // Simple mapping for common terms
        $map = [
            '2spalten' => '1fr 1fr',
            '3spalten' => '1fr 1fr 1fr',
            '50/50' => '1fr 1fr',
            '30/70' => '30fr 70fr', // rough
            '1/3 2/3' => '1fr 2fr',
            '2/3 1/3' => '2fr 1fr',
            '1/4 3/4' => '1fr 3fr'
        ];
        
        if (isset($map[$layout])) return $map[$layout];
        
        return $layout;
    }

    public function getDescription(): string
    {
        return 'fields_structure|label|type(start/end)|layout(1fr 1fr)|gap(20px)';
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_structure',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'type' => [
                    'type' => 'choice',
                    'label' => 'Typ',
                    'choices' => [
                        'start' => 'Struktur Starten',
                        'end' => 'Struktur Beenden',
                    ],
                    'default' => 'start'
                ],
                'layout' => [
                    'type' => 'text', 
                    'label' => 'Layout (Grid Template)', 
                    'notice' => 'z.B. "1fr 1fr" (50/50), "1fr 2fr" (1/3 2/3), "repeat(3, 1fr)" (3 Spalten).',
                    'default' => '1fr 1fr'
                ],
                'gap' => [
                    'type' => 'text', 
                    'label' => 'Abstand (Gap)', 
                    'default' => '15px'
                ],
            ],
            'description' => 'Erstellt ein Grid/Flex-Layout für die eingeschlossenen Felder',
            'db_type' => ['none'],
            'is_searchable' => false,
            'is_hiddeninlist' => true,
        ];
    }
}
