<?php

namespace FriendsOfRedaxo\Fields;

/**
 * Helper-Klasse für die Frontend-Ausgabe von Öffnungszeiten
 *
 * Kopie/Adaption aus TemplateManager, standalone nutzbar.
 *
 * @package fields
 */
class OpeningHoursHelper
{
    private array $data = [];
    private string $locale = 'de';

    private array $translations = [
        'de' => [
            'weekdays' => [
                'monday' => 'Montag', 'tuesday' => 'Dienstag', 'wednesday' => 'Mittwoch',
                'thursday' => 'Donnerstag', 'friday' => 'Freitag', 'saturday' => 'Samstag', 'sunday' => 'Sonntag',
            ],
            'weekdays_short' => [
                'monday' => 'Mo', 'tuesday' => 'Di', 'wednesday' => 'Mi',
                'thursday' => 'Do', 'friday' => 'Fr', 'saturday' => 'Sa', 'sunday' => 'So',
            ],
            'status' => ['closed' => 'Geschlossen', 'open_24h' => '24 Stunden geöffnet', 'open' => 'Geöffnet'],
            'labels' => [
                'today' => 'heute', 'opening_hours' => 'Öffnungszeiten', 'special_hours' => 'Sonderöffnungszeiten',
                'we_are_open' => 'Wir haben geöffnet', 'we_are_closed' => 'Wir haben geschlossen',
                'opens_at' => 'Öffnet um', 'closes_at' => 'Schließt um', 'time_suffix' => 'Uhr',
            ],
        ],
        'en' => [
            'weekdays' => [
                'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
            ],
            'weekdays_short' => [
                'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
                'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
            ],
            'status' => ['closed' => 'Closed', 'open_24h' => 'Open 24 hours', 'open' => 'Open'],
            'labels' => [
                'today' => 'today', 'opening_hours' => 'Opening Hours', 'special_hours' => 'Special Hours',
                'we_are_open' => 'We are open', 'we_are_closed' => 'We are closed',
                'opens_at' => 'Opens at', 'closes_at' => 'Closes at', 'time_suffix' => '',
            ],
        ],
    ];

    private const WEEKDAY_ORDER = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function __construct(?string $json, string $locale = 'de')
    {
        $this->locale = $locale;
        if (!empty($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $this->data = $decoded;
            }
        }
    }

    public function setTranslations(string $locale, array $translations): self
    {
        $this->translations[$locale] = array_replace_recursive(
            $this->translations[$this->locale] ?? $this->translations['de'],
            $translations,
        );
        $this->locale = $locale;
        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function translate(string $key, ?string $fallback = null): string
    {
        $translations = $this->translations[$this->locale] ?? $this->translations['de'];
        $parts = explode('.', $key);
        $value = $translations;
        foreach ($parts as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                return $fallback ?? $key;
            }
            $value = $value[$part];
        }
        return is_string($value) ? $value : ($fallback ?? $key);
    }

    public function hasData(): bool
    {
        return !empty($this->data) && isset($this->data['regular']);
    }

    public function getNote(): ?string
    {
        $note = $this->data['note'] ?? null;
        return !empty($note) ? $note : null;
    }

    public function hasNote(): bool
    {
        return !empty($this->data['note']);
    }

    public function getRegular(bool $shortLabels = false): array
    {
        if (!$this->hasData()) {
            return [];
        }
        $result = [];
        $todayKey = $this->getTodayKey();
        $labelKey = $shortLabels ? 'weekdays_short' : 'weekdays';
        foreach (self::WEEKDAY_ORDER as $dayKey) {
            $dayData = $this->data['regular'][$dayKey] ?? ['status' => 'closed', 'times' => []];
            $status = $dayData['status'] ?? 'closed';
            $times = $dayData['times'] ?? [];
            $isToday = ($dayKey === $todayKey);
            $result[$dayKey] = [
                'key' => $dayKey,
                'label' => $this->translate($labelKey . '.' . $dayKey),
                'label_short' => $this->translate('weekdays_short.' . $dayKey),
                'label_full' => $this->translate('weekdays.' . $dayKey),
                'status' => $status,
                'status_label' => $this->getStatusLabel($status),
                'is_today' => $isToday,
                'is_open' => $status === 'open' || $status === '24h',
                'is_closed' => $status === 'closed',
                'is_24h' => $status === '24h',
                'times' => $times,
                'times_formatted' => $this->formatTimeSlots($times),
                'formatted' => $this->formatDayStatus($status, $times),
            ];
        }
        return $result;
    }

