<?php

/**
 * Fields Addon - Installation
 *
 * @package fields
 */

if (rex_addon::get('metainfo')->isAvailable()) {
	$sql = rex_sql::factory();
	$sql->setQuery('SELECT id FROM ' . rex::getTable('metainfo_type') . ' WHERE label = ?', ['Fields Tagging']);
	if (0 === (int) $sql->getRows()) {
		$sql = rex_sql::factory();
		$sql->setTable(rex::getTable('metainfo_type'));
		$sql->setValue('label', 'Fields Tagging');
		$sql->setValue('dbtype', 'text');
		$sql->setValue('dblength', 0);
		$sql->insert();
	}
}

// Assets kopieren
$addon = rex_addon::get('fields');

// Sicherstellen, dass das Assets-Verzeichnis existiert
rex_dir::create($addon->getAssetsPath());

// Assets kopieren
$addon->setProperty('install', true);
