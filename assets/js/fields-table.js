/**
 * Fields Addon - Table Editor
 * Accessible Table Editor Logic
 */
(function () {
    'use strict';

    function initTableEditor() {
        document.querySelectorAll('.fields-table-wrapper').forEach(function (wrapper) {
            if (wrapper.dataset.initialized) return;
            wrapper.dataset.initialized = '1';

            var hiddenInput = wrapper.querySelector('.fields-table-value');
            var captionInput = wrapper.querySelector('.fields-table-caption');
            var configInputs = wrapper.querySelectorAll('.fields-table-config');
            var table = wrapper.querySelector('.fields-table-editor');
            var dataScript = wrapper.querySelector('script[type="template"]');
            
            var state = {
                rows: JSON.parse(dataScript ? dataScript.innerHTML : '[]'),
                caption: captionInput ? captionInput.value : '',
                has_header_row: wrapper.querySelector('[data-config="has_header_row"]').checked,
                has_header_col: wrapper.querySelector('[data-config="has_header_col"]').checked
            };

            // Ensure at least one cell if empty
            if (state.rows.length === 0) {
                state.rows = [['']];
            }

            function render() {
                var thead = table.querySelector('thead');
                var tbody = table.querySelector('tbody');
                thead.innerHTML = '';
                tbody.innerHTML = '';

                state.rows.forEach(function (row, rowIndex) {
                    var tr = document.createElement('tr');
                    
                    row.forEach(function (cell, colIndex) {
                        var isHeader = (rowIndex === 0 && state.has_header_row) || (colIndex === 0 && state.has_header_col);
                        var cellType = isHeader ? 'th' : 'td';
                        var cellEl = document.createElement(cellType);
                        
                        // Wrapper for Input + Actions
                        var wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'fields-table-cell-wrapper';

                        // Input Field
                        var input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'form-control input-sm';
                        input.value = cell;
                        input.dataset.row = rowIndex;
                        input.dataset.col = colIndex;
                        
                        // Actions (Del Row / Del Col)
                        var actionsHtml = '';
                        // Delete Col (Show only on first row)
                        if (rowIndex === 0 && row.length > 1) {
                             actionsHtml += '<button type="button" class="btn btn-default btn-xs fields-table-del-col" data-col="' + colIndex + '" title="Spalte löschen" tabindex="-1">&times;</button>';
                        }
                        // Delete Row (Show only on last cell of row)
                        if (colIndex === row.length - 1 && state.rows.length > 1) {
                             actionsHtml += '<button type="button" class="btn btn-default btn-xs fields-table-del-row" data-row="' + rowIndex + '" title="Zeile löschen" tabindex="-1">&times;</button>';
                        }

                        wrapperDiv.appendChild(input);
                        
                        if(actionsHtml) {
                             var actionsDiv = document.createElement('div');
                             actionsDiv.className = 'fields-table-actions';
                             actionsDiv.innerHTML = actionsHtml;
                             wrapperDiv.appendChild(actionsDiv);
                        }

                        cellEl.appendChild(wrapperDiv);
                        tr.appendChild(cellEl);
                    });

                    if (rowIndex === 0 && state.has_header_row) {
                        thead.appendChild(tr);
                    } else {
                        tbody.appendChild(tr);
                    }
                });
                updateHidden();
            }

            function updateHidden() {
                var data = {
                    caption: state.caption,
                    has_header_row: state.has_header_row,
                    has_header_col: state.has_header_col,
                    rows: state.rows
                };
                hiddenInput.value = JSON.stringify(data);
            }

            // Events directly on wrapper for delegation
            wrapper.addEventListener('input', function(e) {
                // Cell Input
                if(e.target.matches('input[data-row]')) {
                    var r = e.target.dataset.row;
                    var c = e.target.dataset.col;
                    state.rows[r][c] = e.target.value;
                    updateHidden();
                }
            });

            // Config Checkboxes & Caption
            wrapper.addEventListener('change', function(e) {
                if(e.target.classList.contains('fields-table-config')) {
                    state[e.target.dataset.config] = e.target.checked;
                    render();
                }
                if(e.target.classList.contains('fields-table-caption')) {
                    state.caption = e.target.value;
                    updateHidden();
                }
            });

            // Add Row
            var addRowBtn = wrapper.querySelector('.fields-table-add-row');
            if(addRowBtn) {
                addRowBtn.addEventListener('click', function() {
                    var cols = state.rows[0] ? state.rows[0].length : 1;
                    state.rows.push(new Array(cols).fill(''));
                    render();
                });
            }

            // Add Col
            var addColBtn = wrapper.querySelector('.fields-table-add-col');
            if(addColBtn) {
                addColBtn.addEventListener('click', function() {
                    state.rows.forEach(function(r) { r.push(''); });
                    render();
                });
            }

            // Delete Row/Col (Event Delegation)
            wrapper.addEventListener('click', function(e) {
                if(e.target.classList.contains('fields-table-del-row')) {
                    var r = parseInt(e.target.dataset.row);
                    state.rows.splice(r, 1);
                    render();
                }
                if(e.target.classList.contains('fields-table-del-col')) {
                    var c = parseInt(e.target.dataset.col);
                    state.rows.forEach(function(row) { row.splice(c, 1); });
                    render();
                }
            });

            // Initial Render
            render();
        });
    }

    // Init on Load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTableEditor);
    } else {
        initTableEditor();
    }

    // Init on Pjax
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', initTableEditor);
    }

})();
