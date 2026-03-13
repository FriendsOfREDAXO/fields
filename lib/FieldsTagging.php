<?php

namespace FriendsOfRedaxo\Fields;

use rex_escape;
use rex_sql;
use rex_sql_exception;

/**
 * FieldsTagging – Helper für das fields_tagging Widget
 *
 * Liest, enkodiert und rendert Tags im Format:
 * [{"text":"php","color":"#2980b9"}, ...]
 *
 * @package fields
 */
class FieldsTagging
{
    /** @var list<string> Standard-Farbpalette (alle mit ausreichend Kontrast für weiße Schrift) */
    public const DEFAULT_COLORS = [
        '#e74c3c', '#e67e22', '#f1c40f', '#27ae60',
        '#16a085', '#2980b9', '#8e44ad', '#7f8c8d',
        '#e91e63', '#00bcd4',
    ];

    // ─── De-/Enkodierung ────────────────────────────────────────────────────

    /**
     * Parst einen JSON-String in ein normiertes Tags-Array.
     *
     * @param  string $raw  JSON-String aus der DB
     * @return list<array{text:string,color:string}>
     */
    public static function decode(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }

        $tags = [];
        $seen = [];
        foreach ($data as $item) {
            if (!is_array($item) || !isset($item['text'])) {
                continue;
            }
            $text = trim((string) $item['text']);
            if ($text === '') {
                continue;
            }
            $key = mb_strtolower($text);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $color = isset($item['color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', (string) $item['color'])
                ? (string) $item['color']
                : self::DEFAULT_COLORS[0];
            $tags[] = ['text' => $text, 'color' => $color];
        }

        return $tags;
    }

    /**
     * Enkodiert ein Tags-Array zurück als JSON-String.
     *
     * @param  list<array{text:string,color:string}> $tags
     */
    public static function encode(array $tags): string
    {
        return $tags !== [] ? (string) json_encode($tags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    }

    // ─── Ausgabe-Helfer ────────────────────────────────────────────────────

    /**
     * Gibt nur die Texte der Tags als einfaches Array zurück.
     *
     * @param  list<array{text:string,color:string}> $tags
     * @return list<string>
     */
    public static function getTexts(array $tags): array
    {
        return array_values(array_map(static fn(array $t) => $t['text'], $tags));
    }

    /**
     * Rendert Tags als farbige Chip-Spans (für Modul-Output).
     *
     * @param  list<array{text:string,color:string}> $tags
     * @param  string $emptyText  Rückgabewert wenn keine Tags vorhanden
     */
    public static function toHtml(array $tags, string $emptyText = ''): string
    {
        if ($tags === []) {
            return $emptyText;
        }

        $chips = [];
        foreach ($tags as $tag) {
            $chips[] = self::chipHtml($tag['text'], $tag['color']);
        }

        return implode(' ', $chips);
    }

    /**
     * Rendert einen einzelnen farbigen Chip-Span.
     */
    public static function chipHtml(string $text, string $color = ''): string
    {
        if ($color === '') {
            $color = self::DEFAULT_COLORS[0];
        }
        return sprintf(
            '<span style="display:inline-block;background:%s;color:#fff;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:500;margin:1px 3px;white-space:nowrap;">%s</span>',
            rex_escape($color),
            rex_escape($text),
        );
    }

    /**
     * Dekodiert direkt aus einem DB-Rohwert und rendert als HTML.
     *
     * Kurzform von toHtml(decode($raw)).
     */
    public static function fromRaw(string $raw, string $emptyText = ''): string
    {
        return self::toHtml(self::decode($raw), $emptyText);
    }

    // ─── Datenbank-Helfer ──────────────────────────────────────────────────

    /**
     * Sammelt alle eindeutigen Tags (dedupliziert, case-insensitive) aus einer
     * DB-Tabellenspalte. Gibt ein Array von {text, color} zurück.
     *
     * @param  string $table  Tabellenname (ohne rex_-Prefix, z.B. 'forcal_entries')
     * @param  string $field  Spaltenname
     * @return list<array{text:string,color:string}>
     */
    public static function collectFromTable(string $table, string $field): array
    {
        $sql = rex_sql::factory();
        try {
            $rows = $sql->getArray(
                'SELECT ' . $sql->escapeIdentifier($field) .
                ' FROM ' . $sql->escapeIdentifier(rex::getTablePrefix() . $table) .
                ' WHERE ' . $sql->escapeIdentifier($field) . ' IS NOT NULL' .
                ' AND ' . $sql->escapeIdentifier($field) . " != ''",
            );
        } catch (rex_sql_exception $e) {
            return [];
        }

        $seen  = [];
        $found = [];
        foreach ($rows as $row) {
            foreach (self::decode((string) ($row[$field] ?? '')) as $tag) {
                $key = mb_strtolower($tag['text']);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $found[]    = $tag;
                }
            }
        }

        usort($found, static fn($a, $b) => strcmp(mb_strtolower($a['text']), mb_strtolower($b['text'])));

        return $found;
    }

    /**
     * Gibt alle eindeutigen Tag-Texte aus einer Tabellenspalte zurück.
     *
     * @param  string $table  Tabellenname (ohne rex_-Prefix)
     * @param  string $field  Spaltenname
     * @return list<string>
     */
    public static function collectTextsFromTable(string $table, string $field): array
    {
        return self::getTexts(self::collectFromTable($table, $field));
    }

