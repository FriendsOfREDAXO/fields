<?php

/**
 * @var rex_yform_value_fields_conditional $this
 * @psalm-scope-this rex_yform_value_fields_conditional
 */

$sourceField ??= '';
$operator ??= '=';
$compareValue ??= '';
$targetFields ??= [];
$action ??= 'show';
?>
<div class="fields-conditional-rule" style="display:none;"
     data-source-field="<?= rex_escape($sourceField) ?>"
     data-operator="<?= rex_escape($operator) ?>"
     data-compare-value="<?= rex_escape($compareValue) ?>"
     data-target-fields="<?= rex_escape(json_encode($targetFields)) ?>"
     data-action="<?= rex_escape($action) ?>">
</div>
