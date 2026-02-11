<?php

/**
 * Fields Addon - Uninstall
 *
 * @package fields
 */

// Assets entfernen
rex_dir::delete(rex_path::addonAssets('fields'));
