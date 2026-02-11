<?php

/**
 * @var rex_yform_value_fields_social_web $this
 * @psalm-scope-this rex_yform_value_fields_social_web
 */

$entries ??= [];
$platforms ??= rex_yform_value_fields_social_web::getPlatforms();

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

// Plattform-Optionen für Select
$platformOptions = '';
foreach ($platforms as $key => $platform) {
    $platformOptions .= '<option value="' . rex_escape($key) . '">' . rex_escape($platform['label']) . '</option>';
}
?>
<div class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $fieldId ?>"><?= $this->getLabel() ?></label>

    <div class="fields-social-web-repeater" data-field-name="<?= rex_escape($fieldName) ?>">
        <div class="fields-repeater-entries">
            <?php if (count($entries) === 0): ?>
            <div class="fields-repeater-entry panel panel-default" data-index="0">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <select class="form-control fields-social-platform" data-index="0">
                                <option value="">— <?= rex_i18n::msg('fields_social_web_platform') ?> —</option>
                                <?= $platformOptions ?>
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control fields-social-url" data-index="0" placeholder="https://..." />
                        </div>
                        <div class="col-sm-1">
                            <button type="button" class="btn btn-danger btn-xs fields-repeater-remove" title="<?= rex_i18n::msg('fields_social_web_remove') ?>">
                                <i class="rex-icon fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($entries as $i => $entry): ?>
                <div class="fields-repeater-entry panel panel-default" data-index="<?= $i ?>">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <select class="form-control fields-social-platform" data-index="<?= $i ?>">
                                    <option value="">— <?= rex_i18n::msg('fields_social_web_platform') ?> —</option>
                                    <?php foreach ($platforms as $key => $platform): ?>
                                        <option value="<?= rex_escape($key) ?>" <?= ($entry['platform'] ?? '') === $key ? 'selected' : '' ?>>
                                            <?= rex_escape($platform['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="text" class="form-control fields-social-url" data-index="<?= $i ?>"
                                       value="<?= rex_escape($entry['url'] ?? '') ?>"
                                       placeholder="<?= rex_escape($platforms[$entry['platform'] ?? '']['placeholder'] ?? 'https://...') ?>" />
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-danger btn-xs fields-repeater-remove" title="<?= rex_i18n::msg('fields_social_web_remove') ?>">
                                    <i class="rex-icon fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" class="btn btn-default btn-xs fields-repeater-add">
            <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('fields_social_web_add') ?>
        </button>

        <input type="hidden" name="<?= $fieldName ?>" value="<?= rex_escape(json_encode($entries, JSON_UNESCAPED_UNICODE)) ?>" class="fields-social-web-value" />
    </div>

    <?= $noticeHtml ?>
</div>
