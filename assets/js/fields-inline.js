(function() {
    'use strict';

    /**
     * Fields Inline Edit Logic
     * Handles clicking on list items to edit them in place.
     */

    function initFieldsInline(scope) {
        scope = scope || document;
        
        // 1. Text/Textarea Fields
        var editables = scope.querySelectorAll('.fields-inline-edit:not(.js-processed)');
        
        if (editables.length > 0) {
            Array.prototype.forEach.call(editables, function(el) {
                el.classList.add('js-processed');
                
                // On Click View -> Show Input
                var view = el.querySelector('.fields-inline-view');
                
                view.addEventListener('click', function(e) {
                    if (el.classList.contains('editing')) return;
                    startEditing(el);
                });
            });
        }
        
        // 2. Switch Fields (Checkbox)
        var switches = scope.querySelectorAll('.fields-inline-switch:not(.js-processed)');
        
        if (switches.length > 0) {
            Array.prototype.forEach.call(switches, function(el) {
                el.classList.add('js-processed');
                
                el.addEventListener('click', function(e) {
                     e.preventDefault();
                     if (el.classList.contains('loading')) return;
                     toggleSwitch(el);
                });
            });
        }
    }

    function toggleSwitch(el) {
        // Get current value (0 or 1)
        var currentValue = parseInt(el.dataset.value, 10);
        var newValue = (currentValue === 1) ? 0 : 1;
        
        // Optimistic UI Update
        el.classList.add('loading');
        
        if (newValue === 1) {
            el.classList.add('fields-switch-active');
        } else {
            el.classList.remove('fields-switch-active');
        }
        
        // Collect Data
        var table = el.dataset.table;
        var field = el.dataset.field;
        var id = el.dataset.id;
        var token = el.dataset.token;

        // Perform AJAX
        var url = 'index.php?rex-api-call=fields_inline_update';
        var data = new FormData();
        data.append('table', table);
        data.append('field', field);
        data.append('id', id);
        data.append('value', newValue);
        data.append('_csrf_token', token);
        
        fetch(url, {
             method: 'POST',
             body: data,
             headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(function(res) { return res.json(); })
        .then(function(json) {
             el.classList.remove('loading');
             if (json.success) {
                  // Confirm State
                  el.dataset.value = newValue;
                  
                  // Flash visual confirmation if needed, but the switch state is enough
             } else {
                  // Revert UI on Error
                  alert('Fehler: ' + (json.message || 'Speichern fehlgeschlagen'));
                  if (currentValue === 1) {
                      el.classList.add('fields-switch-active');
                  } else {
                      el.classList.remove('fields-switch-active');
                  }
             }
        })
        .catch(function(err) {
             el.classList.remove('loading');
             console.error(err);
             alert('Netzwerkfehler');
             // Revert
             if (currentValue === 1) {
                 el.classList.add('fields-switch-active');
             } else {
                 el.classList.remove('fields-switch-active');
             }
        });
    }

    function startEditing(el) {
        var view = el.querySelector('.fields-inline-view');
        var inputWrapper = el.querySelector('.fields-inline-input');
        
        // Get raw value: Prefer data attribute if available, else text content
        // Store original value
        var originalValue = el.dataset.rawValue || view.textContent.trim();
        if (originalValue === '') originalValue = ''; // empty placeholder handled by CSS/PHP?
        
        // Create Input Element based on type
        var type = el.dataset.type; // text, textarea
        var input;
        
        if (type === 'textarea') {
            input = document.createElement('textarea');
            input.rows = 3;
            input.className = 'form-control';
        } else {
            input = document.createElement('input');
            // Allow common types, default to text
            if (['number', 'email', 'date', 'datetime-local'].indexOf(type) !== -1) {
                input.type = type;
            } else {
                input.type = 'text';
            }
            input.className = 'form-control input-sm'; // small input for lists
        }
        
        input.value = originalValue;
        
        // Buttons
        var btnGroup = document.createElement('div');
        btnGroup.className = 'input-group-btn';
        
        var saveBtn = document.createElement('button');
        saveBtn.className = 'btn btn-primary btn-sm';
        saveBtn.innerHTML = '<i class="rex-icon fa-check"></i>';
        saveBtn.title = 'Speichern';
        
        var cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-default btn-sm';
        cancelBtn.innerHTML = '<i class="rex-icon fa-times"></i>';
        cancelBtn.title = 'Abbrechen';
        
        // Wrapper for Input + Buttons (Input Group)
        var group = document.createElement('div');
        group.className = 'input-group input-group-sm fields-inline-group';
        
        // Bootstrap 3 Input Group structure
        // <div class="input-group">
        //   <input ...>
        //   <span class="input-group-btn">...</span>
        // </div>
        
        group.appendChild(input);
        
        var span = document.createElement('span');
        span.className = 'input-group-btn';
        span.appendChild(saveBtn);
        span.appendChild(cancelBtn);
        group.appendChild(span);
        
        inputWrapper.innerHTML = '';
        inputWrapper.appendChild(group);
        
        // Switch View
        el.classList.add('editing');
        view.style.display = 'none';
        inputWrapper.style.display = 'block';
        
        // Focus
        input.focus();
        
        // Event Handlers
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            stopEditing(el, false);
        });
        
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            saveValue(el, input.value);
        });
        
        // Key handling (Enter to save in input, Esc to cancel)
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                stopEditing(el, false);
            }
            if (e.key === 'Enter' && type !== 'textarea' && !e.shiftKey) {
                // Save on Enter for text inputs
                e.preventDefault();
                saveValue(el, input.value);
            }
        });
    }

    function stopEditing(el, saved) {
        var view = el.querySelector('.fields-inline-view');
        var inputWrapper = el.querySelector('.fields-inline-input');
        
        el.classList.remove('editing');
        inputWrapper.style.display = 'none';
        view.style.display = 'block';
        
        // Clear input to save memory?
        inputWrapper.innerHTML = '';
    }

    function saveValue(el, newValue) {
        var table = el.dataset.table;
        var field = el.dataset.field;
        var id = el.dataset.id;
        var token = el.dataset.token;
        
        // Optimistic UI? Or wait for response?
        // Wait for response to handle errors properly.
        // Show loading state?
        el.classList.add('saving');
        
        // API Call
        var url = 'index.php?rex-api-call=fields_inline_update';
        // Use Fetch or jQuery? Fetch is modern, but let's be safe.
        // Needs Polyfill for older browsers? REDAXO 5 usually recent enough.
        
        // Prepare FormData
        var data = new FormData();
        data.append('table', table);
        data.append('field', field);
        data.append('id', id);
        data.append('value', newValue);
        data.append('_csrf_token', token); // REDAXO CSRF if needed by API
        
        fetch(url, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(json) {
            el.classList.remove('saving');
            
            if (json.success) {
                // Update View
                var view = el.querySelector('.fields-inline-view');
                
                // Get Prefix/Suffix
                var prefix = el.dataset.prefix;
                var suffix = el.dataset.suffix;
                
                // Use formatted value if available, otherwise raw input
                var displayValue = json.formatted !== undefined ? json.formatted : newValue;

                if (newValue.trim() === '') {
                    view.innerHTML = '&nbsp;<i class="rex-icon fa-pencil" style="opacity:0.3"></i>&nbsp;';
                } else {
                    view.innerHTML = '';
                    if (prefix) {
                        var pSpan = document.createElement('span');
                        pSpan.className = 'fields-inline-prefix';
                        pSpan.textContent = prefix;
                        view.appendChild(pSpan);
                        view.appendChild(document.createTextNode(' '));
                    }
                    
                    view.appendChild(document.createTextNode(displayValue));
                    
                    if (suffix) {
                        view.appendChild(document.createTextNode(' '));
                        var sSpan = document.createElement('span');
                        sSpan.className = 'fields-inline-suffix';
                        sSpan.textContent = suffix;
                        view.appendChild(sSpan);
                    }
                }
                
                // Update dataset raw value
                el.dataset.rawValue = newValue;
                
                stopEditing(el, true);
                
                // Success Flash
                el.classList.add('success-flash');
                setTimeout(function(){ el.classList.remove('success-flash'); }, 1000);
            } else {
                alert('Fahler beim Speichern: ' + (json.message || 'Unbekannter Fehler'));
            }
        })
        .catch(function(err) {
            el.classList.remove('saving');
            console.error(err);
            alert('Netzwerkfehler beim Speichern.');
        });
    }

    // Init Logic with Event Delegation
    var init = function() {
        // Use jQuery for robust event delegation in REDAXO backend
        if (window.jQuery) {
            var $ = window.jQuery;
            
            // Text/Textarea Edit Click
            $(document).on('click', '.fields-inline-view', function(e) {
                var wrapper = $(this).closest('.fields-inline-edit');
                if (wrapper.length && !wrapper.hasClass('editing')) {
                    startEditing(wrapper[0]);
                }
            });

            // Switch Toggle Click
            $(document).on('click', '.fields-inline-switch:not(.fields-form-switch)', function(e) {
                e.preventDefault();
                var wrapper = $(this);
                // Check if it has required data attributes for inline edit
                // The form version (fields-form-switch) does not have data-token
                if (!wrapper.data('token')) {
                    // This is likely a form switch or misconfigured
                    return;
                }
                
                if (!wrapper.hasClass('loading')) {
                    toggleSwitch(wrapper[0]);
                }
            });
            
            console.log('Fields Inline: Event Delegation initialized');
        } else {
            // Fallback for non-jQuery environments (rare in REDAXO)
            document.addEventListener('DOMContentLoaded', function() {
                initFieldsInline(document);
            });
        }
    };
    
    init();

})();
