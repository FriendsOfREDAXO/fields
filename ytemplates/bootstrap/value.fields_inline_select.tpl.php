<?php

/**
 * @var rex_yform_value_fields_inline_select $this
 * @psalm-scope-this rex_yform_value_fields_inline_select
 */

$options ??= [];
$colors ??= [];
$is_locked = (bool) ($is_locked ?? false);

$notice = [];
if ('' != $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()], false) . '</span>';
}
$noticeHtml = count($notice) > 0 ? '<p class="help-block small">' . implode('<br />', $notice) . '</p>' : '';

$class = $this->getElement('required') ? 'form-is-required ' : '';
$classGroup = trim('form-group ' . $class . $this->getWarningClass());

$attributes = [];
$attributes['class'] = 'form-control selectpicker js-fields-inline-select js-fields-inline-select-form';
$attributes['id'] = $this->getFieldId();
$attributes['name'] = $this->getFieldName();
$attributes['data-width'] = '100%';
$attributes['data-live-search'] = 'true';
$attributes['data-container'] = 'body';
$attributes = $this->getAttributeElements($attributes, ['autocomplete', 'pattern', 'required', 'disabled', 'readonly']);

$currentValue = (string) $this->getValue();
$lockedLabel = $options[$currentValue] ?? $currentValue;
?>
<div class="<?= $classGroup ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $this->getFieldId() ?>"><?= $this->getLabel() ?></label>
    <?php if ($is_locked): ?>
        <?php $lockColor = (string) ($colors[$currentValue] ?? ''); ?>
        <input type="hidden" name="<?= $this->getFieldName() ?>" value="<?= rex_escape($currentValue) ?>" />
        <span class="fields-inline-select-lock-badge">
            <?= rex_yform_value_fields_inline_select::renderColorDot($lockColor) ?>
            <span class="fields-inline-select-lock-label"><?= rex_escape((string) $lockedLabel) ?></span>
        </span>
        <p class="help-block small"><?= rex_i18n::msg('fields_inline_select_locked') ?></p>
    <?php else: ?>
        <select <?= implode(' ', $attributes) ?>>
            <?php foreach ($options as $key => $label): ?>
                <?php
                $key = (string) $key;
                $label = (string) $label;
                $selected = ($key === $currentValue) ? ' selected="selected"' : '';
                $color = (string) ($colors[$key] ?? '');
                $optionContent = rex_yform_value_fields_inline_select::renderOptionContent($label, $color);
                ?>
                <option value="<?= rex_escape($key) ?>" data-content="<?= rex_escape($optionContent) ?>"<?= $selected ?>><?= rex_escape($label) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <?= $noticeHtml ?>
</div>
