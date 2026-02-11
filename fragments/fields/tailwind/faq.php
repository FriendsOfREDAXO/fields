<?php

/**
 * Fragment: FAQ – Tailwind CSS
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
<div class="divide-y divide-gray-200 <?= rex_escape($class) ?>">
    <?php foreach ($items as $i => $item): ?>
        <details class="group py-4"<?= $i === 0 ? ' open' : '' ?>>
            <summary class="flex cursor-pointer items-center justify-between text-lg font-medium text-gray-900">
                <span><?= rex_escape($item['question'] ?? '') ?></span>
                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </summary>
            <div class="mt-3 text-gray-600 leading-relaxed">
                <?= rex_escape($item['answer'] ?? '') ?>
            </div>
        </details>
    <?php endforeach; ?>
</div>
