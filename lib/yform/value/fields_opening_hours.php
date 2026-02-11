<?php

/**
 * YForm Value: Opening Hours
 *
 * Öffnungszeiten-Widget mit regulären und Sonderöffnungszeiten.
 * Speichert als JSON, kompatibel mit OpeningHoursHelper.
 *
 * @package fields
 */
class rex_yform_value_fields_opening_hours extends rex_yform_value_abstract
{
    private const WEEKDAYS = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
    ];

    public function enterObject(): void
    {
        $value = $this->getValue();

        // JSON validieren und Standardstruktur sicherstellen
        $data = $this->normalizeData($value);
        $this->setValue(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_opening_hours.tpl.php',
                [
                    'data' => $data,
                    'weekdays' => self::WEEKDAYS,
                ],
            );
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    /**
     * Daten normalisieren und Standardwerte setzen
     *
     * @return array{regular: array, special: array, note: string}
     */
    private function normalizeData(?string $json): array
    {
        $default = [
            'regular' => [],
            'special' => [],
            'note' => '',
        ];

        if (empty($json)) {
            foreach (self::WEEKDAYS as $day) {
                $default['regular'][$day] = ['status' => 'closed', 'times' => []];
            }
            return $default;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            foreach (self::WEEKDAYS as $day) {
                $default['regular'][$day] = ['status' => 'closed', 'times' => []];
            }
            return $default;
        }

        // Reguläre Zeiten sicherstellen
        foreach (self::WEEKDAYS as $day) {
            if (!isset($decoded['regular'][$day])) {
                $decoded['regular'][$day] = ['status' => 'closed', 'times' => []];
            }
        }

        $decoded['special'] = $decoded['special'] ?? [];
        $decoded['note'] = $decoded['note'] ?? '';

        return $decoded;
    }

    public function getDescription(): string
    {
        return 'fields_opening_hours|name|label|';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_opening_hours',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_opening_hours_description'),
            'db_type' => ['text'],
            'famous' => false,
        ];
    }
}
