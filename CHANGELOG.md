# Changelog

## 1.4.0 - 2026-05-12

### Added

- **Inline-Tagging in der Listenansicht** (`fields_tagging`): Neue optionale Felddefinition `list_editable`. Wenn aktiviert, koennen Tags direkt in der YForm-Tabellenuebersicht hinzugefuegt und entfernt werden – inkl. Popover mit Vorschlaegen aus der Quelltabelle, Farbpicker und Speicherung via `rex-api-call=fields_inline_update`.

## 1.3.1 - 2026-05-11

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
