(function($) {
    'use strict';

    /**
     * Fields Tree View
     * Replaces standard table list with Nestable tree if available
     */

    function initTree() {
        var $wrapper = $('#yform-nestable');
        if (!$wrapper.length) return;

        // Init Nestable
        $wrapper.nestable({
            group: 1,
            maxDepth: 10,
            expandBtnHTML: '<button data-action="expand"><i class="rex-icon rex-icon-view-open"></i></button>',
            collapseBtnHTML: '<button data-action="collapse"><i class="rex-icon rex-icon-view-close"></i></button>'
        }).on('change', function(e) {
            var list = e.length ? e : $(e.target);
            var data = list.nestable('serialize');
            var table = $wrapper.data('table');
            
            // Show saving indicator
            $wrapper.addClass('saving');
            
            $.ajax({
                url: 'index.php?rex-api-call=fields_tree_update&table_name=' + table,
                type: 'POST',
                data: {data: data},
                success: function(response) {
                    $wrapper.removeClass('saving');
                    // Optional: Show success flash
                },
                error: function() {
                    $wrapper.removeClass('saving');
                    alert('Fehler beim Speichern der Struktur!');
                }
            });
        });
        
        // Ensure handles work
        $wrapper.find('.dd-handle a').on('mousedown', function(e){
            e.stopPropagation();
        });
    }

    $(document).on('ready pjax:success', function() {
        initTree();
    });

})(jQuery);
