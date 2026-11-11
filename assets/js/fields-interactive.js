(function () {
    'use strict';

    /**
     * Fields Interactive Logic
     * Wraps YForm elements into tabs, accordions or fieldsets based on .fields-interactive-marker
     * elements (fields_interactive).
     *
     * Accessibility (WAI-ARIA Authoring Practices):
     * - Tabs: role tablist/tab/tabpanel, aria-selected, aria-controls/aria-labelledby, roving
     *   tabindex, arrow keys / Home / End switch tabs (automatic activation).
     * - Accordions: the heading contains a real <button> with aria-expanded/aria-controls,
     *   the panel is a region labelled by its button.
     * - Fields with validation errors are never hidden: the tab/panel containing them is opened
     *   on load (and marked), and when the browser reports an invalid field on submit.
     */

    var uid = 0;
    var ERROR_SELECTOR = '.has-error, .text-warning, [aria-invalid="true"]';

    function initFieldsContainers(scope) {
        scope = scope || document;

        var markers = scope.querySelectorAll('.fields-interactive-marker:not(.js-processed)');
        if (markers.length === 0) return;

        // Group markers by group_id; markers without a group are standalone fieldsets
        var groups = {};
        Array.prototype.slice.call(markers).forEach(function (marker) {
            var g = marker.dataset.group;
            if (g && g !== '') {
                if (!groups[g]) groups[g] = [];
                groups[g].push(marker);
            } else {
                processStandalone(marker);
            }
            marker.classList.add('js-processed');
        });

        for (var gid in groups) {
            if (Object.prototype.hasOwnProperty.call(groups, gid)) {
                processGroup(groups[gid], gid);
            }
        }
    }

    function processStandalone(marker) {
        var type = marker.dataset.type;
        var label = marker.dataset.label;

        if (type === 'end_group') {
            // Only a break point
            marker.style.display = 'none';
            return;
        }

        var content = getNextSiblingsUntil(marker, '.fields-interactive-marker, fieldset, .yform-fieldset');
        if (content.length === 0) return;

        var wrapper = document.createElement('div');
        wrapper.className = 'fields-interactive-fieldset';
        if (label) {
            var legend = document.createElement('h3');
            legend.className = 'fields-interactive-legend';
            legend.textContent = label;
            wrapper.appendChild(legend);
        }
        marker.parentNode.insertBefore(wrapper, marker.nextSibling);
        content.forEach(function (el) {
            wrapper.appendChild(el);
        });
    }

    function processGroup(groupMarkers, groupId) {
        if (groupMarkers.length === 0) return;

        var type = groupMarkers[0].dataset.type;
        var items = groupMarkers.filter(function (marker) {
            return marker.dataset.type !== 'end_group';
        });

        // Content of each item: everything up to the next grouped marker. Fieldsets without a group
        // (already wrapped above) stay part of the item, so fieldsets can be used inside a tab.
        var contents = items.map(function (marker) {
            return getNextSiblingsUntil(marker, '.fields-interactive-marker:not([data-group=""])');
        });

        var container = document.createElement('div');
        groupMarkers[0].parentNode.insertBefore(container, groupMarkers[0]);

        if (type === 'tab') {
            buildTabs(container, items, contents, groupId);
        } else if (type === 'accordion') {
            buildAccordion(container, items, contents, groupId);
        } else {
            container.className = (type === 'grid' ? 'row ' : '') + 'fields-group-' + groupId;
            contents.forEach(function (content) {
                content.forEach(function (el) { container.appendChild(el); });
            });
        }
    }

    /* ------------------------------------------------------------------ Tabs */

    function buildTabs(container, items, contents, groupId) {
        var base = 'fields-tabs-' + groupId + '-' + (++uid);
        container.className = 'fields-container-tabs fields-group-' + groupId;

        var nav = document.createElement('ul');
        nav.className = 'nav nav-tabs';
        nav.setAttribute('role', 'tablist');
        container.appendChild(nav);

        var contentBox = document.createElement('div');
        contentBox.className = 'tab-content';
        container.appendChild(contentBox);

        var tabs = [];
        var panes = [];

        items.forEach(function (marker, index) {
            var li = document.createElement('li');
            li.setAttribute('role', 'presentation');

            var tab = document.createElement('a');
            tab.id = base + '-tab-' + index;
            tab.href = '#' + base + '-panel-' + index;
            tab.setAttribute('role', 'tab');
            tab.setAttribute('aria-controls', base + '-panel-' + index);
            tab.textContent = marker.dataset.label || '';
            li.appendChild(tab);
            nav.appendChild(li);

            var pane = document.createElement('div');
            pane.className = 'tab-pane';
            pane.id = base + '-panel-' + index;
            pane.setAttribute('role', 'tabpanel');
            pane.setAttribute('aria-labelledby', tab.id);
            contents[index].forEach(function (el) { pane.appendChild(el); });
            contentBox.appendChild(pane);

            tabs.push(tab);
            panes.push(pane);
        });

        function select(index, focus) {
            tabs.forEach(function (tab, i) {
                var active = i === index;
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.tabIndex = active ? 0 : -1;
                tab.parentNode.classList.toggle('active', active);
                panes[i].classList.toggle('active', active);
                panes[i].hidden = !active;
            });
            if (focus) tabs[index].focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                select(index, false);
            });
            tab.addEventListener('keydown', function (e) {
                var target = null;
                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') target = (index + 1) % tabs.length;
                else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') target = (index - 1 + tabs.length) % tabs.length;
                else if (e.key === 'Home') target = 0;
                else if (e.key === 'End') target = tabs.length - 1;
                if (target === null) return;
                e.preventDefault();
                select(target, true);
            });
        });

        // Start: first tab with an error, otherwise the tab marked "active", otherwise the first
        var start = activeIndex(items);
        var withError = -1;
        panes.forEach(function (pane, i) {
            if (pane.querySelector(ERROR_SELECTOR)) {
                markError(tabs[i]);
                if (withError === -1) withError = i;
            }
        });
        select(withError !== -1 ? withError : start, false);

        // A required/invalid field in a hidden tab: open the tab so the browser can focus it
        panes.forEach(function (pane, i) {
            pane.addEventListener('invalid', function () {
                if (pane.hidden) select(i, false);
            }, true);
        });
    }

    /* ------------------------------------------------------------- Accordion */

    function buildAccordion(container, items, contents, groupId) {
        var base = 'fields-accordion-' + groupId + '-' + (++uid);
        container.className = 'panel-group fields-group-' + groupId;
        container.id = base;

        var buttons = [];
        var regions = [];

        items.forEach(function (marker, index) {
            var panel = document.createElement('div');
            panel.className = 'panel panel-default';

            var heading = document.createElement('div');
            heading.className = 'panel-heading';
            var title = document.createElement('h4');
            title.className = 'panel-title';

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'fields-accordion-toggle';
            button.id = base + '-button-' + index;
            button.setAttribute('aria-controls', base + '-panel-' + index);
            button.textContent = marker.dataset.label || '';
            title.appendChild(button);
            heading.appendChild(title);
            panel.appendChild(heading);

            var region = document.createElement('div');
            region.className = 'panel-collapse';
            region.id = base + '-panel-' + index;
            region.setAttribute('role', 'region');
            region.setAttribute('aria-labelledby', button.id);

            var body = document.createElement('div');
            body.className = 'panel-body';
            contents[index].forEach(function (el) { body.appendChild(el); });
            region.appendChild(body);
            panel.appendChild(region);
            container.appendChild(panel);

            buttons.push(button);
            regions.push(region);
        });

        // Accordion behaviour: at most one panel open
        function setOpen(index, open) {
            buttons.forEach(function (button, i) {
                var isOpen = i === index ? open : false;
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                regions[i].hidden = !isOpen;
                regions[i].classList.toggle('in', isOpen);
            });
        }

        buttons.forEach(function (button, index) {
            button.addEventListener('click', function () {
                setOpen(index, button.getAttribute('aria-expanded') !== 'true');
            });
        });

        var start = -1;
        items.forEach(function (marker, i) {
            if (start === -1 && marker.dataset.active === '1') start = i;
        });
        regions.forEach(function (region, i) {
            if (region.querySelector(ERROR_SELECTOR)) {
                markError(buttons[i]);
                start = i;
            }
        });
        setOpen(start, start !== -1);

        regions.forEach(function (region, i) {
            region.addEventListener('invalid', function () {
                if (region.hidden) setOpen(i, true);
            }, true);
        });
    }

    /* --------------------------------------------------------------- Helpers */

    /** Index of the item marked "active" (data-active="1"), otherwise 0 */
    function activeIndex(items) {
        for (var i = 0; i < items.length; i++) {
            if (items[i].dataset.active === '1') return i;
        }
        return 0;
    }

    /** Visible and announced hint that a tab/panel contains fields with errors */
    function markError(control) {
        control.classList.add('fields-has-error');
        var hint = document.createElement('span');
        hint.className = 'fields-error-hint';
        hint.textContent = ' (!)';
        hint.setAttribute('aria-hidden', 'true');
        var sr = document.createElement('span');
        sr.className = 'sr-only';
        sr.textContent = ' – ' + (document.documentElement.lang === 'de' ? 'enthält Fehler' : 'contains errors');
        control.appendChild(hint);
        control.appendChild(sr);
    }

    function getNextSiblingsUntil(elem, selector) {
        var siblings = [];
        var next = elem.nextElementSibling;
        while (next) {
            if (next.matches && next.matches(selector)) break;
            siblings.push(next);
            next = next.nextElementSibling;
        }
        return siblings;
    }

    var init = function () {
        initFieldsContainers(document);

        // Re-init for content loaded later (REDAXO rex:ready)
        if (window.jQuery) {
            jQuery(document).on('rex:ready', function (e, container) {
                var dom = container instanceof jQuery ? container[0] : container;
                initFieldsContainers(dom);
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
