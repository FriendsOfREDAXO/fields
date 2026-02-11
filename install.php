<?php

/**
 * Fields Addon - Installation
 *
 * @package fields
 */

// Keine zusätzlichen DB-Tabellen benötigt.
// Alle Daten werden als JSON in den YForm-Feldern gespeichert.

// Assets kopieren
$addon = rex_addon::get('fields');

// Sicherstellen, dass das Assets-Verzeichnis existiert
rex_dir::create($addon->getAssetsPath());

// Assets kopieren
$addon->setProperty('install', true);
