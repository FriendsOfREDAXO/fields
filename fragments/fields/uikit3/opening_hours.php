<?php

/**
 * Fragment: Opening Hours – UIkit 3
 */

use FriendsOfRedaxo\Fields\OpeningHoursHelper;

/** @var OpeningHoursHelper $helper */
$helper = $this->getVar('helper');
$showStatus = $this->getVar('show_status', true);
$grouped = $this->getVar('grouped', true);
$showSpecial = $this->getVar('show_special', true);

if (!$helper instanceof OpeningHoursHelper || !$helper->hasData()) {
    return;
}
?>
<div class="fields-opening-hours-output">
    <?php if ($showStatus): ?>
        <?php $status = $helper->getCurrentStatus(); ?>
        <div class="uk-alert uk-alert-<?= $status['is_open'] ? 'success' : 'danger' ?>">
            <strong><?= rex_escape($status['label']) ?></strong>
            <?php if ($status['next_change_label']): ?>
                <br /><span class="uk-text-small"><?= rex_escape($status['next_change_label']) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($grouped): ?>
        <?php $groups = $helper->getRegularGrouped(); ?>
        <table class="uk-table uk-table-small uk-table-divider">
            <tbody>
            <?php foreach ($groups as $group): ?>
                <tr class="<?= $group['contains_today'] ? 'uk-active' : '' ?>">
                    <td class="uk-text-bold uk-width-1-3"><?= rex_escape($group['label']) ?></td>
                    <td><?= rex_escape($group['formatted']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?php $days = $helper->getRegular(); ?>
        <table class="uk-table uk-table-small uk-table-divider">
            <tbody>
            <?php foreach ($days as $day): ?>
                <tr class="<?= $day['is_today'] ? 'uk-active' : '' ?>">
                    <td class="uk-text-bold uk-width-1-3"><?= rex_escape($day['label_short']) ?></td>
                    <td><?= rex_escape($day['formatted']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($showSpecial): ?>
        <?php $specials = $helper->getSpecial(null, true); ?>
        <?php if (count($specials) > 0): ?>
            <h5 class="uk-heading-line"><span><?= $helper->translate('labels.special_hours') ?></span></h5>
            <dl class="uk-description-list">
                <?php foreach ($specials as $special): ?>
                    <dt><?= rex_escape($special['display_name']) ?> <span class="uk-text-muted uk-text-small"><?= rex_escape($special['date_formatted']) ?></span></dt>
                    <dd><?= rex_escape($special['formatted']) ?></dd>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($helper->hasNote()): ?>
        <p class="uk-text-muted uk-text-small"><?= rex_escape($helper->getNote()) ?></p>
    <?php endif; ?>
</div>