    public function getRegularGrouped(): array
    {
        if (!$this->hasData()) {
            return [];
        }
        $regular = $this->getRegular();
        $groups = [];
        $currentGroup = null;
        foreach (self::WEEKDAY_ORDER as $dayKey) {
            $day = $regular[$dayKey];
            $signature = $day['status'] . '|' . json_encode($day['times']);
            if ($currentGroup === null || $currentGroup['signature'] !== $signature) {
                if ($currentGroup !== null) {
                    $groups[] = $this->finalizeGroup($currentGroup);
                }
                $currentGroup = [
                    'signature' => $signature,
                    'days' => [$dayKey],
                    'first_day' => $day,
                    'last_day' => $day,
                    'contains_today' => $day['is_today'],
                ];
            } else {
                $currentGroup['days'][] = $dayKey;
                $currentGroup['last_day'] = $day;
                if ($day['is_today']) {
                    $currentGroup['contains_today'] = true;
                }
            }
        }
        if ($currentGroup !== null) {
            $groups[] = $this->finalizeGroup($currentGroup);
        }
        return $groups;
    }

    private function finalizeGroup(array $group): array
    {
        $firstDay = $group['first_day'];
        $lastDay = $group['last_day'];
        $dayCount = count($group['days']);
        if ($dayCount === 1) {
            $label = $firstDay['label_short'];
            $labelFull = $firstDay['label_full'];
        } elseif ($dayCount === 2) {
            $label = $firstDay['label_short'] . ', ' . $lastDay['label_short'];
            $labelFull = $firstDay['label_full'] . ', ' . $lastDay['label_full'];
        } else {
            $label = $firstDay['label_short'] . ' - ' . $lastDay['label_short'];
            $labelFull = $firstDay['label_full'] . ' - ' . $lastDay['label_full'];
        }
        return [
            'label' => $label, 'label_full' => $labelFull, 'days' => $group['days'],
            'day_count' => $dayCount, 'status' => $firstDay['status'], 'status_label' => $firstDay['status_label'],
            'is_open' => $firstDay['is_open'], 'is_closed' => $firstDay['is_closed'], 'is_24h' => $firstDay['is_24h'],
            'times' => $firstDay['times'], 'times_formatted' => $firstDay['times_formatted'],
            'formatted' => $firstDay['formatted'], 'contains_today' => $group['contains_today'],
        ];
    }

    public function getSpecial(?int $limit = null, bool $futureOnly = false): array
    {
        if (!$this->hasData() || empty($this->data['special'])) {
            return [];
        }
        $result = [];
        $today = date('Y-m-d');
        foreach ($this->data['special'] as $entry) {
            $date = $entry['date'] ?? '';
            $status = $entry['status'] ?? 'closed';
            $times = $entry['times'] ?? [];
            $actualDate = $this->resolveDate($date);
            if ($futureOnly && $actualDate && $actualDate < $today) {
                continue;
            }
            $result[] = [
                'date' => $date, 'date_resolved' => $actualDate,
                'date_formatted' => $actualDate ? $this->formatDate($actualDate) : $date,
                'name' => $entry['name'] ?? '',
                'display_name' => !empty($entry['name']) ? $entry['name'] : $this->formatDate($actualDate ?: $date),
                'status' => $status, 'status_label' => $this->getStatusLabel($status),
                'is_holiday' => $entry['holiday'] ?? false,
                'is_open' => $status === 'open', 'is_closed' => $status === 'closed',
                'times' => $times, 'times_formatted' => $this->formatTimeSlots($times),
                'formatted' => $this->formatDayStatus($status, $times),
            ];
        }
        usort($result, static function ($a, $b) {
            return strcmp($a['date_resolved'] ?? $a['date'], $b['date_resolved'] ?? $b['date']);
        });
        if ($limit !== null && $limit > 0) {
            $result = array_slice($result, 0, $limit);
        }
        return $result;
    }

    public function getToday(): ?array
    {
        $regular = $this->getRegular();
        $todayKey = $this->getTodayKey();
        return $regular[$todayKey] ?? null;
    }

