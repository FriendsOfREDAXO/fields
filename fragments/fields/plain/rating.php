<?php
/**
 * Star Rating Fragment (Plain HTML / CSS)
 * @var rex_fragment $this
 */
$max = (int) $this->getVar('max', 5);
$value = (int) $this->getVar('value', 0);
$class = $this->getVar('class', '');
$charFull = $this->getVar('char_full', '★');
$charEmpty = $this->getVar('char_empty', '☆');
?>
<span class="fields-rating <?= $class ?>" aria-label="<?= $value ?> of <?= $max ?> stars" role="img">
    <?php for ($i = 1; $i <= $max; $i++): ?>
        <span class="star <?= ($i <= $value) ? 'filled' : 'empty' ?>">
            <?= ($i <= $value) ? $charFull : $charEmpty ?>
        </span>
    <?php endfor; ?>
</span>

<style>
.fields-rating .star.filled { color: gold; }
.fields-rating .star.empty { color: #ccc; }
</style>
