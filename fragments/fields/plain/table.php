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

<table>
    <?php if ($caption): ?>
        <caption><?= rex_escape($caption) ?></caption>
    <?php endif; ?>

    <?php
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
                <th scope="col"><?= nl2br(rex_escape($cell)) ?></th>
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
                
                $tag = $isHeaderCol ? 'th' : 'td';
                $scope = $isHeaderCol ? ' scope="row"' : '';
                ?>
                <<?=$tag?><?=$scope?> data-type="<?= $type ?>">
                    <?php
                    if ($type === 'media') {
                        $media = rex_media::get($cell);
                        if ($media) {
                            if ($media->isImage()) {
                                echo '<img src="' . $media->getUrl() . '" alt="' . rex_escape($media->getTitle()) . '">';
                            } else {
                                echo '<a href="' . $media->getUrl() . '" target="_blank">' . rex_escape($media->getFileName()) . '</a>';
                            }
                        } else {
                            echo rex_escape($cell);
                        }
                    } elseif ($type === 'link') {
                        $linkId = (int) $cell;
                        if ($linkId > 0) {
                            $art = rex_article::get($linkId);
                            if ($art) {
                                echo '<a href="' . $art->getUrl() . '">' . rex_escape($art->getName()) . '</a>';
                            } else {
                                echo 'Art. #' . $linkId;
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
