(function() {
    'use strict';

    /**
     * Fields Section Logic
     * Handles wrapping of YForm elements into Grid/Flex Layouts
     */

    function initFieldsSections(scope) {
        scope = scope || document;
        
        var markers = scope.querySelectorAll('.fields-section-marker:not(.js-processed)');
        if (markers.length === 0) return;

        // Process sequentially
        Array.prototype.forEach.call(markers, function(marker) {
            marker.classList.add('js-processed');
            var type = marker.dataset.type;
            
            if (type === 'start') {
                var style = marker.dataset.style;
                
                // Find content until next section marker (start or end)
                // We use a simplified selector here, assuming no deep nesting of SECTIONS within SECTIONS directly without markers
                var content = getNextSiblingsUntil(marker, '.fields-section-marker');
                
                if (content.length > 0) {
                    var wrapper = document.createElement('div');
                    wrapper.className = 'fields-section-grid';
                    // Apply styles
                    wrapper.style.cssText = style;
                    // Add some base robustness
                    wrapper.style.width = '100%';
                    wrapper.style.marginBottom = '15px';
                    wrapper.style.alignItems = 'start'; // Align items to top

                    // Insert
                    marker.parentNode.insertBefore(wrapper, marker.nextSibling);
                    
                    // Move Content
                    content.forEach(function(el) {
                        wrapper.appendChild(el);
                        // Ensure children behave well in grid
                        if (el.style) {
                            el.style.maxWidth = '100%'; // Prevent overflow
                        }
                    });
                }
            }
            // type === 'end' just serves as a stop marker for the 'start' collection loop
        });
    }

    /**
     * Get siblings until selector
     */
    function getNextSiblingsUntil(elem, selector) {
        var siblings = [];
        var next = elem.nextElementSibling;
        
        while (next) {
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
        initFieldsSections(document);
        
        if (window.jQuery) {
            jQuery(document).on('rex:ready', function(e, container) {
                 var dom = container instanceof jQuery ? container[0] : container;
                 initFieldsSections(dom);
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
