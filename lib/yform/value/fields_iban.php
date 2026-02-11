<?php

/**
 * YForm Value: IBAN Check
 *
 * IBAN-Eingabefeld mit Live-Validierung über proxied openIBAN.com API.
 * Speichert die bereinigte IBAN.
 *
 * @package fields
 */
class rex_yform_value_fields_iban extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        $value = $this->getValue();

        // IBAN bereinigen: Leerzeichen entfernen, Großbuchstaben
        $value = strtoupper(preg_replace('/\s+/', '', $value) ?? '');

        $this->setValue($value);

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_iban.tpl.php',
            );
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    /**
     * Serverseitige IBAN-Basis-Validierung (Format)
     */
    public static function isValidFormat(string $iban): bool
    {
        $iban = strtoupper(preg_replace('/\s+/', '', $iban) ?? '');

        // Mindestlänge 15, max 34 Zeichen
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }

        // Muss mit 2 Buchstaben + 2 Ziffern beginnen
        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]+$/', $iban)) {
            return false;
        }

        // IBAN-Prüfsumme (Modulo 97)
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        $numeric = '';
        for ($i = 0, $len = strlen($rearranged); $i < $len; ++$i) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (string) (ord($char) - 55);
            } else {
                $numeric .= $char;
            }
        }

        // bcmod für große Zahlen
        if (function_exists('bcmod')) {
            return bcmod($numeric, '97') === '1';
        }

        // Fallback: manuelle Modulo-Berechnung
        $remainder = 0;
        for ($i = 0, $len = strlen($numeric); $i < $len; ++$i) {
            $remainder = (int) (($remainder . $numeric[$i]) % 97);
        }

        return $remainder === 1;
    }

    public function getDescription(): string
    {
        return 'fields_iban|name|label|';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_iban',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_iban_description'),
            'db_type' => ['varchar(34)'],
            'famous' => false,
        ];
    }
}
