<?php
/**
 * Star Rating Fragment (Bootstrap 3)
 * @var rex_fragment $this
 */
$max = (int) $this->getVar('max', 5);
$value = (int) $this->getVar('value', 0);
$class = $this->getVar('class', '');
$iconFull = $this->getVar('icon_full', 'fa fa-star');
$iconEmpty = $this->getVar('icon_empty', 'fa fa-star-o');
$color = $this->getVar('color', '#ffc107'); // Default warning color-ish

?>
<div class="fields-rating <?= $class ?>" aria-label="<?= $value ?> von <?= $max ?> Sternen" role="img">
    <?php for ($i = 1; $i <= $max; $i++): ?>
        <?php if ($i <= $value): ?>
            <i class="<?= $iconFull ?>" style="color: <?= $color ?>;" aria-hidden="true"></i>
        <?php else: ?>
            <i class="<?= $iconEmpty ?>" style="color: #ccc;" aria-hidden="true"></i>
        <?php endif; ?>
    <?php endfor; ?>
    <span class="sr-only"><?= $value ?>/<?= $max ?></span>
</div>
