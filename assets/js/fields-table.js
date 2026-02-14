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
                headerColPolicy: 'user',
                enableMedia: false,
                enableLink: false
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

            // Sync initial enforcement and migrations to Hidden Input
            state.cols.forEach(function(c) {
                if(!c.header_type) c.header_type = c.type || 'text';
            });
            updateHidden();

            function getIconForType(type) {
                if (type === 'number') return 'fa-hashtag';
                if (type === 'center') return 'fa-align-center';
                if (type === 'media') return 'fa-file-o';
                if (type === 'link') return 'rex-icon-open-linkmap';
                if (type === 'textarea') return 'fa-paragraph';
                return 'fa-font';
            }

            function getTextForType(type) {
                if (type === 'number') return 'Zahl';
                if (type === 'center') return 'Zentriert';
                if (type === 'media') return 'Medien';
                if (type === 'link') return 'Link';
                if (type === 'textarea') return 'Mehrzeilig';
                return 'Text';
            }

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
                    
                    var colWrapper = document.createElement('div');
                    colWrapper.style.display = 'flex';
                    colWrapper.style.justifyContent = 'center';
                    colWrapper.style.alignItems = 'center';
                    colWrapper.style.gap = '2px';

                    // --- Header Align Button ---            
                    var headerTypeBtn = document.createElement('button');
                    headerTypeBtn.type = 'button';
                    headerTypeBtn.className = 'btn btn-default btn-xs fields-table-header-type';
                    headerTypeBtn.dataset.col = colIndex;
                    headerTypeBtn.style.opacity = '1';
                    headerTypeBtn.style.backgroundColor = '#dfe9f5'; // Light blue background like headers often have
                    headerTypeBtn.style.color = '#333';
                    
                    var hIcon = 'fa-align-left';
                    var hTitle = 'Kopf: Text (links)';
                    if (col.header_type === 'center') { hIcon = 'fa-align-center'; hTitle = 'Kopf: Zentriert'; }
                    else if (col.header_type === 'number') { hIcon = 'fa-align-right'; hTitle = 'Kopf: Rechts'; }
                    
                    headerTypeBtn.title = hTitle;
                    headerTypeBtn.innerHTML = '<i class="rex-icon ' + hIcon + '"></i>';
                    
                    // --- Body Align Button ---
                    var typeBtn = document.createElement('button');
                    typeBtn.type = 'button';
                    typeBtn.className = 'btn btn-default btn-xs fields-table-col-type';
                    typeBtn.dataset.col = colIndex;
                    
                    var iconClass = getIconForType(col.type);
                    var titleText = getTextForType(col.type);

                    typeBtn.title = titleText;
                    typeBtn.innerHTML = '<i class="rex-icon ' + iconClass + '"></i>';

                    // Insert Col Button (Inline)
                    var addColInlineBtn = null;
                    if (canAddCol) {
                        addColInlineBtn = document.createElement('button');
                        addColInlineBtn.type = 'button';
                        addColInlineBtn.className = 'btn btn-default btn-xs fields-table-add-col-inline';
                        addColInlineBtn.dataset.col = colIndex;
                        addColInlineBtn.title = 'Spalte rechts einfügen';
                        addColInlineBtn.innerHTML = '<i class="rex-icon fa-plus"></i>';
                    }
                    
                    // Delete Col Button
                    var delBtn = null;
                    if (canDelCol) {
                        delBtn = document.createElement('button');
                        delBtn.type = 'button';
                        delBtn.className = 'btn btn-default btn-xs fields-table-del-col';
                        delBtn.dataset.col = colIndex;
                        delBtn.title = 'Spalte löschen';
                        delBtn.style.color = '#d9534f';
                        delBtn.innerHTML = '<i class="rex-icon fa-times"></i>';
                    }

                    colWrapper.appendChild(headerTypeBtn);
                    colWrapper.appendChild(typeBtn);
                    if (addColInlineBtn) colWrapper.appendChild(addColInlineBtn);
                    if (delBtn) colWrapper.appendChild(delBtn);

                    th.appendChild(colWrapper);
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
                        var isBodyCell = !(rowIndex === 0 && state.has_header_row);
                        
                        // Wrapper for Input + Actions
                        var wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'fields-table-cell-wrapper';
                        
                        // Input Field or TextArea
                        var input;
                        if (isBodyCell && colDef.type === 'textarea') {
                            input = document.createElement('textarea');
                            input.className = 'form-control'; // No input-sm for textarea usually
                            input.style.fontSize = '12px'; // Match input-sm roughly
                            input.style.padding = '5px 10px';
                            input.rows = 1; // Start small
                            input.style.resize = 'vertical';
                            input.style.minHeight = '30px';
                            // Auto-resize could be nice but let's stick to simple resize
                        } else {
                            input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'form-control input-sm';
                        }
                        
                        // Align Logic
                        if (rowIndex === 0 && state.has_header_row) {
                             // Header Row: Use Header Type
                             var hType = colDef.header_type || 'text';
                             if (hType === 'center') input.style.textAlign = 'center';
                             else if (hType === 'number' || hType === 'right') input.style.textAlign = 'right';
                             else input.style.textAlign = 'left';
                        } else {
                             // Data Rows (or Header Col): Use Body Type
                             if (colDef.type === 'center') input.style.textAlign = 'center';
                             else if (colDef.type === 'number') input.style.textAlign = 'right';
                             else input.style.textAlign = 'left';
                        }
                        
                        // MEDIA / LINK HANDLER
                        // Wenn wir NICHT im Header sind (Header ist immer Text), und der Typ Media/Link ist
                        
                        if (isBodyCell && (colDef.type === 'media' || colDef.type === 'link')) {
                             input.type = 'hidden'; // Base input is hidden
                             
                             var widgetGroup = document.createElement('div');
                             widgetGroup.className = 'input-group input-group-xs';
                             
                             var displayInput = document.createElement('input');
                             displayInput.type = 'text';
                             displayInput.className = 'form-control';
                             displayInput.readOnly = true;
                             displayInput.value = cell;
                             
                             var btnSpan = document.createElement('span');
                             btnSpan.className = 'input-group-btn';
                             
                             if (colDef.type === 'media') {
                                 // Media Buttons
                                 // Open
                                 var btnOpen = document.createElement('a');
                                 btnOpen.href = '#';
                                 btnOpen.className = 'btn btn-popup';
                                 btnOpen.innerHTML = '<i class="rex-icon rex-icon-open-mediapool"></i>';
                                 btnOpen.title = 'Medienpool öffnen';
                                 btnOpen.onclick = function(e){
                                     e.preventDefault();
                                     // Wir simulieren ein REX_MEDIA Widget
                                     // Dazu nutzen wir den REDAXO Media Pool Popup mit Callback
                                     // Callback muss global sein oder? 
                                     // Besser: Wir nutzen rex.mediapool.open
                                     // Aber wie bekommen wir den Wert zurück?
                                     // REDAXO expects an ID to fill REX_MEDIA_x input.
                                     // We can generate a fake random ID for this cell, put the hidden input there?
                                     
                                     // Problem: REDAXO JS uses global IDs.
                                     // Hack: Create a unique ID for this cell input
                                 };
                                 
                                 // We need to implement a specialized media select without globals if possible,
                                 // OR use standard REDAXO logic by giving a unique ID to input.
                                 // Unique ID: fields_table_MEDIA_row_col_random
                                 var uniqueId = 'FT_MEDIA_' + rowIndex + '_' + colIndex + '_' + Math.floor(Math.random()*10000);
                                 input.id = 'REX_MEDIA_' + uniqueId; // REDAXO Standard ID Pattern
                                 displayInput.id = 'REX_MEDIA_' + uniqueId + '_NAME_MOCKED'; // Just for display? No, REDAXO fills REX_MEDIA_ID
                                 
                                 // Wait, standard REX_MEDIA widget:
                                 // input type=text id=REX_MEDIA_1 readonly value=filename
                                 // buttons call openREXMedia(1)
                                 
                                 // So we just need to assign input.id = REX_MEDIA_{UID}
                                 // And input.type = text (readonly)
                                 input.type = 'text';
                                 input.readOnly = true;
                                 input.id = 'REX_MEDIA_' + uniqueId;
                                 
                                 // Replace displayInput with our real input
                                 displayInput = input; 
                                 
                                 btnOpen.onclick = function(e) {
                                     e.preventDefault();
                                     openREXMedia(uniqueId);
                                     return false;
                                 };

                                 var btnAdd = document.createElement('a');
                                 btnAdd.href = '#';
                                 btnAdd.className = 'btn btn-popup';
                                 btnAdd.innerHTML = '<i class="rex-icon rex-icon-add-media"></i>';
                                 btnAdd.onclick = function(e) { e.preventDefault(); addREXMedia(uniqueId); return false; };

                                 var btnDel = document.createElement('a');
                                 btnDel.href = '#';
                                 btnDel.className = 'btn btn-popup';
                                 btnDel.innerHTML = '<i class="rex-icon rex-icon-delete-media"></i>';
                                 btnDel.onclick = function(e) { e.preventDefault(); deleteREXMedia(uniqueId); return false; };

                                 var btnView = document.createElement('a');
                                 btnView.href = '#';
                                 btnView.className = 'btn btn-popup';
                                 btnView.innerHTML = '<i class="rex-icon rex-icon-view-media"></i>';
                                 btnView.onclick = function(e) { e.preventDefault(); viewREXMedia(uniqueId); return false; };

                                 btnSpan.appendChild(btnOpen);
                                 btnSpan.appendChild(btnAdd);
                                 btnSpan.appendChild(btnDel);
                                 btnSpan.appendChild(btnView);
                             } 
                             else if (colDef.type === 'link') {
                                 // Link Buttons
                                 var uniqueId = 'FT_LINK_' + rowIndex + '_' + colIndex + '_' + Math.floor(Math.random()*10000);
                                 input.type = 'hidden';
                                 input.id = 'LINK_' + uniqueId; // REDAXO Link ID pattern usually uses just ID?
                                 // openLinkMap('LINK_1', '&clang=1&category_id=1');
                                 // input needs to be hidden, and a name input shown
                                 
                                 // For Linkmap, it usually updates input #LINK_{ID} with ID and #LINK_{ID}_NAME with name
                                 displayInput.id = 'LINK_' + uniqueId + '_NAME';
                                 
                                 var btnOpen = document.createElement('a');
                                 btnOpen.href = '#';
                                 btnOpen.className = 'btn btn-popup';
                                 btnOpen.innerHTML = '<i class="rex-icon rex-icon-open-linkmap"></i>';
                                 btnOpen.onclick = function(e) {
                                     e.preventDefault();
                                     openLinkMap('LINK_' + uniqueId, ''); 
                                     return false;
                                 };

                                 var btnDel = document.createElement('a');
                                 btnDel.href = '#';
                                 btnDel.className = 'btn btn-popup';
                                 btnDel.innerHTML = '<i class="rex-icon rex-icon-delete-link"></i>';
                                 btnDel.onclick = function(e) {
                                     e.preventDefault();
                                     deleteREXLink(uniqueId);
                                     return false;
                                 };
                                 
                                 btnSpan.appendChild(btnOpen);
                                 btnSpan.appendChild(btnDel);
                                 
                                 // Link Value synchronizer:
                                 // When popup closes, it updates the DOM inputs. 
                                 // We need to catch that update to update state.rows.
                                 // MutationObserver on input?
                             }

                             widgetGroup.appendChild(displayInput);
                             if (colDef.type === 'link') {
                                 widgetGroup.appendChild(input); // Hidden ID input
                             }
                             widgetGroup.appendChild(btnSpan);
                             
                             // Replace direct input with widget
                             // But we need to sync value changes from REDAXO to state
                             // Standard input events might not trigger when REDAXO JS changes value programmatically?
                             // Yes, we need specific listener/observer or poll.
                             // Let's use $(input).change() since REDAXO usually triggers change? Hopefully.
                             
                             wrapperDiv.appendChild(widgetGroup);
                             
                             // Add Observer because REDAXO legacy JS often doesn't trigger proper events
                             // Observe 'value' attribute or property changes?
                             // REDAXO JS: $(..).val(id);
                        } else {
                             // Standard Text Input
                             wrapperDiv.appendChild(input); 
                        }

                        input.value = cell;
                        input.dataset.row = rowIndex;
                        input.dataset.col = colIndex;
                        
                        // Action buttons logic remains same...
                        // ...
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

                        // wrapperDiv.appendChild(input); // Removed duplicate append
                        
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

            // Mutation Observer to catch REDAXO Media/Link Changes
            // Since REDAXO scripts update the Input Value property, input event might not fire
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                         // This catches attribute changes, but jQuery.val() changes property?
                    }
                });
            });
            // Better: use setInterval check or hijack triggered events.
            // REDAXO standard popups usually don't trigger native 'input'
            // We can attach a 'change' listener and hope native JS or jQuery triggers it.
            // Also RE-attach listeners to dynamic inputs created in render()
            
            // Actually, we can just use $(wrapper).on('change', 'input', ...) via jQuery if available, 
            // as REDAXO uses jQuery trigger('change').
            if (typeof jQuery !== 'undefined') {
                jQuery(wrapper).on('change', 'input[data-row]', function(e) {
                     var r = this.dataset.row;
                     var c = this.dataset.col;
                     state.rows[r][c] = this.value;
                     updateHidden();
                     
                     // If link name field changed, we might want to store display name? 
                     // Currently fields_table just stores one value per cell.
                     // For links: ID. For Media: Filename.
                     // The Link Widget has a separate ID input and Name Input. 
                     // We only bind data-row to the ID input (hidden).
                });
            }


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
                // Toggle Header Type
                var headerTypeBtn = e.target.closest('.fields-table-header-type');
                if(headerTypeBtn) {
                    var c = parseInt(headerTypeBtn.dataset.col);
                    var currentType = state.cols[c].header_type || 'text';
                    
                    if (currentType === 'text') state.cols[c].header_type = 'center';
                    else if (currentType === 'center') state.cols[c].header_type = 'number';
                    else state.cols[c].header_type = 'text';

                    render();
                    return;
                }


                // Toggle Body Type (Config Button in Header)
                if(e.target.closest('.fields-table-col-type')) {
                    var btn = e.target.closest('.fields-table-col-type');
                    var c = parseInt(btn.dataset.col);
                    
                    // Build active types list based on config and state
                    var types = ['text', 'number', 'center'];
                    if (config.enableMedia) types.push('media');
                    if (config.enableLink) types.push('link');
                    
                    var current = state.cols[c].type || 'text';
                    var idx = types.indexOf(current);
                    if (idx === -1) idx = 0; // Fallback
                    
                    var next = types[(idx + 1) % types.length];
                    state.cols[c].type = next;

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
                    // New column inherits empty or default header type? Default text.
                    state.cols[c+1].header_type = 'text';
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
