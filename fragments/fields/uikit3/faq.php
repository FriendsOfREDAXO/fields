<?php

/**
 * Fragment: FAQ – UIkit 3
 *
 * Variables:
 *   json   – JSON-String der FAQ-Daten [{question, answer}, ...]
 *   class  – zusätzliche CSS-Klasse
 *   schema – true/false, ob Schema.org JSON-LD ausgegeben werden soll (default: true)
 */

$json = $this->getVar('json', '');
$class = $this->getVar('class', '');
$schema = $this->getVar('schema', true);

$items = json_decode($json, true);
if (!is_array($items) || count($items) === 0) {
    return;
}

if ($schema) {
    echo rex_yform_value_fields_faq::getSchemaJsonLd($items);
}
?>
<ul class="<?= rex_escape($class) ?>" uk-accordion>
    <?php foreach ($items as $i => $item): ?>
        <li<?= $i === 0 ? ' class="uk-open"' : '' ?>>
            <a class="uk-accordion-title" href="#"><?= rex_escape($item['question'] ?? '') ?></a>
            <div class="uk-accordion-content">
                <p><?= rex_escape($item['answer'] ?? '') ?></p>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
