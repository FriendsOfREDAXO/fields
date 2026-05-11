# Fields – Erweiterte redaktionelle YForm-Feldtypen für REDAXO

![REDAXO](https://img.shields.io/badge/REDAXO-%3E%3D5.17-red) ![YForm](https://img.shields.io/badge/YForm-%3E%3D5.0-blue) ![PHP](https://img.shields.io/badge/PHP-%3E%3D8.1-purple)

## Worum geht es?

Das AddOn **Fields** erweitert REDAXO und YForm um eine umfangreiche Sammlung redaktioneller Feldtypen. Damit lassen sich häufig benötigte Eingabemuster komfortabel im Backend abbilden – von einfachen UI-Controls wie Inline-Switches und Star-Ratings bis hin zu komplexen Strukturen wie Tabellen, Öffnungszeiten, FAQ-Listen, Kontaktkarten oder Tab-/Akkordeon-Layouts.

Alle Felder integrieren sich nahtlos in den YForm-Tablemanager. Komplexere Datentypen speichern ihre Inhalte als JSON und können im Frontend über mitgelieferte Fragmente (Bootstrap 3, UIkit 3, Tailwind, Plain HTML) ausgegeben werden.

## Installation

1. Im REDAXO-Installer nach **fields** suchen und installieren, **oder**
2. Repository nach `redaxo/src/addons/fields` klonen und im Backend aktivieren.

Anschließend stehen alle Feldtypen automatisch in der YForm-Feldauswahl zur Verfügung. Konfiguration unter **YForm → Fields**.

## Voraussetzungen

- REDAXO >= 5.17
- YForm >= 5.0
- PHP >= 8.1

---

## Features

Die Feldtypen lassen sich in zwei Kategorien einteilen:

- **Felder mit Frontend-Ausgabe** speichern strukturierte Daten und werden über die mitgelieferten Fragmente (Bootstrap 3, UIkit 3, Tailwind, Plain) im Frontend gerendert.
- **Backend-/Helper-Felder** haben keine eigene Frontend-Ausgabe – sie verbessern die redaktionelle Arbeit (Inline-Editing in Listen) oder steuern Layout und Sichtbarkeit von Formularfeldern im Backend.

### Felder mit Frontend-Ausgabe

#### UI Controls

- **Icon Picker** – Icon-Auswahl aus Font Awesome und/oder UIkit-Iconsets
- **Star Rating** – Bewertungsfeld (1–10 Sterne) mit visueller Eingabe
- **IBAN** – IBAN-Eingabe mit Live-Validierung über openIBAN.com (serverseitig geproxied)
- **Tagging** – Farbige Schlagwörter (Chips) mit Custom-Color-Picker und WCAG-Kontrastprüfung

#### Komplexe Datentypen (Repeater & Strukturen)

- **Tabelle** – Barrierefreier Tabelleneditor mit flexiblen Spalten/Zeilen, Min/Max-Constraints und erweiterten Datentypen (Medien, Links, Textarea, Zahlen)
- **Social Web** – Repeater für Social-Media-Profile mit 24 vordefinierten Plattformen
- **Öffnungszeiten** – Wochentags-Editor mit Zeitfenstern, Sondertagen und Notizen
- **Kontakte** – Flexible Kontaktkarten mit konfigurierbaren optionalen Feldern (Avatar, Firma, Adresse, …)
- **FAQ** – Frage/Antwort-Repeater mit automatischer Schema.org-FAQPage-JSON-LD-Ausgabe

### Backend-/Helper-Felder (keine Frontend-Ausgabe)

#### Inline-Editing in der Listenansicht

Diese Felder speichern jeweils einen einfachen Wert (Text, Zahl, Auswahl, Boolean), bringen aber im Backend zusätzlichen Komfort durch Click-to-Edit direkt in der YForm-Tabellenübersicht. Im Frontend werden sie wie ein normales Datenfeld behandelt (`$item->getValue(...)`) – es gibt keine speziellen Fragmente.

- **Inline Switch** – Moderner, eckiger Toggle-Switch für Boolean-Werte
- **Inline Edit** – Direktes Bearbeiten von Text- und Textarea-Feldern (Click-to-Edit)
- **Inline Number** – Zahlenfeld mit Inline-Editing, Präfix/Suffix (z. B. €/km), Min/Max und Step
- **Inline Select** – Auswahlfeld mit Inline-Editing, Selectpicker, Farben und optionaler Query-Quelle

#### Layout & Logik (nur Backend-Formular)

Diese Feldtypen erzeugen **keine eigenen DB-Spalten** und schreiben keine Werte – sie strukturieren ausschließlich das YForm-Formular im Backend.

- **Tabs & Akkordeons** (`fields_interactive`) – Gruppierung von Feldern in Tabs, Akkordeons oder Fieldsets
- **Grid & Layout** (`fields_structure`) – Mehrspaltige Anordnung von Feldern (CSS Grid)
- **Conditional** (`fields_conditional`) – Bedingte Sichtbarkeit von Feldgruppen abhängig von anderen Feldwerten

### Konfiguration

Unter **YForm → Fields**:

| Einstellung | Beschreibung |
|---|---|
| Icon-Sets | Verfügbare Icon-Bibliotheken (z. B. Font Awesome, UIkit) |
| IBAN-Proxy | openIBAN.com-Proxy aktivieren/deaktivieren |

---

## Anwendung im YForm-Tablemanager

Die Feldtypen erscheinen nach der Installation automatisch in der YForm-Feldauswahl unter dem Typ **value**.

#### Felder mit Frontend-Ausgabe

| Feldtyp | DB-Typ | Beschreibung |
|---|---|---|
| `fields_social_web` | `mediumtext` | Social-Media-Profile als JSON |
| `fields_opening_hours` | `mediumtext` | Öffnungszeiten als JSON |
| `fields_contacts` | `mediumtext` | Kontaktdaten als JSON |
| `fields_table` | `mediumtext` | Tabelle mit Spalten- und Zeilendaten sowie Meta-Infos |
| `fields_iban` | `varchar(34)` | IBAN mit Validierung |
| `fields_faq` | `mediumtext` | FAQ-Einträge als JSON |
| `fields_icon_picker` | `varchar(191)` | Ausgewähltes Icon (z. B. `fa-home`) |
| `fields_rating` | `int` | Ganzzahlige Bewertung |
| `fields_tagging` | `text` | Farbige Tag-Chips als JSON |

#### Backend-/Helper-Felder ohne eigene Frontend-Ausgabe

| Feldtyp | DB-Typ | Beschreibung |
|---|---|---|
| `fields_inline_switch` | `tinyint(1)` | Boolescher Switch mit Inline-Editing in der Liste |
| `fields_inline` | `text`, `mediumtext` | Text/Textarea mit Inline-Editing in der Liste |
| `fields_inline_number` | `int`, `float`, `decimal` | Zahlenfeld mit Inline-Editing in der Liste |
| `fields_inline_select` | `varchar(191)`, `text` | Auswahlfeld mit Inline-Editing in der Liste |
| `fields_conditional` | *kein DB-Feld* | Steuert Sichtbarkeit anderer Backend-Felder |
| `fields_interactive` | *kein DB-Feld* | Felder im Backend in Tabs/Akkordeons gruppieren |
| `fields_structure` | *kein DB-Feld* | Felder im Backend mehrspaltig anordnen (Grid) |

> Die Inline-Felder speichern reguläre Werte und können im Frontend einfach über `$item->getValue(...)` ausgelesen werden. `fields_conditional`, `fields_interactive` und `fields_structure` erzeugen **keine DB-Spalten** – sie strukturieren nur das Backend-Formular.

### Inline Editing in der Listenansicht

Mit `fields_inline`, `fields_inline_number`, `fields_inline_select` und `fields_inline_switch` lassen sich Werte direkt in der YForm-Tabellenübersicht bearbeiten:

- **Click-to-Edit**: Anklicken aktiviert den Bearbeitungsmodus.
- **Speichern**: Enter (bei Textfeldern) oder Klick auf den Haken.
- **Abbruch**: ESC oder Klick auf das X.
- **Events**: Löst beim Speichern `YFORM_DATA_UPDATED` aus, sodass andere AddOns (z. B. URL AddOn) Änderungen registrieren.

### Tagging: Suche im YForm-Manager

Das Feld `fields_tagging` bringt eine eigene Suchintegration für den Tablemanager mit:

- Mehrfachauswahl als **Checkbox-Liste** statt Freitext
- Farbige Marker je Tag
- Zusatzauswahl für **(empty)** und **Nicht leer**
- Robuste Filterung für `NULL`, leere Strings und `[]`

### Tagging: Inline-Editing in der Listenansicht

Mit der Feldoption **„Tags in der Listenansicht bearbeitbar“** (`list_editable = 1`) lassen sich Tags direkt in der YForm-Tabellenübersicht über einen **+**-Button (Popover mit Texteingabe, Farbpicker, Vorschlägen) hinzufügen und per `×` entfernen. `max_tags` wird respektiert. Standard: aus.

---

## Anwendung im Metainfo-AddOn

Aktuell stellt **Fields** für das Metainfo-AddOn genau einen Feldtyp bereit: **Fields Tagging**. Damit lassen sich in Artikeln, Medien, Kategorien und Sprachen farbige Tags mit Autocomplete-Vorschlägen verwalten.

### Schritt für Schritt

1. **Verwaltung → Metainfo** öffnen und **Neue Spalte** anlegen.
2. Feldkonfiguration:
   - **Bezeichnung**: z. B. „Artikel-Tags“
   - **Feldname**: z. B. `art_tags` (Präfix `art_` = Artikel, `med_` = Medien, `clang_` = Kategorien/Sprachen)
   - **Feldtyp**: **Fields Tagging**
   - **Datentyp**: `text`
3. **Parameter** (optional, im Parameter-Feld als Querystring):
   - `source_table=rex_article&source_field=art_tags&max_tags=5`
4. Speichern und im Artikel/Medium über „Tags bearbeiten“ verwenden.

### Parameter

| Parameter | Beschreibung |
|---|---|
| `source_table` | Tabelle für Autocomplete-Vorschläge (mit oder ohne `rex_`-Präfix) |
| `source_field` | Spaltenname der Quelle |
| `max_tags` | Maximale Tag-Anzahl (0 = unbegrenzt) |
| `list_editable` | `1` aktiviert Inline-Editing direkt in der YForm-Listenansicht (Tags ohne Detailformular hinzufügen/entfernen) |
| `notice` | Hinweistext unter dem Feld |

> **Automatische Quelle:** Bleibt `source_table`/`source_field` leer, leitet das Widget die Quelle aus dem Feldnamen ab: `art_*` → `rex_article`, `med_*` → `rex_media`, `clang_*` → `rex_clang`. Ist gar keine Quelle gepflegt, bleibt das Feld trotzdem speicherbar – nur die Vorschlagsliste ist leer.

### Troubleshooting

- **Widget wird nicht angezeigt**: Feldtyp muss explizit auf **Fields Tagging** stehen.
- **Autocomplete leer**: `source_table` und `source_field` prüfen; in der Quelle müssen bereits Tags gespeichert sein.
- **Tags verschwinden nach dem Speichern**: Spalte muss vom Typ `text` sein.

---

# Für Entwickler

Dieser Abschnitt dokumentiert vollständig:

1. Wie die mitgelieferten **Fragmente** im Frontend eingebunden werden (inkl. aller `setVar`-Variablen).
2. **Verarbeitungsbeispiele** für typische Modul-/Template-Aufgaben (Auslesen, Filtern, Eigene Ausgabe).
3. Eine **API-Referenz** aller öffentlichen Methoden der Helper-Klassen und Feldklassen.
4. Alle **REX-API-Endpunkte** (`rex-api-call=...`) mit Request- und Response-Format.

---

## 1. Frontend-Ausgabe via Fragmente

Für jedes Feld mit Frontend-Output stehen Fragmente in vier Framework-Varianten bereit. Der Aufruf erfolgt immer nach demselben Muster:

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('feldname'));
echo $fragment->parse('fields/<framework>/<feld>.php');
```

| Variante | Pfad-Prefix | Hinweise |
|---|---|---|
| Bootstrap 3 | `fields/bootstrap3/` | Standard im REDAXO-Backend, viele REDAXO-Themes |
| UIkit 3 | `fields/uikit3/` | Für UIkit-basierte Frontends |
| Tailwind | `fields/tailwind/` | Utility-Klassen, keine eigenen CSS-Dateien nötig |
| Plain HTML | `fields/plain/` | Framework-unabhängig, semantische Auszeichnung |

> **Hinweis:** Bei `opening_hours` erwarten die Fragmente **kein `json`**, sondern eine fertige `OpeningHoursHelper`-Instanz – siehe unten.

### 1.1 Social Web – `fields/<framework>/social_web.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `json` | string | `''` | JSON-String aus `$item->getValue('social_web')` |
| `icon_set` | string | `fontawesome` | `fontawesome` oder `uikit` |
| `class` | string | `''` | Zusätzliche CSS-Klasse für den Wrapper |

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('social_web'));
$fragment->setVar('icon_set', 'fontawesome');
$fragment->setVar('class', 'my-social-links');
echo $fragment->parse('fields/uikit3/social_web.php');
```

### 1.2 Öffnungszeiten – `fields/<framework>/opening_hours.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `helper` | `OpeningHoursHelper` | — | **Pflichtparameter**: Helper-Instanz, NICHT JSON |
| `show_status` | bool | `true` | Aktuellen Öffnungsstatus oben anzeigen |
| `grouped` | bool | `true` | Wochentage mit gleichen Zeiten zusammenfassen (Mo–Fr) |
| `show_special` | bool | `true` | Sonderöffnungszeiten ausgeben |

