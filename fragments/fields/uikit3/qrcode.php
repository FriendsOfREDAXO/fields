<?php

/**
 * Fragment: QR Code – UIkit 3
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
<div class="uk-card uk-card-default uk-card-body uk-text-center <?= rex_escape($class) ?>">
    <div class="fields-qrcode-image">
        <?= $svg ?>
    </div>
    <?php if ($label !== ''): ?>
        <p class="uk-text-meta uk-margin-small-top"><?= rex_escape($label) ?></p>
    <?php endif; ?>
    <div class="uk-margin-small-top uk-button-group">
        <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'svg', 'size' => $size])) ?>"
           class="uk-button uk-button-default uk-button-small" download="qrcode.svg">
            <span uk-icon="icon: download; ratio: 0.8"></span> SVG
        </a>
        <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'png', 'size' => $size])) ?>"
           class="uk-button uk-button-default uk-button-small" download="qrcode.png">
            <span uk-icon="icon: download; ratio: 0.8"></span> PNG
        </a>
    </div>
</div>
