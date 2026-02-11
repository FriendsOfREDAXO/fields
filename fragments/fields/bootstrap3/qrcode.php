<?php

/**
 * Fragment: QR Code – Bootstrap 3
 *
 * Variables:
 *   content – Der Inhalt des QR-Codes (URL, Text, etc.)
 *   size    – Größe in Pixel (default: 200)
 *   class   – zusätzliche CSS-Klasse
 *   label   – optionale Beschreibung unter dem QR-Code
 */

use FriendsOfRedaxo\Fields\QrCodeGenerator;

$content = $this->getVar('content', '');
$size = $this->getVar('size', 200);
$class = $this->getVar('class', '');
$label = $this->getVar('label', '');

if ($content === '') {
    return;
}

$svg = QrCodeGenerator::generateSvg($content, (int) $size);
?>
<div class="panel panel-default <?= rex_escape($class) ?>">
    <div class="panel-body text-center">
        <div class="fields-qrcode-image">
            <?= $svg ?>
        </div>
        <?php if ($label !== ''): ?>
            <p class="text-muted" style="margin-top:10px;"><?= rex_escape($label) ?></p>
        <?php endif; ?>
        <div style="margin-top:10px;">
            <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'svg', 'size' => $size])) ?>"
               class="btn btn-default btn-sm" download="qrcode.svg">
                <i class="fa fa-download"></i> SVG
            </a>
            <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'png', 'size' => $size])) ?>"
               class="btn btn-default btn-sm" download="qrcode.png">
                <i class="fa fa-download"></i> PNG
            </a>
        </div>
    </div>
</div>
