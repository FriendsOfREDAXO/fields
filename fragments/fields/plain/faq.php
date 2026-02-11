<?php

/**
 * Fragment: FAQ – Plain / Framework-independent
 *
 * Verwendet <details>/<summary> für native Accordion-Funktionalität.
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
<div class="fields-faq <?= rex_escape($class) ?>">
    <?php foreach ($items as $i => $item): ?>
        <details class="fields-faq-item"<?= $i === 0 ? ' open' : '' ?>>
            <summary class="fields-faq-question"><?= rex_escape($item['question'] ?? '') ?></summary>
            <div class="fields-faq-answer">
                <?= rex_escape($item['answer'] ?? '') ?>
            </div>
        </details>
    <?php endforeach; ?>
</div>