```php
use FriendsOfRedaxo\Fields\OpeningHoursHelper;

$helper = new OpeningHoursHelper($item->getValue('opening_hours'), 'de');

$fragment = new rex_fragment();
$fragment->setVar('helper', $helper, false); // false = kein Escaping (Objekt!)
$fragment->setVar('show_status', true);
$fragment->setVar('grouped', true);
$fragment->setVar('show_special', true);
echo $fragment->parse('fields/bootstrap3/opening_hours.php');
```

### 1.3 Tabelle – `fields/<framework>/table.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `json` | string | `''` | JSON-String aus `$item->getValue('table')` |

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('table'));
echo $fragment->parse('fields/bootstrap3/table.php');
```

Editor-Funktionen: Min/Max-Grenzen für Zeilen/Spalten, unabhängige Textausrichtung Kopf-/Datenzellen, Inline-Hinzufügen, Strict Mode, erweiterte Spaltentypen (Medien `REX_MEDIA`, Links `REX_LINK`, Textarea, Zahlen).

### 1.4 Kontakte – `fields/<framework>/contacts.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `json` | string | `''` | JSON aus `$item->getValue('contacts')` |
| `class` | string | `''` | Zusatzklasse für den Wrapper |

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('contacts'));
$fragment->setVar('class', 'team-cards');
echo $fragment->parse('fields/tailwind/contacts.php');
```

### 1.5 FAQ – `fields/<framework>/faq.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `json` | string | `''` | JSON aus `$item->getValue('faq')` |
| `schema` | bool | `true` | Schema.org-`FAQPage`-JSON-LD im Output mitliefern |
| `class` | string | `''` | Zusatzklasse |
| `id` | string | `fields-faq-<rand>` | DOM-ID-Präfix (Bootstrap-Akkordeon-Gruppen) |

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('faq'));
$fragment->setVar('schema', true);
$fragment->setVar('id', 'faq-produktinfo');
echo $fragment->parse('fields/bootstrap3/faq.php');
```

JSON-LD separat (z. B. nur im `<head>`) ausgeben:

```php
echo rex_yform_value_fields_faq::getSchemaJsonLd($item->getValue('faq'));
```

### 1.6 Star Rating – `fields/<framework>/rating.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `value` | int | `0` | Aktuelle Bewertung |
| `max` | int | `5` | Maximale Sterne |
| `color` | string | `#ffc107` | Farbe der gefüllten Sterne (Bootstrap/UIkit) |
| `icon_full` | string | `fa fa-star` | CSS-Klasse für gefüllten Stern (Bootstrap/UIkit) |
| `icon_empty` | string | `fa fa-star-o` | CSS-Klasse für leeren Stern |
| `char_full` | string | `★` | Zeichen für gefüllten Stern (nur `plain`) |
| `char_empty` | string | `☆` | Zeichen für leeren Stern (nur `plain`) |
| `class` | string | `''` | Zusatzklasse |

