<?php

/**
 * Fragment: Contacts – Bootstrap 3
 *
 * Variablen:
 * - json: string (JSON der Kontakte)
 * - class: string (zusätzliche CSS-Klasse)
 *
 * Beispiel:
 * $fragment = new rex_fragment();
 * $fragment->setVar('json', $dataset->getValue('contacts'));
 * echo $fragment->parse('fields/bootstrap3/contacts.php');
 */

$json = $this->getVar('json', '');
$class = $this->getVar('class', '');

$contacts = json_decode($json, true);
if (!is_array($contacts) || count($contacts) === 0) {
    return;
}
?>
<div class="row <?= rex_escape($class) ?>">
    <?php foreach ($contacts as $contact): ?>
        <div class="col-sm-6 col-md-4">
            <div class="panel panel-default fields-contact-card">
                <?php if (!empty($contact['avatar'])): ?>
                    <div class="panel-heading text-center" style="padding:0;">
                        <img src="<?= rex_escape(rex_url::media($contact['avatar'])) ?>"
                             alt="<?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>"
                             class="img-responsive" style="width:100%;" />
                    </div>
                <?php endif; ?>
                <div class="panel-body">
                    <?php if (!empty($contact['company_logo'])): ?>
                        <img src="<?= rex_escape(rex_url::media($contact['company_logo'])) ?>"
                             alt="<?= rex_escape($contact['company'] ?? '') ?>"
                             class="img-responsive" style="max-height:40px; margin-bottom:10px;" />
                    <?php endif; ?>

                    <h4 class="panel-title">
                        <?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>
                    </h4>

                    <?php if (!empty($contact['function'])): ?>
                        <p class="text-muted"><?= rex_escape($contact['function']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($contact['company'])): ?>
                        <p><strong><?= rex_escape($contact['company']) ?></strong></p>
                    <?php endif; ?>

                    <ul class="list-unstyled">
                        <?php if (!empty($contact['phone'])): ?>
                            <li><i class="fa fa-phone"></i> <a href="tel:<?= rex_escape($contact['phone']) ?>"><?= rex_escape($contact['phone']) ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($contact['mobile'])): ?>
                            <li><i class="fa fa-mobile"></i> <a href="tel:<?= rex_escape($contact['mobile']) ?>"><?= rex_escape($contact['mobile']) ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($contact['email'])): ?>
                            <li><i class="fa fa-envelope"></i> <a href="mailto:<?= rex_escape($contact['email']) ?>"><?= rex_escape($contact['email']) ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($contact['homepage'])): ?>
                            <li><i class="fa fa-globe"></i> <a href="<?= rex_escape($contact['homepage']) ?>" target="_blank" rel="noopener"><?= rex_escape(parse_url($contact['homepage'], PHP_URL_HOST) ?: $contact['homepage']) ?></a></li>
                        <?php endif; ?>
                    </ul>

                    <?php if (!empty($contact['street']) || !empty($contact['city'])): ?>
                        <address>
                            <?php if (!empty($contact['street'])): ?><?= rex_escape($contact['street']) ?><br /><?php endif; ?>
                            <?php if (!empty($contact['zip']) || !empty($contact['city'])): ?>
                                <?= rex_escape(($contact['zip'] ?? '') . ' ' . ($contact['city'] ?? '')) ?><br />
                            <?php endif; ?>
                            <?php if (!empty($contact['country'])): ?><?= rex_escape($contact['country']) ?><?php endif; ?>
                        </address>
                    <?php endif; ?>

                    <?php if (!empty($contact['social']) && is_array($contact['social'])): ?>
                        <div class="fields-contact-social">
                            <?php foreach ($contact['social'] as $social): ?>
                                <a href="<?= rex_escape($social['url'] ?? '') ?>" target="_blank" rel="noopener noreferrer"
                                   title="<?= rex_escape($social['platform'] ?? '') ?>">
                                    <?= rex_escape($social['platform'] ?? '') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
