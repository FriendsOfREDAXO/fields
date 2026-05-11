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

### Spezielle Eingabetypen (UI Controls)

- **Inline Switch** – Moderner, eckiger Toggle-Switch für Boolean-Werte (Liste & Formular)
- **Inline Edit** – Direktes Bearbeiten von Text- und Textarea-Feldern in der Listenansicht (Click-to-Edit)
- **Inline Number** – Zahlenfeld mit Inline-Editing, Präfix/Suffix (z. B. €/km), Min/Max und Step
- **Inline Select** – Auswahlfeld mit Inline-Editing, Selectpicker, Farben und optionaler Query-Quelle
- **Icon Picker** – Icon-Auswahl aus Font Awesome und/oder UIkit-Iconsets
- **Star Rating** – Bewertungsfeld (1–10 Sterne) mit visueller Eingabe
- **IBAN** – IBAN-Eingabe mit Live-Validierung über openIBAN.com (serverseitig geproxied)
- **Tagging** – Farbige Schlagwörter (Chips) mit Custom-Color-Picker und WCAG-Kontrastprüfung

### Komplexe Datentypen (Repeater & Strukturen)

- **Tabelle** – Barrierefreier Tabelleneditor mit flexiblen Spalten/Zeilen, Min/Max-Constraints und erweiterten Datentypen (Medien, Links, Textarea, Zahlen)
- **Social Web** – Repeater für Social-Media-Profile mit 24 vordefinierten Plattformen
- **Öffnungszeiten** – Wochentags-Editor mit Zeitfenstern, Sondertagen und Notizen
- **Kontakte** – Flexible Kontaktkarten mit konfigurierbaren optionalen Feldern (Avatar, Firma, Adresse, …)
- **FAQ** – Frage/Antwort-Repeater mit automatischer Schema.org-FAQPage-JSON-LD-Ausgabe

### Layout & Logik

- **Tabs & Akkordeons** – Gruppierung von Feldern in Tabs, Akkordeons oder Fieldsets
- **Grid & Layout** – Mehrspaltige Anordnung von Feldern (CSS Grid)
- **Conditional** – Bedingte Sichtbarkeit von Feldgruppen abhängig von anderen Feldwerten

### Konfiguration

Unter **YForm → Fields**:

| Einstellung | Beschreibung |
|---|---|
| Icon-Sets | Verfügbare Icon-Bibliotheken (z. B. Font Awesome, UIkit) |
| IBAN-Proxy | openIBAN.com-Proxy aktivieren/deaktivieren |

---

## Anwendung im YForm-Tablemanager

Die Feldtypen erscheinen nach der Installation automatisch in der YForm-Feldauswahl unter dem Typ **value**:

| Feldtyp | DB-Typ | Beschreibung |
|---|---|---|
| `fields_social_web` | `mediumtext` | Social-Media-Profile als JSON |
| `fields_opening_hours` | `mediumtext` | Öffnungszeiten als JSON |
| `fields_contacts` | `mediumtext` | Kontaktdaten als JSON |
| `fields_table` | `mediumtext` | Tabelle mit Spalten- und Zeilendaten sowie Meta-Infos |
| `fields_iban` | `varchar(34)` | IBAN mit Validierung |
| `fields_faq` | `mediumtext` | FAQ-Einträge als JSON |
| `fields_conditional` | *kein DB-Feld* | Steuert Sichtbarkeit anderer Felder |
| `fields_interactive` | *kein DB-Feld* | Felder in Tabs/Akkordeons gruppieren |
| `fields_structure` | *kein DB-Feld* | Felder mehrspaltig anordnen (Grid) |
| `fields_icon_picker` | `varchar(191)` | Ausgewähltes Icon (z. B. `fa-home`) |
| `fields_rating` | `int` | Ganzzahlige Bewertung |
| `fields_inline_switch` | `tinyint(1)` | Boolescher Switch für Liste & Formular |
| `fields_inline` | `text`, `mediumtext` | Text/Textarea mit Inline-Editing |
| `fields_inline_number` | `int`, `float`, `decimal` | Zahlenfeld mit Inline-Editing |
| `fields_inline_select` | `varchar(191)`, `text` | Auswahlfeld mit Inline-Editing |
| `fields_tagging` | `text` | Farbige Tag-Chips als JSON |

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
| `notice` | Hinweistext unter dem Feld |

> **Automatische Quelle:** Bleibt `source_table`/`source_field` leer, leitet das Widget die Quelle aus dem Feldnamen ab: `art_*` → `rex_article`, `med_*` → `rex_media`, `clang_*` → `rex_clang`. Ist gar keine Quelle gepflegt, bleibt das Feld trotzdem speicherbar – nur die Vorschlagsliste ist leer.

### Troubleshooting

- **Widget wird nicht angezeigt**: Feldtyp muss explizit auf **Fields Tagging** stehen.
- **Autocomplete leer**: `source_table` und `source_field` prüfen; in der Quelle müssen bereits Tags gespeichert sein.
- **Tags verschwinden nach dem Speichern**: Spalte muss vom Typ `text` sein.

---

# Für Entwickler

## Frontend-Ausgabe via Fragmente

Für jedes Feld stehen Fragmente in vier Framework-Varianten bereit:

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

Zusätzliche Hilfsmethoden über `OpeningHoursHelper`:

