<?php

/**
 * Fields Addon - Info & Hilfe
 *
 * @package fields
 */

$addon = rex_addon::get('fields');

$content = '
<h3>Verfügbare YForm-Feldtypen</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Typ</th>
            <th>Beschreibung</th>
            <th>DB-Typ</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code>fields_social_web</code></td>
            <td>Social-Media-Profile (Repeater mit Plattform + URL)</td>
            <td>text</td>
        </tr>
        <tr>
            <td><code>fields_opening_hours</code></td>
            <td>Öffnungszeiten mit Sonderzeiten (JSON)</td>
            <td>text</td>
        </tr>
        <tr>
            <td><code>fields_contacts</code></td>
            <td>Kontakt-/Profildaten (Repeater, optionale Felder konfigurierbar)</td>
            <td>text / mediumtext</td>
        </tr>
        <tr>
            <td><code>fields_iban</code></td>
            <td>IBAN mit Live-Validierung (proxied über openIBAN.com)</td>
            <td>varchar(34)</td>
        </tr>
        <tr>
            <td><code>fields_faq</code></td>
            <td>FAQ-Repeater mit Schema.org JSON-LD</td>
            <td>text / mediumtext</td>
        </tr>
        <tr>
            <td><code>fields_qrcode</code></td>
            <td>QR-Code-Generator (SVG, rein PHP)</td>
            <td>text</td>
        </tr>
        <tr>
            <td><code>fields_conditional</code></td>
            <td>Bedingte Feldgruppe (zeigt/versteckt Felder)</td>
            <td>keine DB</td>
        </tr>
        <tr>
            <td><code>fields_icon_picker</code></td>
            <td>Icon-Auswahl (Font Awesome + UIkit)</td>
            <td>varchar(191)</td>
        </tr>
    </tbody>
</table>

<h3>Frontend-Fragmente</h3>
<p>Für jedes Feld stehen Ausgabe-Fragmente in verschiedenen CSS-Frameworks bereit:</p>
<ul>
    <li><strong>Bootstrap 3</strong> – <code>fields/bootstrap3/</code> (auch für Backend)</li>
    <li><strong>UIkit 3</strong> – <code>fields/uikit3/</code></li>
    <li><strong>Tailwind CSS</strong> – <code>fields/tailwind/</code></li>
    <li><strong>Plain (ohne Framework)</strong> – <code>fields/plain/</code></li>
</ul>

<h4>Beispiel: Öffnungszeiten im Frontend</h4>
<pre><code>&lt;?php
use FriendsOfRedaxo\Fields\OpeningHoursHelper;

$json = $dataset->getValue(\'opening_hours\');
$helper = new OpeningHoursHelper($json);

// Fragment verwenden
$fragment = new rex_fragment();
$fragment->setVar(\'helper\', $helper);
echo $fragment->parse(\'fields/uikit3/opening_hours.php\');
?&gt;</code></pre>

<h4>Beispiel: Social Web mit UIkit</h4>
<pre><code>&lt;?php
$json = $dataset->getValue(\'social_web\');
$fragment = new rex_fragment();
$fragment->setVar(\'json\', $json);
$fragment->setVar(\'icon_set\', \'uikit\'); // oder \'fontawesome\'
echo $fragment->parse(\'fields/uikit3/social_web.php\');
?&gt;</code></pre>

<h4>Beispiel: FAQ mit Schema.org</h4>
<pre><code>&lt;?php
$json = $dataset->getValue(\'faq\');

// Schema.org JSON-LD im &lt;head&gt;
echo rex_yform_value_fields_faq::getSchemaJsonLd($json);

// FAQ-Ausgabe
$fragment = new rex_fragment();
$fragment->setVar(\'json\', $json);
echo $fragment->parse(\'fields/tailwind/faq.php\');
?&gt;</code></pre>

<h4>Beispiel: IBAN-Validierung (Proxy-API)</h4>
<pre><code>// Die IBAN-Validierung läuft über den Proxy:
// GET index.php?rex-api-call=fields_iban_validate&amp;iban=DE89370400440532013000
// Antwort: { "valid": true, "bank": "Commerzbank", "bic": "COBADEFFXXX" }</code></pre>

<h4>Beispiel: QR-Code</h4>
<pre><code>&lt;?php
use FriendsOfRedaxo\Fields\QrCodeGenerator;

// SVG direkt generieren
$svg = QrCodeGenerator::generateSvg(\'https://example.com\', 200);
echo $svg;

// Oder als Data-URI
$dataUri = QrCodeGenerator::generateDataUri(\'https://example.com\');
echo \'&lt;img src="\' . $dataUri . \'" alt="QR Code" /&gt;\';

// Oder über API
// GET index.php?rex-api-call=fields_qrcode&amp;content=https://example.com&amp;size=200
?&gt;</code></pre>
';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', rex_i18n::msg('fields_info'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
