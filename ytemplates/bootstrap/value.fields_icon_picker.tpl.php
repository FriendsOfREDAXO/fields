<?php

/**
 * @var rex_yform_value_fields_icon_picker $this
 * @psalm-scope-this rex_yform_value_fields_icon_picker
 */

$value ??= $this->getValue();
$enabledSets ??= ['fontawesome', 'uikit'];

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
?>
<div class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $fieldId ?>"><?= $this->getLabel() ?></label>

    <div class="fields-icon-picker" data-enabled-sets="<?= rex_escape(json_encode($enabledSets)) ?>">
        <div class="input-group">
            <span class="input-group-addon fields-icon-preview">
                <?php if (!empty($value)): ?>
                    <i class="<?= rex_escape($value) ?>"></i>
                <?php else: ?>
                    <i class="rex-icon fa-image text-muted"></i>
                <?php endif; ?>
            </span>
            <input type="text"
                   class="form-control fields-icon-value"
                   name="<?= $fieldName ?>"
                   id="<?= $fieldId ?>"
                   value="<?= rex_escape($value) ?>"
                   readonly />
            <span class="input-group-btn">
                <button type="button" class="btn btn-default fields-icon-pick" title="<?= rex_i18n::msg('fields_icon_picker') ?>">
                    <i class="rex-icon fa-search"></i>
                </button>
                <?php if (!empty($value)): ?>
                <button type="button" class="btn btn-default fields-icon-clear" title="<?= rex_i18n::msg('fields_icon_picker_clear') ?>">
                    <i class="rex-icon fa-times"></i>
                </button>
                <?php endif; ?>
            </span>
        </div>

        <!-- Icon-Auswahl Modal (wird per JS befüllt) -->
        <div class="fields-icon-modal" style="display:none;">
            <div class="panel panel-default" style="margin-top: 10px;">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" class="form-control input-sm fields-icon-search"
                                   placeholder="<?= rex_i18n::msg('fields_icon_picker_search') ?>" />
                        </div>
                        <div class="col-sm-6">
                            <div class="btn-group btn-group-sm">
                                <?php if (in_array('fontawesome', $enabledSets, true)): ?>
                                <button type="button" class="btn btn-default active fields-icon-tab" data-set="fontawesome">
                                    <?= rex_i18n::msg('fields_icon_picker_fontawesome') ?>
                                </button>
                                <?php endif; ?>
                                <?php if (in_array('uikit', $enabledSets, true)): ?>
                                <button type="button" class="btn btn-default fields-icon-tab" data-set="uikit">
                                    <?= rex_i18n::msg('fields_icon_picker_uikit') ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body fields-icon-grid" style="max-height: 300px; overflow-y: auto;">
                    <!-- Icons werden per JS geladen -->
                </div>
            </div>
        </div>
    </div>

    <?= $noticeHtml ?>
</div>
