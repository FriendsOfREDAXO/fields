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
    
    public static function getListValue(array $params)
    {
        $value = $params['value'];
        if (!$value) return '-';

        $contacts = json_decode($value, true);
        if (!$contacts || !is_array($contacts)) return '-';
        
        $count = count($contacts);
        if ($count === 0) return '-';
        
        $items = [];
        $maxShow = 3;
        
        foreach ($contacts as $c) {
            $parts = [];
            
            // Name (First Last)
            $nameParts = array_filter([$c['firstname'] ?? '', $c['lastname'] ?? '']);
            $hasName = !empty($nameParts);
            if ($hasName) {
                $parts[] = '<strong>' . rex_escape(implode(' ', $nameParts)) . '</strong>';
            }
            
            // Company
            if (!empty($c['company'])) {
                $comp = rex_escape($c['company']);
                if ($hasName) {
                    $parts[] = '<span class="text-muted">(' . $comp . ')</span>';
                } else {
                    $parts[] = '<strong>' . $comp . '</strong>';
                }
            }
            
            // Email
            if (!empty($c['email'])) {
                $parts[] = '<a href="mailto:' . rex_escape($c['email']) . '" title="' . rex_escape($c['email']) . '" onclick="event.stopPropagation();"><i class="rex-icon fa-envelope-o"></i></a>';
            }

            if ($parts) {
                // Formatting: Name (Company) Email
                $items[] = '<div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.3;">' . implode(' ', $parts) . '</div>';
            }
        }
        
        $display = array_slice($items, 0, $maxShow);
        $out = implode('', $display);
        
        if ($count > $maxShow) {
             $out .= '<div class="text-muted" style="font-size: 85%; padding-top:2px;">(+' . ($count - $maxShow) . ' weitere)</div>';
        }
        
        return $out;
    }
}
