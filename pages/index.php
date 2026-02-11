<?php

/**
 * Fields Addon - Index (Weiterleitung auf Info)
 *
 * @package fields
 */

$addon = rex_addon::get('fields');
echo rex_view::title($addon->i18n('fields_title'));

rex_be_controller::includeCurrentPageSubPath();