```php
$fragment = new rex_fragment();
$fragment->setVar('value', (int) $item->getValue('rating'));
$fragment->setVar('max', 5);
$fragment->setVar('color', '#f39c12');
echo $fragment->parse('fields/bootstrap3/rating.php');
```

### 1.7 QR-Code – `fields/<framework>/qrcode.php`

| Variable | Typ | Default | Beschreibung |
|---|---|---|---|
| `content` | string | `''` | Zu kodierender Inhalt (URL, vCard, Text) |
| `size` | int | `200` | Kantenlänge in Pixeln |
| `label` | string | `''` | Optionaler Beschriftungstext unter dem Code |
| `class` | string | `''` | Zusatzklasse |

```php
$fragment = new rex_fragment();
$fragment->setVar('content', 'https://example.com');
$fragment->setVar('size', 256);
$fragment->setVar('label', 'Zur Website');
echo $fragment->parse('fields/plain/qrcode.php');
```

### 1.8 Icon Picker (manuelle Ausgabe)

Für `fields_icon_picker` existiert kein eigenes Fragment – der gespeicherte Wert ist eine CSS-Klasse:

```php
$icon = $item->getValue('icon');
// Font Awesome
echo '<i class="' . rex_escape($icon) . '"></i>';
// UIkit
echo '<span uk-icon="icon: ' . rex_escape(str_replace('uk-icon-', '', $icon)) . '"></span>';
```

