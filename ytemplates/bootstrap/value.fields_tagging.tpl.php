<?php

/**
 * @var rex_yform_value_fields_tagging $this
 * @psalm-scope-this rex_yform_value_fields_tagging
 */

$value ??= $this->getValue();
$tags ??= [];
$source_table ??= '';
$source_field ??= '';
$max_tags ??= 0;

$notice = [];
if ('' !== $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()]) . '</span>';
}
$noticeHtml = count($notice) > 0 ? '<p class="help-block small">' . implode('<br />', $notice) . '</p>' : '';

$classGroup = trim('form-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass());
$fieldName = $this->getFieldName();
$fieldId = $this->getFieldId();
$apiUrl = rex_url::backendController(['rex-api-call' => 'fields_tagging_suggest']);
?>
<div class="<?= $classGroup ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $fieldId ?>"><?= $this->getLabel() ?></label>

    <div class="fields-tagging" data-field-name="<?= rex_escape($fieldName) ?>" data-api-url="<?= rex_escape($apiUrl) ?>" data-source-table="<?= rex_escape((string) $source_table) ?>" data-source-field="<?= rex_escape((string) $source_field) ?>" data-max-tags="<?= (int) $max_tags ?>">
        <div class="fields-tagging-tags" aria-live="polite">
            <?php foreach ($tags as $tag): ?>
                <span class="label label-primary fields-tagging-tag" data-tag="<?= rex_escape($tag) ?>">
                    <?= rex_escape($tag) ?>
                    <button type="button" class="fields-tagging-remove" aria-label="<?= rex_i18n::msg('fields_tagging_remove') ?>">&times;</button>
                </span>
            <?php endforeach; ?>
        </div>

        <div class="input-group" style="margin-top:8px;">
            <input type="text" class="form-control fields-tagging-input" id="<?= $fieldId ?>_input" list="<?= $fieldId ?>_suggestions" placeholder="<?= rex_i18n::msg('fields_tagging_placeholder') ?>" autocomplete="off">
            <datalist id="<?= $fieldId ?>_suggestions"></datalist>
            <span class="input-group-btn">
                <button class="btn btn-default fields-tagging-add" type="button"><?= rex_i18n::msg('fields_tagging_add') ?></button>
            </span>
        </div>

        <input type="hidden" name="<?= $fieldName ?>" value="<?= rex_escape((string) $value) ?>" class="fields-tagging-value">
    </div>

    <?= $noticeHtml ?>
</div>
