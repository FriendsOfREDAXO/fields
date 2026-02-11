<?php

/**
 * @var rex_yform_value_fields_contacts $this
 * @psalm-scope-this rex_yform_value_fields_contacts
 */

$contacts ??= [];
$enabledFields ??= [];
$avatarRatio ??= '1:1';
$mediaCategory ??= 0;

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
$mediaTypes = 'jpg,jpeg,png,gif,webp,svg';

// Einzigartige ID-Basis für Media-Widgets (pro Feld-Instanz)
$widgetIdBase = 'FIELDS_CONTACT_' . $this->getId() . '_';
?>
<div class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label"><?= $this->getLabel() ?></label>

    <div class="fields-contacts-repeater" data-field-name="<?= rex_escape($fieldName) ?>"
         data-enabled-fields="<?= rex_escape(json_encode($enabledFields)) ?>"
         data-avatar-ratio="<?= rex_escape($avatarRatio) ?>"
         data-media-category="<?= $mediaCategory ?>">
        <div class="fields-repeater-entries">
            <?php
            $items = count($contacts) > 0 ? $contacts : [[]];
            foreach ($items as $i => $contact):
                $avatarWidgetId = $widgetIdBase . $i . '_avatar';
                $logoWidgetId = $widgetIdBase . $i . '_logo';
                $avatarCrop = $contact['avatar_crop'] ?? [];
                $logoCrop = $contact['company_logo_crop'] ?? [];
            ?>
            <div class="fields-repeater-entry panel panel-default" data-index="<?= $i ?>">
                <div class="panel-heading fields-repeater-heading">
                    <strong>#<?= $i + 1 ?>
                        <?php if (!empty($contact['firstname']) || !empty($contact['lastname'])): ?>
                            – <?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>
                        <?php endif; ?>
                    </strong>
                    <button type="button" class="btn btn-danger btn-xs fields-repeater-remove" title="<?= rex_i18n::msg('fields_contacts_remove') ?>">
                        <i class="rex-icon fa-trash"></i>
                    </button>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php if (!empty($enabledFields['avatar'])): ?>
                        <div class="col-sm-6">
                            <div class="form-group fields-media-field" data-media-type="avatar">
                                <label><?= rex_i18n::msg('fields_contacts_avatar') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control input-sm fields-contact-avatar fields-media-input"
                                           value="<?= rex_escape($contact['avatar'] ?? '') ?>"
                                           id="<?= $avatarWidgetId ?>" readonly />
                                    <span class="input-group-btn">
                                        <a href="#" class="btn btn-default btn-sm fields-media-open"
                                           data-input-id="<?= $avatarWidgetId ?>"
                                           data-category="<?= $mediaCategory ?>"
                                           data-types="<?= $mediaTypes ?>"
                                           title="<?= rex_i18n::msg('fields_contacts_media_select') ?>">
                                            <i class="rex-icon rex-icon-open-mediapool"></i>
                                        </a>
                                        <a href="#" class="btn btn-default btn-sm fields-media-crop"
                                           data-input-id="<?= $avatarWidgetId ?>"
                                           data-ratio="<?= rex_escape($avatarRatio) ?>"
                                           title="<?= rex_i18n::msg('fields_contacts_crop') ?>">
                                            <i class="rex-icon fa-crop"></i>
                                        </a>
                                        <a href="#" class="btn btn-default btn-sm fields-media-delete"
                                           data-input-id="<?= $avatarWidgetId ?>"
                                           title="<?= rex_i18n::msg('fields_contacts_media_remove') ?>">
                                            <i class="rex-icon rex-icon-delete-media"></i>
                                        </a>
                                    </span>
                                </div>
                                <input type="hidden" class="fields-contact-avatar-crop fields-crop-data"
                                       value="<?= rex_escape(json_encode($avatarCrop)) ?>" />
                                <?php if (!empty($contact['avatar'])): ?>
                                <div class="fields-media-preview" style="margin-top:5px;">
                                    <img src="<?= rex_escape(rex_url::media($contact['avatar'])) ?>"
                                         alt="" style="max-height:80px;<?= !empty($avatarCrop) ? ' object-fit:cover; object-position:' . rex_escape(($avatarCrop['posX'] ?? 50) . '% ' . ($avatarCrop['posY'] ?? 50) . '%') . ';' : '' ?>" />
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($enabledFields['company_logo'])): ?>
                        <div class="col-sm-6">
                            <div class="form-group fields-media-field" data-media-type="company_logo">
                                <label><?= rex_i18n::msg('fields_contacts_company_logo') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control input-sm fields-contact-company-logo fields-media-input"
                                           value="<?= rex_escape($contact['company_logo'] ?? '') ?>"
                                           id="<?= $logoWidgetId ?>" readonly />
                                    <span class="input-group-btn">
                                        <a href="#" class="btn btn-default btn-sm fields-media-open"
                                           data-input-id="<?= $logoWidgetId ?>"
                                           data-category="<?= $mediaCategory ?>"
                                           data-types="<?= $mediaTypes ?>"
                                           title="<?= rex_i18n::msg('fields_contacts_media_select') ?>">
                                            <i class="rex-icon rex-icon-open-mediapool"></i>
                                        </a>
                                        <a href="#" class="btn btn-default btn-sm fields-media-delete"
                                           data-input-id="<?= $logoWidgetId ?>"
                                           title="<?= rex_i18n::msg('fields_contacts_media_remove') ?>">
                                            <i class="rex-icon rex-icon-delete-media"></i>
                                        </a>
                                    </span>
                                </div>
                                <input type="hidden" class="fields-contact-company-logo-crop fields-crop-data"
                                       value="<?= rex_escape(json_encode($logoCrop)) ?>" />
                                <?php if (!empty($contact['company_logo'])): ?>
                                <div class="fields-media-preview" style="margin-top:5px;">
                                    <img src="<?= rex_escape(rex_url::media($contact['company_logo'])) ?>"
                                         alt="" style="max-height:40px;" />
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <?php if (!empty($enabledFields['company'])): ?>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_company') ?></label>
                                <input type="text" class="form-control input-sm fields-contact-company"
                                       value="<?= rex_escape($contact['company'] ?? '') ?>" />
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($enabledFields['function'])): ?>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_function') ?></label>
                                <input type="text" class="form-control input-sm fields-contact-function"
                                       value="<?= rex_escape($contact['function'] ?? '') ?>" />
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_firstname') ?> *</label>
                                <input type="text" class="form-control input-sm fields-contact-firstname"
                                       value="<?= rex_escape($contact['firstname'] ?? '') ?>" />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_lastname') ?> *</label>
                                <input type="text" class="form-control input-sm fields-contact-lastname"
                                       value="<?= rex_escape($contact['lastname'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php if (!empty($enabledFields['phone'])): ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_phone') ?></label>
                                <input type="tel" class="form-control input-sm fields-contact-phone"
                                       value="<?= rex_escape($contact['phone'] ?? '') ?>" />
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($enabledFields['mobile'])): ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_mobile') ?></label>
                                <input type="tel" class="form-control input-sm fields-contact-mobile"
                                       value="<?= rex_escape($contact['mobile'] ?? '') ?>" />
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($enabledFields['email'])): ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_email') ?></label>
                                <input type="email" class="form-control input-sm fields-contact-email"
                                       value="<?= rex_escape($contact['email'] ?? '') ?>" />
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($enabledFields['address'])): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_address_street') ?></label>
                                <input type="text" class="form-control input-sm fields-contact-street"
                                       value="<?= rex_escape($contact['street'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_address_zip') ?></label>
                                <input type="text" class="form-control input-sm fields-contact-zip"
                                       value="<?= rex_escape($contact['zip'] ?? '') ?>" />
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_address_city') ?></label>
                                <input type="text" class="form-control input-sm fields-contact-city"
                                       value="<?= rex_escape($contact['city'] ?? '') ?>" />
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_address_country') ?></label>
                                <input type="text" class="form-control input-sm fields-contact-country"
                                       value="<?= rex_escape($contact['country'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($enabledFields['homepage'])): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label><?= rex_i18n::msg('fields_contacts_homepage') ?></label>
                                <input type="url" class="form-control input-sm fields-contact-homepage"
                                       value="<?= rex_escape($contact['homepage'] ?? '') ?>" placeholder="https://..." />
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($enabledFields['social'])): ?>
                    <div class="form-group">
                        <label><?= rex_i18n::msg('fields_contacts_social') ?></label>
                        <div class="fields-contact-social-entries">
                            <?php foreach ($contact['social'] ?? [] as $si => $social): ?>
                            <div class="form-inline fields-contact-social-entry" style="margin-bottom: 5px;">
                                <input type="text" class="form-control input-sm" style="width: 120px;"
                                       value="<?= rex_escape($social['platform'] ?? '') ?>" placeholder="Plattform" />
                                <input type="url" class="form-control input-sm" style="width: calc(100% - 180px);"
                                       value="<?= rex_escape($social['url'] ?? '') ?>" placeholder="URL" />
                                <button type="button" class="btn btn-danger btn-xs fields-contact-social-remove">
                                    <i class="rex-icon fa-minus"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-default btn-xs fields-contact-social-add">
                            <i class="rex-icon fa-plus"></i> Social Web
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-default btn-xs fields-repeater-add">
            <i class="rex-icon fa-plus"></i> <?= rex_i18n::msg('fields_contacts_add') ?>
        </button>

        <input type="hidden" name="<?= $fieldName ?>" value="<?= rex_escape(json_encode($contacts, JSON_UNESCAPED_UNICODE)) ?>" class="fields-contacts-value" />
    </div>

    <?= $noticeHtml ?>
</div>