### 1.9 IBAN (manuelle Ausgabe)

```php
$iban = $item->getValue('iban');
if (rex_yform_value_fields_iban::isValidFormat($iban)) {
    echo rex_escape(trim(chunk_split($iban, 4, ' ')));
}
```

### 1.10 Backend-Only-Felder

`fields_conditional`, `fields_interactive`, `fields_structure` haben **keine Frontend-Ausgabe**. Die Inline-Felder (`fields_inline*`, `fields_inline_switch`) speichern einfache Skalarwerte:

```php
echo rex_escape($item->getValue('beschreibung_inline'));    // Text
echo (int) $item->getValue('aktiv');                        // Switch
echo number_format((float) $item->getValue('preis'), 2);    // Zahl
```

---

## 1.11 Wie Fragmente funktionieren – am Beispiel `rating.php`

Alle Fragmente folgen demselben Muster: Sie sind eigenständige PHP-Templates, die per `rex_fragment` aufgerufen werden. REDAXO sucht das Template in allen registrierten Fragment-Verzeichnissen – das Addon registriert dafür den eigenen Ordner in `boot.php`:

```php
rex_fragment::addDirectory(rex_addon::get('fields')->getPath('fragments'));
```

### Datenfluss

```
$item->getValue('rating')   ──►  rex_fragment::setVar(...)   ──►  fragments/fields/<fw>/rating.php
        (int aus DB)              (Variable für das Template)        (rendert HTML)
```

### Datenformat des Rating-Felds

Das Feld `fields_rating` speichert einen **einfachen Integer** in der DB (Spaltentyp `int`, Wertebereich 1–10). Es gibt kein JSON, kein Subschema – nur einen Skalarwert:

```sql
SELECT rating FROM rex_my_table;  -- z. B. 4
```

### Das mitgelieferte Bootstrap-3-Fragment

`fragments/fields/bootstrap3/rating.php`:

```php
<?php
/**
 * Star Rating Fragment (Bootstrap 3)
 * @var rex_fragment $this
 */
$max       = (int) $this->getVar('max', 5);
$value     = (int) $this->getVar('value', 0);
$class     = $this->getVar('class', '');
$iconFull  = $this->getVar('icon_full', 'fa fa-star');
$iconEmpty = $this->getVar('icon_empty', 'fa fa-star-o');
$color     = $this->getVar('color', '#ffc107');
?>
<div class="fields-rating <?= $class ?>" aria-label="<?= $value ?> von <?= $max ?> Sternen" role="img">
    <?php for ($i = 1; $i <= $max; $i++): ?>
        <?php if ($i <= $value): ?>
            <i class="<?= $iconFull ?>" style="color: <?= $color ?>;" aria-hidden="true"></i>
        <?php else: ?>
            <i class="<?= $iconEmpty ?>" style="color: #ccc;" aria-hidden="true"></i>
        <?php endif; ?>
    <?php endfor; ?>
    <span class="sr-only"><?= $value ?>/<?= $max ?></span>
</div>
```

**Wichtige Regeln, die jedes Fragment einhalten sollte:**

1. **Variablen mit Defaults** via `$this->getVar('name', $default)` lesen – nie ohne Default, sonst bricht das Template bei fehlenden Vars.
2. **Typen casten** (`(int)`, `(string)`, `(bool)`), denn `setVar` macht keine Typprüfung.
3. **Output escapen** mit `rex_escape(...)` für alle dynamischen Werte, die aus Nutzerdaten stammen (CSS-Klassen, Labels, Texte). Reine konstante Strings wie `fa fa-star` müssen nicht escapt werden, dynamische Eingaben schon.
4. **Barrierefreiheit**: `aria-label`, `role`, `sr-only`-Text – wie im Beispiel.
5. **Kein PHP-State leaken**: alle Variablen werden lokal im Template definiert, nicht global.

