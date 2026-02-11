# Fields – Advanced editorial YForm Field Types for REDAXO

![REDAXO](https://img.shields.io/badge/REDAXO-%3E%3D5.17-red) ![YForm](https://img.shields.io/badge/YForm-%3E%3D4.0-blue) ![PHP](https://img.shields.io/badge/PHP-%3E%3D8.1-purple)

The **Fields** addon provides 8 additional YForm value field types covering commonly needed input patterns – from social media profiles and opening hours to FAQ accordions with Schema.org support.

## Features

- **Social Web** – Repeater for social media profiles with 24 predefined platforms (Font Awesome & UIkit icons)
- **Opening Hours** – Weekday editor with time slots, special days, and notes
- **Contacts** – Flexible contact cards with configurable optional fields (avatar, company, address, etc.)
- **IBAN** – IBAN input with live validation via openIBAN.com (server-side proxied)
- **FAQ** – Question/answer repeater with automatic Schema.org FAQPage JSON-LD output
- **Conditional** – Conditional field groups (show/hide fields based on other field values)
- **Icon Picker** – Icon selection from Font Awesome and/or UIkit icon sets

## Installation

1. Search for **fields** in the REDAXO installer
2. Or: Clone the repository to `redaxo/src/addons/fields` and activate in the backend

## Configuration

Under **Addons → Fields → Settings** the following options can be configured:

| Setting | Description |
|---|---|
| Icon Set (Social Web) | Font Awesome and/or UIkit icons |
| Contact Fields | Which optional fields are available for contacts |
| IBAN Proxy | Enable/disable openIBAN.com proxy |
| Icon Picker Sets | Available icon libraries |

## Usage in YForm Table Manager

The field types automatically appear in the YForm field selection after installation. All fields are available under the **value** type:

| Field Type | DB Type | Description |
|---|---|---|
| `fields_social_web` | `mediumtext` | Social media profiles as JSON |
| `fields_opening_hours` | `mediumtext` | Opening hours as JSON |
| `fields_contacts` | `mediumtext` | Contact data as JSON |
| `fields_iban` | `varchar(34)` | IBAN with validation |
| `fields_faq` | `mediumtext` | FAQ entries as JSON |
| `fields_conditional` | *no DB field* | Controls visibility of other fields |
| `fields_icon_picker` | `varchar(191)` | Selected icon (e.g. `fa-home`) |

## Frontend Output with Fragments

Frontend fragments are available for each field in four framework variants:

- `fields/bootstrap3/` – Bootstrap 3
- `fields/uikit3/` – UIkit 3
- `fields/tailwind/` – Tailwind CSS
- `fields/plain/` – Framework-independent (semantic HTML)

### Social Web

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('social_web'));
$fragment->setVar('class', 'my-social-links');
echo $fragment->parse('fields/uikit3/social_web.php');
```

### Opening Hours

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('opening_hours'));
$fragment->setVar('show_status', true);
echo $fragment->parse('fields/bootstrap3/opening_hours.php');
```

The `OpeningHoursHelper` provides additional methods:

```php
use FriendsOfRedaxo\Fields\OpeningHoursHelper;

$data = json_decode($item->getValue('opening_hours'), true);
$helper = new OpeningHoursHelper($data);

// Current status
if ($helper->isOpenNow()) {
    echo 'Currently open';
}

// Today's hours
$today = $helper->getToday();

// Grouped opening hours (Mon–Fri combined when identical)
$grouped = $helper->getRegularGrouped();
```

### Contacts

```php
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('contacts'));
echo $fragment->parse('fields/tailwind/contacts.php');
```

### FAQ with Schema.org

```php
// Fragment output (including Schema.org JSON-LD)
$fragment = new rex_fragment();
$fragment->setVar('json', $item->getValue('faq'));
$fragment->setVar('schema', true); // Output Schema.org FAQPage
echo $fragment->parse('fields/uikit3/faq.php');
```

