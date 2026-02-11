<?php

/**
 * Fragment: Opening Hours – Bootstrap 3
 *
 * Variablen:
 * - helper: OpeningHoursHelper Instanz
 * - show_status: bool (aktuellen Status anzeigen), default: true
 * - grouped: bool (Tage gruppieren), default: true
 * - show_special: bool (Sonderzeiten anzeigen), default: true
 *
 * Beispiel:
 * $helper = new \FriendsOfRedaxo\Fields\OpeningHoursHelper($json);
 * $fragment = new rex_fragment();
 * $fragment->setVar('helper', $helper);
 * echo $fragment->parse('fields/bootstrap3/opening_hours.php');
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
        <div class="alert alert-<?= $status['is_open'] ? 'success' : 'danger' ?> alert-sm">
            <strong><?= rex_escape($status['label']) ?></strong>
            <?php if ($status['next_change_label']): ?>
                <br /><small><?= rex_escape($status['next_change_label']) ?></small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($grouped): ?>
        <?php $groups = $helper->getRegularGrouped(); ?>
        <table class="table table-condensed">
            <?php foreach ($groups as $group): ?>
                <tr class="<?= $group['contains_today'] ? 'active' : '' ?>">
                    <td><strong><?= rex_escape($group['label']) ?></strong></td>
                    <td><?= rex_escape($group['formatted']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <?php $days = $helper->getRegular(); ?>
        <table class="table table-condensed">
            <?php foreach ($days as $day): ?>
                <tr class="<?= $day['is_today'] ? 'active' : '' ?>">
                    <td><strong><?= rex_escape($day['label_short']) ?></strong></td>
                    <td><?= rex_escape($day['formatted']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($showSpecial): ?>
        <?php $specials = $helper->getSpecial(null, true); ?>
        <?php if (count($specials) > 0): ?>
            <h5><?= $helper->translate('labels.special_hours') ?></h5>
            <table class="table table-condensed table-sm">
                <?php foreach ($specials as $special): ?>
                    <tr>
                        <td><?= rex_escape($special['display_name']) ?></td>
                        <td><small class="text-muted"><?= rex_escape($special['date_formatted']) ?></small></td>
                        <td><?= rex_escape($special['formatted']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($helper->hasNote()): ?>
        <p class="text-muted"><small><?= rex_escape($helper->getNote()) ?></small></p>
    <?php endif; ?>
</div>