### Eigenes Fragment für ein anderes Framework schreiben (Bulma als Beispiel)

Das Fields-Addon liefert nur `bootstrap3`, `uikit3`, `tailwind` und `plain`. Wer **Bulma**, **Foundation** oder ein hauseigenes Designsystem verwendet, legt ein eigenes Fragment-Verzeichnis im Projekt-Addon (oder Project-Addon) an. REDAXO durchsucht alle registrierten Verzeichnisse in der Reihenfolge ihrer Registrierung – das erste passende Template gewinnt, eigene Templates können also auch die mitgelieferten überschreiben.

**Schritt 1 – Verzeichnis registrieren** (z. B. in der `boot.php` des Project-Addons):

```php
rex_fragment::addDirectory(
    rex_path::addon('project', 'fragments/'),
);
```

**Schritt 2 – Datei anlegen** unter `redaxo/src/addons/project/fragments/fields/bulma/rating.php`:

```php
<?php
/**
 * Star Rating Fragment (Bulma)
 * @var rex_fragment $this
 */
$max       = (int) $this->getVar('max', 5);
$value     = (int) $this->getVar('value', 0);
$class     = rex_escape((string) $this->getVar('class', ''));
$colorFull = rex_escape((string) $this->getVar('color', 'has-text-warning'));
$colorEmpty = rex_escape((string) $this->getVar('color_empty', 'has-text-grey-light'));
?>
<div class="fields-rating <?= $class ?> is-inline-flex"
     role="img"
     aria-label="<?= rex_escape($value . ' von ' . $max . ' Sternen') ?>">
    <?php for ($i = 1; $i <= $max; $i++): ?>
        <span class="icon <?= $i <= $value ? $colorFull : $colorEmpty ?>">
            <i class="fas <?= $i <= $value ? 'fa-star' : 'fa-star' ?>"></i>
        </span>
    <?php endfor; ?>
    <span class="is-sr-only"><?= (int) $value ?>/<?= (int) $max ?></span>
</div>
```

**Schritt 3 – Im Modul/Template nutzen:**

```php
$fragment = new rex_fragment();
$fragment->setVar('value', (int) $item->getValue('rating'));
$fragment->setVar('max', 5);
$fragment->setVar('color', 'has-text-warning');
$fragment->setVar('color_empty', 'has-text-grey-light');
echo $fragment->parse('fields/bulma/rating.php');
```

> **Tipp:** Möchte man die mitgelieferten Fragmente nur **anpassen** (statt eines neuen Frameworks), genügt es, eine Datei mit identischem Pfad im eigenen Addon anzulegen, z. B. `project/fragments/fields/bootstrap3/rating.php`. Da das eigene Verzeichnis i. d. R. zuletzt registriert wird, hat es Vorrang vor dem Original aus dem `fields`-Addon.

Dieses Schema funktioniert für **alle** Fragmente des Addons – `social_web.php`, `opening_hours.php`, `faq.php`, `contacts.php`, `table.php`, `qrcode.php`. Die Eingabe-Variablen sind in Abschnitt 1.1–1.7 dokumentiert.

---

## 2. Verarbeitungsbeispiele

Praxisrezepte für typische Modul-/Template-Aufgaben.


### 2.1 Eine Liste filtern (Tagging)

Alle Datensätze ausgeben, die das Tag `php` enthalten – einmal per SQL (effizient), einmal per PHP (für gemischte Quellen):

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

// Variante A: SQL (MySQL >= 5.7) – idealerweise mit YOrm Query
$sql  = rex_sql::factory();
$rows = $sql->getArray(
    'SELECT * FROM ' . rex::getTable('produkte')
    . ' WHERE ' . FieldsTagging::sqlHasTag('tags', 'php'),
);

// Variante B: PHP-seitig auf bereits geladenen Daten
$rows = rex_yform_manager_dataset::query('rex_produkte')->find()->toArray();
$rows = FieldsTagging::filterByTag($rows, 'tags', 'php');
```

### 2.2 Tag-Cloud aller Tags einer Tabelle

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

$tags = FieldsTagging::collectFromTable('produkte', 'tags');
echo FieldsTagging::toHtml($tags, 'Noch keine Tags');
```

### 2.3 Aktuellen Öffnungsstatus im Header anzeigen

```php
use FriendsOfRedaxo\Fields\OpeningHoursHelper;

$helper = new OpeningHoursHelper($item->getValue('opening_hours'), 'de');
$status = $helper->getCurrentStatus();

$cssClass = $status['is_open'] ? 'badge-success' : 'badge-secondary';
echo '<span class="badge ' . $cssClass . '">'
   . rex_escape($status['label']);
if ($status['next_change_label']) {
    echo ' – ' . rex_escape($status['next_change_label']);
}
echo '</span>';
```

### 2.4 FAQ mit Schema.org-Markup im Template

```php
$faqJson = $item->getValue('faq');

// JSON-LD in den <head>
rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) use ($faqJson) {
    $jsonLd = rex_yform_value_fields_faq::getSchemaJsonLd($faqJson);
    return str_replace('</head>', $jsonLd . '</head>', $ep->getSubject());
});

// Sichtbare Ausgabe im Artikel
$fragment = new rex_fragment();
$fragment->setVar('json', $faqJson);
$fragment->setVar('schema', false); // schema schon im head
echo $fragment->parse('fields/bootstrap3/faq.php');
```

### 2.5 Social-Web-Profile in einer eigenen Schleife rendern

