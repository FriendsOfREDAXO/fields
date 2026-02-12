<?php

/**
 * @var rex_yform_value_fields_rating $this
 * @psalm-scope-this rex_yform_value_fields_rating
 */

$stars = (int) $this->getElement('max_stars');
if ($stars < 1) {
    $stars = 5;
}
$value = (int) $this->getValue();
$name = $this->getFieldName();
$id = $this->getFieldId(); // ID-Prefix
$notice = $this->getElement('notice');

?>
<div class="<?= $this->getHTMLClass() ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label"><?= $this->getLabel() ?></label>
    <div class="fields-rating-container">
        <div class="fields-rating-group">
            <?php for ($i = $stars; $i >= 1; $i--): ?>
                <?php
                $inputId = $id . '-star-' . $i;
                ?>
                <input type="radio" id="<?= $inputId ?>" name="<?= $name ?>" value="<?= $i ?>" <?= ($value == $i) ? 'checked' : '' ?> />
                <label for="<?= $inputId ?>" title="<?= $i ?> Sterne"></label>
            <?php endfor; ?>
            <!-- Option for 0 / Reset needs to be handled? Usually rating is 1..5. If 0 is needed, we need a clear button. -->
        </div>
        
        <?php if ($value > 0): ?>
            <span class="text-muted">(Aktuell: <?= $value ?>/<?= $stars ?>)</span>
            <!-- Hidden clear button script could be added here if needed -->
        <?php endif; ?>
    </div>
    <?php if ($notice): ?>
        <p class="help-block small"><?= rex_i18n::translate($notice, false) ?></p>
    <?php endif; ?>
</div>
