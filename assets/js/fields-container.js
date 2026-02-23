(function() {
    'use strict';

    /**
     * Fields Container Logic
     * Handles wrapping of YForm elements into Tabs, Accordions or Custom Groups
     * based on .fields-container-marker elements.
     */

    function initFieldsContainers(scope) {
        scope = scope || document;
        
        // Find all markers that haven't been processed
        // We use querySelectorAll to get a static list, but we might manipulate DOM, so be careful.
        var markers = scope.querySelectorAll('.fields-container-marker:not(.js-processed)');
        if (markers.length === 0) return;

        // Group markers by 'group_id'
        // If no group_id, treat as standalone 'fieldset' logic (auto-close previous of same type?)
        // Actually, for simplicity, let's process linearly first.
        
        // Convert to Array for easier handling
        var markersArr = Array.prototype.slice.call(markers);
        
        // We need to process groups together.
        // Map: group_id -> [markers]
        var groups = {};
        
        markersArr.forEach(function(marker) {
            var g = marker.dataset.group;
            if (g && g !== '') {
                if (!groups[g]) groups[g] = [];
                groups[g].push(marker);
            } else {
                // No group? Maybe a standalone fieldset separator.
                // We can process strictly linear here.
                processStandalone(marker);
            }
            marker.classList.add('js-processed');
        });

        // Process Groups (Tabs/Accordions)
        for (var gid in groups) {
            if (groups.hasOwnProperty(gid)) {
                processGroup(groups[gid], gid);
            }
        }
    }

    function processStandalone(marker) {
        // Just a separator? E.g. fieldset legend?
        // Standard YForm fieldset behavior:
        // It should wrap everything until next marker or end.
        // Implementation: Find siblings until next marker. Wrap them.
        
        var type = marker.dataset.type; // fieldset
        var label = marker.dataset.label;
        
        if (type === 'end_group') {
            // Just a stopper. Do nothing visually, just serves as break point.
            marker.style.display = 'none';
            return;
        }

        // Collect Content
        var content = getNextSiblingsUntil(marker, '.fields-container-marker, fieldset, .yform-fieldset');
        // Stop also at standard fieldsets to avoid breaking layout

        if (content.length > 0) {
            // Create Wrapper
            var wrapper = document.createElement('div');
            wrapper.className = 'fields-container-fieldset';
            if (label) {
                var legend = document.createElement('h3'); // or legend if we use fieldset tag
                legend.className = 'fields-container-legend';
                legend.textContent = label;
                wrapper.appendChild(legend);
            }
            
            // Insert Wrapper
            marker.parentNode.insertBefore(wrapper, marker.nextSibling);

            // Move Content
            content.forEach(function(el) {
                wrapper.appendChild(el);
            });
        }
    }

    function processGroup(groupMarkers, groupId) {
        // Validation: We need at least 1 tab/item.
        if (groupMarkers.length === 0) return;

        var firstMarker = groupMarkers[0];
        var type = firstMarker.dataset.type; // tab or accordion
        var container = null;
        var inputs = []; // List of item objects { marker, contentWrapper, label, active }

        // 1. Create Main Container
        if (type === 'tab') {
            container = document.createElement('div');
            container.className = 'fields-container-tabs fields-group-' + groupId;
            // Create Nav
            var nav = document.createElement('ul');
            nav.className = 'nav nav-tabs'; 
            // UIkit support? If generic:
            // nav.className = 'fields-tabs-nav';
            container.appendChild(nav);
            
            // Content Container
            var contentBox = document.createElement('div');
            contentBox.className = 'tab-content';
            container.appendChild(contentBox);
            
            container._nav = nav;
            container._content = contentBox;

        } else if (type === 'accordion') {
            container = document.createElement('div');
            container.className = 'panel-group fields-group-' + groupId;
            container.id = 'accordion-' + groupId;

        } else if (type === 'grid') {
            container = document.createElement('div');
            container.className = 'row fields-group-' + groupId; // Bootstrap row
            // UIkit: container.className = 'uk-grid fields-group-' + groupId; container.setAttribute('uk-grid', '');
            
        } else {
             // Fallback
             container = document.createElement('div');
             container.className = 'fields-group-' + groupId;
        }

        // Insert container before first marker
        firstMarker.parentNode.insertBefore(container, firstMarker);

        // 2. Process Items
        groupMarkers.forEach(function(marker, index) {
            var mType = marker.dataset.type;
            if (mType === 'end_group') {
                // Just stop collecting.
                // The loop continues but we don't start a new item.
                return;
            }

            var label = marker.dataset.label;
            var isActive = marker.dataset.active == '1'; // active from PHP
            
            // Collect Content
            // Stop at next marker of SAME group OR any marker?
            // "Next marker of same group" is safer for nesting?
            // But if we have nesting, inner markers are just content.
            // But we can't easily distinguish inner from outer if selectors match.
            // Assumption: YForm structure is linear. Nested items (e.g. tab inside tab)
            // would have markers in between.
            
            var content = getNextSiblingsUntil(marker, '.fields-container-marker[data-group="'+groupId+'"]');
            
            if (type === 'tab') {
                // Add Tab Nav Item
                var li = document.createElement('li');
                var a = document.createElement('a');
                a.href = '#tab-' + groupId + '-' + index;
                a.textContent = label;
                a.dataset.toggle = 'tab'; // Bootstrap 3
                
                // UIkit 3 support?
                // If we detect UIkit:
                // li.innerHTML = '<a href="#">' + label + '</a>';
                
                li.appendChild(a);
                
                // Content Pane
                var pane = document.createElement('div');
                pane.className = 'tab-pane';
                pane.id = 'tab-' + groupId + '-' + index;

                if (isActive || index === 0 && !hasActive(inputs)) {
                    li.classList.add('active');
                    pane.classList.add('active');
                    // Store that we found an active one
                }

                container._nav.appendChild(li);
                container._content.appendChild(pane);
                
                // Move content
                content.forEach(function(el) { pane.appendChild(el); });

                inputs.push({marker: marker, pane: pane, nav: li});

                // Bootstrap 3 Click Handler (if JS not loaded)
                a.addEventListener('click', function(e) {
                     e.preventDefault();
                     // Simple vanilla toggle if BS not present
                     if (!window.jQuery || !jQuery.fn.tab) {
                         var allLis = container._nav.querySelectorAll('li');
                         var allPanes = container._content.querySelectorAll('.tab-pane');
                         for(var i=0; i<allLis.length; i++) allLis[i].classList.remove('active');
                         for(var i=0; i<allPanes.length; i++) allPanes[i].classList.remove('active');
                         
                         li.classList.add('active');
                         pane.classList.add('active');
                     } else {
                         // Let Bootstrap handle it
                         jQuery(this).tab('show');
                     }
                });
            } 
            else if (type === 'accordion') {
                // Bootstrap 3 Panel
                var panel = document.createElement('div');
                panel.className = 'panel panel-default';
                
                var heading = document.createElement('div');
                heading.className = 'panel-heading';
                var title = document.createElement('h4');
                title.className = 'panel-title';
                var a = document.createElement('a');
                a.href = '#collapse-' + groupId + '-' + index;
                a.dataset.toggle = 'collapse';
                a.dataset.parent = '#accordion-' + groupId;
                a.textContent = label;
                
                title.appendChild(a);
                heading.appendChild(title);
                panel.appendChild(heading);
                
                var collapse = document.createElement('div');
                collapse.className = 'panel-collapse collapse';
                collapse.id = 'collapse-' + groupId + '-' + index;
                if (isActive) collapse.classList.add('in'); // BS3 open class
                
                var body = document.createElement('div');
                body.className = 'panel-body';
                
                // Move content
                content.forEach(function(el) { body.appendChild(el); });
                
                collapse.appendChild(body);
                panel.appendChild(collapse);
                container.appendChild(panel);

                // Vanilla Fallback (fixes closing bug)
                 a.addEventListener('click', function(e) {
                     e.preventDefault();
                     
                     // We prefer standard bootstrap if available
                     if (window.jQuery && jQuery.fn.collapse) {
                         jQuery(this).collapse('toggle');
                         return;
                     } 

                     // Vanilla Logic
                     if (collapse.classList.contains('in')) {
                         collapse.classList.remove('in');
                         collapse.style.display = 'none';
                         // Maintain heading! 'panel' and 'panel-heading' are untouched.
                     } else {
                         // Close other panels in this group?
                         var all = container.querySelectorAll('.panel-collapse');
                         for(var i=0; i<all.length; i++) {
                             // Only if inside SAME container directly?
                             // Bootstrap accordions id-based.
                             // We select all in THIS container.
                             all[i].classList.remove('in');
                             all[i].style.display = 'none';
                         }
                         collapse.classList.add('in');
                         collapse.style.display = 'block';
                     }
                });
            } else if (type === 'grid') {
                // Grid Item
                var colDiv = document.createElement('div');
                var cssClass = marker.dataset.class || 'col-md-12'; // Default to full width
                colDiv.className = cssClass;
                
                // Move content
                content.forEach(function(el) { colDiv.appendChild(el); });
                container.appendChild(colDiv);
            }
        });

        // 3. Update Input Active State based on definition
        // (Handled inside loop default to first if none active)
    }
    
    // Check if element is visible
    function isVisible(el){
        return !!( el.offsetWidth || el.offsetHeight || el.getClientRects().length );
    }

    // Helper: Check if we already have an active tab in our list
    function hasActive(inputs) {
        // iterate inputs logic... simplified in loop
        return false; 
    }

    /**
     * Get siblings until selector
     */
    function getNextSiblingsUntil(elem, selector) {
        var siblings = [];
        var next = elem.nextElementSibling;
        
        while (next) {
            // Check if matches selector
            if (next.matches && next.matches(selector)) {
                break;
            }
            siblings.push(next);
            next = next.nextElementSibling;
        }
        return siblings;
    }

    // Init Logic
    var init = function() {
        initFieldsContainers(document);
        
        // Rex Ready for AJAX
        if (window.jQuery) {
            jQuery(document).on('rex:ready', function(e, container) {
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
