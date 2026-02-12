<?php
/**
 * Star Rating Fragment (UIkit 3)
 * @var rex_fragment $this
 */
$max = (int) $this->getVar('max', 5);
$value = (int) $this->getVar('value', 0);
$class = $this->getVar('class', '');
$iconFull = $this->getVar('icon_full', 'star');
$iconEmpty = $this->getVar('icon_empty', 'star'); // UIkit often uses same icon but different style or opacity
// UIkit specific: we might need filled vs outlined icons if available, 
// but standard uikit icons package has only 'star'. 
// Often users use FontAwesome with UIkit, but here we assume uikit icons.

?>
<div class="fields-rating uk-flex uk-flex-middle <?= $class ?>" aria-label="<?= $value ?> von <?= $max ?> Sternen" role="img">
    <?php for ($i = 1; $i <= $max; $i++): ?>
        <?php if ($i <= $value): ?>
            <span uk-icon="<?= $iconFull ?>" class="uk-text-warning"></span>
        <?php else: ?>
            <span uk-icon="<?= $iconEmpty ?>" class="uk-text-muted"></span>
        <?php endif; ?>
    <?php endfor; ?>
</div>
