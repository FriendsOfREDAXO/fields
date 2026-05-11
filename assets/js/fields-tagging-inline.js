/**
 * Fields Tagging Inline (Listenansicht)
 *
 * Bearbeitet Tags direkt in der YForm-Tabellenübersicht ohne Detailformular.
 * Speichert via rex-api-call=fields_inline_update (JSON-Array).
 */
jQuery(function ($) {
    'use strict';

    function hexToLum(hex) {
        var s = (hex || '').replace('#', '');
        if (s.length !== 6) return 1;
        var r = parseInt(s.slice(0, 2), 16) / 255;
        var g = parseInt(s.slice(2, 4), 16) / 255;
        var b = parseInt(s.slice(4, 6), 16) / 255;
        function lin(c) { return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); }
        return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b);
    }

    function textColorFor(hex) {
        return (1.05 / (hexToLum(hex) + 0.05)) >= 3.0 ? '#ffffff' : '#2c3e50';
    }

    function getApiUrl() {
        var pathname = window.location.pathname;
        var script = pathname.substring(pathname.lastIndexOf('/') + 1) || 'index.php';
        return script + '?rex-api-call=fields_inline_update';
    }

    function readChips($wrapper) {
        var tags = [];
        $wrapper.find('.fields-tagging-inline-chip').each(function () {
            tags.push({
                text: $(this).data('text'),
                color: $(this).data('color')
            });
        });
        return tags;
    }

    function renderChip(text, color) {
        var $chip = $('<span class="fields-tagging-inline-chip"></span>')
            .attr('data-text', text)
            .attr('data-color', color)
            .attr('title', text)
            .data('text', text)
            .data('color', color)
            .css('background', color)
            .css('color', textColorFor(color));
        $('<span class="fields-tagging-inline-chip-text"></span>').text(text).appendTo($chip);
        $('<button type="button" class="fields-tagging-inline-remove" aria-label="×">&times;</button>')
            .appendTo($chip);
        return $chip;
    }

    function save($wrapper, tags) {
        var $btn = $wrapper.find('.fields-tagging-inline-add');
        $btn.prop('disabled', true).addClass('is-saving');

        $.ajax({
            url: getApiUrl(),
            method: 'POST',
            data: {
                _csrf_token: $wrapper.data('token'),
                table: $wrapper.data('table'),
                field: $wrapper.data('field'),
                id: $wrapper.data('id'),
                value: tags.length ? JSON.stringify(tags) : ''
            },
            dataType: 'json'
        }).done(function (resp) {
            if (resp && resp.success) {
                $wrapper.addClass('is-saved');
                setTimeout(function () { $wrapper.removeClass('is-saved'); }, 800);
            } else {
                alert((resp && resp.message) || 'Fehler beim Speichern');
            }
        }).fail(function () {
            alert('Netzwerkfehler beim Speichern');
        }).always(function () {
            $btn.prop('disabled', false).removeClass('is-saving');
        });
    }

    function closeAllPopovers(except) {
        $('.fields-tagging-inline-popover').each(function () {
            if (except && this === except) return;
            $(this).remove();
        });
    }

    function loadSuggestions($wrapper, $popover) {
        var apiUrl = $wrapper.data('api-suggest');
        var srcTable = $wrapper.data('source-table');
        var srcField = $wrapper.data('source-field');
        var $list = $popover.find('.fields-tagging-inline-suggestions');

        if (!apiUrl || !srcTable || !srcField) {
            $list.html('<em class="text-muted">Keine Quelle konfiguriert.</em>');
            return;
        }
        $list.html('<em class="text-muted">…</em>');

        $.get(apiUrl, { table: srcTable, field: srcField }).done(function (data) {
            $list.empty();
            if (!data || !data.success || !data.tags || !data.tags.length) {
                $list.html('<em class="text-muted">Keine Vorschläge.</em>');
                return;
            }
            var current = readChips($wrapper).map(function (t) { return t.text.toLowerCase(); });
            $.each(data.tags, function (i, tag) {
                if (current.indexOf((tag.text || '').toLowerCase()) !== -1) return;
                $('<button type="button" class="fields-tagging-inline-suggest"></button>')
                    .attr('data-text', tag.text)
                    .attr('data-color', tag.color)
                    .css('background', tag.color)
                    .css('color', textColorFor(tag.color))
                    .text(tag.text)
                    .appendTo($list);
            });
            if (!$list.children().length) {
                $list.html('<em class="text-muted">Alle Vorschläge bereits vergeben.</em>');
            }
        }).fail(function () {
            $list.html('<em class="text-muted">Fehler beim Laden.</em>');
        });
    }

    function openPopover($wrapper) {
        closeAllPopovers();
        var defaultColor = $wrapper.data('default-color') || '#7f8c8d';
        var maxTags = parseInt($wrapper.data('max-tags'), 10) || 0;

        var $popover = $(
            '<div class="fields-tagging-inline-popover">' +
                '<div class="fields-tagging-inline-input-row">' +
                    '<input type="text" class="form-control input-sm fields-tagging-inline-text" placeholder="Tag…">' +
                    '<input type="color" class="fields-tagging-inline-color" value="' + defaultColor + '">' +
                    '<button type="button" class="btn btn-primary btn-sm fields-tagging-inline-confirm">+</button>' +
                '</div>' +
                '<div class="fields-tagging-inline-suggestions"></div>' +
            '</div>'
        );

        $('body').append($popover);

        var $btn = $wrapper.find('.fields-tagging-inline-add');
        var offset = $btn.offset();
        $popover.css({
            top: offset.top + $btn.outerHeight() + 4,
            left: Math.max(8, offset.left - 180)
        });

        $popover.data('wrapper', $wrapper);

        $popover.on('click', '.fields-tagging-inline-confirm', function () {
            var text = ($popover.find('.fields-tagging-inline-text').val() || '').trim();
            if (!text) return;
            var color = $popover.find('.fields-tagging-inline-color').val() || defaultColor;
            addTag($wrapper, text, color, $popover);
            $popover.find('.fields-tagging-inline-text').val('').trigger('focus');
        });

        $popover.on('keydown', '.fields-tagging-inline-text', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                $popover.find('.fields-tagging-inline-confirm').trigger('click');
            } else if (e.key === 'Escape' || e.keyCode === 27) {
                closeAllPopovers();
            }
        });

        $popover.on('click', '.fields-tagging-inline-suggest', function () {
            var text = $(this).data('text');
            var color = $(this).data('color');
            addTag($wrapper, text, color, $popover);
            $(this).remove();
        });

        loadSuggestions($wrapper, $popover);
        $popover.find('.fields-tagging-inline-text').trigger('focus');

        // Max-Tags Check
        if (maxTags > 0 && readChips($wrapper).length >= maxTags) {
            $popover.find('.fields-tagging-inline-input-row').css('opacity', 0.4);
            $popover.find('.fields-tagging-inline-text').prop('disabled', true);
            $popover.find('.fields-tagging-inline-confirm').prop('disabled', true);
        }
    }

    function addTag($wrapper, text, color, $popover) {
        var maxTags = parseInt($wrapper.data('max-tags'), 10) || 0;
        var tags = readChips($wrapper);
        var lower = text.toLowerCase();
        var exists = tags.some(function (t) { return t.text.toLowerCase() === lower; });
        if (exists) return;
        if (maxTags > 0 && tags.length >= maxTags) return;

        var $chip = renderChip(text, color);
        $wrapper.find('.fields-tagging-inline-chips').append($chip);
        save($wrapper, readChips($wrapper));

        if ($popover && maxTags > 0 && readChips($wrapper).length >= maxTags) {
            $popover.find('.fields-tagging-inline-input-row').css('opacity', 0.4);
            $popover.find('.fields-tagging-inline-text').prop('disabled', true);
            $popover.find('.fields-tagging-inline-confirm').prop('disabled', true);
        }
    }

    // Event delegation
    $(document).on('click', '.fields-tagging-inline-add', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openPopover($(this).closest('.fields-tagging-inline'));
    });

    $(document).on('click', '.fields-tagging-inline-remove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $chip = $(this).closest('.fields-tagging-inline-chip');
        var $wrapper = $chip.closest('.fields-tagging-inline');
        $chip.remove();
        save($wrapper, readChips($wrapper));
    });

    // Klick außerhalb schließt Popover
    $(document).on('click', function (e) {
        if ($(e.target).closest('.fields-tagging-inline-popover, .fields-tagging-inline-add').length) return;
        closeAllPopovers();
    });
});
