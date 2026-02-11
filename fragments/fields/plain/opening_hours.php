<?php

/**
 * Fragment: Opening Hours – Plain (framework-unabhängig)
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
<div class="fields-opening-hours" itemscope itemtype="https://schema.org/LocalBusiness">
    <?php if ($showStatus): ?>
        <?php $status = $helper->getCurrentStatus(); ?>
        <p class="fields-oh-status fields-oh-status--<?= $status['is_open'] ? 'open' : 'closed' ?>">
            <strong><?= rex_escape($status['label']) ?></strong>
            <?php if ($status['next_change_label']): ?>
                <br /><span><?= rex_escape($status['next_change_label']) ?></span>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <h3><?= $helper->translate('labels.opening_hours') ?></h3>

    <?php if ($grouped): ?>
        <?php $groups = $helper->getRegularGrouped(); ?>
        <dl class="fields-oh-list">
            <?php foreach ($groups as $group): ?>
                <div class="fields-oh-entry <?= $group['contains_today'] ? 'fields-oh-entry--today' : '' ?>" itemprop="openingHoursSpecification" itemscope itemtype="https://schema.org/OpeningHoursSpecification">
                    <dt><?= rex_escape($group['label']) ?></dt>
                    <dd><?= rex_escape($group['formatted']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php else: ?>
        <?php $days = $helper->getRegular(); ?>
        <dl class="fields-oh-list">
            <?php foreach ($days as $day): ?>
                <div class="fields-oh-entry <?= $day['is_today'] ? 'fields-oh-entry--today' : '' ?>">
                    <dt><?= rex_escape($day['label_short']) ?></dt>
                    <dd><?= rex_escape($day['formatted']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>

    <?php if ($showSpecial): ?>
        <?php $specials = $helper->getSpecial(null, true); ?>
        <?php if (count($specials) > 0): ?>
            <h4><?= $helper->translate('labels.special_hours') ?></h4>
            <dl class="fields-oh-special-list">
                <?php foreach ($specials as $special): ?>
                    <div class="fields-oh-special-entry">
                        <dt><?= rex_escape($special['display_name']) ?> <small>(<?= rex_escape($special['date_formatted']) ?>)</small></dt>
                        <dd><?= rex_escape($special['formatted']) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($helper->hasNote()): ?>
        <p class="fields-oh-note"><?= rex_escape($helper->getNote()) ?></p>
    <?php endif; ?>
</div>