    /**
     * Erzeugt eine SQL-WHERE-Bedingung, die prüft ob ein Feld einen bestimmten
     * Tag-Text enthält.
     *
     * Erfordert MySQL 5.7+.
     * Verwendung: WHERE {FieldsTagging::sqlHasTag('tags', 'php')}
     *
     * @param  string $field    Spaltenname
     * @param  string $tagText  Gesuchter Tag-Text (exact match, case-sensitive)
     */
    public static function sqlHasTag(string $field, string $tagText): string
    {
        $sql          = rex_sql::factory();
        $escapedField = $sql->escapeIdentifier($field);
        $escapedText  = $sql->escape($tagText);

        return "JSON_SEARCH({$escapedField}, 'one', {$escapedText}, NULL, '$[*].text') IS NOT NULL";
    }

    /**
     * Filtert ein Array von Datensätzen nach einem Tag-Text.
     *
     * @param  array<int,array<string,mixed>> $rows      Datensätze aus der DB
     * @param  string                          $field     Feldname in den Datensätzen
     * @param  string                          $tagText   Gesuchter Tag-Text
     * @return array<int,array<string,mixed>>
     */
    public static function filterByTag(array $rows, string $field, string $tagText): array
    {
        $tagText = mb_strtolower($tagText);
        return array_values(array_filter($rows, static function (array $row) use ($field, $tagText) {
            foreach (self::decode((string) ($row[$field] ?? '')) as $tag) {
                if (mb_strtolower($tag['text']) === $tagText) {
                    return true;
                }
            }
            return false;
        }));
    }

    // ─── Widget-Rendering (für rex_form / Forcal) ─────────────────────────

    /**
     * Rendert das öffnende HTML des Tagging-Widgets.
     * Zu verwenden als rex_form prefix. Das schließende </div> muss als suffix
     * oder die nachfolgende <input>-Zeile direkt gesetzt werden.
     *
     * @param  string $targetId  ID des zugehörigen (ggf. versteckten) Textfeldes
     * @param  array{
     *   api_url?:      string,
     *   source_table?: string,
     *   source_field?: string,
     *   max_tags?:     int,
     *   colors?:       list<string>,
     * } $options
     */
    public static function renderWidgetOpen(string $targetId, array $options = []): string
    {
        $colors      = $options['colors']       ?? self::DEFAULT_COLORS;
        $apiUrl      = $options['api_url']       ?? '';
        $srcTable    = $options['source_table']  ?? '';
        $srcField    = $options['source_field']  ?? '';
        $maxTags     = (int) ($options['max_tags'] ?? 0);

        $colorsJson  = rex_escape((string) json_encode($colors, JSON_UNESCAPED_UNICODE));
        $firstColor  = $colors[0] ?? '#2980b9';

        // Farbpalette HTML
        $paletteButtons = '';
        foreach ($colors as $i => $hex) {
            $active = $i === 0 ? ' active' : '';
            $paletteButtons .= sprintf(
                '<button type="button" class="fields-tagging-color-btn%s" data-color="%s" style="background:%s" title="%s" aria-label="Farbe %s"></button>',
                $active,
                rex_escape($hex),
                rex_escape($hex),
                rex_escape($hex),
                rex_escape($hex),
            );
        }

        $counterHtml = $maxTags > 0
            ? sprintf(
                '<span class="fields-tagging-counter text-muted" style="font-size:12px;margin-left:8px;"><span class="fields-tagging-count">0</span> / %d Tags</span>',
                $maxTags,
            )
            : '';

        return sprintf(
            '<div class="fields-tagging-widget"'
            . ' data-api-url="%s"'
            . ' data-source-table="%s"'
            . ' data-source-field="%s"'
            . ' data-max-tags="%d"'
            . ' data-colors="%s">'
            . '<div class="fields-tagging-chips">'
            . '<button type="button" class="btn btn-default btn-sm fields-tagging-open-btn">'
            . '<i class="rex-icon fa-tag"></i> Tags bearbeiten'
            . '</button>'
            . '</div>'
            . '<div class="fields-tagging-panel" style="display:none">'
            . '<div class="fields-tagging-palette">'
            . '<span class="fields-tagging-palette-label">Farbe:</span>'
            . '%s' // palette buttons
            . '<span class="fields-tagging-palette-sep"></span>'
            . '<input type="color" class="fields-tagging-custom-color" value="%s" title="Eigene Farbe (nur dunkle Farben für weiße Schrift)">'
            . '<span class="fields-tagging-contrast-hint" style="display:none">&#9888; Zu hell für weiße Schrift</span>'
            . '</div>'
            . '<div class="input-group fields-tagging-input-group">'
            . '<input type="text" class="form-control fields-tagging-input" placeholder="Neuen Tag eingeben …" autocomplete="off">'
            . '<span class="input-group-addon fields-tagging-color-preview" style="background:%s;width:30px;"></span>'
            . '<span class="input-group-btn">'
            . '<button type="button" class="btn btn-primary fields-tagging-add-btn"><i class="rex-icon fa-plus"></i> Hinzufügen</button>'
            . '</span>'
            . '</div>'
            . '<div class="fields-tagging-suggestions-wrap">'
            . '<div class="fields-tagging-suggestions-label">Vorhandene Tags:</div>'
            . '<div class="fields-tagging-suggestions"><em class="text-muted" style="font-size:12px;">Wird geladen …</em></div>'
            . '</div>'
            . '<div class="fields-tagging-panel-footer">'
            . '<button type="button" class="btn btn-default btn-sm fields-tagging-close-btn"><i class="rex-icon fa-check"></i> Fertig</button>'
            . $counterHtml
            . '</div>'
            . '</div>',
            rex_escape($apiUrl),
            rex_escape($srcTable),
            rex_escape($srcField),
            $maxTags,
            $colorsJson,
            $paletteButtons,
            rex_escape($firstColor),
            rex_escape($firstColor),
        );
    }
}
