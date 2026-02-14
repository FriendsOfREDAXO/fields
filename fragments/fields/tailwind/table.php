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

<div class="overflow-x-auto">
    <table class="table-auto w-full border-collapse border border-gray-200">
        <?php if ($caption): ?>
            <caption class="caption-top text-lg font-semibold py-2"><?= rex_escape($caption) ?></caption>
        <?php endif; ?>

        <?php
        $headerRows = [];
        $bodyRows = $rows;
        
        if ($hasHeaderRow && !empty($rows)) {
            $headerRows[] = array_shift($bodyRows);
        }
        ?>

        <?php if (!empty($headerRows)): ?>
        <thead class="bg-gray-100">
            <?php foreach ($headerRows as $rowIndex => $row): ?>
            <tr>
                <?php foreach ($row as $colIndex => $cell): ?>
                    <?php
                    $colDef = $cols[$colIndex] ?? ['type' => 'text'];
                    $align = $colDef['header_type'] ?? 'text';
                    $alignClass = '';
                    if ($align === 'center') $alignClass = 'text-center';
                    elseif ($align === 'number' || $align === 'right') $alignClass = 'text-right';
                    else $alignClass = 'text-left';
                    ?>
                    <th class="px-4 py-2 border border-gray-300 font-bold <?= $alignClass ?>"><?= nl2br(rex_escape($cell)) ?></th>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </thead>
        <?php endif; ?>

        <tbody>
            <?php foreach ($bodyRows as $rowIndex => $row): ?>
            <tr class="hover:bg-gray-50">
                <?php foreach ($row as $colIndex => $cell): ?>
                    <?php
                    $isHeaderCol = ($colIndex === 0 && $hasHeaderCol);
                    $colDef = $cols[$colIndex] ?? ['type' => 'text'];
                    $type = $isHeaderCol ? 'text' : ($colDef['type'] ?? 'text');
                    
                    $align = $type;
                    $alignClass = '';
                    if ($align === 'center') $alignClass = 'text-center';
                    elseif ($align === 'number' || $align === 'right') $alignClass = 'text-right';
                    else $alignClass = 'text-left';
                    
                    $tag = $isHeaderCol ? 'th' : 'td';
                    $cellClass = $isHeaderCol ? 'bg-gray-50 font-semibold' : '';
                    ?>
                    <<?=$tag?> class="px-4 py-2 border border-gray-300 <?= $alignClass ?> <?= $cellClass ?>">
                        <?php
                        if ($type === 'media') {
                            $media = rex_media::get($cell);
                            if ($media) {
                                if ($media->isImage()) {
                                    echo '<img src="' . $media->getUrl() . '" alt="' . rex_escape($media->getTitle()) . '" class="max-h-24">';
                                } else {
                                    echo '<a href="' . $media->getUrl() . '" target="_blank" class="text-blue-600 hover:underline flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> ' . rex_escape($media->getFileName()) . '</a>';
                                }
                            } else {
                                echo rex_escape($cell);
                            }
                        } elseif ($type === 'link') {
                            $linkId = (int) $cell;
                            if ($linkId > 0) {
                                $art = rex_article::get($linkId);
                                if ($art) {
                                    echo '<a href="' . $art->getUrl() . '" class="text-blue-600 hover:underline">' . rex_escape($art->getName()) . '</a>';
                                } else {
                                    echo '<span class="text-gray-500">Art. #' . $linkId . '</span>';
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
