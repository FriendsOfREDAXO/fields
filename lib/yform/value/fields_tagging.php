<?php

/**
 * YForm Value: Tagging
 *
 * Speichert Tags als normalisierte, kommaseparierte Liste.
 * Vorschläge können optional per API aus einer Quelltabelle geladen werden.
 *
 * @package fields
 */
class rex_yform_value_fields_tagging extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        $value = (string) $this->getValue();

        if ($this->params['send']) {
            $value = (string) rex_request($this->getFieldName(), 'string', '');
        }

        $value = $this->normalizeTagString($value);
        $this->setValue($value);

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_tagging.tpl.php',
                [
                    'value' => $value,
                    'tags' => $this->toTagArray($value),
                    'source_table' => (string) $this->getElement('source_table'),
                    'source_field' => (string) $this->getElement('source_field'),
                    'max_tags' => max(0, (int) $this->getElement('max_tags')),
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
        return 'fields_tagging|name|label|source_table|source_field|max_tags|notice';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_tagging',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'source_table' => ['type' => 'text', 'label' => rex_i18n::msg('fields_tagging_source_table')],
                'source_field' => ['type' => 'text', 'label' => rex_i18n::msg('fields_tagging_source_field')],
                'max_tags' => ['type' => 'text', 'label' => rex_i18n::msg('fields_tagging_max_tags')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_tagging_description'),
            'db_type' => ['text'],
            'famous' => false,
        ];
    }

    public static function getListValue(array $params): string
    {
        $value = trim((string) ($params['value'] ?? ''));
        if ($value === '') {
            return '-';
        }

        $tags = array_filter(array_map('trim', explode(',', $value)), static fn (string $tag): bool => $tag !== '');
        if ($tags === []) {
            return '-';
        }

        $output = [];
        foreach ($tags as $tag) {
            $output[] = '<span class="label label-default" style="margin-right:4px;">' . rex_escape($tag) . '</span>';
        }

        return implode('', $output);
    }

    private function normalizeTagString(string $rawValue): string
    {
        $parts = preg_split('/[,;]+/', $rawValue);
        if (!is_array($parts)) {
            return '';
        }

        $tags = [];
        foreach ($parts as $part) {
            $tag = trim((string) $part);
            if ($tag === '') {
                continue;
            }

            $normalized = rex_string::normalize($tag, '-', '_');
            if ($normalized === '') {
                continue;
            }

            $tags[$normalized] = $normalized;
        }

        return implode(',', array_values($tags));
    }

    /**
     * @return array<int, string>
     */
    private function toTagArray(string $value): array
    {
        $tags = array_filter(array_map('trim', explode(',', $value)), static fn (string $tag): bool => $tag !== '');

        return array_values(array_unique($tags));
    }
}
