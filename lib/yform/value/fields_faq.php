<?php

/**
 * YForm Value: FAQ Repeater
 *
 * Wiederholbares Frage/Antwort-Feld mit Schema.org JSON-LD Ausgabe.
 * Speichert als JSON-Array.
 *
 * @package fields
 */
class rex_yform_value_fields_faq extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        $value = $this->getValue();

        // JSON validieren und bereinigen
        $entries = [];
        if (!empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $entries = array_values(array_filter($decoded, static function (array $entry): bool {
                    return !empty($entry['question']) && !empty($entry['answer']);
                }));
            }
        }

        $this->setValue(json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_faq.tpl.php',
                [
                    'entries' => $entries,
                ],
            );
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    /**
     * Schema.org FAQ JSON-LD generieren
     *
     * @param string $json JSON-String mit FAQ-Einträgen
     * @return string JSON-LD Script-Tag
     */
    public static function getSchemaJsonLd(string $json): string
    {
        $entries = json_decode($json, true);
        if (!is_array($entries) || count($entries) === 0) {
            return '';
        }

        $mainEntity = [];
        foreach ($entries as $entry) {
            if (empty($entry['question']) || empty($entry['answer'])) {
                continue;
            }
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $entry['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $entry['answer'],
                ],
            ];
        }

        if (count($mainEntity) === 0) {
            return '';
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    }

    public function getDescription(): string
    {
        return 'fields_faq|name|label|';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_faq',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_faq_description'),
            'db_type' => ['text', 'mediumtext'],
            'famous' => false,
        ];
    }
}
