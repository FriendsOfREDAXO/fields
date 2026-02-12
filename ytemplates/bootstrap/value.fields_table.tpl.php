<?php

/**
 * @var rex_yform_value_fields_table $this
 * @psalm-scope-this rex_yform_value_fields_table
 */

$value = $this->getValue();
if (!is_string($value) || $value === '') {
    $data = [
        'caption' => '',
        'has_header_row' => true,
        'has_header_col' => false,
        'cols' => [], // { type: 'text'|'number' }
        'rows' => [
            ['Spalte 1', 'Spalte 2', 'Spalte 3'],
            ['', '', '']
        ]
    ];
} else {
    $data = json_decode($value, true);
    // Migration: Falls cols noch nicht existiert
    if (!isset($data['cols']) && isset($data['rows'][0])) {
        $data['cols'] = array_fill(0, count($data['rows'][0]), ['type' => 'text']);
    }
}

$id = $this->getFieldId();
$name = $this->getFieldName();
$notice = $this->getElement('notice');

?>
<div class="<?= $this->getHTMLClass() ?> fields-table-wrapper" id="<?= $this->getHTMLId() ?>" data-fields-table>
    <label class="control-label"><?= $this->getLabel() ?></label>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="form-horizontal">
                <div class="form-group" style="margin-bottom:0">
                    <label class="col-sm-2 control-label" for="<?= $id ?>_caption">Tabellen-Überschrift (Caption)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control fields-table-caption" id="<?= $id ?>_caption" value="<?= rex_escape($data['caption'] ?? '') ?>" placeholder="Beschriftung für Screenreader (wichtig für Barrierefreiheit)">
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body" style="overflow-x: auto;">
            <div class="form-inline" style="margin-bottom: 10px;">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="fields-table-config" data-config="has_header_row" <?= ($data['has_header_row'] ?? false) ? 'checked' : '' ?>>
                        Erste Zeile ist Kopfzeile
                    </label>
                </div>
                <div class="checkbox" style="margin-left: 15px;">
                    <label>
                        <input type="checkbox" class="fields-table-config" data-config="has_header_col" <?= ($data['has_header_col'] ?? false) ? 'checked' : '' ?>>
                        Erste Spalte ist Kopfspalte
                    </label>
                </div>
            </div>

            <table class="table table-bordered table-striped fields-table-editor">
                <thead>
                    <!-- Rendered by JS -->
                </thead>
                <tbody>
                    <!-- Rendered by JS -->
                </tbody>
            </table>
            
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-xs fields-table-add-row" title="Zeile hinzufügen"><i class="rex-icon fa-plus"></i> Zeile +</button>
                <button type="button" class="btn btn-default btn-xs fields-table-add-col" title="Spalte hinzufügen"><i class="rex-icon fa-plus"></i> Spalte +</button>
            </div>
        </div>
    </div>

    <input type="hidden" name="<?= $name ?>" class="fields-table-value" value="<?= rex_escape(json_encode($data)) ?>">

    <?php if ($notice): ?>
        <p class="help-block small"><?= rex_i18n::translate($notice, false) ?></p>
    <?php endif; ?>
</div>

<script type="template" id="<?= $id ?>_data">
    <?= json_encode([
        'rows' => $data['rows'] ?? [],
        'cols' => $data['cols'] ?? []
    ]) ?>
</script>
