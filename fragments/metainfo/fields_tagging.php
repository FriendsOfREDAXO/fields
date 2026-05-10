<?php
/** @var rex_fragment $this */

$fieldName = (string) $this->getVar('fieldName');
$fieldValue = (string) $this->getVar('fieldValue');
$fieldId = (string) $this->getVar('fieldId');
$fieldLabel = (string) $this->getVar('fieldLabel', '');
$fieldClass = (string) $this->getVar('fieldClass', 'form-control');
$additionalAttributes = (array) $this->getVar('additionalAttributes', []);
$sourceTable = (string) $this->getVar('sourceTable', '');
$sourceField = (string) $this->getVar('sourceField', '');
$maxTags = (int) $this->getVar('maxTags', 0);
$colors = (array) $this->getVar('colors', \FriendsOfRedaxo\Fields\FieldsTagging::DEFAULT_COLORS);
$tags = \FriendsOfRedaxo\Fields\FieldsTagging::decode($fieldValue);

if ([] === $colors) {
    $colors = \FriendsOfRedaxo\Fields\FieldsTagging::DEFAULT_COLORS;
}

$colorsJson = rex_escape((string) json_encode($colors, JSON_UNESCAPED_UNICODE));
$firstColor = (string) ($colors[0] ?? \FriendsOfRedaxo\Fields\FieldsTagging::DEFAULT_COLORS[0]);
$apiUrl = rex_url::backendController(['rex-api-call' => 'fields_tagging_suggest']);

$additionalAttrsString = '';
foreach ($additionalAttributes as $name => $value) {
    $additionalAttrsString .= ' ' . rex_escape((string) $name) . '="' . rex_escape((string) $value) . '"';
}
?>

<div class="form-group fields-tagging-group" id="<?= rex_escape($fieldId) ?>">
    <label class="control-label"><?= rex_escape($fieldLabel !== '' ? $fieldLabel : $fieldName) ?></label>

    <div class="fields-tagging-widget"
        data-api-url="<?= rex_escape($apiUrl) ?>"
        data-source-table="<?= rex_escape($sourceTable) ?>"
        data-source-field="<?= rex_escape($sourceField) ?>"
        data-max-tags="<?= (int) $maxTags ?>"
        data-colors="<?= $colorsJson ?>">
    <div class="fields-tagging-chips">
            <?php foreach ($tags as $tag): ?>
                <span class="fields-tagging-chip"
                    data-text="<?= rex_escape($tag['text']) ?>"
                    data-color="<?= rex_escape($tag['color']) ?>"
                    style="background:<?= rex_escape($tag['color']) ?>">
                    <?= rex_escape($tag['text']) ?>
                    <button type="button" class="fields-tagging-chip-remove" aria-label="Entfernen">&times;</button>
                </span>
            <?php endforeach; ?>
        <button type="button" class="btn btn-default btn-sm fields-tagging-open-btn">
            <i class="rex-icon fa-tag"></i> Tags bearbeiten
        </button>
    </div>
    <div class="fields-tagging-panel" style="display:none">
        <div class="fields-tagging-palette">
            <span class="fields-tagging-palette-label">Farbe:</span>
            <?php foreach ($colors as $index => $hex): ?>
                <button type="button" class="fields-tagging-color-btn<?= 0 === $index ? ' active' : '' ?>" data-color="<?= rex_escape((string) $hex) ?>" style="background:<?= rex_escape((string) $hex) ?>" title="<?= rex_escape((string) $hex) ?>" aria-label="Farbe <?= rex_escape((string) $hex) ?>"></button>
            <?php endforeach; ?>
            <span class="fields-tagging-palette-sep"></span>
            <input type="color" class="fields-tagging-custom-color" value="<?= rex_escape($firstColor) ?>" title="Eigene Farbe (nur dunkle Farben für weiße Schrift)">
            <span class="fields-tagging-contrast-hint" style="display:none">&#9888; Zu hell für weiße Schrift</span>
        </div>
        <div class="input-group fields-tagging-input-group">
            <input type="text" class="form-control fields-tagging-input <?= rex_escape($fieldClass) ?>" value="" placeholder="Tag eingeben und Enter drücken" autocomplete="off"<?= $additionalAttrsString ?>>
            <span class="input-group-addon fields-tagging-color-preview" style="background:<?= rex_escape($firstColor) ?>;width:30px;"></span>
            <span class="input-group-btn">
                <button type="button" class="btn btn-primary fields-tagging-add-btn"><i class="rex-icon fa-plus"></i> Tag hinzufügen</button>
            </span>
        </div>
        <div class="fields-tagging-suggestions-wrap">
            <div class="fields-tagging-suggestions-label">Vorschläge:</div>
            <div class="fields-tagging-suggestions"><em class="text-muted" style="font-size:12px;">Wird geladen …</em></div>
        </div>
        <div class="fields-tagging-panel-footer">
            <button type="button" class="btn btn-default btn-sm fields-tagging-close-btn"><i class="rex-icon fa-check"></i> Fertig</button>
            <span class="fields-tagging-counter text-muted" style="font-size:12px;margin-left:8px;"><span class="fields-tagging-count"><?= count($tags) ?></span> Tags</span>
        </div>
    </div>
        <input type="hidden" id="<?= rex_escape($fieldId) ?>" name="<?= rex_escape($fieldName) ?>[0]" class="fields-tagging-value" value="<?= rex_escape($fieldValue) ?>">
    </div>
</div>