<?php

/**
 * Fields Addon - Boot
 *
 * Registriert YForm-Values, Templates und API-Endpunkte.
 *
 * @package fields
 */

use FriendsOfRedaxo\Fields\rex_api_fields_iban_validate;

$addon = rex_addon::get('fields');

// YForm Values registrieren
if (rex_addon::get('yform')->isAvailable()) {
    // Template-Pfad für YForm registrieren
    rex_yform::addTemplatePath($addon->getPath('ytemplates'));

    // Fragment-Pfad registrieren
    rex_fragment::addDirectory($addon->getPath('fragments'));
}

// API-Klassen registrieren
rex_api_function::register('fields_iban_validate', rex_api_fields_iban_validate::class);

// Backend-Assets laden
if (rex::isBackend() && rex::getUser()) {
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-interactive.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-structure.js'));
    
    $faIcons = $addon->getConfig('icons_fontawesome');
    $uikitIcons = $addon->getConfig('icons_uikit');

    // Split and filter custom icons if available
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

    rex_view::addCssFile($addon->getAssetsUrl('css/fields-backend.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-backend.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/fields-table.js'));
}
