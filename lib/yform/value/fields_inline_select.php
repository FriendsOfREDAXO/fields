<?php

/**
 * YForm Fields Inline Select Value
 *
 * Ermöglicht das direkte Bearbeiten von Auswahlfeldern in der Listenansicht.
 * Im Formular verhält es sich wie ein normales Select-Feld.
 *
 * Choices-Format: key=Label (eine pro Zeile), z.B.:
 *   neu=Neu
 *   in_bearbeitung=In Bearbeitung
 *   abgelehnt=Abgelehnt
 *
 * @package fields
 */
class rex_yform_value_fields_inline_select extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        if ('' === $this->getValue() && !$this->params['send']) {
            $this->setValue($this->getElement('default'));
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }

        if (!$this->needsOutput() || !$this->isViewable()) {
            return;
        }

        $choices = self::resolveChoices(
            (string) $this->getElement('choices'),
            (string) $this->getElement('query'),
        );
        $colors = self::parseColors((string) $this->getElement('colors'));
        $value = (string) $this->getValue();
        $isLocked = self::isValueLocked($value, (string) $this->getElement('lock_values'));

        if (!$this->isEditable()) {
            $label = $choices[$value] ?? rex_escape($value);
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.showvalue.tpl.php',
                ['value' => $label],
            );
            return;
        }

        $this->params['form_output'][$this->getId()] = $this->parse(
            'value.fields_inline_select.tpl.php',
            [
                'options' => $choices,
                'colors' => $colors,
                'is_locked' => $isLocked,
            ],
        );
    }

    public function getDescription(): string
    {
        return 'fields_inline_select|name|label|choices|[query]|[colors]|[lock_values]|default|[no_db]|[notice]';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type'        => 'value',
            'name'        => 'fields_inline_select',
            'values'      => [
                'name'    => ['type' => 'name',     'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'   => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_defaults_label')],
                'choices' => ['type' => 'textarea', 'label' => rex_i18n::msg('fields_inline_select_choices', 'key=Label'), 'notice' => 'key=Label (eine pro Zeile)'],
                'query'   => ['type' => 'textarea', 'label' => rex_i18n::msg('fields_inline_select_query'), 'notice' => rex_i18n::msg('fields_inline_select_query_notice')],
                'colors'  => ['type' => 'textarea', 'label' => rex_i18n::msg('fields_inline_select_colors'), 'notice' => rex_i18n::msg('fields_inline_select_colors_notice')],
                'lock_values' => ['type' => 'text', 'label' => rex_i18n::msg('fields_inline_select_lock_values'), 'notice' => rex_i18n::msg('fields_inline_select_lock_values_notice')],
                'default' => ['type' => 'text',     'label' => rex_i18n::msg('fields_inline_select_default_value')],
                'no_db'   => ['type' => 'no_db',    'label' => rex_i18n::msg('yform_values_defaults_table'), 'default' => 0],
                'notice'  => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_inline_select_description'),
            'db_type'     => ['varchar(191)', 'text'],
            'db_null'     => true,
        ];
    }

    /**
     * Rendered für die Listenansicht: zeigt ein sofort speicherndes <select>.
     *
     * @param array<string, mixed> $params
     */
    public static function getListValue(array $params): string
    {
        $value = (string) ($params['subject'] ?? '');
        /** @var rex_list $list */
        $list = $params['list'];
        $field = $params['params']['field'];

        $id = (int) $list->getValue('id');

        $table = '';
        $listParams = $list->getParams();
        if (isset($listParams['table_name'])) {
            $table = (string) $listParams['table_name'];
        }
        if ('' === $table) {
            $table = rex_request('table_name', 'string');
        }

        $fieldName = (string) $field['name'];
        $choices = self::resolveChoices(
            (string) ($field['choices'] ?? ''),
            (string) ($field['query'] ?? ''),
        );
        $token = rex_csrf_token::factory('fields_inline_edit')->getValue();

        $colors = self::parseColors((string) ($field['colors'] ?? ''));
        $lockValues = self::parseLockValues((string) ($field['lock_values'] ?? ''));
        $isLocked = in_array($value, $lockValues, true);

        if ($choices === []) {
            $choices = [$value => $value];
        }

        $currentLabel = $choices[$value] ?? $value;
        $currentColor = $colors[$value] ?? '';

        if ($isLocked) {
            return '<span class="fields-inline-select-lock-badge" title="' . rex_escape(rex_i18n::msg('fields_inline_select_locked')) . '">'
                . self::renderColorDot((string) $currentColor)
                . '<span class="fields-inline-select-lock-label">' . rex_escape((string) $currentLabel) . '</span>'
                . '</span>';
        }

        $options = '';
        foreach ($choices as $key => $label) {
            $selectedAttr = ((string) $key === $value) ? ' selected="selected"' : '';
            $options .= '<option value="' . rex_escape((string) $key) . '"' . $selectedAttr . '>'
                . rex_escape((string) $label)
                . '</option>';
        }

        $colorsJson = rex_escape((string) (json_encode($colors, \JSON_HEX_QUOT | \JSON_HEX_APOS) ?: '{}'));
        $labelsJson = rex_escape((string) (json_encode($choices, \JSON_HEX_QUOT | \JSON_HEX_APOS) ?: '{}'));

        return '<div class="fields-inline-select-cell" data-colors="' . $colorsJson . '" data-labels="' . $labelsJson . '">'
            . '<span class="fields-inline-select-display">'
            . self::renderColorDot((string) $currentColor)
            . '<span class="fields-inline-select-label">' . rex_escape((string) $currentLabel) . '</span>'
            . '</span>'
            . '<select class="fields-inline-select-native js-fields-inline-select-list" '
            . 'data-table="' . rex_escape($table) . '" '
            . 'data-field="' . rex_escape($fieldName) . '" '
            . 'data-id="' . $id . '" '
            . 'data-token="' . rex_escape($token) . '">'
            . $options
            . '</select>'
            . '</div>';
    }

    /**
     * @return array<string, string>
     */
    public static function resolveChoices(string $rawChoices, string $query): array
    {
        $queryChoices = self::parseChoicesFromQuery($query);
        $configuredChoices = self::parseChoices($rawChoices);

        if ($queryChoices === []) {
            return $configuredChoices;
        }

        // Konfigurierte Choices überschreiben Query-Labels bei gleichem Key.
        return array_replace($queryChoices, $configuredChoices);
    }

    /**
     * Parst Choices aus dem Format "key=Label\nkey2=Label2".
     *
     * @return array<string, string>
     */
    public static function parseChoices(string $raw): array
    {
        $choices = [];

        // Rückwärtskompatibilität: altes Kommaformat "a=A,b=B".
        if (!str_contains($raw, "\n") && str_contains($raw, ',')) {
            $raw = str_replace(',', "\n", $raw);
        }

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $label] = explode('=', $line, 2);
                $choices[trim($key)] = trim($label);
            } else {
                $choices[$line] = $line;
            }
        }
        return $choices;
    }

    /**
     * @return array<string, string>
     */
    public static function parseChoicesFromQuery(string $query): array
    {
        $query = trim($query);
        if ('' === $query) {
            return [];
        }

        try {
            $rows = rex_sql::factory()->getArray($query);
        } catch (Throwable $e) {
            return [];
        }

        $choices = [];
        foreach ($rows as $row) {
            $values = array_values($row);
            if ($values === []) {
                continue;
            }

            $key = trim((string) $values[0]);
            if ('' === $key) {
                continue;
            }

            $label = isset($values[1]) ? trim((string) $values[1]) : $key;
            $choices[$key] = '' !== $label ? $label : $key;
        }

        return $choices;
    }

    /**
     * Parst Farben aus dem Format "key=#hexcolor\nkey2=#hex2".
     *
     * @return array<string, string>
     */
    public static function parseColors(string $raw): array
    {
        $colors = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $color] = explode('=', $line, 2);
                $colors[trim($key)] = trim($color);
            }
        }
        return $colors;
    }

    /**
     * @return string[]
     */
    public static function parseLockValues(string $raw): array
    {
        if ('' === trim($raw)) {
            return [];
        }

        $raw = str_replace(',', "\n", $raw);
        $values = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ('' !== $line) {
                $values[] = $line;
            }
        }

        return array_values(array_unique($values));
    }

    public static function isValueLocked(string $value, string $lockRaw): bool
    {
        return in_array($value, self::parseLockValues($lockRaw), true);
    }

    public static function renderColorDot(string $color): string
    {
        if ('' === trim($color)) {
            return '';
        }

        return '<span class="fields-inline-color-dot" style="background:' . rex_escape($color) . ';"></span>';
    }

    public static function renderOptionContent(string $label, string $color): string
    {
        return '<span class="fields-inline-select-option">'
            . self::renderColorDot($color)
            . '<span class="fields-inline-select-option-label">' . rex_escape($label) . '</span>'
            . '</span>';
    }
}
