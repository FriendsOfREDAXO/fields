<?php

/**
 * Fragment: Contacts – Tailwind CSS
 */

$json = $this->getVar('json', '');
$class = $this->getVar('class', '');

$contacts = json_decode($json, true);
if (!is_array($contacts) || count($contacts) === 0) {
    return;
}
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 <?= rex_escape($class) ?>">
    <?php foreach ($contacts as $contact): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if (!empty($contact['avatar'])): ?>
                <img src="<?= rex_escape(rex_url::media($contact['avatar'])) ?>"
                     alt="<?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>"
                     class="w-full h-48 object-cover" />
            <?php endif; ?>

            <div class="p-6">
                <?php if (!empty($contact['company_logo'])): ?>
                    <img src="<?= rex_escape(rex_url::media($contact['company_logo'])) ?>"
                         alt="<?= rex_escape($contact['company'] ?? '') ?>"
                         class="h-8 mb-3" />
                <?php endif; ?>

                <h3 class="text-lg font-semibold text-gray-900">
                    <?= rex_escape(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')) ?>
                </h3>

                <?php if (!empty($contact['function'])): ?>
                    <p class="text-sm text-gray-500 mt-1"><?= rex_escape($contact['function']) ?></p>
                <?php endif; ?>

                <?php if (!empty($contact['company'])): ?>
                    <p class="text-sm font-medium text-gray-700 mt-1"><?= rex_escape($contact['company']) ?></p>
                <?php endif; ?>

                <div class="mt-4 space-y-2">
                    <?php if (!empty($contact['phone'])): ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <a href="tel:<?= rex_escape($contact['phone']) ?>" class="hover:text-blue-600"><?= rex_escape($contact['phone']) ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($contact['mobile'])): ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <a href="tel:<?= rex_escape($contact['mobile']) ?>" class="hover:text-blue-600"><?= rex_escape($contact['mobile']) ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($contact['email'])): ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <a href="mailto:<?= rex_escape($contact['email']) ?>" class="hover:text-blue-600"><?= rex_escape($contact['email']) ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($contact['homepage'])): ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            <a href="<?= rex_escape($contact['homepage']) ?>" target="_blank" rel="noopener" class="hover:text-blue-600"><?= rex_escape(parse_url($contact['homepage'], PHP_URL_HOST) ?: $contact['homepage']) ?></a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($contact['street']) || !empty($contact['city'])): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200 text-sm text-gray-600">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div>
                                <?php if (!empty($contact['street'])): ?><?= rex_escape($contact['street']) ?><br><?php endif; ?>
                                <?= rex_escape(trim(($contact['zip'] ?? '') . ' ' . ($contact['city'] ?? ''))) ?>
                                <?php if (!empty($contact['country'])): ?><br><?= rex_escape($contact['country']) ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
