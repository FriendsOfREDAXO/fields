# Fields – Erweiterte redaktionelle YForm-Feldtypen für REDAXO

![REDAXO](https://img.shields.io/badge/REDAXO-%3E%3D5.17-red) ![YForm](https://img.shields.io/badge/YForm-%3E%3D4.0-blue) ![PHP](https://img.shields.io/badge/PHP-%3E%3D8.1-purple)

Das AddOn **Fields** stellt 8 zusätzliche YForm-Value-Feldtypen bereit, die häufig benötigte Eingabemuster abdecken – von Social-Media-Profilen über Öffnungszeiten bis hin zu FAQ-Akkordeons mit Schema.org-Unterstützung.

## Funktionen

- **Social Web** – Repeater für Social-Media-Profile mit 24 vordefinierten Plattformen (Font Awesome & UIkit Icons)
- **Öffnungszeiten** – Wochentags-Editor mit Zeitfenstern, Sondertagen und Notizen
- **Kontakte** – Flexible Kontaktkarten mit konfigurierbaren optionalen Feldern (Avatar, Firma, Adresse, etc.)
- **Tabelle** – Barrierefreier Tabelleneditor mit flexiblen Spalten/Zeilen, Min/Max-Constraints und unabhängiger Ausrichtung für Kopf und Daten
- **IBAN** – IBAN-Eingabe mit Live-Validierung über openIBAN.com (serverseitig geproxied)
- **FAQ** – Frage/Antwort-Repeater mit automatischer Schema.org FAQPage JSON-LD Ausgabe
- **Conditional** – Bedingte Feldgruppen (Felder ein-/ausblenden basierend auf anderen Feldwerten)
- **Icon Picker** – Icon-Auswahl aus Font Awesome und/oder UIkit-Iconsets- **Star Rating** – Bewertungsfeld (1-10 Sterne) mit visueller Eingabe
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
| `fields_icon_picker` | `varchar(191)` | Ausgewähltes Icon (z.B. `fa-home`) |
| `fields_rating` | `int` | Ganzzahlige Bewertung (Default 1-5 Sterne) |

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

Alle komplexen Felder speichern ihre Daten als JSON. Beispiele:

<details>
<summary><strong>Social Web JSON</strong></summary>

```json
[
    {"platform": "facebook", "url": "https://facebook.com/example"},
    {"platform": "instagram", "url": "https://instagram.com/example"},
    {"platform": "custom", "url": "https://example.com", "label": "Website"}
]
```
</details>

<details>
<summary><strong>Öffnungszeiten JSON</strong></summary>

```json
{
    "regular": {
        "monday": {"status": "open", "times": [{"from": "08:00", "to": "12:00"}, {"from": "13:00", "to": "17:00"}]},
        "tuesday": {"status": "open", "times": [{"from": "08:00", "to": "17:00"}]},
        "wednesday": {"status": "open", "times": [{"from": "08:00", "to": "17:00"}]},
        "thursday": {"status": "open", "times": [{"from": "08:00", "to": "17:00"}]},
        "friday": {"status": "open", "times": [{"from": "08:00", "to": "14:00"}]},
        "saturday": {"status": "closed", "times": []},
        "sunday": {"status": "closed", "times": []}
    },
    "special": [
        {"date": "2026-12-24", "status": "closed", "label": "Heiligabend", "times": []}
    ],
    "note": "Termine nach Vereinbarung"
}
```
</details>

<details>
<summary><strong>Kontakte JSON</strong></summary>

```json
[
    {
        "firstname": "Max",
        "lastname": "Mustermann",
        "function": "Geschäftsführer",
        "company": "Muster GmbH",
        "phone": "+49 123 456789",
        "mobile": "+49 170 1234567",
        "email": "max@muster.de",
        "street": "Musterstraße 1",
        "zip": "12345",
        "city": "Musterstadt",
        "country": "Deutschland",
        "homepage": "https://www.muster.de",
        "avatar": "portrait.jpg",
        "company_logo": "logo.png"
    }
]
```
</details>

<details>
<summary><strong>FAQ JSON</strong></summary>

```json
[
    {"question": "Wie sind die Öffnungszeiten?", "answer": "Mo–Fr 8–17 Uhr"},
    {"question": "Wo finde ich Parkplätze?", "answer": "Direkt hinter dem Gebäude."}
]
```
</details>

<details>
<summary><strong>Tabelle JSON</strong></summary>

```json
{
    "caption": "Preisliste 2024",
    "has_header_row": true,
    "has_header_col": false,
    "cols": [
         {"type": "text", "header_type": "text"},
         {"type": "number", "header_type": "right"},
         {"type": "center", "header_type": "center"}
    ],
    "rows": [
         ["Produkt A", "10,00 €", "Lagernd"],
         ["Produkt B", "20,00 €", "Bestellt"]
    ]
}
```
</details>

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
