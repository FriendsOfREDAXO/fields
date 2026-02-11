<?php

/**
 * Fragment: Social Web – Plain (framework-unabhängig)
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
<nav class="fields-social-links <?= rex_escape($class) ?>" aria-label="Social Media">
    <ul>
        <?php foreach ($entries as $entry): ?>
            <?php
            $platform = $entry['platform'] ?? 'custom';
            $url = $entry['url'] ?? '';
            $platformData = $platforms[$platform] ?? $platforms['custom'];
            ?>
            <li>
                <a href="<?= rex_escape($url) ?>" target="_blank" rel="noopener noreferrer">
                    <?= rex_escape($platformData['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
