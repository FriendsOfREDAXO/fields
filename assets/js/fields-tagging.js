/**
 * Fields Tagging Widget
 *
 * Handles tag chip creation, removal, suggestion loading and hidden input sync.
 * Follows the Fields AddOn event-delegation pattern:
 *  - All listeners on document (no direct element binding)
 *  - Always scope to the closest .fields-tagging wrapper
 */
(function () {
    'use strict';

    var FieldsTagging = {

        init: function () {
            FieldsTagging.registerGlobalEvents();
            FieldsTagging.initAllWidgets(document);
        },

        registerGlobalEvents: function () {
            FieldsTagging.registerTagRemoveEvents();
            FieldsTagging.registerAddButtonEvents();
            FieldsTagging.registerInputEnterEvents();
            FieldsTagging.registerAddSuggestEvents();
        },

        registerTagRemoveEvents: function () {
            document.addEventListener('click', function (e) {
                var removeBtn = e.target.closest('.fields-tagging-remove');
                if (!removeBtn) return;

                var tag = removeBtn.closest('.fields-tagging-tag');
                var wrapper = removeBtn.closest('.fields-tagging');
                if (tag && wrapper) {
                    tag.remove();
                    FieldsTagging.updateHiddenValue(wrapper);
                }
            });
        },

        registerAddButtonEvents: function () {
            document.addEventListener('click', function (e) {
                var addBtn = e.target.closest('.fields-tagging-add');
                if (!addBtn) return;

                var wrapper = addBtn.closest('.fields-tagging');
                if (!wrapper) return;

                var input = wrapper.querySelector('.fields-tagging-input');
                FieldsTagging.addTagFromInput(wrapper, input);
            });
        },

        registerInputEnterEvents: function () {
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') return;

                var input = e.target.closest('.fields-tagging-input');
                if (!input) return;

                e.preventDefault();
                var wrapper = input.closest('.fields-tagging');
                if (wrapper) {
                    FieldsTagging.addTagFromInput(wrapper, input);
                }
            });
        },

        registerAddSuggestEvents: function () {
            document.addEventListener('click', function (e) {
                var addSuggestBtn = e.target.closest('.fields-tagging-add-suggest');
                if (!addSuggestBtn) return;

                var wrapper = addSuggestBtn.closest('.fields-tagging');
                if (!wrapper) return;

                var select = wrapper.querySelector('.fields-tagging-suggest');
                if (!select) return;

                var options = select.options;
                for (var i = 0; i < options.length; i++) {
                    if (options[i].selected) {
                        FieldsTagging.addTag(wrapper, options[i].value);
                    }
                }

                // Deselect all options
                for (var j = 0; j < options.length; j++) {
                    options[j].selected = false;
                }

                // Refresh Bootstrap Selectpicker if loaded
                if (window.jQuery && jQuery.fn.selectpicker) {
                    jQuery(select).selectpicker('refresh');
                }
            });
        },

        initAllWidgets: function (scope) {
            var widgets = (scope || document).querySelectorAll('.fields-tagging');
            widgets.forEach(function (wrapper) {
                FieldsTagging.loadSuggestions(wrapper);
            });
        },

        addTagFromInput: function (wrapper, input) {
            if (!input) return;
            var value = input.value.trim();
            if (value === '') return;

            FieldsTagging.addTag(wrapper, value);
            input.value = '';
        },

        addTag: function (wrapper, rawTag) {
            var tag = rawTag.trim();
            if (tag === '') return;

            // Enforce max tags limit
            var maxTags = parseInt(wrapper.dataset.maxTags, 10) || 0;
            var existing = wrapper.querySelectorAll('.fields-tagging-tag');
            if (maxTags > 0 && existing.length >= maxTags) {
                return;
            }

            // Prevent duplicates (case-insensitive)
            var tagLower = tag.toLowerCase();
            var duplicate = false;
            existing.forEach(function (el) {
                if ((el.dataset.tag || '').toLowerCase() === tagLower) {
                    duplicate = true;
                }
            });
            if (duplicate) return;

            var tagsContainer = wrapper.querySelector('.fields-tagging-tags');
            if (!tagsContainer) return;

            var span = document.createElement('span');
            span.className = 'label label-primary fields-tagging-tag';
            span.dataset.tag = tag;

            var textNode = document.createTextNode(tag + '\u00a0');
            span.appendChild(textNode);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'fields-tagging-remove';
            btn.setAttribute('aria-label', 'Tag entfernen');
            btn.textContent = '\u00d7';
            span.appendChild(btn);

            tagsContainer.appendChild(span);
            FieldsTagging.updateHiddenValue(wrapper);
        },

        updateHiddenValue: function (wrapper) {
            var hiddenInput = wrapper.querySelector('.fields-tagging-value');
            if (!hiddenInput) return;

            var tags = wrapper.querySelectorAll('.fields-tagging-tag');
            var values = [];
            tags.forEach(function (tag) {
                var val = (tag.dataset.tag || '').trim();
                if (val !== '') {
                    values.push(val);
                }
            });
            hiddenInput.value = values.join(',');
        },

        loadSuggestions: function (wrapper) {
            var apiUrl = (wrapper.dataset.apiUrl || '').trim();
            var table = (wrapper.dataset.sourceTable || '').trim();
            var field = (wrapper.dataset.sourceField || '').trim();

            if (!apiUrl || !table || !field) return;

            var url = apiUrl + '&table=' + encodeURIComponent(table) + '&field=' + encodeURIComponent(field);

            fetch(url, { credentials: 'same-origin' })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success || !Array.isArray(data.tags)) return;

                    var select = wrapper.querySelector('.fields-tagging-suggest');
                    if (!select) return;

                    select.innerHTML = '';
                    data.tags.forEach(function (tag) {
                        var option = document.createElement('option');
                        option.value = tag;
                        option.textContent = tag;
                        select.appendChild(option);
                    });

                    if (window.jQuery && jQuery.fn.selectpicker) {
                        jQuery(select).selectpicker('refresh');
                    }
                })
                .catch(function () {
                    // Suggestions not critical – fail silently
                });
        }
    };

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { FieldsTagging.init(); });
    } else {
        FieldsTagging.init();
    }

    // Re-init on PJAX / rex:ready (e.g. after YForm AJAX reloads)
    document.addEventListener('rex:ready', function (e) {
        var scope = (e.detail && e.detail[1]) ? e.detail[1] : document;
        FieldsTagging.initAllWidgets(scope);
    });

})();
