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
            var parsedData = JSON.parse(dataScript ? dataScript.innerHTML : '{}');
            
            var state = {
                rows: parsedData.rows || [],
                cols: parsedData.cols || [],
                caption: captionInput ? captionInput.value : '',
                has_header_row: wrapper.querySelector('[data-config="has_header_row"]').checked,
                has_header_col: wrapper.querySelector('[data-config="has_header_col"]').checked
            };

            // Ensure at least one cell if empty & Init Cols
            if (state.rows.length === 0) {
                state.rows = [['']];
            }
            if (!state.cols || state.cols.length !== state.rows[0].length) {
                state.cols = new Array(state.rows[0].length).fill({type: 'text'});
            }

            function render() {
                var thead = table.querySelector('thead');
                var tbody = table.querySelector('tbody');
                thead.innerHTML = '';
                tbody.innerHTML = '';

                // 1. Column Controls Row (Meta-Header)
                var configRow = document.createElement('tr');
                configRow.className = 'fields-table-config-row';
                state.cols.forEach(function(col, colIndex) {
                    var th = document.createElement('th');
                    th.className = 'text-center';
                    th.style.background = '#f9f9f9';
                    th.style.borderBottom = '1px solid #ddd';
                    th.style.padding = '5px';
                    
                    // Column Type Toggle (Text/Number)
                    var typeBtn = document.createElement('button');
                    typeBtn.type = 'button';
                    typeBtn.className = 'btn btn-default btn-xs fields-table-col-type';
                    typeBtn.dataset.col = colIndex;
                    typeBtn.title = col.type === 'number' ? 'Zahlenformat (rechtsbündig)' : 'Textformat (linksbündig)';
                    typeBtn.innerHTML = col.type === 'number' ? '<i class="rex-icon fa-sort-numeric-asc"></i>' : '<i class="rex-icon fa-align-left"></i>';
                    
                    // Delete Col Button
                    var delBtn = '';
                    if (state.rows[0].length > 1) {
                        delBtn = '<button type="button" class="btn btn-default btn-xs fields-table-del-col" data-col="' + colIndex + '" title="Spalte löschen" style="margin-left:5px; color:#d9534f;"><i class="rex-icon fa-times"></i></button>';
                    }

                    th.appendChild(typeBtn);
                    if (delBtn) {
                        var span = document.createElement('span');
                        span.innerHTML = delBtn;
                        th.appendChild(span.firstChild);
                    }
                    configRow.appendChild(th);
                });
                thead.appendChild(configRow);

                state.rows.forEach(function (row, rowIndex) {
                    var tr = document.createElement('tr');
                    
                    row.forEach(function (cell, colIndex) {
                        var isHeader = (rowIndex === 0 && state.has_header_row) || (colIndex === 0 && state.has_header_col);
                        var cellType = isHeader ? 'th' : 'td';
                        var cellEl = document.createElement(cellType);
                        var colDef = state.cols[colIndex] || {type: 'text'};
                        
                        // Wrapper for Input + Actions
                        var wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'fields-table-cell-wrapper';
                        
                        // Input Field
                        var input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'form-control input-sm';
                        input.style.textAlign = colDef.type === 'number' ? 'right' : 'left';
                        input.value = cell;
                        input.dataset.row = rowIndex;
                        input.dataset.col = colIndex;
                        
                        // Actions (Del Row only)
                        var actionsHtml = '';
                        if (colIndex === row.length - 1 && state.rows.length > 1) {
                             actionsHtml += '<button type="button" class="btn btn-default btn-xs fields-table-del-row" data-row="' + rowIndex + '" title="Zeile löschen" tabindex="-1"><i class="rex-icon fa-times"></i></button>';
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

                    // Add to appropriate section. If has_header_row, first data row goes to thead too? 
                    // No, let's keep data rows in tbody for cleaner editing structure, 
                    // visually distinguish header row by styling.
                    if (rowIndex === 0 && state.has_header_row) {
                        tr.classList.add('info'); // Bootstrap info class for headers
                    }
                    tbody.appendChild(tr);
                });
                updateHidden();
            }

            function updateHidden() {
                var data = {
                    caption: state.caption,
                    has_header_row: state.has_header_row,
                    has_header_col: state.has_header_col,
                    rows: state.rows,
                    cols: state.cols
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
                    state.cols.push({type: 'text'});
                    state.rows.forEach(function(r) { r.push(''); });
                    render();
                });
            }

            // Buttons (Del Row/Col, Toggle Type)
            wrapper.addEventListener('click', function(e) {
                // Toggle Type
                var typeBtn = e.target.closest('.fields-table-col-type');
                if(typeBtn) {
                    var c = parseInt(typeBtn.dataset.col);
                    state.cols[c].type = state.cols[c].type === 'text' ? 'number' : 'text';
                    render();
                    return;
                }

                // Del Row
                var delRowBtn = e.target.closest('.fields-table-del-row');
                if(delRowBtn) {
                    var r = parseInt(delRowBtn.dataset.row);
                    state.rows.splice(r, 1);
                    render();
                    return;
                }

                // Del Col
                var delColBtn = e.target.closest('.fields-table-del-col');
                if(delColBtn) {
                    var c = parseInt(delColBtn.dataset.col);
                    state.cols.splice(c, 1);
                    state.rows.forEach(function(row) { row.splice(c, 1); });
                    render();
                    return;
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
