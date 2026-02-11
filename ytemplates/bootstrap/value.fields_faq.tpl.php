<?php

/**
 * @var rex_yform_value_fields_faq $this
 * @psalm-scope-this rex_yform_value_fields_faq
 */

$entries ??= [];

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
?>
<div class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label"><?= $this->getLabel() ?></label>

    <div class="fields-faq-repeater" data-field-name="<?= rex_escape($fieldName) ?>">
        <div class="fields-repeater-entries">
            <?php
            $items = count($entries) > 0 ? $entries : [['question' => '', 'answer' => '']];
            foreach ($items as $i => $entry):
            ?>
            <div class="fields-repeater-entry panel panel-default" data-index="<?= $i ?>">
                <div class="panel-heading">
                    <div class="pull-right">
                        <button type="button" class="btn btn-danger btn-xs fields-repeater-remove" title="<?= rex_i18n::msg('fields_faq_remove') ?>">
                            <i class="rex-icon fa-trash"></i>
                        </button>
                    </div>
                    <strong>#<?= $i + 1 ?></strong>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label><?= rex_i18n::msg('fields_faq_question') ?></label>
                        <input type="text" class="form-control fields-faq-question"
                               value="<?= rex_escape($entry['question'] ?? '') ?>"
                               placeholder="<?= rex_i18n::msg('fields_faq_question') ?>" />
                    </div>
                    <div class="form-group">
                        <label><?= rex_i18n::msg('fields_faq_answer') ?></label>
                        <textarea class="form-control fields-faq-answer" rows="3"
                                  placeholder="<?= rex_i18n::msg('fields_faq_answer') ?>"><?= rex_escape($entry['answer'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-default btn-xs fields-repeater-add">
            <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('fields_faq_add') ?>
        </button>

        <input type="hidden" name="<?= $fieldName ?>" value="<?= rex_escape(json_encode($entries, JSON_UNESCAPED_UNICODE)) ?>" class="fields-faq-value" />
    </div>

    <?= $noticeHtml ?>
</div>
