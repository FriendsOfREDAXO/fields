<?php

/** @var rex_addon $this */

echo rex_view::title(rex_i18n::msg('yform'));

$content = '';

$readmePath = $this->getPath('README.' . rex_i18n::getLanguage() . '.md');
if (!is_readable($readmePath)) {
    $readmePath = $this->getPath('README.md');
}

if (is_readable($readmePath)) {
    [$readmeToc, $readmeContent] = rex_markdown::factory()->parseWithToc(rex_file::require($readmePath), 2, 3, [
        rex_markdown::SOFT_LINE_BREAKS => false,
        rex_markdown::HIGHLIGHT_PHP => true,
    ]);

    $fragment = new rex_fragment();
    $fragment->setVar('content', $readmeContent, false);
    $fragment->setVar('toc', $readmeToc, false);
    $content = $fragment->parse('core/page/docs.php');
} else {
    $content = rex_view::info(rex_i18n::msg('package_no_help_file'));
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('fields_help'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
