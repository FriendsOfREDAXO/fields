<?php

use FriendsOfRedaxo\Fields\FieldsTagging;

/**
 * YForm Value: Tagging
 *
 * Speichert Tags als JSON-Array mit Text und Farbe.
 * Format: [{"text":"php","color":"#3498db"}, ...]
 *
 * @package fields
 */
class rex_yform_value_fields_tagging extends rex_yform_value_abstract
{
    /** @var list<string> – delegiert an FieldsTagging::DEFAULT_COLORS */
    public const DEFAULT_COLORS = FieldsTagging::DEFAULT_COLORS;

    public function enterObject(): void
    {
        // YForm sets $this->value from POST automatically before calling enterObject()
        $value = (string) $this->getValue();

        // Normalize: parse submitted JSON, re-encode clean
        $tags = $this->decodeTagJson($value);
        $maxTags = max(0, (int) $this->getElement('max_tags'));
        if ($maxTags > 0 && count($tags) > $maxTags) {
            $tags = array_slice($tags, 0, $maxTags);
        }

        $value = $tags !== [] ? (string) json_encode($tags, JSON_UNESCAPED_UNICODE) : '';
        $this->setValue($value);

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_tagging.tpl.php',
                [
                    'value'        => $value,
                    'tags'         => $tags,
                    'source_table' => (string) $this->getElement('source_table'),
                    'source_field' => (string) $this->getElement('source_field'),
                    'max_tags'     => $maxTags,
                    'colors'       => self::DEFAULT_COLORS,
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
            'type'  => 'value',
            'name'  => 'fields_tagging',
            'values' => [
                'name'         => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'        => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'source_table' => ['type' => 'text', 'label' => rex_i18n::msg('fields_tagging_source_table')],
                'source_field' => ['type' => 'text', 'label' => rex_i18n::msg('fields_tagging_source_field')],
                'max_tags'     => ['type' => 'text', 'label' => rex_i18n::msg('fields_tagging_max_tags')],
                'notice'       => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_tagging_description'),
            'db_type'     => ['text'],
            'famous'      => false,
        ];
    }

    public static function getListValue(array $params): string
    {
        $raw = trim((string) ($params['value'] ?? ''));
        if ($raw === '') {
            return '-';
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || $decoded === []) {
            return '-';
        }

        $output = [];
        foreach ($decoded as $item) {
            if (!is_array($item) || !isset($item['text'])) {
                continue;
            }
            $color = isset($item['color']) ? rex_escape($item['color']) : '#7f8c8d';
            $output[] = sprintf(
                '<span style="display:inline-block;background:%s;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;margin:1px 2px;">%s</span>',
                $color,
                rex_escape((string) $item['text']),
            );
        }

        return $output !== [] ? implode('', $output) : '-';
    }

    public static function getSearchField($params): void
    {
        /** @var rex_yform_manager_field $field */
        $field = $params['field'];

        $tags = self::getSearchTags($field);
        $choices = [
            '(empty)' => '(empty)',
            '!(empty)' => rex_i18n::msg('fields_search_not_empty'),
        ];

        $colorsByValue = [];

        foreach ($tags as $tag) {
            $text = (string) ($tag['text'] ?? '');
            if ('' === $text) {
                continue;
            }

            $color = (string) ($tag['color'] ?? '#7f8c8d');
            $choices[$text] = $text;
            $colorsByValue[$text] = $color;
        }

        $params['searchForm']->setValueField('choice', [
            'name' => $field->getName(),
            'label' => $field->getLabel(),
            'choices' => $choices,
            'multiple' => 1,
            'expanded' => 1,
            'choice_attributes' => static function (string $value) use ($colorsByValue): array {
                if (!isset($colorsByValue[$value])) {
                    return [];
                }

                $color = $colorsByValue[$value];

                return [
                    'style' => 'accent-color:' . $color . ';',
                    'data-tag-color' => $color,
                ];
            },
        ]);
    }

    public static function getSearchFilter($params)
    {
        $value = $params['value'];

        /** @var rex_yform_manager_query $query */
        $query = $params['query'];
        $field = $query->getTableAlias() . '.' . $params['field']->getName();

        $self = new self();
        $values = $self->getArrayFromString($value);

        foreach ($values as $searchValue) {
            $searchValue = trim((string) $searchValue);
            if ('' === $searchValue) {
                continue;
            }

            switch ($searchValue) {
                case '(empty)':
                    $query->whereRaw(
                        $field . ' IS NULL OR ' . $field . ' = :fields_tagging_empty OR ' . $field . ' = :fields_tagging_empty_json',
                        [
                            'fields_tagging_empty' => '',
                            'fields_tagging_empty_json' => '[]',
                        ],
                    );
                    break;
                case '!(empty)':
                    $query->whereRaw(
                        $field . ' IS NOT NULL AND ' . $field . ' <> :fields_tagging_not_empty AND ' . $field . ' <> :fields_tagging_not_empty_json',
                        [
                            'fields_tagging_not_empty' => '',
                            'fields_tagging_not_empty_json' => '[]',
                        ],
                    );
                    break;
                default:
                    $needle = '"text":"' . str_replace('"', '\\"', $searchValue) . '"';
                    $needle = str_replace(['%', '_'], ['\\%', '\\_'], $needle);
                    $query->where($field, '%' . $needle . '%', 'LIKE');
                    break;
            }
        }

        return $query;
    }

    /**
     * @return list<array{text:string,color:string}>
     */
    private static function getSearchTags(rex_yform_manager_field $field): array
    {
        $sourceTable = trim((string) $field->getElement('source_table'));
        $sourceField = trim((string) $field->getElement('source_field'));

        if ('' === $sourceField) {
            $sourceField = (string) $field->getName();
        }

        if ('' === $sourceTable) {
            $sourceTable = trim((string) $field->getElement('table_name'));
        }

        if ('' === $sourceTable || '' === $sourceField) {
            return [];
        }

        $prefix = rex::getTablePrefix();
        if (str_starts_with($sourceTable, $prefix)) {
            $sourceTable = substr($sourceTable, strlen($prefix));
        }

        if ('' === $sourceTable) {
            return [];
        }

        return FieldsTagging::collectFromTable($sourceTable, $sourceField);
    }

    /**
     * @return list<array{text:string,color:string}>
     */
    public function decodeTagJson(string $raw): array
    {
        return FieldsTagging::decode($raw);
    }
}
