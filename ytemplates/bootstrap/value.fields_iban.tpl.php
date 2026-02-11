<?php

/**
 * @var rex_yform_value_fields_iban $this
 * @psalm-scope-this rex_yform_value_fields_iban
 */

$value ??= $this->getValue();

$notice = [];
if ('' != $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()]) . '</span>';
}
$noticeHtml = count($notice) > 0 ? '<p class="help-block small">' . implode('<br />', $notice) . '</p>' : '';

$class_group = trim('form-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass());
$fieldName = $this->getFieldName();
$fieldId = $this->getFieldId();

// API-URL für Proxy
$apiUrl = rex_url::backendController(['rex-api-call' => 'fields_iban_validate']);
?>
<div class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $fieldId ?>"><?= $this->getLabel() ?></label>

    <div class="input-group fields-iban-wrapper" data-api-url="<?= rex_escape($apiUrl) ?>">
        <input type="text"
               class="form-control fields-iban-input"
               name="<?= $fieldName ?>"
               id="<?= $fieldId ?>"
               value="<?= rex_escape($value) ?>"
               placeholder="<?= rex_i18n::msg('fields_iban_placeholder') ?>"
               maxlength="34"
               autocomplete="off" />
        <span class="input-group-addon fields-iban-status">
            <i class="rex-icon fa-question-circle text-muted"></i>
        </span>
    </div>

    <div class="fields-iban-result" style="display:none; margin-top: 5px;">
        <small>
            <span class="fields-iban-bank"></span>
            <span class="fields-iban-bic"></span>
        </small>
    </div>

    <?= $noticeHtml ?>
</div>
