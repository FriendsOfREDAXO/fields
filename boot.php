<?php

/**
 * Fields Addon - Boot
 *
 * Registriert YForm-Values, Templates und API-Endpunkte.
 *
 * @package fields
 */

use FriendsOfRedaxo\Fields\rex_api_fields_iban_validate;
use FriendsOfRedaxo\Fields\rex_api_fields_inline_update;
use FriendsOfRedaxo\Fields\rex_api_fields_tagging_suggest;

$addon = rex_addon::get('fields');

// YForm Values registrieren
rex_fragment::addDirectory($addon->getPath('fragments'));

if (rex_addon::get('yform')->isAvailable()) {
    // Template-Pfad für YForm registrieren
    rex_yform::addTemplatePath($addon->getPath('ytemplates'));
}

if (rex_addon::get('metainfo')->isAvailable() && rex::isBackend()) {
    rex_extension::register('METAINFO_CUSTOM_FIELD', 'fields_metainfo_tagging_custom_field');
}

// API-Klassen registrieren
rex_api_function::register('fields_iban_validate', rex_api_fields_iban_validate::class);
rex_api_function::register('fields_inline_update', rex_api_fields_inline_update::class);
rex_api_function::register('fields_tagging_suggest', rex_api_fields_tagging_suggest::class);

// Backend-Assets laden
if (rex::isBackend() && rex::getUser()) {
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-inline.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-interactive.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-structure.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-tagging.js'));

    rex_view::addCssFile($addon->getAssetsUrl('css/fields-backend.css'));
    rex_view::addCssFile($addon->getAssetsUrl('css/fields-tagging.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-backend.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-table.js'));

    $faIcons    = $addon->getConfig('icons_fontawesome');
    $uikitIcons = $addon->getConfig('icons_uikit');
    $customIcons = [];
    if (!empty($faIcons)) {
        $customIcons['fa'] = array_filter(array_map('trim', explode(',', $faIcons)));
    }
    if (!empty($uikitIcons)) {
        $customIcons['uikit'] = array_filter(array_map('trim', explode(',', $uikitIcons)));
    }
    if (!empty($customIcons)) {
        rex_view::setJsProperty('fields_icons', $customIcons);
    }
}

/**
 * Rendert den Fields-Tagging-Typ als Metainfo-Feld.
 */
function fields_metainfo_tagging_custom_field(rex_extension_point $ep): void
{
    $subject = $ep->getSubject();

    if (!isset($subject['type']) || 'Fields Tagging' !== $subject['type']) {
        return;
    }

    $fieldName = str_replace('rex-metainfo-', '', (string) $subject[3]);
    $fieldValue = (string) ($subject['values'][0] ?? '');
    $fieldId = (string) $subject[3];
    $fieldLabel = (string) ($subject[4] ?? '');
    if (preg_match('/<label[^>]*>(.*)<\/label>/i', $fieldLabel, $matches)) {
        $fieldLabel = (string) ($matches[1] ?? $fieldLabel);
    }
    $fieldLabel = trim(strip_tags(html_entity_decode($fieldLabel, ENT_QUOTES, 'UTF-8')));
    $fieldClass = 'form-control';
    $additionalAttributes = [];
    $options = [];

    $defaultSourceTable = '';
    if (str_starts_with($fieldName, 'art_')) {
        $defaultSourceTable = rex::getTable('article');
    } elseif (str_starts_with($fieldName, 'med_')) {
        $defaultSourceTable = rex::getTable('media');
    } elseif (str_starts_with($fieldName, 'clang_')) {
        $defaultSourceTable = rex::getTable('clang');
    }

    /** @var rex_sql $sql */
    $sql = $subject['sql'];
    $attributes = (string) $sql->getValue('attributes');
    if ('' !== trim($attributes)) {
        $parsedAttributes = rex_string::split($attributes);

        if (isset($parsedAttributes['class'])) {
            $fieldClass = (string) $parsedAttributes['class'];
            unset($parsedAttributes['class']);
        }

        if (isset($parsedAttributes['note'])) {
            unset($parsedAttributes['note']);
        }

        foreach ($parsedAttributes as $key => $value) {
            if (is_int($key)) {
                $additionalAttributes[(string) $value] = '';
                continue;
            }

            $additionalAttributes[(string) $key] = (string) $value;
        }
    }

    $params = trim((string) $sql->getValue('params'));
    if ('' !== $params) {
        $decodedParams = json_decode($params, true);
        if (is_array($decodedParams)) {
            $options = $decodedParams;
        } else {
            parse_str(str_replace(["\r\n", "\r", "\n", ';'], '&', $params), $options);
        }
    }

    $fragment = new rex_fragment();
    $fragment->setVar('fieldName', $fieldName);
    $fragment->setVar('fieldValue', $fieldValue);
    $fragment->setVar('fieldId', $fieldId);
    $fragment->setVar('fieldLabel', $fieldLabel);
    $fragment->setVar('fieldClass', $fieldClass);
    $fragment->setVar('additionalAttributes', $additionalAttributes);
    $fragment->setVar('sourceTable', trim((string) ($options['source_table'] ?? $defaultSourceTable)));
    $fragment->setVar('sourceField', trim((string) ($options['source_field'] ?? $fieldName)));
    $fragment->setVar('maxTags', max(0, (int) ($options['max_tags'] ?? 0)));
    $fragment->setVar('colors', array_values(array_filter((array) ($options['colors'] ?? []), 'is_string')));

    $subject[0] = $fragment->parse('metainfo/fields_tagging.php');
    $ep->setSubject($subject);
}
