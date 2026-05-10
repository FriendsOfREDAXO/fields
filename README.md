# Fields – Erweiterte redaktionelle YForm-Feldtypen für REDAXO

![REDAXO](https://img.shields.io/badge/REDAXO-%3E%3D5.17-red) ![YForm](https://img.shields.io/badge/YForm-%3E%3D5.0-blue) ![PHP](https://img.shields.io/badge/PHP-%3E%3D8.1-purple)

Das AddOn **Fields** stellt eine umfangreiche Sammlung zusätzlicher YForm-Value-Feldtypen bereit, die häufig benötigte Eingabemuster und Strukturierungsmöglichkeiten abdecken – von Social-Media-Profilen und Tabellen bis hin zu Tabs, Akkordeons und Grid-Layouts.

## Neu in 1.3.0

- **Metainfo Tagging**: Neuer Feldtyp 'Fields Tagging' für die Metainfo-Verwaltung. Ermöglicht farbige Tags mit Autocomplete-Unterstützung aus konfigurierbarer Quelltabelle direkt im Metainfo-Kontext (siehe [Metainfo Tagging](#metainfo-tagging) weiter unten).


## Funktionen

### Spezielle Eingabetypen (UI Controls)
- **Inline Switch** – Moderner, eckiger Toggle-Switch für Boolean-Werte (Liste & Formular)
- **Inline Edit** – Direktes Bearbeiten von Text- und Textarea-Feldern in der YForm-Listenansicht (Click-to-Edit)
- **Inline Number** – Zahlenfeld mit Inline-Editing, Präfix/Suffix (z.B. €/km), Min/Max und Step-Optionen
- **Icon Picker** – Icon-Auswahl aus Font Awesome und/oder UIkit-Iconsets
- **Star Rating** – Bewertungsfeld (1-10 Sterne) mit visueller Eingabe
- **IBAN** – IBAN-Eingabe mit Live-Validierung über openIBAN.com (serverseitig geproxied)
- **Tagging** – Farbige Schlagwörter (Chips) mit Custom-Color-Picker und WCAG-Kontrastprüfung

### Tagging: Suche im YForm-Manager

Das Feld `fields_tagging` bringt eine eigene Suchintegration fuer den YForm-Tablemanager mit:

- Mehrfachauswahl als **Checkbox-Liste** (statt Freitext)
- Farbige Marker je Tag in der Auswahl
- Zusatzauswahl fuer **(empty)** und **Nicht leer**
- Robuste Filterung fuer `NULL`, leere Strings und `[]`

### Metainfo Tagging

Das AddOn stellt den Feldtyp **"Fields Tagging"** für die Metainfo-Verwaltung zur Verfügung. Damit lassen sich in Artikel, Medien und Kategorien farbige Tags mit Autocomplete verwalten.

#### Schritt-für-Schritt: Metainfo-Feld für Tagging erstellen

1. **Im Backend**: Gehe zu **Verwaltung → Metainfo**
2. **Neue Spalte**: Klicke auf **Neue Spalte** oder **Neue Spalte hinzufügen**
3. **Feldkonfiguration**:
   - **Bezeichnung**: z.B. "Artikel-Tags"
   - **Feldname**: z.B. `art_tags` (Präfix `art_` = Artikel, `med_` = Medien, `clang_` = Kategorien)
   - **Feldtyp**: Wähle **"Fields Tagging"** aus der Liste
   - **Datentyp**: Bleibt auf `text`
4. **Speichern**: Klick auf Speichern
5. **In Artikel verwenden**:
   - Gehe zu einem Artikel
   - Scrolle zu "Metadaten"
   - Das neue Tagging-Feld ist jetzt verfügbar
   - Gib Tags ein, wähle Farben, speichere

#### Konfiguration (Parameter im Metainfo-Feldtyp)

Das Tagging-Feld akzeptiert folgende optionale Parameter im `extra`-Feld:

```json
{
  "source_table": "rex_article",
  "source_field": "art_tags",
  "max_tags": 20,
  "colors": ["#2980b9", "#27ae60", "#e74c3c", "#f39c12", "#9b59b6"]
}
```

| Parameter | Beschreibung | Beispiel |
|---|---|---|
| `source_table` | Tabelle für Autocomplete-Vorschläge | `rex_article` oder `my_custom_table` |
| `source_field` | Spalte in der Quelltabelle zum Sammeln von Tags | `art_tags` |
| `max_tags` | Maximale Anzahl Tags pro Datensatz | `20` |
| `colors` | Verfügbare Farben (Hex-Codes) | `["#2980b9", "#27ae60", ...]` |

#### Beispiel: Tags in der Frontend-Ausgabe

```php
<?php
use FriendsOfRedaxo\Fields\FieldsTagging;

// Tags aus Metainfo laden
$tagsJson = rex_metainfo::getMetaInfo('art_tags', $article_id);

// Als HTML-Chips rendern
echo FieldsTagging::fromRaw($tagsJson, 'Keine Tags vorhanden');

// Oder manuell decodieren
$tags = FieldsTagging::decode($tagsJson);
foreach ($tags as $tag) {
    echo '<span style="background-color: ' . rex_escape($tag['color']) . '; padding: 4px 8px; border-radius: 12px; color: white;">'
         . rex_escape($tag['text'])
         . '</span> ';
}
?>
```

#### Troubleshooting

- **Widget wird nicht angezeigt**: Stelle sicher, dass der Feldtyp `"Fields Tagging"` in der Metainfo-Spalte gewählt ist.
- **Autocomplete funktioniert nicht**: Prüfe, dass `source_table` und `source_field` korrekt in der Feldkonfiguration gesetzt sind und die Quelltabelle existiert.
- **Keine Farben sichtbar**: Überprüfe, dass `colors`-Array gültige Hex-Codes enthält (z.B. `#2980b9`).
- **Tags verschwinden nach dem Speichern**: Stelle sicher, dass die Spalte vom Typ `text` ist und genug Platz für JSON hat.



### Komplexe Datentypen (Repeater & Strukturen)
- **Tabelle** – Barrierefreier Tabelleneditor mit flexiblen Spalten/Zeilen, Min/Max-Constraints und erweiterten Datentypen (Medien, Links, Textarea)
- **Social Web** – Repeater für Social-Media-Profile mit 24 vordefinierten Plattformen
- **Öffnungszeiten** – Wochentags-Editor mit Zeitfenstern, Sondertagen und Notizen
- **Kontakte** – Flexible Kontaktkarten mit konfigurierbaren optionalen Feldern (Avatar, Firma, Adresse, etc.)
- **FAQ** – Frage/Antwort-Repeater mit automatischer Schema.org FAQPage JSON-LD Ausgabe

### Layout & Logik
- **Tabs & Akkordeons** – Gruppierung von Feldern in Tabs, Akkordeons oder Fieldsets
- **Grid & Layout** – Mehrspaltige Anordnung von Feldern (Grid/Flexbox)
- **Conditional** – Bedingte Feldgruppen (Felder ein-/ausblenden basierend auf anderen Feldwerten)

## Installation

1. Im REDAXO-Installer nach **fields** suchen und installieren
2. Oder: Repository nach `redaxo/src/addons/fields` klonen und im Backend aktivieren

## Konfiguration

Unter **AddOns → Fields → Einstellungen** können folgende Optionen konfiguriert werden:

| Einstellung | Beschreibung |
|---|---|
| Icon-Set (Social Web) | Font Awesome und/oder UIkit Icons |
| Kontakt-Felder | Welche optionalen Felder bei Kontakten verfügbar sind |
| IBAN-Proxy | openIBAN.com-Proxy aktivieren/deaktivieren |
| Icon Picker Sets | Verfügbare Icon-Bibliotheken |

## Verwendung im YForm-Tablemanager

Die Feldtypen erscheinen nach der Installation automatisch in der YForm-Feldauswahl. Alle Felder sind unter dem Typ **value** verfügbar:

| Feldtyp | DB-Typ | Beschreibung |
|---|---|---|
| `fields_social_web` | `mediumtext` | Social-Media-Profile als JSON |
| `fields_opening_hours` | `mediumtext` | Öffnungszeiten als JSON |
| `fields_contacts` | `mediumtext` | Kontaktdaten als JSON |
| `fields_table` | `mediumtext` | Tabelle mit Spalten- und Zeilendaten sowie Meta-Infos |
| `fields_iban` | `varchar(34)` | IBAN mit Validierung |
| `fields_faq` | `mediumtext` | FAQ-Einträge als JSON |
| `fields_conditional` | *kein DB-Feld* | Steuert Sichtbarkeit anderer Felder |
| `fields_interactive` | *kein DB-Feld* | Formularfelder in Tabs/Akkordeons gruppieren |
| `fields_structure` | *kein DB-Feld* | Formularfelder mehrspaltig anordnen (Grid) |
| `fields_icon_picker` | `varchar(191)` | Ausgewähltes Icon (z.B. `fa-home`) |
| `fields_rating` | `int` | Ganzzahlige Bewertung (Default 1-5 Sterne) |
| `fields_inline_switch` | `tinyint(1)` | Boolescher Switch für Listen- & Formularansicht |
| `fields_inline` | `text`, `mediumtext` | Text/Textarea mit Inline-Editing in der Liste |
| `fields_inline_number` | `int`, `float`, `decimal` | Zahlenfeld mit Inline-Editing (Präfix/Suffix/Min/Max) |
| `fields_inline_select` | `varchar(191)`, `text` | Auswahlfeld mit Inline-Editing, Selectpicker, Farben, optionaler Query-Quelle und Lock-Status |
| `fields_tagging` | `text` | Farbige Tag-Chips als JSON mit WCAG-konformem Farbpicker |

## Frontend-Ausgabe mit Fragmenten

Für jedes Feld stehen Frontend-Fragmente in vier Framework-Varianten bereit:

- `fields/bootstrap3/` – Bootstrap 3
- `fields/uikit3/` – UIkit 3
- `fields/tailwind/` – Tailwind CSS
- `fields/plain/` – Framework-unabhängig (semantisches HTML)

### Social Web

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('social_web'));
$fragment->setVar('class', 'my-social-links');
echo $fragment->parse('fields/uikit3/social_web.php');
```

### Öffnungszeiten

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('opening_hours'));
$fragment->setVar('show_status', true);
echo $fragment->parse('fields/bootstrap3/opening_hours.php');
```

Der `OpeningHoursHelper` bietet zusätzliche Methoden:

```php
use FriendsOfRedaxo\Fields\OpeningHoursHelper;

$data = json_decode($item->getValue('opening_hours'), true);
$helper = new OpeningHoursHelper($data);

// Aktueller Status
if ($helper->isOpenNow()) {
    echo 'Jetzt geöffnet';
}

// Heutiger Tag
$today = $helper->getToday();

// Gruppierte Öffnungszeiten (Mo–Fr zusammengefasst wenn gleich)
$grouped = $helper->getRegularGrouped();
```

### Tabelle (Barrierefrei)

Die Tabelle speichert Daten und Spaltenkonfiguration getrennt, was responsive Darstellungen erleichtert.

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('table'));
echo $fragment->parse('fields/bootstrap3/table.php');
```

**Funktionen des Editors:**
- Definierbare **Min/Max-Grenzen** für Zeilen und Spalten
- Unabhängige **Textausrichtung** für Kopf- und Datenzellen (Links, Mitte, Rechts/Zahl)
- **Inline-Hinzufügen** von Zeilen und Spalten mitten in der Tabelle
- Strict Mode für Kopfzeilen/spalten (zwingend an/aus oder optional)
- **Erweiterte Datentypen (Optional aktivierbar):**
  - **Medien:** Integration des Medienpools (REX_MEDIA)
  - **Links:** Integration der Linkmap (REX_LINK)
  - **Mehrzeiliger Text:** Textarea für umfangreichere Inhalte
  - **Zahlen:** Automatisch rechtsbündig formatiert

### Kontakte

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('contacts'));
echo $fragment->parse('fields/tailwind/contacts.php');
```

### FAQ mit Schema.org

```php
// Fragment-Ausgabe (inkl. Schema.org JSON-LD)
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('faq'));
$fragment->setVar('schema', true); // Schema.org FAQPage ausgeben
echo $fragment->parse('fields/uikit3/faq.php');
```

Das Schema.org JSON-LD kann auch separat erzeugt werden:

```php
$items = json_decode($item->getValue('faq'), true);
echo rex_yform_value_fields_faq::getSchemaJsonLd($items);
```

### IBAN

```php
// Serverseitige Validierung
$isValid = rex_yform_value_fields_iban::isValidFormat('DE89370400440532013000');
```

Die IBAN-Validierung über openIBAN.com erfolgt live im Backend-Formular. Der API-Aufruf wird über den eigenen Server geproxied, um den API-Key nicht im Frontend zu exponieren.

### Conditional (Bedingte Felder)

Das Conditional-Feld benötigt keine Frontend-Ausgabe – es steuert nur die Sichtbarkeit von Feldern im Backend:

- **Quellfeld**: Das Feld, dessen Wert geprüft wird
- **Operator**: `==`, `!=`, `>`, `<`, `contains`, `empty`, `!empty`, `switch`
  - *Hinweis zu Switch:* Vergleicht den aktuellen Wert mit dem Ziel-Selektor. Ist z.B. der Wert `video`, wird das Ziel `.group-video` oder `type_video` angezeigt.
- **Vergleichswert**: Der erwartete Wert (bei `switch` irrelevant)
- **Zielfelder**: Kommagetrennte Liste von Feldnamen ODER CSS-Selektoren
  - *Beispiel Feldnamen:* `street,city` (sucht nach Feldern oder Wrapper-IDs wie `*-city`)
  - *Beispiel CSS-Selektor:* `.my-group,#special-section` (blendet beliebige Elemente ein/aus)
- **Aktion**: `show` oder `hide`

### Tabs & Akkordeons (Interaktiv)

Mit `fields_interactive` lassen sich Formularfelder im Backend gruppieren, um Übersichtlichkeit zu schaffen:

- **Tabs**: Mehrere Felder werden in Reitern dargestellt.
- **Akkordeon**: Felder werden in aufklappbaren Bereichen organisiert.
- **Fieldset**: Eine einfache Gruppierung mit Legende.

**Verwendung:**
1. Feld `fields_interactive` anlegen
2. Typ wählen (z.B. Tab Start, Akkordeon Start oder Gruppe Ende)
3. Gleiche `Gruppen-ID` für zusammengehörige Elemente vergeben

Beispielaufbau für 2 Tabs:
1. `fields_interactive` (Typ: Tab, Label: "Basisdaten", Gruppen-ID: 1)
2. ... Felder für Tab 1 ...
3. `fields_interactive` (Typ: Tab, Label: "Erweitert", Gruppen-ID: 1)
4. ... Felder für Tab 2 ...
5. `fields_interactive` (Typ: Ende, Gruppen-ID: 1)

### Grid & Layout (Struktur)

`fields_structure` ermöglicht die mehrspaltige Anordnung von Feldern mittels CSS Grid.

- **Start**: Beginnt einen Grid-Container.
- **Layout**: Definition der Spalten (z.B. `1fr 1fr` für 50/50, `1fr 2fr` für 1/3 zu 2/3).
- **Gap**: Abstand zwischen den Spalten.

Alle Felder zwischen "Start" und "Ende" (oder dem nächsten "Start") werden in das Grid aufgenommen.

### Icon Picker

```php
$icon = $item->getValue('icon');
// Font Awesome
echo '<i class="' . rex_escape($icon) . '"></i>';
// UIkit
echo '<span uk-icon="icon: ' . rex_escape(str_replace('uk-icon-', '', $icon)) . '"></span>';
```

### Star Rating (Bewertung)

```php
$fragment = new rex_fragment();
$fragment->setVar('value', $item->getValue('rating'));
$fragment->setVar('max', 5); // Optional, default 5
echo $fragment->parse('fields/bootstrap3/rating.php');
```

## API-Endpunkte

### IBAN-Validierung

```
GET index.php?rex-api-call=fields_iban_validate&iban=DE89370400440532013000
```

Antwort:
```json
{
    "valid": true,
    "iban": "DE89 3704 0044 0532 0130 00",
    "bank": "Commerzbank",
    "bic": "COBADEFFXXX",
    "city": "Aachen"
}
```

## Datenformat

Die komplexen Feldtypen speichern ihre Inhalte als JSON-Strukturen, um maximale Flexibilität zu gewährleisten. Hier die Schemata im Detail:

### Tabelle (Table)
```json
{
    "caption": "Preisliste",
    "has_header_row": true,
    "has_header_col": false,
    "rows": [
         ["Produkt A", "2024-01-01", "10,00 €"], // Zeile 1
         ["Produkt B", "2024-02-01", "20,00 €"]  // Zeile 2
    ],
    // Optional: Constraints, falls Spalten konfiguriert sind
    "constraints": {
        "cols": [
            "text",     // Spalte 1: einfacher Text (left)
            "date",     // Spalte 2: Datum (left)
            "number"    // Spalte 3: Zahl (right)
        ]
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
        "icon": "fa-facebook-f", // FontAwesome Klasse
        "color": "#1877f2"       // Markenfarbe
    }
]
```

### Öffnungszeiten (Opening Hours)
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
        "saturday": {
            "status": "closed",
            "times": []
        }
    },
    "special": [
        {
            "date": "2024-12-24", 
            "status": "closed", 
            "label": "Heiligabend"
        }
    ],
    "note": "Termine nur nach Vereinbarung"
}
```

### FAQ (Question/Answer)
```json
[
    {
        "question": "Wie lauten die Öffnungszeiten?",
        "answer": "Mo–Fr von 8–17 Uhr"
    }
]
```

### Kontakte (Contacts)
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

## Inline Editing (Listenansicht)

Mit dem Feldtyp `fields_inline` können Text- und Textarea-Felder in der YForm-Tabellenübersicht direkt bearbeitet werden.

### Einrichtung
1. Im Table Manager den Datentyp des gwünschten Feldes auf `fields_inline` setzen.
2. In den Optionen wählen, ob es sich um ein einzeiliges Textfeld oder eine Textarea handelt.

### Features
- **Click-to-Edit**: Anklicken aktiviert den Bearbeitungsmodus.
- **Speichern**: Enter (bei Textfeldern) oder Klick auf den Haken.
- **Abbruch**: ESC oder Klick auf das X.
- **Events**: Löst beim Speichern die Standard YForm-Events (`YFORM_DATA_UPDATED`) aus, sodass andere AddOns (z.B. URL AddOn) Änderungen registrieren.

---

## Tags & Tagging (`fields_tagging`)

Der Feldtyp **`fields_tagging`** ermöglicht die Vergabe farbiger Schlagwörter direkt im YForm-Formular. Tags werden als JSON gespeichert und tragen jeweils einen Text und eine Farbe.

Das gleiche Tagging-Widget steht auch als Metainfo-Feld zur Verfügung. Dort kann der Typ `Fields Tagging` gewählt werden; optionale Vorschlagsdaten lassen sich ueber den Feld-Parameter `params` setzen, z.B. `source_table=rex_products&source_field=tags&max_tags=5`.

Für Metainfo ist am einfachsten die Spalte als Vorschlagsquelle zu nehmen, in der bereits Tags gespeichert werden. Beispiel fuer eine Artikel-Metainfo-Spalte:

`source_table=rex_article&source_field=art_tags`

Wenn du im Metainfo-Feld nichts einträgst, versucht das Widget die passende Quelltabelle automatisch aus dem Feldnamen abzuleiten. Bei `art_` wird also `rex_article`, bei `med_` `rex_media` und bei `clang_` `rex_clang` verwendet.

Das funktioniert genauso fuer globale Metainfo-Felder auf Artikeln, Medien, Kategorien oder Sprachen. Wenn keine Quelle gepflegt ist, bleibt das Feld trotzdem speicherbar, nur die Vorschläge werden nicht geladen.

**DB-Typ:** `text`  
**Datenformat:** `[{"text":"php","color":"#2980b9"}, ...]`

### Schritt-fuer-Schritt: Verwendung als Metainfo-Feld

1. Metainfo-Feld anlegen:
    - Prefix passend zum Kontext waehlen (`art_`, `med_`, `clang_`)
    - Spaltenname setzen, z.B. `tags` (ergibt z.B. `art_tags`)
    - Feldtyp auf `Fields Tagging` setzen

2. Parameter setzen (optional, aber empfohlen):
    - Im Feld **Parameter** eintragen, z.B. `source_table=rex_article&source_field=art_tags`
    - Optional begrenzen: `max_tags=5`
    - Komplettbeispiel: `source_table=rex_article&source_field=art_tags&max_tags=5`

3. Feld speichern und im Objekt bearbeiten:
    - Im Artikel/Medium/Sprache Tags ueber den Button `Tags bearbeiten` setzen
    - Mit `Metadaten aktualisieren` speichern

4. Ergebnis pruefen:
    - Nach dem Reload muessen die gespeicherten Chips wieder sichtbar sein
    - In der Datenbank steht der Wert als JSON in der Metainfo-Spalte (z.B. `art_tags`)

5. Wenn keine Vorschlaege erscheinen:
    - Quelle pruefen (`source_table` + `source_field`)
    - Es muessen in der Quelle bereits Datensaetze mit Tags vorhanden sein
    - Ohne konfigurierte Quelle bleibt das Speichern moeglich, nur die Vorschlagsliste bleibt leer

### Einrichtung im Table Manager

| Parameter | Beschreibung |
|---|---|
| `source_table` | Tabelle fuer Vorschlaege (mit oder ohne `rex_` Prefix, z.B. `rex_article` oder `article`) |
| `source_field` | Spaltenname der Quelle |
| `max_tags` | Maximale Tag-Anzahl (0 = unbegrenzt) |
| `notice` | Hinweistext unter dem Feld |

### Widget

- Button öffnet ein Editor-Panel mit Farbpalette und Texteingabe
- **Eigene Farbe** per Farbpicker wählbar – WCAG-Kontrastprüfung (≥ 3.0:1) blockiert zu helle Farben für weiße Schrift
- Vorschläge aus einer konfigurierbaren Quelltabelle (AJAX)
- Vorschlag anklicken → hinzufügen, erneut anklicken → entfernen

### Frontend-Ausgabe mit `FieldsTagging`

Die Hilfsklasse `FieldsTagging` (Namespace `FriendsOfRedaxo\Fields`, automatisch via REDAXO-Autoloader geladen) stellt alle nötigen Methoden bereit:

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

// Tags aus DB-Wert dekodieren
$tags = FieldsTagging::decode($item->getValue('tags'));
// → [["text" => "php", "color" => "#2980b9"], ...]

// Nur Texte als String-Array
$texte = FieldsTagging::getTexts($tags);
// → ["php", "redaxo"]

// Als farbige HTML-Chips ausgeben
echo FieldsTagging::toHtml($tags);
// → <span style="background:#2980b9;...">php</span> ...

// Direkt aus DB-Rohwert (Kurzform)
echo FieldsTagging::fromRaw($item->getValue('tags'), '–');

// Einzelnen Chip rendern
echo FieldsTagging::chipHtml('php', '#2980b9');

// Tags enkodieren (z.B. nach manueller Manipulation)
$json = FieldsTagging::encode($tags);
```

