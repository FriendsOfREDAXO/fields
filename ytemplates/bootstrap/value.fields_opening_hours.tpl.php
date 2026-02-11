<?php

/**
 * @var rex_yform_value_fields_opening_hours $this
 * @psalm-scope-this rex_yform_value_fields_opening_hours
 */

$data ??= ['regular' => [], 'special' => [], 'note' => ''];
$weekdays ??= ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

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

    <div class="fields-opening-hours" data-field-name="<?= rex_escape($fieldName) ?>">
        <!-- Reguläre Öffnungszeiten -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><?= rex_i18n::msg('fields_opening_hours_regular') ?></strong>
            </div>
            <div class="panel-body">
                <table class="table table-condensed fields-oh-table">
                    <thead>
                        <tr>
                            <th style="width:120px;">Tag</th>
                            <th style="width:120px;">Status</th>
                            <th>Zeiten</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($weekdays as $day): ?>
                        <?php
                        $dayData = $data['regular'][$day] ?? ['status' => 'closed', 'times' => []];
                        $status = $dayData['status'] ?? 'closed';
                        $times = $dayData['times'] ?? [];
                        ?>
                        <tr class="fields-oh-day" data-day="<?= $day ?>">
                            <td>
                                <strong><?= rex_i18n::msg('fields_opening_hours_' . $day) ?></strong>
                            </td>
                            <td>
                                <select class="form-control input-sm fields-oh-status" data-day="<?= $day ?>">
                                    <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>><?= rex_i18n::msg('fields_opening_hours_status_closed') ?></option>
                                    <option value="open" <?= $status === 'open' ? 'selected' : '' ?>><?= rex_i18n::msg('fields_opening_hours_status_open') ?></option>
                                    <option value="24h" <?= $status === '24h' ? 'selected' : '' ?>><?= rex_i18n::msg('fields_opening_hours_status_24h') ?></option>
                                </select>
                            </td>
                            <td>
                                <div class="fields-oh-times" data-day="<?= $day ?>" style="<?= $status !== 'open' ? 'display:none' : '' ?>">
                                    <?php if (count($times) === 0): ?>
                                    <div class="fields-oh-timeslot" data-index="0">
                                        <div class="form-inline">
                                            <input type="time" class="form-control input-sm fields-oh-open" value="09:00" />
                                            <span> – </span>
                                            <input type="time" class="form-control input-sm fields-oh-close" value="17:00" />
                                            <button type="button" class="btn btn-danger btn-xs fields-oh-remove-time" title="Entfernen">
                                                <i class="rex-icon fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                        <?php foreach ($times as $ti => $slot): ?>
                                        <div class="fields-oh-timeslot" data-index="<?= $ti ?>">
                                            <div class="form-inline">
                                                <input type="time" class="form-control input-sm fields-oh-open" value="<?= rex_escape($slot['open'] ?? '09:00') ?>" />
                                                <span> – </span>
                                                <input type="time" class="form-control input-sm fields-oh-close" value="<?= rex_escape($slot['close'] ?? '17:00') ?>" />
                                                <button type="button" class="btn btn-danger btn-xs fields-oh-remove-time" title="Entfernen">
                                                    <i class="rex-icon fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-default btn-xs fields-oh-add-time" data-day="<?= $day ?>">
                                        <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('fields_opening_hours_add_time') ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sonderöffnungszeiten -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><?= rex_i18n::msg('fields_opening_hours_special') ?></strong>
            </div>
            <div class="panel-body">
                <div class="fields-oh-special-entries">
                    <?php foreach ($data['special'] ?? [] as $si => $special): ?>
                    <div class="fields-oh-special-entry panel panel-default" data-index="<?= $si ?>">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <input type="text" class="form-control input-sm fields-oh-special-date"
                                           value="<?= rex_escape($special['date'] ?? '') ?>"
                                           placeholder="<?= rex_i18n::msg('fields_opening_hours_special_date') ?> (YYYY-MM-DD)" />
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control input-sm fields-oh-special-name"
                                           value="<?= rex_escape($special['name'] ?? '') ?>"
                                           placeholder="<?= rex_i18n::msg('fields_opening_hours_special_name') ?>" />
                                </div>
                                <div class="col-sm-2">
                                    <select class="form-control input-sm fields-oh-special-status">
                                        <option value="closed" <?= ($special['status'] ?? 'closed') === 'closed' ? 'selected' : '' ?>><?= rex_i18n::msg('fields_opening_hours_status_closed') ?></option>
                                        <option value="open" <?= ($special['status'] ?? '') === 'open' ? 'selected' : '' ?>><?= rex_i18n::msg('fields_opening_hours_status_open') ?></option>
                                    </select>
                                </div>
                                <div class="col-sm-3 fields-oh-special-times" style="<?= ($special['status'] ?? 'closed') !== 'open' ? 'display:none' : '' ?>">
                                    <?php foreach ($special['times'] ?? [] as $slot): ?>
                                    <div class="form-inline">
                                        <input type="time" class="form-control input-sm" value="<?= rex_escape($slot['open'] ?? '') ?>" />
                                        <span> – </span>
                                        <input type="time" class="form-control input-sm" value="<?= rex_escape($slot['close'] ?? '') ?>" />
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-danger btn-xs fields-oh-special-remove">
                                        <i class="rex-icon fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-default btn-xs fields-oh-special-add">
                    <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('fields_opening_hours_add_special') ?>
                </button>
            </div>
        </div>

        <!-- Hinweis -->
        <div class="form-group">
            <label><?= rex_i18n::msg('fields_opening_hours_note') ?></label>
            <input type="text" class="form-control fields-oh-note"
                   value="<?= rex_escape($data['note'] ?? '') ?>"
                   placeholder="<?= rex_i18n::msg('fields_opening_hours_note_placeholder') ?>" />
        </div>

        <input type="hidden" name="<?= $fieldName ?>" value="<?= rex_escape(json_encode($data, JSON_UNESCAPED_UNICODE)) ?>" class="fields-opening-hours-value" />
    </div>

    <?= $noticeHtml ?>
</div>
