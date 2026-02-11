<?php

/**
 * Fragment: Contacts – UIkit 3
 */

$json = $this->getVar('json', '');
$class = $this->getVar('class', '');

$contacts = json_decode($json, true);
if (!is_array($contacts) || count($contacts) === 0) {
    return;
}
?>
<div class="uk-grid uk-grid-match uk-child-width-1-2@m uk-child-width-1-3@l <?= rex_escape($class) ?>" uk-grid>
    <?php foreach ($contacts as $contact): ?>
        <div>
            <div class="uk-card uk-card-default">
                <?php if (!empty($contact['avatar'])): ?>
                    <div class="uk-card-media-top">
                        <img src="<?= rex_escape(rex_url::media($contact['avatar'])) ?>"
                             alt="<?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>" />
                    </div>
                <?php endif; ?>
                <div class="uk-card-body">
                    <?php if (!empty($contact['company_logo'])): ?>
                        <img src="<?= rex_escape(rex_url::media($contact['company_logo'])) ?>"
                             alt="<?= rex_escape($contact['company'] ?? '') ?>"
                             style="max-height:40px; margin-bottom:10px;" />
                    <?php endif; ?>

                    <h3 class="uk-card-title">
                        <?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>
                    </h3>

                    <?php if (!empty($contact['function'])): ?>
                        <p class="uk-text-meta"><?= rex_escape($contact['function']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($contact['company'])): ?>
                        <p class="uk-text-bold"><?= rex_escape($contact['company']) ?></p>
                    <?php endif; ?>

                    <ul class="uk-list">
                        <?php if (!empty($contact['phone'])): ?>
                            <li><span uk-icon="icon: receiver"></span> <a href="tel:<?= rex_escape($contact['phone']) ?>"><?= rex_escape($contact['phone']) ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($contact['mobile'])): ?>
                            <li><span uk-icon="icon: phone"></span> <a href="tel:<?= rex_escape($contact['mobile']) ?>"><?= rex_escape($contact['mobile']) ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($contact['email'])): ?>
                            <li><span uk-icon="icon: mail"></span> <a href="mailto:<?= rex_escape($contact['email']) ?>"><?= rex_escape($contact['email']) ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($contact['homepage'])): ?>
                            <li><span uk-icon="icon: world"></span> <a href="<?= rex_escape($contact['homepage']) ?>" target="_blank" rel="noopener"><?= rex_escape(parse_url($contact['homepage'], PHP_URL_HOST) ?: $contact['homepage']) ?></a></li>
                        <?php endif; ?>
                    </ul>

                    <?php if (!empty($contact['street']) || !empty($contact['city'])): ?>
                        <div class="uk-text-small">
                            <span uk-icon="icon: location"></span>
                            <?php if (!empty($contact['street'])): ?><?= rex_escape($contact['street']) ?>, <?php endif; ?>
                            <?= rex_escape(trim(($contact['zip'] ?? '') . ' ' . ($contact['city'] ?? ''))) ?>
                            <?php if (!empty($contact['country'])): ?>, <?= rex_escape($contact['country']) ?><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
