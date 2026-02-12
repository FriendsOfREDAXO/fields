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
            
            var config = parsedData.config || {
                minCols: 1, maxCols: 999,
                minRows: 1, maxRows: 999,
                headerRowPolicy: 'user',
                headerColPolicy: 'user'
            };

            function getBoolConfig(name) {
                var el = wrapper.querySelector('[data-config="' + name + '"]');
                if(!el) return false;
                return el.type === 'checkbox' ? el.checked : (el.value === '1');
            }
            
            var state = {
                rows: parsedData.rows || [],
                cols: parsedData.cols || [],
                caption: captionInput ? captionInput.value : '',
                has_header_row: getBoolConfig('has_header_row'),
                has_header_col: getBoolConfig('has_header_col')
            };

            // Enforce Min Constraints immediately
            while (state.rows.length < config.minRows) {
                state.rows.push(new Array(state.cols ? state.cols.length : 1).fill(''));
            }
            if (state.rows.length === 0) state.rows = [['']]; // Absolute fallback

            if (!state.cols || state.cols.length !== state.rows[0].length) {
                state.cols = new Array(state.rows[0].length).fill({type: 'text'});
            }
            while (state.cols.length < config.minCols) {
                state.cols.push({type: 'text'});
                state.rows.forEach(function(r) { r.push(''); });
            }

            // Sync initial enforcement to Hidden Input
            updateHidden();

            function render() {
                var thead = table.querySelector('thead');
                var tbody = table.querySelector('tbody');
                thead.innerHTML = '';
                tbody.innerHTML = '';
                
                // Permission Flags
                var canAddCol = state.cols.length < config.maxCols;
                var canDelCol = state.cols.length > config.minCols;
                var canAddRow = state.rows.length < config.maxRows;
                var canDelRow = state.rows.length > config.minRows;

                // Toggle Main Buttons
                var mainAddRowBtn = wrapper.querySelector('.fields-table-add-row');
                var mainAddColBtn = wrapper.querySelector('.fields-table-add-col');
                if(mainAddRowBtn) mainAddRowBtn.style.display = canAddRow ? '' : 'none';
                if(mainAddColBtn) mainAddColBtn.style.display = canAddCol ? '' : 'none';

                // 1. Column Controls Row (Meta-Header)
                var configRow = document.createElement('tr');
                configRow.className = 'fields-table-config-row';
                state.cols.forEach(function(col, colIndex) {
                    var th = document.createElement('th');
                    th.className = 'text-center';
                    th.style.padding = '5px';
                    
                    // Column Type Toggle (Text/Center/Number)
                    var typeBtn = document.createElement('button');
                    typeBtn.type = 'button';
                    typeBtn.className = 'btn btn-default btn-xs fields-table-col-type';
                    typeBtn.dataset.col = colIndex;
                    
                    var iconClass = 'fa-align-left';
                    var titleText = 'Text (links)';
                    if (col.type === 'center') { iconClass = 'fa-align-center'; titleText = 'Zentriert'; }
                    else if (col.type === 'number') { iconClass = 'fa-sort-numeric-asc'; titleText = 'Zahl (rechts)'; }

                    typeBtn.title = titleText;
                    typeBtn.innerHTML = '<i class="rex-icon ' + iconClass + '"></i>';

                    // Insert Col Button (Inline)
                    var addColInlineBtn = '';
                    if (canAddCol) {
                        addColInlineBtn = document.createElement('button');
                        addColInlineBtn.type = 'button';
                        addColInlineBtn.className = 'btn btn-default btn-xs fields-table-add-col-inline';
                        addColInlineBtn.dataset.col = colIndex;
                        addColInlineBtn.title = 'Spalte rechts einfügen';
                        addColInlineBtn.style.marginLeft = '2px';
                        addColInlineBtn.innerHTML = '<i class="rex-icon fa-plus"></i>';
                    }
                    
                    // Delete Col Button
                    var delBtn = '';
                    if (canDelCol) {
                        delBtn = '<button type="button" class="btn btn-default btn-xs fields-table-del-col" data-col="' + colIndex + '" title="Spalte löschen" style="margin-left:5px; color:#d9534f;"><i class="rex-icon fa-times"></i></button>';
                    }

                    th.appendChild(typeBtn);
                    if (addColInlineBtn) th.appendChild(addColInlineBtn);
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
                        if (isHeader) {
                            cellEl.classList.add('fields-is-header');
                        }
                        var colDef = state.cols[colIndex] || {type: 'text'};
                        
                        // Wrapper for Input + Actions
                        var wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'fields-table-cell-wrapper';
                        
                        // Input Field
                        var input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'form-control input-sm';
                        
                        if (colDef.type === 'center') input.style.textAlign = 'center';
                        else if (colDef.type === 'number') input.style.textAlign = 'right';
                        else input.style.textAlign = 'left';

                        input.value = cell;
                        input.dataset.row = rowIndex;
                        input.dataset.col = colIndex;
                        
                        // Actions (Add Row & Del Row)
                        var actionsHtml = '';
                        if (colIndex === row.length - 1) {
                             // Add Row Inline (Insert after)
                             if (canAddRow) {
                                actionsHtml += '<button type="button" class="btn btn-default btn-xs fields-table-add-row-inline" data-row="' + rowIndex + '" title="Zeile darunter einfügen" tabindex="-1" style="margin-right:2px;"><i class="rex-icon fa-plus"></i></button>';
                             }

                             if (canDelRow) {
                                 actionsHtml += '<button type="button" class="btn btn-default btn-xs fields-table-del-row" data-row="' + rowIndex + '" title="Zeile löschen" tabindex="-1"><i class="rex-icon fa-times"></i></button>';
                             }
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
                    // Prevent change if policy is strictly enforced (though UI should hide it)
                    var key = e.target.dataset.config;
                    if (key === 'has_header_row' && config.headerRowPolicy !== 'user') return;
                    if (key === 'has_header_col' && config.headerColPolicy !== 'user') return;

                    state[key] = e.target.checked;
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
                    if (state.rows.length >= config.maxRows) return;
                    var cols = state.rows[0] ? state.rows[0].length : 1;
                    state.rows.push(new Array(cols).fill(''));
                    render();
                });
            }

            // Add Col
            var addColBtn = wrapper.querySelector('.fields-table-add-col');
            if(addColBtn) {
                addColBtn.addEventListener('click', function() {
                    if (state.cols.length >= config.maxCols) return;
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
                    var currentType = state.cols[c].type || 'text';
                    
                    if (currentType === 'text') state.cols[c].type = 'center';
                    else if (currentType === 'center') state.cols[c].type = 'number';
                    else state.cols[c].type = 'text';

                    render();
                    return;
                }

                // Add Col Inline
                var addColInlineBtn = e.target.closest('.fields-table-add-col-inline');
                if(addColInlineBtn) {
                    if (state.cols.length >= config.maxCols) return;
                    var c = parseInt(addColInlineBtn.dataset.col);
                    // Insert after current col
                    state.cols.splice(c + 1, 0, {type: 'text'});
                    state.rows.forEach(function(r) { r.splice(c + 1, 0, ''); });
                    render();
                    return;
                }

                // Add Row Inline
                var addRowInlineBtn = e.target.closest('.fields-table-add-row-inline');
                if(addRowInlineBtn) {
                    if (state.rows.length >= config.maxRows) return;
                    var r = parseInt(addRowInlineBtn.dataset.row);
                    var cols = state.cols.length;
                    // Insert after current row
                    state.rows.splice(r + 1, 0, new Array(cols).fill(''));
                    render();
                    return;
                }

                // Del Row
                var delRowBtn = e.target.closest('.fields-table-del-row');
                if(delRowBtn) {
                    if (state.rows.length <= config.minRows) return;
                    var r = parseInt(delRowBtn.dataset.row);
                    state.rows.splice(r, 1);
                    render();
                    return;
                }

                // Del Col
                var delColBtn = e.target.closest('.fields-table-del-col');
                if(delColBtn) {
                    if (state.cols.length <= config.minCols) return;
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
