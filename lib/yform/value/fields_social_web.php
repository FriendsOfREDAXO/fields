<?php

/**
 * YForm Value: Social Web Repeater
 *
 * Wiederholbares Eingabefeld für Social-Media-Profile.
 * Speichert Plattform + URL als JSON.
 *
 * @package fields
 */
class rex_yform_value_fields_social_web extends rex_yform_value_abstract
{
    /**
     * Verfügbare Social-Media-Plattformen
     */
    private const PLATFORMS = [
        'facebook' => ['label' => 'Facebook', 'fa' => 'fa-facebook', 'uikit' => 'facebook', 'placeholder' => 'https://facebook.com/...'],
        'instagram' => ['label' => 'Instagram', 'fa' => 'fa-instagram', 'uikit' => 'instagram', 'placeholder' => 'https://instagram.com/...'],
        'twitter' => ['label' => 'X (Twitter)', 'fa' => 'fa-x-twitter', 'uikit' => 'x', 'placeholder' => 'https://x.com/...'],
        'linkedin' => ['label' => 'LinkedIn', 'fa' => 'fa-linkedin', 'uikit' => 'linkedin', 'placeholder' => 'https://linkedin.com/in/...'],
        'xing' => ['label' => 'XING', 'fa' => 'fa-xing', 'uikit' => 'xing', 'placeholder' => 'https://xing.com/profile/...'],
        'youtube' => ['label' => 'YouTube', 'fa' => 'fa-youtube', 'uikit' => 'youtube', 'placeholder' => 'https://youtube.com/@...'],
        'tiktok' => ['label' => 'TikTok', 'fa' => 'fa-tiktok', 'uikit' => 'tiktok', 'placeholder' => 'https://tiktok.com/@...'],
        'pinterest' => ['label' => 'Pinterest', 'fa' => 'fa-pinterest', 'uikit' => 'pinterest', 'placeholder' => 'https://pinterest.com/...'],
        'threads' => ['label' => 'Threads', 'fa' => 'fa-threads', 'uikit' => 'threads', 'placeholder' => 'https://threads.net/@...'],
        'mastodon' => ['label' => 'Mastodon', 'fa' => 'fa-mastodon', 'uikit' => 'mastodon', 'placeholder' => 'https://mastodon.social/@...'],
        'bluesky' => ['label' => 'Bluesky', 'fa' => 'fa-bluesky', 'uikit' => 'bluesky', 'placeholder' => 'https://bsky.app/profile/...'],
        'whatsapp' => ['label' => 'WhatsApp', 'fa' => 'fa-whatsapp', 'uikit' => 'whatsapp', 'placeholder' => 'https://wa.me/...'],
        'telegram' => ['label' => 'Telegram', 'fa' => 'fa-telegram', 'uikit' => 'telegram', 'placeholder' => 'https://t.me/...'],
        'github' => ['label' => 'GitHub', 'fa' => 'fa-github', 'uikit' => 'github', 'placeholder' => 'https://github.com/...'],
        'vimeo' => ['label' => 'Vimeo', 'fa' => 'fa-vimeo', 'uikit' => 'vimeo', 'placeholder' => 'https://vimeo.com/...'],
        'flickr' => ['label' => 'Flickr', 'fa' => 'fa-flickr', 'uikit' => 'flickr', 'placeholder' => 'https://flickr.com/...'],
        'snapchat' => ['label' => 'Snapchat', 'fa' => 'fa-snapchat', 'uikit' => 'snapchat', 'placeholder' => 'https://snapchat.com/add/...'],
        'reddit' => ['label' => 'Reddit', 'fa' => 'fa-reddit', 'uikit' => 'reddit', 'placeholder' => 'https://reddit.com/u/...'],
        'twitch' => ['label' => 'Twitch', 'fa' => 'fa-twitch', 'uikit' => 'twitch', 'placeholder' => 'https://twitch.tv/...'],
        'discord' => ['label' => 'Discord', 'fa' => 'fa-discord', 'uikit' => 'discord', 'placeholder' => 'https://discord.gg/...'],
        'spotify' => ['label' => 'Spotify', 'fa' => 'fa-spotify', 'uikit' => 'spotify', 'placeholder' => 'https://open.spotify.com/...'],
        'soundcloud' => ['label' => 'SoundCloud', 'fa' => 'fa-soundcloud', 'uikit' => 'soundcloud', 'placeholder' => 'https://soundcloud.com/...'],
        'rss' => ['label' => 'RSS Feed', 'fa' => 'fa-rss', 'uikit' => 'rss', 'placeholder' => 'https://example.com/feed'],
        'custom' => ['label' => 'Benutzerdefiniert', 'fa' => 'fa-globe', 'uikit' => 'world', 'placeholder' => 'https://...'],
    ];

    public function enterObject(): void
    {
        // Wert aus POST oder DB
        $value = $this->getValue();

        // JSON validieren und bereinigen
        $entries = [];
        if (!empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $entries = array_values(array_filter($decoded, static function (array $entry): bool {
                    return !empty($entry['platform']) && !empty($entry['url']);
                }));
            }
        }

        $this->setValue(json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($this->needsOutput() && $this->isViewable()) {
            $this->params['form_output'][$this->getId()] = $this->parse(
                'value.fields_social_web.tpl.php',
                [
                    'entries' => $entries,
                    'platforms' => self::PLATFORMS,
                ],
            );
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }
    }

    /**
     * Plattform-Definitionen abrufen
     *
     * @return array<string, array{label: string, fa: string, uikit: string, placeholder: string}>
     */
    public static function getPlatforms(): array
    {
        return self::PLATFORMS;
    }

    public function getDescription(): string
    {
        return 'fields_social_web|name|label|';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'fields_social_web',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('fields_social_web_description'),
            'db_type' => ['text'],
            'famous' => false,
        ];
    }
}
