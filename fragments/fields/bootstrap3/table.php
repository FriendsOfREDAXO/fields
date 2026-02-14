<?php
/** @var rex_fragment $this */
$data = $this->getVar('json');
if (is_string($data)) {
    $data = json_decode($data, true);
}

if (!is_array($data) || empty($data['rows'])) {
    return;
}

$rows = $data['rows'];
$cols = $data['cols'] ?? [];
$caption = $data['caption'] ?? '';
$hasHeaderRow = $data['has_header_row'] ?? false;
$hasHeaderCol = $data['has_header_col'] ?? false;
?>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <?php if ($caption): ?>
            <caption><?= rex_escape($caption) ?></caption>
        <?php endif; ?>

        <?php foreach ($rows as $rowIndex => $row): ?>
            <?php
            $isHeaderRow = ($rowIndex === 0 && $hasHeaderRow);
            $rowTag = $isHeaderRow ? 'thead' : ($rowIndex === 1 && $hasHeaderRow ? 'tbody' : ''); // Simple structure
            
            // If header row is separate, we might want <thead>...</thead><tbody>...</tbody>
            // But iteration is linear. Let's just output tr.
            // Better: Collect header and body.
            ?>
        <?php endforeach; ?>
        
        <?php
        // Separate Header and Body for valid HTML
        $headerRows = [];
        $bodyRows = $rows;
        
        if ($hasHeaderRow && !empty($rows)) {
            $headerRows[] = array_shift($bodyRows);
        }
        ?>

        <?php if (!empty($headerRows)): ?>
        <thead>
            <?php foreach ($headerRows as $rowIndex => $row): ?>
            <tr>
                <?php foreach ($row as $colIndex => $cell): ?>
                    <?php
                    $colDef = $cols[$colIndex] ?? ['type' => 'text'];
                    $align = $colDef['header_type'] ?? 'text'; // Default for header is text
                    
                    $alignClass = '';
                    if ($align === 'center') $alignClass = 'text-center';
                    elseif ($align === 'number' || $align === 'right') $alignClass = 'text-right';
                    ?>
                    <th class="<?= $alignClass ?>"><?= nl2br(rex_escape($cell)) ?></th>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </thead>
        <?php endif; ?>

        <tbody>
            <?php foreach ($bodyRows as $rowIndex => $row): ?>
            <tr>
                <?php foreach ($row as $colIndex => $cell): ?>
                    <?php
                    $isHeaderCol = ($colIndex === 0 && $hasHeaderCol);
                    $colDef = $cols[$colIndex] ?? ['type' => 'text'];
                    $type = $isHeaderCol ? 'text' : ($colDef['type'] ?? 'text');
                    
                    // Header Col Alignment (uses header_type if it is a header col? No usually body type unless styled otherwise)
                    // But in editor, header col uses simple text input.
                    
                    $align = $type;
                    $alignClass = '';
                    if ($align === 'center') $alignClass = 'text-center';
                    elseif ($align === 'number' || $align === 'right') $alignClass = 'text-right';
                    
                    $tag = $isHeaderCol ? 'th' : 'td';
                    ?>
                    <<?=$tag?> class="<?= $alignClass ?>">
                        <?php
                        // Render Content based on Type
                        if ($type === 'media') {
                            $media = rex_media::get($cell);
                            if ($media) {
                                if ($media->isImage()) {
                                    echo '<img src="' . $media->getUrl() . '" alt="' . rex_escape($media->getTitle()) . '" style="max-width: 100%; height: auto; max-height: 100px;">';
                                } else {
                                    echo '<a href="' . $media->getUrl() . '" target="_blank"><i class="rex-icon fa-file-o"></i> ' . rex_escape($media->getFileName()) . '</a>';
                                }
                            } else {
                                echo rex_escape($cell);
                            }
                        } elseif ($type === 'link') {
                            // Link format? It stores ID? Or "LINK_1"?
                            // Editor stores input.value which is usually ID or "rex_link_1"
                            // Wait, openLinkMap returns an ID (integer) usually or text?
                            // In JS we used: input.id='LINK_'+unique; displayInput.id='LINK_'+unique+'_NAME';
                            // When REDAXO linkmap closes, it fills the hidden input with ID (e.g. "12") and name input with "Article Name [12]".
                            // So $cell should be the ID.
                            
                            $linkId = (int) $cell;
                            if ($linkId > 0) {
                                $art = rex_article::get($linkId);
                                if ($art) {
                                    echo '<a href="' . $art->getUrl() . '">' . rex_escape($art->getName()) . '</a>';
                                } else {
                                    echo 'Art. #' . $linkId . ' (offline?)';
                                }
                            } else {
                                echo rex_escape($cell);
                            }
                        } elseif ($type === 'textarea') {
                             echo nl2br(rex_escape($cell));
                        } else {
                            echo nl2br(rex_escape($cell));
                        }
                        ?>
                    </<?=$tag?>>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
