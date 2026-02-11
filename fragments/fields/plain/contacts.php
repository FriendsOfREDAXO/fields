<?php

/**
 * Fragment: Contacts – Plain / Framework-independent
 */

$json = $this->getVar('json', '');
$class = $this->getVar('class', '');

$contacts = json_decode($json, true);
if (!is_array($contacts) || count($contacts) === 0) {
    return;
}
?>
<div class="fields-contacts <?= rex_escape($class) ?>">
    <?php foreach ($contacts as $contact): ?>
        <article class="fields-contact-card">
            <?php if (!empty($contact['avatar'])): ?>
                <div class="fields-contact-avatar">
                    <img src="<?= rex_escape(rex_url::media($contact['avatar'])) ?>"
                         alt="<?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>" />
                </div>
            <?php endif; ?>

            <div class="fields-contact-body">
                <?php if (!empty($contact['company_logo'])): ?>
                    <img src="<?= rex_escape(rex_url::media($contact['company_logo'])) ?>"
                         alt="<?= rex_escape($contact['company'] ?? '') ?>"
                         class="fields-contact-logo" />
                <?php endif; ?>

                <h3 class="fields-contact-name">
                    <?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>
                </h3>

                <?php if (!empty($contact['function'])): ?>
                    <p class="fields-contact-function"><?= rex_escape($contact['function']) ?></p>
                <?php endif; ?>

                <?php if (!empty($contact['company'])): ?>
                    <p class="fields-contact-company"><?= rex_escape($contact['company']) ?></p>
                <?php endif; ?>

                <ul class="fields-contact-info">
                    <?php if (!empty($contact['phone'])): ?>
                        <li><a href="tel:<?= rex_escape($contact['phone']) ?>"><?= rex_escape($contact['phone']) ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($contact['mobile'])): ?>
                        <li><a href="tel:<?= rex_escape($contact['mobile']) ?>"><?= rex_escape($contact['mobile']) ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($contact['email'])): ?>
                        <li><a href="mailto:<?= rex_escape($contact['email']) ?>"><?= rex_escape($contact['email']) ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($contact['homepage'])): ?>
                        <li><a href="<?= rex_escape($contact['homepage']) ?>" target="_blank" rel="noopener"><?= rex_escape(parse_url($contact['homepage'], PHP_URL_HOST) ?: $contact['homepage']) ?></a></li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($contact['street']) || !empty($contact['city'])): ?>
                    <address class="fields-contact-address">
                        <?php if (!empty($contact['street'])): ?><?= rex_escape($contact['street']) ?><br><?php endif; ?>
                        <?= rex_escape(trim(($contact['zip'] ?? '') . ' ' . ($contact['city'] ?? ''))) ?>
                        <?php if (!empty($contact['country'])): ?><br><?= rex_escape($contact['country']) ?><?php endif; ?>
                    </address>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