```php
use FriendsOfRedaxo\Fields\OpeningHoursHelper;

$data = json_decode($item->getValue('opening_hours'), true);
$helper = new OpeningHoursHelper($data);

if ($helper->isOpenNow()) {
    echo 'Jetzt geöffnet';
}

$today   = $helper->getToday();
$grouped = $helper->getRegularGrouped(); // Mo–Fr zusammengefasst wenn gleich
```

### Tabelle

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('table'));
echo $fragment->parse('fields/bootstrap3/table.php');
```

Funktionen des Editors:
- Definierbare **Min/Max-Grenzen** für Zeilen/Spalten
- Unabhängige **Textausrichtung** für Kopf- und Datenzellen
- **Inline-Hinzufügen** von Zeilen/Spalten
- Strict Mode für Kopfzeilen/-spalten
- Erweiterte Datentypen: Medien (REX_MEDIA), Links (REX_LINK), Textarea, Zahlen

### Kontakte

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('contacts'));
echo $fragment->parse('fields/tailwind/contacts.php');
```

### FAQ mit Schema.org

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('faq'));
$fragment->setVar('schema', true); // Schema.org FAQPage ausgeben
echo $fragment->parse('fields/uikit3/faq.php');
```

JSON-LD separat erzeugen:

```php
$items = json_decode($item->getValue('faq'), true);
echo rex_yform_value_fields_faq::getSchemaJsonLd($items);
```

### IBAN

```php
$isValid = rex_yform_value_fields_iban::isValidFormat('DE89370400440532013000');
```

Die Validierung läuft über openIBAN.com und wird serverseitig geproxied, damit der API-Key nicht im Frontend liegt.

### Icon Picker

```php
$icon = $item->getValue('icon');
// Font Awesome
echo '<i class="' . rex_escape($icon) . '"></i>';
// UIkit
echo '<span uk-icon="icon: ' . rex_escape(str_replace('uk-icon-', '', $icon)) . '"></span>';
```

### Star Rating

```php
$fragment = new rex_fragment();
$fragment->setVar('value', $item->getValue('rating'));
$fragment->setVar('max', 5);
echo $fragment->parse('fields/bootstrap3/rating.php');
```

### Conditional (Bedingte Felder)

Keine Frontend-Ausgabe – steuert nur Sichtbarkeit im Backend:

- **Quellfeld**: Feld, dessen Wert geprüft wird
- **Operator**: `==`, `!=`, `>`, `<`, `contains`, `empty`, `!empty`, `switch`
- **Vergleichswert**: erwarteter Wert (bei `switch` irrelevant)
- **Zielfelder**: Feldnamen ODER CSS-Selektoren, kommasepariert
- **Aktion**: `show` oder `hide`

### Tabs / Akkordeons (`fields_interactive`)

1. Feld `fields_interactive` anlegen
2. Typ wählen (Tab Start, Akkordeon Start, Gruppe Ende)
3. Gleiche **Gruppen-ID** für zusammengehörige Elemente vergeben

Beispiel:
1. `fields_interactive` (Tab, Label „Basisdaten“, Gruppen-ID 1)
2. … Felder für Tab 1 …
3. `fields_interactive` (Tab, Label „Erweitert“, Gruppen-ID 1)
4. … Felder für Tab 2 …
5. `fields_interactive` (Ende, Gruppen-ID 1)

### Grid (`fields_structure`)

- **Start**: beginnt einen Grid-Container
- **Layout**: Spalten via Grid-Template (z. B. `1fr 1fr` oder `1fr 2fr`)
- **Gap**: Abstand zwischen Spalten
- Alle Felder zwischen Start und Ende werden ins Grid aufgenommen

---

## Tagging: Daten auslesen & verwenden

Die Hilfsklasse `FriendsOfRedaxo\Fields\FieldsTagging` ist automatisch geladen.

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

// Aus YForm-Dataset
$tags  = FieldsTagging::decode($item->getValue('tags'));
$texte = FieldsTagging::getTexts($tags);

// Aus Metainfo (Artikel/Medium/Kategorie)
$tagsJson = rex_metainfo::getMetaInfo('art_tags', $article_id);
echo FieldsTagging::fromRaw($tagsJson, 'Keine Tags vorhanden');

// HTML-Ausgabe
echo FieldsTagging::toHtml($tags);
echo FieldsTagging::chipHtml('php', '#2980b9');

// Encoding nach manueller Manipulation
$json = FieldsTagging::encode($tags);
```

### Datenbankabfragen

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

// Alle eindeutigen Tags einer Spalte
$alleTags = FieldsTagging::collectFromTable('rex_products', 'tags');
$texte    = FieldsTagging::collectTextsFromTable('rex_products', 'tags');

// SQL-WHERE für MySQL JSON-Suche (>= 5.7)
$sql = rex_sql::factory();
$rows = $sql->getArray(
    'SELECT * FROM rex_products WHERE ' . FieldsTagging::sqlHasTag('tags', 'php')
);

// PHP-Filter
$gefiltert = FieldsTagging::filterByTag($rows, 'tags', 'php');
```

### Manuelle Frontend-Ausgabe

```php
use FriendsOfRedaxo\Fields\FieldsTagging;

$tags = FieldsTagging::decode($item->getValue('tags'));
foreach ($tags as $tag) {
    echo '<span style="background-color:' . rex_escape($tag['color']) . ';padding:4px 8px;border-radius:12px;color:#fff;">'
       . rex_escape($tag['text'])
       . '</span> ';
}
```

---

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
