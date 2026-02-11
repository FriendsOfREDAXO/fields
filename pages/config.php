<?php

/**
 * Fields Addon - Konfigurationsseite
 *
 * @package fields
 */

$addon = rex_addon::get('fields');

// Formular erstellen
$form = rex_config_form::factory('fields');

// --- Icon-Picker ---
$form->addFieldset(rex_i18n::msg('fields_config_icon_picker'));

$field = $form->addTextAreaField('icons_fontawesome');
$field->setLabel(rex_i18n::msg('fields_config_icons_fa'));
$field->setNotice(rex_i18n::msg('fields_config_icons_fa_notice'));

$field = $form->addTextAreaField('icons_uikit');
$field->setLabel(rex_i18n::msg('fields_config_icons_uikit'));
$field->setNotice(rex_i18n::msg('fields_config_icons_uikit_notice'));

$field = $form->addTextField('icon_sets');
$field->setLabel(rex_i18n::msg('fields_config_icon_sets'));
$field->setNotice(rex_i18n::msg('fields_config_icon_sets_notice'));

// --- IBAN ---
$form->addFieldset(rex_i18n::msg('fields_config_iban'));

$field = $form->addCheckboxField('iban_proxy_enabled');
$field->setLabel(rex_i18n::msg('fields_iban_proxy_enabled'));
$field->addOption('', 1);
$field->setNotice(rex_i18n::msg('fields_iban_proxy_notice'));

// Formular ausgeben
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('fields_config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