```php
$social = json_decode($item->getValue('social_web') ?: '[]', true);
foreach ($social as $entry) {
    printf(
        '<a href="%s" rel="me" style="color:%s"><i class="%s"></i> %s</a>',
        rex_escape($entry['url']),
        rex_escape($entry['color']),
        rex_escape($entry['icon']),
        rex_escape($entry['label'] ?: $entry['platform']),
    );
}
```

### 2.6 Tabelle aus Code befüllen und ausgeben

```php
$tableData = [
    'caption'        => 'Preisliste 2026',
    'has_header_row' => true,
    'has_header_col' => false,
    'rows' => [
        ['Produkt', 'Menge', 'Preis'],
        ['Stuhl', '1', '199,00 €'],
        ['Tisch', '1', '499,00 €'],
    ],
];

$fragment = new rex_fragment();
$fragment->setVar('json', json_encode($tableData));
echo $fragment->parse('fields/bootstrap3/table.php');
```

### 2.7 Inline-Update programmgesteuert auslösen (JavaScript)

Felder mit Inline-Editing senden automatisch ein DOM-Event `YFORM_DATA_UPDATED`. So lässt sich z. B. das URL-AddOn benachrichtigen:

```js
document.addEventListener('YFORM_DATA_UPDATED', (e) => {
    console.log('Update', e.detail); // { table, field, id, value }
});
```

### 2.8 Eigene Tagging-Suchquelle (Metainfo + YForm gemischt)

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

$artikelTags = FieldsTagging::collectFromTable('article', 'art_tags');
$produktTags = FieldsTagging::collectFromTable('produkte', 'tags');

// zusammenführen, nach Text deduplizieren
$alle = array_merge($artikelTags, $produktTags);
$seen = [];
$alle = array_filter($alle, function ($t) use (&$seen) {
    $k = mb_strtolower($t['text']);
    if (isset($seen[$k])) return false;
    return $seen[$k] = true;
});
```

---

## 3. API-Referenz – PHP-Klassen

### 3.1 `FriendsOfRedaxo\Fields\FieldsTagging`

Statischer Helper für das Tagging-Feldformat `[{text,color}, …]`.

| Methode | Signatur | Zweck |
|---|---|---|
| `decode` | `decode(string $raw): array` | JSON aus DB → normalisiertes Tags-Array (dedupliziert, validierte Farben) |
| `encode` | `encode(array $tags): string` | Tags-Array → JSON-String für die DB |
| `getTexts` | `getTexts(array $tags): array` | Nur die Tag-Texte als Liste |
| `toHtml` | `toHtml(array $tags, string $emptyText = ''): string` | Tags als farbige Chip-Spans |
| `chipHtml` | `chipHtml(string $text, string $color = ''): string` | Einzelnen Chip-Span rendern |
| `fromRaw` | `fromRaw(string $raw, string $emptyText = ''): string` | Kurzform von `toHtml(decode($raw))` |
| `collectFromTable` | `collectFromTable(string $table, string $field): array` | Alle eindeutigen `{text,color}` aus einer DB-Spalte (Tabelle **ohne** `rex_`-Präfix) |
| `collectTextsFromTable` | `collectTextsFromTable(string $table, string $field): array` | Nur Texte aller eindeutigen Tags |
| `sqlHasTag` | `sqlHasTag(string $field, string $tagText): string` | SQL-`WHERE`-Snippet für JSON-Tag-Suche (MySQL ≥ 5.7) |
| `filterByTag` | `filterByTag(array $rows, string $field, string $tagText): array` | PHP-Filter über Datensatz-Array |
| `renderWidgetOpen` | `renderWidgetOpen(string $targetId, array $options = []): string` | Tagging-Widget-HTML für eigene `rex_form`-Integrationen |

Konstante `FieldsTagging::DEFAULT_COLORS` enthält die Standard-Farbpalette (10 WCAG-kontraststarke Farben).

### 3.2 `FriendsOfRedaxo\Fields\OpeningHoursHelper`

Instanziierbarer Helper für strukturierte Öffnungszeiten.

| Methode | Signatur | Zweck |
|---|---|---|
| `__construct` | `__construct(?string $json, string $locale = 'de')` | JSON-String und Sprache (`de`/`en`) übergeben |
| `setTranslations` | `setTranslations(string $locale, array $translations): self` | Eigene Übersetzungen registrieren |
| `setLocale` | `setLocale(string $locale): self` | Aktive Sprache wechseln |
| `translate` | `translate(string $key, ?string $fallback = null): string` | Übersetzung per Punkt-Notation (z. B. `labels.we_are_open`) |
| `hasData` | `hasData(): bool` | Gültige Daten vorhanden? |
| `getNote` | `getNote(): ?string` | Hinweistext aus dem Editor |
| `hasNote` | `hasNote(): bool` | Hinweistext vorhanden? |
| `getRegular` | `getRegular(bool $shortLabels = false): array` | Alle Wochentage mit Label, Zeiten, `is_today`, `is_open`, … |
| `getRegularGrouped` | `getRegularGrouped(): array` | Wochentage mit identischen Zeiten zusammenfassen (Mo–Fr-Gruppen) |
| `getSpecial` | `getSpecial(?int $limit = null, bool $futureOnly = false): array` | Sondertage (sortiert, optional limitiert) |
| `getToday` | `getToday(): ?array` | Heutiger Tag (Eintrag aus `getRegular`) |
| `isOpenNow` | `isOpenNow(): bool` | Aktuell geöffnet? (berücksichtigt Sondertage) |
| `getCurrentStatus` | `getCurrentStatus(): array` | `[is_open, label, today, next_change, next_change_label]` |
| `getRawData` | `getRawData(): array` | Roh-Array aus dem JSON |

### 3.3 `rex_yform_value_fields_faq`

Klassisch-globale YForm-Wertklasse (kein Namespace).

| Methode | Signatur | Zweck |
|---|---|---|
| `getSchemaJsonLd` | `getSchemaJsonLd(string $json): string` | Schema.org-`FAQPage`-JSON-LD `<script>`-Tag aus dem FAQ-JSON erzeugen |
| `getListValue` | `getListValue(array $params): string` | YForm-Listenrenderer (intern verwendet) |

### 3.4 `rex_yform_value_fields_iban`

| Methode | Signatur | Zweck |
|---|---|---|
| `isValidFormat` | `isValidFormat(string $iban): bool` | IBAN-Format-Validierung (Länge, Mod-97-Prüfsumme) |
| `getListValue` | `getListValue(array $params)` | YForm-Listenrenderer |

### 3.5 `rex_yform_value_fields_social_web`

| Methode | Signatur | Zweck |
|---|---|---|
| `getPlatforms` | `getPlatforms(): array` | Liste aller 24 vordefinierten Plattformen `[key => [name, icon, color]]` |
| `getListValue` | `getListValue(array $params)` | YForm-Listenrenderer |

```php
foreach (rex_yform_value_fields_social_web::getPlatforms() as $key => $meta) {
    echo $key . ': ' . $meta['name'] . ' ' . $meta['icon'] . '<br>';
}
```

### 3.6 `rex_yform_value_fields_inline_select`

Hilfsmethoden für das Choices-Parsing des Inline-Select-Felds.

| Methode | Signatur | Zweck |
|---|---|---|
| `resolveChoices` | `resolveChoices(string $rawChoices, string $query): array` | Liefert Endgültige `[value => label]`-Map (statisch + Query-Quelle) |
| `parseChoices` | `parseChoices(string $raw): array` | Parst die `key=value`-Syntax aus dem Feldwert |
| `parseChoicesFromQuery` | `parseChoicesFromQuery(string $query): array` | Lädt Choices aus SQL-Query |
| `parseColors` | `parseColors(string $raw): array` | Parst Farbzuordnungen `key=#hex` |
| `parseLockValues` | `parseLockValues(string $raw): array` | Werte, die Inline-Editing sperren |
| `isValueLocked` | `isValueLocked(string $value, string $lockRaw): bool` | Prüft Locked-Zustand |
| `renderColorDot` | `renderColorDot(string $color): string` | HTML eines farbigen Punkts |
| `renderOptionContent` | `renderOptionContent(string $label, string $color): string` | Option-HTML für Listen/Selectpicker |

