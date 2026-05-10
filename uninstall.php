<?php

if (rex_addon::get('metainfo')->isAvailable()) {
	$sql = rex_sql::factory();
	$sql->setQuery('DELETE FROM ' . rex::getTable('metainfo_type') . ' WHERE label = ?', ['Fields Tagging']);
}

/**
 * Fields Addon - Uninstall
 *
 * @package fields
 */

// Assets entfernen
rex_dir::delete(rex_path::addonAssets('fields'));
