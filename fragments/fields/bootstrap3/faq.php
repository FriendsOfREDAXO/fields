<?php

/**
 * Fragment: FAQ – Bootstrap 3
 *
 * Variables:
 *   json      – JSON-String der FAQ-Daten [{question, answer}, ...]
 *   class     – zusätzliche CSS-Klasse
 *   schema    – true/false, ob Schema.org JSON-LD ausgegeben werden soll (default: true)
 *   id        – Panel-Group-ID (default: auto)
 */

$json = $this->getVar('json', '');
$class = $this->getVar('class', '');
$schema = $this->getVar('schema', true);
$groupId = $this->getVar('id', 'fields-faq-' . mt_rand(1000, 9999));

$items = json_decode($json, true);
if (!is_array($items) || count($items) === 0) {
    return;
}

if ($schema) {
    echo rex_yform_value_fields_faq::getSchemaJsonLd($items);
}
?>
<div class="panel-group <?= rex_escape($class) ?>" id="<?= rex_escape($groupId) ?>" role="tablist" aria-multiselectable="true">
    <?php foreach ($items as $i => $item): ?>
        <?php
        $headId = $groupId . '-head-' . $i;
        $bodyId = $groupId . '-body-' . $i;
        ?>
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="<?= $headId ?>">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#<?= rex_escape($groupId) ?>"
                       href="#<?= $bodyId ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                       aria-controls="<?= $bodyId ?>"<?= $i !== 0 ? ' class="collapsed"' : '' ?>>
                        <?= rex_escape($item['question'] ?? '') ?>
                    </a>
                </h4>
            </div>
            <div id="<?= $bodyId ?>" class="panel-collapse collapse<?= $i === 0 ? ' in' : '' ?>"
                 role="tabpanel" aria-labelledby="<?= $headId ?>">
                <div class="panel-body">
                    <?= rex_escape($item['answer'] ?? '') ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
