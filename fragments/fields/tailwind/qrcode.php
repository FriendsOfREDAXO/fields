<?php

/**
 * Fragment: QR Code – Tailwind CSS
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
<div class="bg-white rounded-lg shadow p-6 text-center <?= rex_escape($class) ?>">
    <div class="inline-block">
        <?= $svg ?>
    </div>
    <?php if ($label !== ''): ?>
        <p class="text-sm text-gray-500 mt-3"><?= rex_escape($label) ?></p>
    <?php endif; ?>
    <div class="mt-4 flex justify-center gap-2">
        <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'svg', 'size' => $size])) ?>"
           class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition"
           download="qrcode.svg">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            SVG
        </a>
        <a href="<?= rex_escape(rex_url::backendController(['rex-api-call' => 'fields_qrcode', 'content' => $content, 'format' => 'png', 'size' => $size])) ?>"
           class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition"
           download="qrcode.png">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            PNG
        </a>
    </div>
</div>
