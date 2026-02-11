<?php

/**
 * Fragment: Opening Hours – Tailwind CSS
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
        <div class="rounded-lg px-4 py-3 mb-4 <?= $status['is_open'] ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
            <strong class="font-semibold"><?= rex_escape($status['label']) ?></strong>
            <?php if ($status['next_change_label']): ?>
                <br /><span class="text-sm"><?= rex_escape($status['next_change_label']) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($grouped): ?>
        <?php $groups = $helper->getRegularGrouped(); ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($groups as $group): ?>
                <div class="flex justify-between py-2 <?= $group['contains_today'] ? 'bg-yellow-50 px-2 -mx-2 rounded' : '' ?>">
                    <span class="font-medium text-gray-900"><?= rex_escape($group['label']) ?></span>
                    <span class="text-gray-600"><?= rex_escape($group['formatted']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php $days = $helper->getRegular(); ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($days as $day): ?>
                <div class="flex justify-between py-2 <?= $day['is_today'] ? 'bg-yellow-50 px-2 -mx-2 rounded' : '' ?>">
                    <span class="font-medium text-gray-900"><?= rex_escape($day['label_short']) ?></span>
                    <span class="text-gray-600"><?= rex_escape($day['formatted']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($showSpecial): ?>
        <?php $specials = $helper->getSpecial(null, true); ?>
        <?php if (count($specials) > 0): ?>
            <h5 class="text-lg font-semibold mt-6 mb-3 text-gray-900"><?= $helper->translate('labels.special_hours') ?></h5>
            <div class="divide-y divide-gray-100">
                <?php foreach ($specials as $special): ?>
                    <div class="flex justify-between py-2">
                        <div>
                            <span class="font-medium"><?= rex_escape($special['display_name']) ?></span>
                            <span class="text-sm text-gray-400 ml-2"><?= rex_escape($special['date_formatted']) ?></span>
                        </div>
                        <span class="text-gray-600"><?= rex_escape($special['formatted']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($helper->hasNote()): ?>
        <p class="text-sm text-gray-500 mt-4"><?= rex_escape($helper->getNote()) ?></p>
    <?php endif; ?>
</div>
