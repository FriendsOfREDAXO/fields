<?php

/**
 * YForm Value: Contacts
 *
 * Flexibles Kontakt-/Profil-Erfassungsfeld mit optionalen Feldern.
 * Speichert als JSON-Array mit mehreren Kontakten.
 *
 * @package fields
 */
class rex_yform_value_fields_contacts extends rex_yform_value_abstract
{
    /**
     * Parst die kommaseparierte Liste der optionalen Felder aus dem Feld-Element.
     *
     * @return array<string, bool>
     */
    private function getEnabledFields(): array
    {
        $allOptional = ['avatar', 'company_logo', 'company', 'function', 'phone', 'mobile', 'email', 'address', 'social', 'homepage'];
        $raw = trim((string) $this->getElement('optional_fields'));

        // Wenn leer oder "all" → alle aktiviert
        if ($raw === '' || $raw === 'all') {
            return array_fill_keys($allOptional, true);
        }

        // Kommaseparierte Liste parsen
        $selected = array_map('trim', explode(',', $raw));
        $enabled = [];
        foreach ($allOptional as $field) {
            $enabled[$field] = in_array($field, $selected, true);
        }

        return $enabled;
    }

    public function enterObject(): void
    {
        $value = $this->getValue();

        // JSON validieren und bereinigen
        $contacts = [];
        if (!empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $contacts = array_values(array_filter($decoded, static function (array $contact): bool {
                    return !empty($contact['lastname']) || !empty($contact['firstname']) || !empty($contact['company']);
                }));
            }
        }

        $this->setValue(json_encode($contacts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // Welche optionalen Felder sind aktiviert?
        $enabledFields = $this->getEnabledFields();
        $avatarRatio = trim((string) $this->getElement('avatar_ratio'));
        if ($avatarRatio === '') {
            $avatarRatio = '1:1';
        }
        $mediaCategory = (int) $this->getElement('media_category');

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_contacts.tpl.php',
                [
                    'contacts' => $contacts,
                    'enabledFields' => $enabledFields,
                    'avatarRatio' => $avatarRatio,
                    'mediaCategory' => $mediaCategory,
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
        return 'fields_contacts|name|label|optional_fields|avatar_ratio|media_category|';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_contacts',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'optional_fields' => [
                    'type' => 'text',
                    'label' => rex_i18n::msg('fields_contacts_optional_fields'),
                    'notice' => rex_i18n::msg('fields_contacts_optional_fields_notice'),
                ],
                'avatar_ratio' => [
                    'type' => 'choice',
                    'label' => rex_i18n::msg('fields_contacts_avatar_ratio'),
                    'choices' => 'free=free,1:1=1:1,4:3=4:3,3:4=3:4,16:9=16:9,3:2=3:2,2:3=2:3',
                    'default' => '1:1',
                    'notice' => rex_i18n::msg('fields_contacts_avatar_ratio_notice'),
                ],
                'media_category' => [
                    'type' => 'text',
                    'label' => rex_i18n::msg('fields_contacts_media_category'),
                    'notice' => rex_i18n::msg('fields_contacts_media_category_notice'),
                    'default' => '0',
                ],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_contacts_description'),
            'db_type' => ['text', 'mediumtext'],
            'famous' => false,
        ];
    }
}
