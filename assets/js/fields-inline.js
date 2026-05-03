(function() {
    'use strict';

    function hasJQuery() {
        return typeof window.jQuery !== 'undefined';
    }

    function initSelectPickers(scope) {
        if (!hasJQuery()) {
            return;
        }

        var $ = window.jQuery;
        var $scope = scope ? $(scope) : $(document);
        var $selects = $scope.find('.js-fields-inline-select.selectpicker:not(.js-fields-inline-select-list)');

        if (!$selects.length || typeof $selects.selectpicker !== 'function') {
            return;
        }

        $selects.each(function() {
            var $select = $(this);
            if ($select.data('fieldsPickerInit')) {
                try {
                    $select.selectpicker('refresh');
                } catch (e) {}
                return;
            }

            $select.data('fieldsPickerInit', true);
            try {
                $select.selectpicker();
            } catch (e) {}
        });
    }

    function postInlineUpdate(payload) {
        var url = 'index.php?rex-api-call=fields_inline_update';
        var data = new FormData();
        data.append('table', payload.table);
        data.append('field', payload.field);
        data.append('id', payload.id);
        data.append('value', payload.value);
        data.append('_csrf_token', payload.token);

        return fetch(url, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function(response) {
            return response.json();
        });
    }

    function stopEditing(el) {
        var view = el.querySelector('.fields-inline-view');
        var inputWrapper = el.querySelector('.fields-inline-input');

        el.classList.remove('editing');
        if (view) {
            view.style.display = 'block';
        }
        if (inputWrapper) {
            inputWrapper.style.display = 'none';
            inputWrapper.innerHTML = '';
        }
    }

    function saveTextValue(el, newValue) {
        var table = el.dataset.table;
        var field = el.dataset.field;
        var id = el.dataset.id;
        var token = el.dataset.token;

        if (!table || !field || !id || !token) {
            return;
        }

        el.classList.add('saving');

        postInlineUpdate({
            table: table,
            field: field,
            id: id,
            value: newValue,
            token: token
        })
        .then(function(json) {
            el.classList.remove('saving');

            if (!json.success) {
                alert('Fehler beim Speichern: ' + (json.message || 'Unbekannter Fehler'));
                return;
            }

            var view = el.querySelector('.fields-inline-view');
            var displayValue = json.formatted !== undefined ? json.formatted : newValue;

            if (view) {
                if (String(newValue).trim() === '') {
                    view.innerHTML = '&nbsp;<i class="rex-icon fa-pencil" style="opacity:0.3"></i>&nbsp;';
                } else {
                    view.textContent = displayValue;
                }
            }

            el.dataset.rawValue = newValue;
            stopEditing(el);

            el.classList.add('success-flash');
            window.setTimeout(function() {
                el.classList.remove('success-flash');
            }, 1000);
        })
        .catch(function(err) {
            el.classList.remove('saving');
            console.error(err);
            alert('Netzwerkfehler beim Speichern.');
        });
    }

    function startEditing(el) {
        var view = el.querySelector('.fields-inline-view');
        var inputWrapper = el.querySelector('.fields-inline-input');

        if (!view || !inputWrapper || el.classList.contains('editing')) {
            return;
        }

        var originalValue = el.dataset.rawValue || '';
        var type = el.dataset.type || 'text';
        var input;

        if (type === 'textarea') {
            input = document.createElement('textarea');
            input.rows = 3;
            input.className = 'form-control';
        } else {
            input = document.createElement('input');
            input.type = ['number', 'email', 'date', 'datetime-local'].indexOf(type) !== -1 ? type : 'text';
            input.className = 'form-control input-sm';
        }

        input.value = originalValue;

        var group = document.createElement('div');
        group.className = 'input-group input-group-sm fields-inline-group';

        var buttonSpan = document.createElement('span');
        buttonSpan.className = 'input-group-btn';

        var saveBtn = document.createElement('button');
        saveBtn.className = 'btn btn-primary btn-sm';
        saveBtn.type = 'button';
        saveBtn.innerHTML = '<i class="rex-icon fa-check"></i>';
        saveBtn.title = 'Speichern';

        var cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-default btn-sm';
        cancelBtn.type = 'button';
        cancelBtn.innerHTML = '<i class="rex-icon fa-times"></i>';
        cancelBtn.title = 'Abbrechen';

        buttonSpan.appendChild(saveBtn);
        buttonSpan.appendChild(cancelBtn);
        group.appendChild(input);
        group.appendChild(buttonSpan);

        inputWrapper.innerHTML = '';
        inputWrapper.appendChild(group);

        el.classList.add('editing');
        view.style.display = 'none';
        inputWrapper.style.display = 'block';

        input.focus();

        cancelBtn.addEventListener('click', function() {
            stopEditing(el);
        });

        saveBtn.addEventListener('click', function() {
            saveTextValue(el, input.value);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                stopEditing(el);
            }
            if (e.key === 'Enter' && type !== 'textarea' && !e.shiftKey) {
                e.preventDefault();
                saveTextValue(el, input.value);
            }
        });
    }

    function toggleSwitch(el) {
        var currentValue = parseInt(el.dataset.value || '0', 10);
        var newValue = currentValue === 1 ? 0 : 1;

        var table = el.dataset.table;
        var field = el.dataset.field;
        var id = el.dataset.id;
        var token = el.dataset.token;

        if (!table || !field || !id || !token) {
            return;
        }

        el.classList.add('loading');
        el.classList.toggle('fields-switch-active', newValue === 1);

        postInlineUpdate({
            table: table,
            field: field,
            id: id,
            value: String(newValue),
            token: token
        })
        .then(function(json) {
            el.classList.remove('loading');

            if (!json.success) {
                el.classList.toggle('fields-switch-active', currentValue === 1);
                alert('Fehler: ' + (json.message || 'Speichern fehlgeschlagen'));
                return;
            }

            el.dataset.value = String(newValue);
        })
        .catch(function(err) {
            el.classList.remove('loading');
            el.classList.toggle('fields-switch-active', currentValue === 1);
            console.error(err);
            alert('Netzwerkfehler');
        });
    }

    function saveListSelectValue(selectEl) {
        var currentValue = String(selectEl.dataset.previousValue || selectEl.value);
        var newValue = String(selectEl.value);

        var table = selectEl.dataset.table;
        var field = selectEl.dataset.field;
        var id = selectEl.dataset.id;
        var token = selectEl.dataset.token;

        if (!table || !field || !id || !token) {
            return;
        }

        if (selectEl.dataset.loading === '1') {
            return;
        }

        var cell = selectEl.closest('.fields-inline-select-cell');
        var display = cell ? cell.querySelector('.fields-inline-select-display') : null;

        // Sofortiges visuelles Feedback
        if (display) {
            display.style.opacity = '0.5';
        }
        selectEl.classList.add('fields-inline-select-loading');
        selectEl.dataset.loading = '1';

        postInlineUpdate({
            table: table,
            field: field,
            id: id,
            value: newValue,
            token: token
        })
        .then(function(json) {
            delete selectEl.dataset.loading;
            selectEl.classList.remove('fields-inline-select-loading');

            if (!json.success) {
                // Wert zurücksetzen
                selectEl.value = currentValue;
                if (display) {
                    display.style.opacity = '';
                }
                alert('Fehler: ' + (json.message || 'Speichern fehlgeschlagen'));
                return;
            }

            selectEl.dataset.previousValue = newValue;

            // Badge aktualisieren
            if (cell && display) {
                var colors = {};
                var labels = {};
                try { colors = JSON.parse(cell.dataset.colors || '{}'); } catch (e) {}
                try { labels = JSON.parse(cell.dataset.labels || '{}'); } catch (e) {}

                var newColor = colors[newValue] || '';
                var newLabel = labels[newValue] || newValue;

                var dot = display.querySelector('.fields-inline-color-dot');
                var labelEl = display.querySelector('.fields-inline-select-label');

                if (dot) {
                    dot.style.background = newColor;
                    dot.style.display = newColor ? '' : 'none';
                }
                if (labelEl) {
                    labelEl.textContent = newLabel;
                }

                display.style.opacity = '';
                display.classList.add('fields-inline-select-saved');
                window.setTimeout(function() {
                    display.classList.remove('fields-inline-select-saved');
                }, 1200);
            }
        })
        .catch(function(err) {
            delete selectEl.dataset.loading;
            selectEl.classList.remove('fields-inline-select-loading');
            selectEl.value = currentValue;
            if (display) {
                display.style.opacity = '';
            }
            console.error(err);
            alert('Netzwerkfehler beim Speichern.');
        });
    }

    function bindEvents() {
        if (!hasJQuery()) {
            document.addEventListener('click', function(e) {
                var view = e.target.closest('.fields-inline-view');
                if (view) {
                    var wrapper = view.closest('.fields-inline-edit');
                    if (wrapper) {
                        startEditing(wrapper);
                    }
                }
            });
            return;
        }

        var $ = window.jQuery;

        $(document).on('click', '.fields-inline-view', function() {
            var wrapper = this.closest('.fields-inline-edit');
            if (wrapper) {
                startEditing(wrapper);
            }
        });

        $(document).on('click', '.fields-inline-switch:not(.fields-form-switch)', function(e) {
            e.preventDefault();
            if (!this.dataset.token || this.classList.contains('loading')) {
                return;
            }
            toggleSwitch(this);
        });

        $(document).on('change', '.js-fields-inline-select-list', function() {
            saveListSelectValue(this);
        });
    }

    function init() {
        bindEvents();
        initSelectPickers(document);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
