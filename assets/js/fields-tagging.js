/**
 * Fields Tagging Widget
 * jQuery-based, direct widget initialization per instance.
 */
(function ($) {
    'use strict';

    function initWidget(wrapper) {
        var $wrapper    = $(wrapper);
        var $tags       = $wrapper.find('.fields-tagging-tags');
        var $input      = $wrapper.find('.fields-tagging-input');
        var $hidden     = $wrapper.find('.fields-tagging-value');
        var $addBtn     = $wrapper.find('.fields-tagging-add');
        var datalistId  = $input.attr('list');
        var apiUrl      = $wrapper.data('api-url');
        var sourceTable = $wrapper.data('source-table');
        var sourceField = $wrapper.data('source-field');
        var maxTags     = parseInt($wrapper.data('max-tags'), 10) || 0;

        if (!$hidden.length) { return; }

        // ---- Suggestions via datalist -----------------------------------
        if (apiUrl && sourceTable && sourceField && datalistId) {
            var url = apiUrl + '&table=' + encodeURIComponent(sourceTable) + '&field=' + encodeURIComponent(sourceField);
            $.get(url, function (data) {
                if (!data || !data.success || !$.isArray(data.tags)) { return; }
                var $dl = $('#' + datalistId);
                $dl.empty();
                $.each(data.tags, function (i, tag) {
                    $dl.append($('<option>').val(tag));
                });
            }).fail(function () { /* suggestions not critical */ });
        }

        // ---- Helpers ----------------------------------------------------
        function existingTags() {
            var result = [];
            $tags.find('.fields-tagging-tag').each(function () {
                result.push($(this).data('tag'));
            });
            return result;
        }

        function updateHidden() {
            $hidden.val(existingTags().join(','));
        }

        function addTag(raw) {
            var tag = $.trim(raw);
            if (tag === '') { return; }

            if (maxTags > 0 && existingTags().length >= maxTags) { return; }

            var lower = tag.toLowerCase();
            var dupe = false;
            $.each(existingTags(), function (i, t) {
                if (String(t).toLowerCase() === lower) { dupe = true; return false; }
            });
            if (dupe) { return; }

            var $chip = $('<span>')
                .addClass('label label-primary fields-tagging-tag')
                .css({ display: 'inline-flex', alignItems: 'center', gap: '4px', margin: '2px 4px 2px 0', padding: '4px 6px', fontSize: '13px' })
                .data('tag', tag)
                .attr('data-tag', tag)
                .text(tag + '\u00a0');

            $('<button type="button" aria-label="Tag entfernen">')
                .addClass('fields-tagging-remove')
                .css({ background: 'none', border: 'none', padding: 0, lineHeight: 1, cursor: 'pointer', color: 'inherit' })
                .text('\u00d7')
                .appendTo($chip);

            $tags.append($chip);
            updateHidden();
        }

        // ---- Events (scoped to this widget) -----------------------------

        $tags.on('click', '.fields-tagging-remove', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.fields-tagging-tag').remove();
            updateHidden();
        });

        $addBtn.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            addTag($input.val());
            $input.val('').trigger('focus');
        });

        $input.on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();
                addTag($input.val());
                $input.val('');
            }
        });

        // Safety net: sync hidden value before form submit
        $wrapper.closest('form').on('submit.fields-tagging', function () {
            updateHidden();
        });
    }

    function initAll(scope) {
        $(scope || document).find('.fields-tagging').each(function () {
            if (!$(this).data('tagging-init')) {
                $(this).data('tagging-init', true);
                initWidget(this);
            }
        });
    }

    $(document).on('rex:ready', function (e, container) {
        initAll(container);
    });

    $(function () { initAll(document); });

}(jQuery));
