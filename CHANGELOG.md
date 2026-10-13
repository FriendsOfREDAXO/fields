# Changelog

## 1.4.8 - 2026-10-13

### Fixed

- Inline-Felder in YForm-Listen (`fields_inline_switch`, `fields_inline`, `fields_inline_number`, `fields_inline_select`): Speichern brach für alle Nicht-Admins mit „Permission denied for table …“ ab, obwohl sie die Tabelle bearbeiten dürfen. Die Schnittstelle prüfte das Recht `yform[tabelle]`, das es nicht gibt – YForm 5 vergibt Tabellenrechte als komplexes Recht `yform_manager_table_edit`. Jetzt prüft sie über `rex_yform_manager_table_authorization` (wie das YForm-Formular); Admins wie bisher immer, Benutzer ohne Bearbeitungsrecht für die Tabelle weiterhin nicht.

## 1.4.7 - 2026-10-12

### Fixed

- Inline-Felder in YForm-Listen wieder bearbeitbar: Die Prüfung aus 1.4.6 erwartete ein `rex_list`-Objekt; die Tabellenliste von YForm ist aber `rex_yform_list` (keine Unterklasse). Dadurch zeigten Listen nur noch „ja/nein“ bzw. Text statt Schalter, Auswahl und Eingabe. Jetzt genügt ein Listen-Objekt mit `getValue()`; ohne Liste (Datensatz-Historie) weiterhin nur lesbar.

## 1.4.6 - 2026-10-12

### Fixed

- Datensatz-Historie von YForm („ansehen“) brach mit Fehler 500 ab, sobald die Tabelle ein Inline-Feld enthielt (`fields_inline_switch`, `fields_inline`, `fields_inline_number`, `fields_inline_select`): YForm ruft `getListValue()` dort nur mit dem Wert auf, ohne Listen-Objekt – die Felder setzten `$params['list']` voraus („Call to a member function getValue() on null“). Ohne Liste zeigen sie den Wert jetzt nur lesbar an (Schalter als Ja/Nein, Auswahl als Klartext).

## 1.4.5 - 2026-10-11

### Fixed

- `fields_inline_switch` im Formular: Der Schalter ist jetzt eine echte Checkbox mit `role="switch"` (optisch unverändert als Schalter). Damit ist er per Tastatur bedienbar (Tab, Leertaste), hat einen sichtbaren Fokus, wird von Screenreadern als Schalter mit Zustand angesagt, und ein Klick auf die Beschriftung schaltet um. Bisher war es ein `div` mit `onclick`, das weder Fokus noch `change`-Event hatte – `fields_conditional` mit einem Schalter als Quellfeld reagierte deshalb nicht.
- `fields_inline_switch`: Standardwert bei neuen Datensätzen wird übernommen, Werte wie bei der YForm-Checkbox auf 0/1 normiert; Fehlermeldungen der Validierung erscheinen am Feld.
- `fields_interactive`: Fieldsets ohne Gruppe beenden einen Reiter nicht mehr. Bisher endete ein Reiter am ersten Fieldset, der Rest stand außerhalb der Reiter. Jetzt lassen sich Fieldsets innerhalb eines Reiters nutzen – z. B. zwei Fieldsets als Spalten eines `fields_structure`-Rasters (Inhalt links, Sidebar rechts); ein `end_group` ohne Gruppe beendet das Fieldset.
- Schalter im Formular mit Rand und dunklerem Grün (Kontrast ≥ 3:1 für Bedienelemente); mehrere Schalter in einer `fields_structure`-Spalte stehen auf einer Linie.

## 1.4.4 - 2026-10-04

### Added

