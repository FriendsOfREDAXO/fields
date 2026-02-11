<?php

/**
 * Fragment: QR Code – Plain / Framework-independent
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
<div class="fields-qrcode <?= rex_escape($class) ?>">
    <div class="fields-qrcode-image">
        <?= $svg ?>
    </div>
    <?php if ($label !== ''): ?>
        <p class="fields-qrcode-label"><?= rex_escape($label) ?></p>
    <?php endif; ?>
    <div class="fields-qrcode-download">
        <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'svg', 'size' => $size])) ?>"
           download="qrcode.svg">SVG herunterladen</a>
        <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'png', 'size' => $size])) ?>"
           download="qrcode.png">PNG herunterladen</a>
    </div>
</div>