    public function isOpenNow(): bool
    {
        if (!$this->hasData()) {
            return false;
        }
        $todayDate = date('Y-m-d');
        $currentTime = date('H:i');
        foreach ($this->data['special'] ?? [] as $special) {
            $resolvedDate = $this->resolveDate($special['date'] ?? '');
            if ($resolvedDate === $todayDate) {
                if (($special['status'] ?? 'closed') === 'closed') {
                    return false;
                }
                return $this->isTimeInSlots($currentTime, $special['times'] ?? []);
            }
        }
        $todayKey = $this->getTodayKey();
        $todayData = $this->data['regular'][$todayKey] ?? ['status' => 'closed'];
        $status = $todayData['status'] ?? 'closed';
        if ($status === 'closed') {
            return false;
        }
        if ($status === '24h') {
            return true;
        }
        return $this->isTimeInSlots($currentTime, $todayData['times'] ?? []);
    }

    public function getCurrentStatus(): array
    {
        $isOpen = $this->isOpenNow();
        $today = $this->getToday();
        $currentTime = date('H:i');
        $result = [
            'is_open' => $isOpen,
            'label' => $isOpen ? $this->translate('labels.we_are_open') : $this->translate('labels.we_are_closed'),
            'today' => $today,
            'next_change' => null,
            'next_change_label' => null,
        ];
        if ($today && $today['status'] === 'open') {
            foreach ($today['times'] as $slot) {
                if ($isOpen && $currentTime >= $slot['open'] && $currentTime < $slot['close']) {
                    $result['next_change'] = $slot['close'];
                    $result['next_change_label'] = $this->translate('labels.closes_at') . ' ' . $slot['close'];
                    if ($this->translate('labels.time_suffix') !== '') {
                        $result['next_change_label'] .= ' ' . $this->translate('labels.time_suffix');
                    }
                    break;
                }
                if (!$isOpen && $currentTime < $slot['open']) {
                    $result['next_change'] = $slot['open'];
                    $result['next_change_label'] = $this->translate('labels.opens_at') . ' ' . $slot['open'];
                    if ($this->translate('labels.time_suffix') !== '') {
                        $result['next_change_label'] .= ' ' . $this->translate('labels.time_suffix');
                    }
                    break;
                }
            }
        }
        return $result;
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    private function getTodayKey(): string
    {
        return strtolower(date('l'));
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'closed' => $this->translate('status.closed'),
            '24h' => $this->translate('status.open_24h'),
            'open' => $this->translate('status.open'),
            default => $status,
        };
    }

    private function formatTimeSlots(array $times): string
    {
        if (count($times) === 0) {
            return '';
        }
        $formatted = [];
        foreach ($times as $slot) {
            $formatted[] = ($slot['open'] ?? '') . "\u{2013}" . ($slot['close'] ?? '');
        }
        $result = implode(', ', $formatted);
        $suffix = $this->translate('labels.time_suffix');
        return $suffix !== '' ? $result . ' ' . $suffix : $result;
    }

    private function formatDayStatus(string $status, array $times): string
    {
        return match ($status) {
            'closed' => $this->translate('status.closed'),
            '24h' => $this->translate('status.open_24h'),
            'open' => $this->formatTimeSlots($times),
            default => $status,
        };
    }

    private function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        if (preg_match('/^(\d{2})-(\d{2})$/', $date, $m)) {
            return $m[2] . '.' . $m[1] . '.';
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            return $m[3] . '.' . $m[2] . '.';
        }
        return $date;
    }

    private function resolveDate(string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        if (preg_match('/^(\d{2})-(\d{2})$/', $date, $m)) {
            return date('Y') . '-' . $m[1] . '-' . $m[2];
        }
        if (str_starts_with($date, 'easter')) {
            $year = (int) date('Y');
            $offset = 0;
            if (preg_match('/easter([+-]\d+)/', $date, $m)) {
                $offset = (int) $m[1];
            }
            if (function_exists('easter_date')) {
                try {
                    $easter = easter_date($year);
                    return date('Y-m-d', $easter + ($offset * 86400));
                } catch (\Throwable) {
                    // Fallback
                }
            }
            $easterDate = $this->calculateEaster($year);
            if ($easterDate !== null) {
                $timestamp = strtotime($easterDate . ' +' . $offset . ' days');
                return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
            }
        }
        return null;
    }

    private function calculateEaster(int $year): ?string
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function isTimeInSlots(string $currentTime, array $slots): bool
    {
        foreach ($slots as $slot) {
            $open = $slot['open'] ?? '';
            $close = $slot['close'] ?? '';
            if ($currentTime >= $open && $currentTime < $close) {
                return true;
            }
        }
        return false;
    }
}