Schema.org JSON-LD can also be generated separately:

```php
$items = json_decode($item->getValue('faq'), true);
echo rex_yform_value_fields_faq::getSchemaJsonLd($items);
```

### IBAN

```php
// Server-side validation
$isValid = rex_yform_value_fields_iban::isValidFormat('DE89370400440532013000');
```

IBAN validation via openIBAN.com runs live in the backend form. API calls are proxied through your own server to avoid exposing the API in the frontend.

### Conditional Fields

The conditional field requires no frontend output – it only controls field visibility in the backend:

- **Source field**: The field whose value is checked
- **Operator**: `=`, `!=`, `>`, `<`, `contains`, `empty`, `!empty`
- **Compare value**: The expected value
- **Target fields**: Comma-separated list of field names
- **Action**: `show` or `hide`

### Icon Picker

```php
$icon = $item->getValue('icon');
// Font Awesome
echo '<i class="' . rex_escape($icon) . '"></i>';
// UIkit
echo '<span uk-icon="icon: ' . rex_escape(str_replace('uk-icon-', '', $icon)) . '"></span>';
```

## API Endpoints

### IBAN Validation

```
GET index.php?rex-api-call=fields_iban_validate&iban=DE89370400440532013000
```

Response:
```json
{
    "valid": true,
    "iban": "DE89 3704 0044 0532 0130 00",
    "bank": "Commerzbank",
    "bic": "COBADEFFXXX",
    "city": "Aachen"
}
```

## Data Format

All complex fields store their data as JSON. Examples:

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
<summary><strong>Opening Hours JSON</strong></summary>

```json
{
    "regular": {
        "monday": {"status": "open", "times": [{"from": "08:00", "to": "12:00"}, {"from": "13:00", "to": "17:00"}]},
        "tuesday": {"status": "open", "times": [{"from": "08:00", "to": "17:00"}]}
    },
    "special": [
        {"date": "2026-12-24", "status": "closed", "label": "Christmas Eve", "times": []}
    ],
    "note": "By appointment only"
}
```
</details>

<details>
<summary><strong>Contacts JSON</strong></summary>

```json
[
    {
        "firstname": "John",
        "lastname": "Doe",
        "function": "CEO",
        "company": "Acme Inc.",
        "phone": "+1 555 123456",
        "email": "john@acme.com",
        "street": "123 Main St",
        "zip": "10001",
        "city": "New York",
        "country": "USA",
        "homepage": "https://www.acme.com"
    }
]
```
</details>

<details>
<summary><strong>FAQ JSON</strong></summary>

```json
[
    {"question": "What are your opening hours?", "answer": "Mon–Fri 8am–5pm"},
    {"question": "Where can I park?", "answer": "Parking is available behind the building."}
]
```
</details>

## Supported Platforms (Social Web)

Facebook, Instagram, Twitter/X, LinkedIn, Xing, YouTube, TikTok, Pinterest, Threads, Mastodon, Bluesky, WhatsApp, Telegram, GitHub, Vimeo, Flickr, Snapchat, Reddit, Twitch, Discord, Spotify, SoundCloud, RSS, Custom

## Requirements

- REDAXO >= 5.17
- YForm >= 4.0
- PHP >= 8.1

## Author

**Friends Of REDAXO**

* https://www.redaxo.org
* https://github.com/FriendsOfREDAXO

## Credits

**Project Lead**

[Thomas Skerbis](https://github.com/skerbis)

**Concept & Development**

Created with support of GitHub Copilot (Claude)

**Based on:**

- OpeningHoursHelper inspired by [Template Manager](https://github.com/FriendsOfREDAXO/template_manager) by [Thomas Skerbis](https://github.com/skerbis)
- IBAN validation via [openIBAN.com](https://openiban.com) (free service)


## License

MIT License – see [LICENSE.md](LICENSE.md)