### 3.7 Weitere `getListValue`-Hooks

Folgende Feldklassen stellen `public static function getListValue(array $params)` für die YForm-Listenanzeige bereit – nicht für den Frontend-Einsatz gedacht:

`rex_yform_value_fields_opening_hours`, `rex_yform_value_fields_rating`, `rex_yform_value_fields_contacts`, `rex_yform_value_fields_tagging`, `rex_yform_value_fields_table`, `rex_yform_value_fields_inline`, `rex_yform_value_fields_inline_switch`, `rex_yform_value_fields_inline_number`.

---

## 4. REX-API-Endpunkte

Alle Endpunkte sind klassische `rex_api_function`-Implementierungen und liefern JSON.

### 4.1 IBAN-Validierung – `fields_iban_validate`

- **Klasse:** `FriendsOfRedaxo\Fields\rex_api_fields_iban_validate`
- **Frontend-tauglich:** ja (`$published = true`)
- **Methode:** `GET`
- **Parameter:** `iban` (string, Pflicht)

```
GET index.php?rex-api-call=fields_iban_validate&iban=DE89370400440532013000
```

Erfolgreiche Antwort:

```json
{
    "valid": true,
    "iban": "DE89 3704 0044 0532 0130 00",
    "bank": "Commerzbank",
    "bic": "COBADEFFXXX",
    "city": "Aachen"
}
```

Fallback wenn openIBAN.com nicht erreichbar ist (nur lokale Format-/Prüfsummen-Validierung):

```json
{ "valid": true, "local_only": true, "iban": "DE89370400440532013000",
  "message": "Local validation only (API unavailable)" }
```

Fehler:

```json
{ "valid": false, "error": "Invalid IBAN format", "iban": "…" }
```

HTTP-Status `400` bei fehlender IBAN, `200` sonst.

### 4.2 Inline-Update – `fields_inline_update`

- **Klasse:** `FriendsOfRedaxo\Fields\rex_api_fields_inline_update`
- **Backend-only:** `$published = false` – erfordert eingeloggten REDAXO-Benutzer
- **Methode:** `POST`
- **CSRF:** erforderlich (`rex_csrf_token::factory('fields_inline_edit')`)
- **Permissions:** Admin **oder** `yform[<tabellenname inkl. Präfix>]`

Parameter:

| Name | Typ | Beschreibung |
|---|---|---|
| `table` | string | YForm-Tabellenname inkl. `rex_`-Präfix |
| `field` | string | Feldname |
| `id` | int | Datensatz-ID |
| `value` | string | Neuer Wert |
| `_csrf_token` | string | CSRF-Token aus `rex_csrf_token::factory('fields_inline_edit')->getValue()` |

Erfolgsantwort:

```json
{ "success": true, "id": 42, "value": "neu", "formatted": "1.234,50" }
```

Bei Fehlern (CSRF, Permission, Lock, ungültiger Wert):

