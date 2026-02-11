<?php

/**
 * Fragment: Social Web – Tailwind CSS
 *
 * Variablen:
 * - json: string (JSON der Social-Web-Einträge)
 * - icon_set: string ('fontawesome' oder 'uikit'), default: 'fontawesome'
 * - class: string (zusätzliche CSS-Klasse)
 */

$json = $this->getVar('json', '');
$iconSet = $this->getVar('icon_set', 'fontawesome');
$class = $this->getVar('class', '');

$entries = json_decode($json, true);
if (!is_array($entries) || count($entries) === 0) {
    return;
}

$platforms = rex_yform_value_fields_social_web::getPlatforms();
?>
<div class="flex flex-wrap items-center gap-3 <?= rex_escape($class) ?>">
    <?php foreach ($entries as $entry): ?>
        <?php
        $platform = $entry['platform'] ?? 'custom';
        $url = $entry['url'] ?? '';
        $platformData = $platforms[$platform] ?? $platforms['custom'];
        $iconClass = $iconSet === 'uikit'
            ? 'uk-icon-' . ($platformData['uikit'] ?? 'world')
            : 'fa ' . ($platformData['fa'] ?? 'fa-globe');
        ?>
        <a href="<?= rex_escape($url) ?>" target="_blank" rel="noopener noreferrer"
           title="<?= rex_escape($platformData['label']) ?>"
           class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors">
            <i class="<?= rex_escape($iconClass) ?>"></i>
            <span class="sr-only"><?= rex_escape($platformData['label']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
