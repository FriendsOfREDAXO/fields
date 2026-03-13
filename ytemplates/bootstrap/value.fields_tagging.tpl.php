<?php

/**
 * @var rex_yform_value_fields_tagging $this
 * @psalm-scope-this rex_yform_value_fields_tagging
 */

$value        ??= '';
$tags         ??= [];
$source_table ??= '';
$source_field ??= '';
$max_tags     ??= 0;
$colors       ??= rex_yform_value_fields_tagging::DEFAULT_COLORS;

$notice = [];
if ('' !== $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()]) . '</span>';
}
$noticeHtml = $notice !== [] ? '<p class="help-block small">' . implode('<br>', $notice) . '</p>' : '';

$classGroup = trim('form-group fields-tagging-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass());
$fieldName  = $this->getFieldName();
$widgetId   = $this->getFieldId();
$apiUrl     = rex_url::backendController(['rex-api-call' => 'fields_tagging_suggest']);
?>
<div class="<?= $classGroup ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label"><?= $this->getLabel() ?></label>

    <div class="fields-tagging-widget"
         id="<?= $widgetId ?>"
         data-api-url="<?= rex_escape($apiUrl) ?>"
         data-source-table="<?= rex_escape($source_table) ?>"
         data-source-field="<?= rex_escape($source_field) ?>"
         data-max-tags="<?= (int) $max_tags ?>"
         data-colors="<?= rex_escape((string) json_encode($colors)) ?>">

        <!-- Chip-Anzeige + Edit-Button -->
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

        <!-- Editor-Panel -->
        <div class="fields-tagging-panel" style="display:none">

            <!-- Farbpalette -->
            <div class="fields-tagging-palette">
                <span class="fields-tagging-palette-label">Farbe:</span>
                <?php foreach ($colors as $i => $hex): ?>
                    <button type="button"
                            class="fields-tagging-color-btn<?= $i === 0 ? ' active' : '' ?>"
                            data-color="<?= rex_escape($hex) ?>"
                            style="background:<?= rex_escape($hex) ?>"
                            title="<?= rex_escape($hex) ?>"
                            aria-label="Farbe <?= rex_escape($hex) ?>"></button>
                <?php endforeach; ?>
                <span class="fields-tagging-palette-sep"></span>
                <input type="color"
                       class="fields-tagging-custom-color"
                       value="<?= rex_escape($colors[0]) ?>"
                       title="Eigene Farbe (nur dunkle Farben für weiße Schrift)">
                <span class="fields-tagging-contrast-hint" style="display:none">&#9888; Zu hell für weiße Schrift</span>
            </div>

            <!-- Eingabe -->
            <div class="input-group fields-tagging-input-group">
                <input type="text"
                       class="form-control fields-tagging-input"
                       placeholder="Neuen Tag eingeben …"
                       autocomplete="off">
                <span class="input-group-addon fields-tagging-color-preview" style="background:<?= rex_escape($colors[0]) ?>;width:30px;"></span>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-primary fields-tagging-add-btn">
                        <i class="rex-icon fa-plus"></i> Hinzufügen
                    </button>
                </span>
            </div>

            <!-- Vorschläge -->
            <div class="fields-tagging-suggestions-wrap">
                <div class="fields-tagging-suggestions-label">Vorhandene Tags:</div>
                <div class="fields-tagging-suggestions">
                    <em class="text-muted" style="font-size:12px;">Wird geladen …</em>
                </div>
            </div>

            <div class="fields-tagging-panel-footer">
                <button type="button" class="btn btn-default btn-sm fields-tagging-close-btn">
                    <i class="rex-icon fa-check"></i> Fertig
                </button>
                <?php if ($max_tags > 0): ?>
                    <span class="fields-tagging-counter text-muted" style="font-size:12px;margin-left:8px;">
                        <span class="fields-tagging-count"><?= count($tags) ?></span> / <?= $max_tags ?> Tags
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <input type="hidden"
               name="<?= rex_escape($fieldName) ?>"
               value="<?= rex_escape($value) ?>"
               class="fields-tagging-value">
    </div>

    <?= $noticeHtml ?>
</div>
