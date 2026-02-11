<?php

/**
 * Fragment: Social Web – UIkit 3
 *
 * Variablen:
 * - json: string (JSON der Social-Web-Einträge)
 * - icon_set: string ('fontawesome' oder 'uikit'), default: 'uikit'
 * - class: string (zusätzliche CSS-Klasse)
 */

$json = $this->getVar('json', '');
$iconSet = $this->getVar('icon_set', 'uikit');
$class = $this->getVar('class', '');

$entries = json_decode($json, true);
if (!is_array($entries) || count($entries) === 0) {
    return;
}

$platforms = rex_yform_value_fields_social_web::getPlatforms();
?>
<div class="uk-flex uk-flex-wrap uk-flex-middle uk-grid-small <?= rex_escape($class) ?>" uk-grid>
    <?php foreach ($entries as $entry): ?>
        <?php
        $platform = $entry['platform'] ?? 'custom';
        $url = $entry['url'] ?? '';
        $platformData = $platforms[$platform] ?? $platforms['custom'];
        $ukIcon = $platformData['uikit'] ?? 'world';
        ?>
        <div>
            <a href="<?= rex_escape($url) ?>" target="_blank" rel="noopener noreferrer"
               title="<?= rex_escape($platformData['label']) ?>"
               class="uk-icon-button" uk-icon="icon: <?= rex_escape($ukIcon) ?>"></a>
        </div>
    <?php endforeach; ?>
</div>