- `fields_iban`: zusätzlicher Datenbanktyp `text`. Verschlüsselte IBANs (AddOn [YForm Encryption](https://github.com/FriendsOfREDAXO/yform_encryption)) sind deutlich länger als 34 Zeichen; mit `text` bleibt die erweiterte Spalte auch dann erhalten, wenn das Feld im Tablemanager gespeichert oder die Tabelle neu aufgebaut wird. Bisher konnte YForm die Spalte wieder auf `varchar(34)` verkleinern und das Chiffrat abschneiden.

### Docs

- README: Abschnitt zur Verschlüsselung mit YForm Encryption (welche Feldtypen, was zu beachten ist).

## 1.4.3 - 2026-09-24

### Fixed

- `fields_opening_hours`: Sonderöffnungszeiten mit Status „Geöffnet“ haben jetzt Zeitfelder (inkl. „Zeitslot hinzufügen“/Entfernen) und die Zeiten werden gespeichert. Bisher blieb der Zeitenbereich leer und das JavaScript schrieb für Sondertage fest `times: []`, ein geöffneter Sondertag kam also immer ohne Zeiten an. Bereits gespeicherte Zeiten werden wieder bearbeitbar dargestellt.
- README: Beispiel-JSON der Öffnungszeiten an das tatsächliche Format angepasst (`open`/`close` statt `from`/`to`, `name` statt `label`) und um die Datumsformate `MM-DD` und `easter±N` ergänzt.

## 1.4.2 - 2026-09-10

### Fixed

- `fields_table`: Der leere Ausgangszustand rendert nicht mehr hartkodiert 3 Spalten mit den Platzhaltertexten „Spalte 1/2/3“, sondern leitet Spalten-/Zeilenanzahl aus `min_cols`/`max_cols`/`min_rows`/`max_rows` ab. Verhindert, dass die Platzhaltertexte in Kombination mit `header_row_policy = no` ungewollt als echte Datenzeile gespeichert werden. ([#1](https://github.com/FriendsOfREDAXO/fields/issues/1))

## 1.4.1 - 2026-06-07

### Fixed

- `fields_structure`: Re-Initialisierung zusätzlich bei `rex:change`, damit Inline-Feldlayouts auch nach dynamischen Backend-Updates zuverlässig erhalten bleiben.

## 1.4.0 - 2026-05-11

### Added

- **Inline-Tagging in der Listenansicht** (`fields_tagging`): Neue optionale Felddefinition `list_editable`. Wenn aktiviert, koennen Tags direkt in der YForm-Tabellenuebersicht hinzugefuegt und entfernt werden – inkl. Popover mit Vorschlaegen aus der Quelltabelle, Farbpicker und Speicherung via `rex-api-call=fields_inline_update`.

### Changed

- Backend-Menue: Fields wird nicht mehr als eigener Hauptmenuepunkt gefuehrt, sondern als Unterseite von **YForm** eingehaengt (analog `yform_spam_protection`).
- Hilfe-Seite: README.md wird ueber `rex_markdown::parseWithToc()` mit Inhaltsverzeichnis und PHP-Highlighting gerendert (Core-Pattern aus `packages.help.php`).
- README.md neu strukturiert: Einleitung, Features, Anwendung im Tablemanager, Anwendung im Metainfo-AddOn, Entwickler-Sektion (Fragmente, FieldsTagging-Helper, API, JSON-Schemata).

### Removed

- Veraltete Seite `pages/info.php` entfernt (durch package.yml + README-Help-Page ersetzt).

## 1.3.0 - 2026-05-10

### Added

- **Metainfo Tagging**: Neuer Feldtyp 'Fields Tagging' für die Metainfo-Verwaltung. Ermöglicht farbige Tags mit Autocomplete-Unterstützung aus konfigurierbarer Quelltabelle direkt im Metainfo-Kontext.

## 1.2.1 - 2026-05-04

### Fixed

- `fields_inline_select`: Übersetzungs-Keys auf addon-spezifische Präfixe umgestellt (`fields_inline_select_choices`, `fields_inline_select_default_value`) statt generischer YForm-Keys.

## 1.2.0 - 2026-05-03

### Added

- **Inline Select** (`fields_inline_select`): Neues YForm-Value fuer direktes Aendern von Auswahlwerten in der Listenansicht, inklusive optionaler Farbdefinitionen, Query-Quelle und Lock-Status fuer finale Werte.
- **Tagging-Suche** (`fields_tagging`): YForm-Manager-Suchwidget mit Mehrfachauswahl (`expanded=1`) und farbigen Tag-Checkboxen.

### Improved

- `fields_tagging` Search-UX: Klareres Label fuer `!(empty)` via Sprachschluessel (`fields_search_not_empty`) statt technischer Notation.
- Irrefuehrender Multi-Select-Hinweis („Please use CTRL or COMMAND …“) in der Checkbox-basierten Tag-Suche entfernt.

### Fixed

- `fields_tagging` Suchfilter fuer `(empty)` / `!(empty)` auf robuste SQL-Bedingungen umgestellt (`NULL`, leerer String, `[]`).
- Namespace-Fix in `FieldsTagging`: korrekte Aufloesung von `rex::getTablePrefix()` durch `use rex;`.

## 1.1.0 - 2026-03-13

### Added

- **Tagging-Widget** (`fields_tagging`): Neuer YForm-Feldtyp zum Erfassen farbiger Schlagwörter (Tags). Daten werden als JSON `[{"text":"...","color":"#..."}]` in einer `text`-Spalte gespeichert.
- **Custom Color Picker mit WCAG-Kontrastprüfung**: Eigene Farben sind über einen nativen `<input type="color">` wählbar. Farben, bei denen weiße Schrift ein Kontrastverhältnis < 3,0:1 hätte, werden abgelehnt.
- **Suggest-Endpunkt** (`rex_api_fields_tagging_suggest`): Liefert vorhandene Tags aus einer konfigurierbaren Quelltabelle als JSON für das Autocomplete-Widget.
- **`FieldsTagging` Helper-Klasse** (`lib/FieldsTagging.php`): Eigenständige PHP-Klasse für alle gängigen Tag-Operationen im Frontend und Backend:
  - `decode(string $raw): array` – JSON → `list<array{text, color}>`
  - `encode(array $tags): string` – Tags → JSON-String
  - `getTexts(array $tags): array` – Nur Texte als String-Array
  - `toHtml(array $tags, string $emptyText = ''): string` – Farbige Chip-Spans
  - `chipHtml(string $text, string $color): string` – Einzelner Chip
  - `fromRaw(string $raw, string $emptyText = ''): string` – Kurzform decode + render
  - `collectFromTable(string $table, string $field): array` – Alle eindeutigen Tags aus einer DB-Tabelle (alphabetisch sortiert)
  - `collectTextsFromTable(string $table, string $field): array` – Nur Texte aus DB
  - `sqlHasTag(string $field, string $tagText): string` – MySQL `JSON_SEARCH`-WHERE-Fragment
  - `filterByTag(array $rows, string $field, string $tagText): array` – PHP-seitiger Filter

## 1.0.0 - 2025-01-01

### Added
- Initiales Release mit den Feldtypen `fields_inline_edit`, `fields_media_advanced`, `fields_social_web`, `fields_inline_number` u.a.