```json
{ "success": false, "message": "Permission denied for table rex_products" }
```

Hinweis: Für `fields_inline_select` werden Locked-Werte und nicht erlaubte Choices serverseitig geprüft.

### 4.3 Tagging-Vorschläge – `fields_tagging_suggest`

- **Klasse:** `FriendsOfRedaxo\Fields\rex_api_fields_tagging_suggest`
- **Backend-only:** `$published = false`, prüft `rex::isBackend()` und Login

Parameter:

| Name | Typ | Beschreibung |
|---|---|---|
| `table` | string | Tabellenname (mit oder ohne `rex_`-Präfix) |
| `field` | string | Spaltenname |

Antwort:

```json
{
    "success": true,
    "tags": [
        { "text": "php", "color": "#2980b9" },
        { "text": "redaxo", "color": "#27ae60" }
    ]
}
```

Liefert max. 500 Quellzeilen, parst sowohl das aktuelle JSON-Format als auch Legacy-Kommalisten und liefert pro Tag-Text die zuletzt verwendete Farbe.

---

## 5. Layout-/Logik-Felder (Backend-Konfiguration)

Diese Felder erzeugen keine Datenbankspalten. Sie strukturieren ausschließlich die Backend-Formulare des YForm-Managers und werden in der Feldkonfiguration parametrisiert.

### 5.1 Conditional – `fields_conditional`

| Option | Beschreibung |
|---|---|
| Quellfeld | Feld, dessen Wert geprüft wird |
| Operator | `==`, `!=`, `>`, `<`, `contains`, `empty`, `!empty`, `switch` |
| Vergleichswert | Erwarteter Wert (bei `switch` irrelevant) |
| Zielfelder | Feldnamen oder CSS-Selektoren, kommasepariert |
| Aktion | `show` oder `hide` |

### 5.2 Tabs / Akkordeons – `fields_interactive`

1. `fields_interactive` (Typ: **Tab Start**, Label „Basisdaten“, Gruppen-ID `1`)
2. … Felder für Tab 1 …
3. `fields_interactive` (Typ: **Tab Start**, Label „Erweitert“, Gruppen-ID `1`)
4. … Felder für Tab 2 …
5. `fields_interactive` (Typ: **Gruppe Ende**, Gruppen-ID `1`)

Statt `Tab Start` ist auch **Akkordeon Start** oder **Fieldset Start** möglich.

### 5.3 Grid / Layout – `fields_structure`

- **Start**: beginnt einen CSS-Grid-Container
- **Layout**: Spalten via Grid-Template (`1fr 1fr`, `1fr 2fr`, …)
- **Gap**: Abstand zwischen Spalten
- **Ende**: schließt den Grid-Container

Alle Felder zwischen Start und Ende werden ins Grid aufgenommen.

---

## Datenformate (JSON-Schemata)

### Tabelle

```json
{
    "caption": "Preisliste",
    "has_header_row": true,
    "has_header_col": false,
    "rows": [
        ["Produkt A", "2024-01-01", "10,00 €"],
        ["Produkt B", "2024-02-01", "20,00 €"]
    ],
    "constraints": {
        "cols": ["text", "date", "number"]
    }
}
```

### Social Web

```json
[
    {
        "platform": "facebook",
        "url": "https://facebook.com/example",
        "label": "Facebook Fanpage",
        "icon": "fa-facebook-f",
        "color": "#1877f2"
    }
]
```

### Öffnungszeiten

```json
{
    "regular": {
        "monday": {
            "status": "open",
            "times": [
                {"from": "08:00", "to": "12:00"},
                {"from": "13:00", "to": "17:00"}
            ]
        },
        "saturday": { "status": "closed", "times": [] }
    },
    "special": [
        { "date": "2024-12-24", "status": "closed", "label": "Heiligabend" }
    ],
    "note": "Termine nur nach Vereinbarung"
}
```

### FAQ

```json
[
    { "question": "Wie lauten die Öffnungszeiten?", "answer": "Mo–Fr von 8–17 Uhr" }
]
```

### Kontakte

```json
[
    {
        "firstname": "Max",
        "lastname": "Mustermann",
        "company": "Muster GmbH",
        "email": "max@muster.de",
        "locations": [
            { "street": "Musterweg 1", "city": "Musterstadt" }
        ]
    }
]
```

### Tagging

```json
[
    { "text": "php",    "color": "#2980b9" },
    { "text": "redaxo", "color": "#27ae60" }
]
```

---

## Unterstützte Plattformen (Social Web)

Facebook, Instagram, Twitter/X, LinkedIn, Xing, YouTube, TikTok, Pinterest, Threads, Mastodon, Bluesky, WhatsApp, Telegram, GitHub, Vimeo, Flickr, Snapchat, Reddit, Twitch, Discord, Spotify, SoundCloud, RSS, Benutzerdefiniert

---

## Autor

**Friends Of REDAXO**

- https://www.redaxo.org
- https://github.com/FriendsOfREDAXO

## Credits

**Projektleitung:** [Thomas Skerbis](https://github.com/skerbis)

**Konzept & Entwicklung:** Erstellt mit Unterstützung von GitHub Copilot (Gemini)

**Basiert auf:**

- OpeningHoursHelper inspiriert durch [Template Manager](https://github.com/FriendsOfREDAXO/template_manager) von [Thomas Skerbis](https://github.com/skerbis)
- IBAN-Validierung über [openIBAN.com](https://openiban.com) (kostenloser Dienst)

## Lizenz

MIT-Lizenz – siehe [LICENSE.md](LICENSE.md)
