<?php

/**
 * Fragment: Social Web – Bootstrap 3
 *
 * Variablen:
 * - json: string (JSON der Social-Web-Einträge)
 * - icon_set: string ('fontawesome' oder 'uikit'), default: 'fontawesome'
 * - class: string (zusätzliche CSS-Klasse)
 *
 * Beispiel:
 * $fragment = new rex_fragment();
 * $fragment->setVar('json', $dataset->getValue('social_web'));
 * $fragment->setVar('icon_set', 'fontawesome');
 * echo $fragment->parse('fields/bootstrap3/social_web.php');
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
<ul class="list-inline fields-social-web <?= rex_escape($class) ?>">
    <?php foreach ($entries as $entry): ?>
        <?php
        $platform = $entry['platform'] ?? 'custom';
        $url = $entry['url'] ?? '';
        $platformData = $platforms[$platform] ?? $platforms['custom'];
        $iconClass = $iconSet === 'uikit'
            ? 'uk-icon-' . ($platformData['uikit'] ?? 'world')
            : 'fa ' . ($platformData['fa'] ?? 'fa-globe');
        ?>
        <li>
            <a href="<?= rex_escape($url) ?>" target="_blank" rel="noopener noreferrer" title="<?= rex_escape($platformData['label']) ?>">
                <i class="<?= rex_escape($iconClass) ?>"></i>
                <span class="sr-only"><?= rex_escape($platformData['label']) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