### Datenbankabfragen mit `FieldsTagging`

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

// Alle eindeutigen Tags aus einer Tabellenspalte sammeln (alphabetisch sortiert)
$alleTags = FieldsTagging::collectFromTable('rex_products', 'tags');
// → [["text" => "php", "color" => "#2980b9"], ["text" => "redaxo", ...], ...]

// Nur Texte
$texte = FieldsTagging::collectTextsFromTable('rex_products', 'tags');
// → ["php", "redaxo", ...]

// SQL-WHERE-Fragment für MySQL JSON-Suche (MySQL >= 5.7)
$sql = rex_sql::factory();
$rows = $sql->getArray(
    'SELECT * FROM rex_products WHERE ' . FieldsTagging::sqlHasTag('tags', 'php')
);

// Mit YOrm (rex_yorm)
$products = rex_yorm::table('rex_products')
    ->whereRaw(FieldsTagging::sqlHasTag('tags', 'php'))
    ->find();

// PHP-Filter (wenn Ergebnisse bereits im Speicher)
$gefiltert = FieldsTagging::filterByTag($rows, 'tags', 'php');
```

### Listenansicht

In der YForm-Tabellenübersicht werden Tags automatisch als farbige Chips dargestellt (via `getListValue()`).

---

## Unterstützte Plattformen (Social Web)

Facebook, Instagram, Twitter/X, LinkedIn, Xing, YouTube, TikTok, Pinterest, Threads, Mastodon, Bluesky, WhatsApp, Telegram, GitHub, Vimeo, Flickr, Snapchat, Reddit, Twitch, Discord, Spotify, SoundCloud, RSS, Benutzerdefiniert

## Voraussetzungen

- REDAXO >= 5.17
- YForm >= 4.0
- PHP >= 8.1

## Autor

**Friends Of REDAXO**

* https://www.redaxo.org
* https://github.com/FriendsOfREDAXO

## Credits

**Projektleitung**

[Thomas Skerbis](https://github.com/skerbis)

**Konzept & Entwicklung**

Erstellt mit Unterstützung von GitHub Copilot (Gemini)

**Basiert auf:**

- OpeningHoursHelper inspiriert durch [Template Manager](https://github.com/FriendsOfREDAXO/template_manager) von [Thomas Skerbis](https://github.com/skerbis)
- IBAN-Validierung über [openIBAN.com](https://openiban.com) (kostenloser Dienst)

## Lizenz

MIT Lizenz – siehe [LICENSE.md](LICENSE.md)
