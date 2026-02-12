/**
 * Fields Addon - Backend JavaScript
 *
 * Handles all repeater fields, IBAN validation, conditional fields,
 * icon picker, QR code preview, and opening hours widget.
 */
(function () {
    'use strict';

    // ============================================================
    // Init & Global Event Management
    // ============================================================

    var _eventsRegistered = false;

    function registerGlobalEvents() {
        if (_eventsRegistered) return;
        _eventsRegistered = true;

        registerRepeaterEvents();
        registerContactMediaEvents();
        registerOpeningHoursEvents();
        registerIbanEvents();
        registerIconPickerEvents();
        registerRepeaterSocialEvents();
    }

    function init() {
        registerGlobalEvents();
        initRepeaterState();
        initConditionalFields();
    }

    // ============================================================
    // Generic Repeater Logic
    // ============================================================

    function registerRepeaterEvents() {
        // Add entry
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.fields-repeater-add');
            if (!btn) return;

            var wrapper = btn.closest('[data-field-name]');
            if (!wrapper) return;

            var entries = wrapper.querySelector('.fields-repeater-entries');
            var lastEntry = entries.querySelector('.fields-repeater-entry:last-child');
            if (!lastEntry) return;

            var clone = lastEntry.cloneNode(true);
            var newIndex = entries.querySelectorAll('.fields-repeater-entry').length;
            clone.dataset.index = newIndex;

            // Clear values
            clone.querySelectorAll('input, textarea, select').forEach(function (input) {
                if (input.type === 'hidden') {
                    if (input.classList.contains('fields-crop-data')) input.value = '{}';
                    return;
                }
                if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                    input.checked = false;
                }
            });

            // Clear previews
            clone.querySelectorAll('.fields-media-preview').forEach(function(el) { el.innerHTML = ''; });
            // Remove select2 artifacts
            clone.querySelectorAll('.select2-container').forEach(function(el) { el.remove(); });
            clone.querySelectorAll('.select2-hidden-accessible').forEach(function(el) {
                el.classList.remove('select2-hidden-accessible');
                el.removeAttribute('data-select2-id');
                el.removeAttribute('aria-hidden');
                el.removeAttribute('tabindex');
                el.style.display = '';
            });

            // Update heading number
            var heading = clone.querySelector('.fields-repeater-heading strong, .panel-heading strong');
            if (heading) {
                heading.textContent = '#' + (newIndex + 1);
            }

            entries.appendChild(clone);
            updateHiddenValue(wrapper);
        });

        // Remove entry
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.fields-repeater-remove');
            if (!btn) return;

            var entry = btn.closest('.fields-repeater-entry');
            var wrapper = btn.closest('[data-field-name]');
            if (!entry || !wrapper) return;

            var entries = wrapper.querySelectorAll('.fields-repeater-entry');
            if (entries.length <= 1) {
                // Clear instead of remove if last one
                entry.querySelectorAll('input, textarea, select').forEach(function (input) {
                    if (input.type === 'hidden') {
                        if (input.classList.contains('fields-crop-data')) input.value = '{}';
                        return;
                    }
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                        input.checked = false;
                    }
                });
                entry.querySelectorAll('.fields-media-preview').forEach(function(el) { el.innerHTML = ''; });
            } else {
                entry.remove();
            }

            // Re-number
            wrapper.querySelectorAll('.fields-repeater-entry').forEach(function (el, idx) {
                el.dataset.index = idx;
                var heading = el.querySelector('.fields-repeater-heading strong, .panel-heading strong');
                if (heading) {
                    var text = heading.textContent;
                    var suffix = text.indexOf('\u2013') !== -1 ? text.substring(text.indexOf('\u2013')) : '';
                    if (text.indexOf('–') !== -1) suffix = text.substring(text.indexOf('–')); // en-dash
                    heading.textContent = '#' + (idx + 1) + (suffix ? ' ' + suffix : '');
                }
            });

            updateHiddenValue(wrapper);
        });

        // Value changes
        document.addEventListener('input', function (e) {
            var wrapper = e.target.closest('[data-field-name]');
            if (wrapper) {
                updateHiddenValue(wrapper);
            }
        });
        document.addEventListener('change', function (e) {
            var wrapper = e.target.closest('[data-field-name]');
            if (wrapper) {
                updateHiddenValue(wrapper);
            }
        });
    }

    function initRepeaterState() {
        document.querySelectorAll('.fields-repeater-entries').forEach(function (container) {
            if (container.dataset.fieldsInit) return;
            container.dataset.fieldsInit = '1';
        });
    }

    function updateHiddenValue(wrapper) {
        if (wrapper.classList.contains('fields-social-web-repeater')) {
            updateSocialWebValue(wrapper);
        } else if (wrapper.classList.contains('fields-faq-repeater')) {
            updateFaqValue(wrapper);
        } else if (wrapper.classList.contains('fields-contacts-repeater')) {
            updateContactsValue(wrapper);
        } else if (wrapper.classList.contains('fields-opening-hours')) {
            updateOpeningHoursValue(wrapper);
        }
    }

    // ============================================================
    // Social Web Repeater
    // ============================================================

    function updateSocialWebValue(wrapper) {
        var entries = [];
        wrapper.querySelectorAll('.fields-repeater-entry').forEach(function (entry) {
            var platform = entry.querySelector('.fields-social-platform');
            var url = entry.querySelector('.fields-social-url');
            if (platform && url && (platform.value || url.value)) {
                entries.push({
                    platform: platform.value,
                    url: url.value
                });
            }
        });
        var hidden = wrapper.querySelector('.fields-social-web-value');
        if (hidden) {
            hidden.value = JSON.stringify(entries);
        }
    }

    // ============================================================
    // FAQ Repeater
    // ============================================================

    function updateFaqValue(wrapper) {
        var entries = [];
        wrapper.querySelectorAll('.fields-repeater-entry').forEach(function (entry) {
            var question = entry.querySelector('.fields-faq-question');
            var answer = entry.querySelector('.fields-faq-answer');
            if (question && answer) {
                entries.push({
                    question: question.value,
                    answer: answer.value
                });
            }
        });
        var hidden = wrapper.querySelector('.fields-faq-value');
        if (hidden) {
            hidden.value = JSON.stringify(entries);
        }
    }

    // ============================================================
    // Contacts Repeater
    // ============================================================

    function updateContactsValue(wrapper) {
        var contacts = [];
        wrapper.querySelectorAll('.fields-repeater-entry').forEach(function (entry) {
            var contact = {};

            var fields = {
                'firstname': '.fields-contact-firstname',
                'lastname': '.fields-contact-lastname',
                'company': '.fields-contact-company',
                'function': '.fields-contact-function',
                'phone': '.fields-contact-phone',
                'mobile': '.fields-contact-mobile',
                'email': '.fields-contact-email',
                'street': '.fields-contact-street',
                'zip': '.fields-contact-zip',
                'city': '.fields-contact-city',
                'country': '.fields-contact-country',
                'homepage': '.fields-contact-homepage',
                'avatar': '.fields-contact-avatar',
                'company_logo': '.fields-contact-company-logo'
            };

            for (var key in fields) {
                var el = entry.querySelector(fields[key]);
                if (el && el.value) {
                    contact[key] = el.value;
                }
            }

            // Crop-Daten
            var avatarCropEl = entry.querySelector('.fields-contact-avatar-crop');
            if (avatarCropEl && avatarCropEl.value) {
                try {
                    var cropData = JSON.parse(avatarCropEl.value);
                    if (cropData && typeof cropData === 'object' && Object.keys(cropData).length > 0) {
                        contact.avatar_crop = cropData;
                    }
                } catch (ex) { /* ignore */ }
            }

            var logoCropEl = entry.querySelector('.fields-contact-company-logo-crop');
            if (logoCropEl && logoCropEl.value) {
                try {
                    var logoCropData = JSON.parse(logoCropEl.value);
                    if (logoCropData && typeof logoCropData === 'object' && Object.keys(logoCropData).length > 0) {
                        contact.company_logo_crop = logoCropData;
                    }
                } catch (ex) { /* ignore */ }
            }

            // Social entries
            var socialEntries = [];
            entry.querySelectorAll('.fields-contact-social-entry').forEach(function (sEntry) {
                var inputs = sEntry.querySelectorAll('input');
                if (inputs.length >= 2 && (inputs[0].value || inputs[1].value)) {
                    socialEntries.push({
                        platform: inputs[0].value,
                        url: inputs[1].value
                    });
                }
            });
            if (socialEntries.length > 0) {
                contact.social = socialEntries;
            }

            if (contact.firstname || contact.lastname || contact.company) {
                contacts.push(contact);
            }
        });

        var hidden = wrapper.querySelector('.fields-contacts-value');
        if (hidden) {
            hidden.value = JSON.stringify(contacts);
        }
    }

    // ============================================================
    // Contact Media Pool Integration
    // ============================================================

    function registerContactMediaEvents() {
        // Media-Pool öffnen
        document.addEventListener('click', function (e) {
            var openBtn = e.target.closest('.fields-media-open');
            if (openBtn) {
                e.preventDefault();
                var inputId = openBtn.dataset.inputId;
                var category = openBtn.dataset.category || '0';
                var types = openBtn.dataset.types || '';
                var params = '&rex_file_category=' + category;
                if (types) {
                    params += '&args[types]=' + types;
                }
                // REDAXO Mediapool-Popup öffnen
                var url = 'index.php?page=mediapool/media' + params + '&opener_input_field=' + inputId;
                newPoolWindow(url);
                return;
            }

            // Media löschen
            var deleteBtn = e.target.closest('.fields-media-delete');
            if (deleteBtn) {
                e.preventDefault();
                var delInputId = deleteBtn.dataset.inputId;
                var delInput = document.getElementById(delInputId);
                if (delInput) {
                    delInput.value = '';
                    // Preview entfernen
                    var previewDiv = delInput.closest('.fields-media-field').querySelector('.fields-media-preview');
                    if (previewDiv) previewDiv.innerHTML = '';
                    // Crop-Daten zurücksetzen
                    var cropInput = delInput.closest('.fields-media-field').querySelector('.fields-crop-data');
                    if (cropInput) cropInput.value = '{}';
                    // Hauptwert aktualisieren
                    var wrapper = delInput.closest('.fields-contacts-repeater');
                    if (wrapper) updateContactsValue(wrapper);
                }
                return;
            }

            // Crop-Button
            var cropBtn = e.target.closest('.fields-media-crop');
            if (cropBtn) {
                e.preventDefault();
                var cropInputId = cropBtn.dataset.inputId;
                var ratio = cropBtn.dataset.ratio || '1:1';
                var mediaInput = document.getElementById(cropInputId);
                if (!mediaInput || !mediaInput.value) {
                    alert('Bitte zuerst ein Bild auswählen.');
                    return;
                }
                openCropDialog(mediaInput, ratio);
                return;
            }
        });

        // Auf Media-Auswahl reagieren (REDAXO setzt input.value und triggert 'change')
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on('change', '.fields-media-input', function () {
                var input = this;
                var filename = input.value;
                updateMediaPreview(input, filename);
                var wrapper = input.closest('.fields-contacts-repeater');
                if (wrapper) updateContactsValue(wrapper);
            });
        }
    }

    function updateMediaPreview(input, filename) {
        var field = input.closest('.fields-media-field');
        if (!field) return;
        var previewDiv = field.querySelector('.fields-media-preview');
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.className = 'fields-media-preview';
            previewDiv.style.marginTop = '5px';
            field.appendChild(previewDiv);
        }
        if (filename) {
            var maxH = field.dataset.mediaType === 'company_logo' ? '40px' : '80px';
            var imgUrl = 'index.php?rex_media_type=rex_media_small&rex_media_file=' + encodeURIComponent(filename);
            previewDiv.innerHTML = '<img src="' + imgUrl + '" alt="" style="max-height:' + maxH + ';" />';
        } else {
            previewDiv.innerHTML = '';
        }
    }

    // ============================================================
    // Crop-Dialog (Croppie.js Integration)
    // ============================================================

    var _croppieLoaded = null; // null = nicht geladen, Promise wenn am Laden

    function loadCroppie() {
        if (_croppieLoaded) return _croppieLoaded;
        _croppieLoaded = new Promise(function (resolve, reject) {
            // Prüfen ob Croppie schon global verfügbar ist
            if (typeof Croppie !== 'undefined') {
                resolve();
                return;
            }
            // CSS laden
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css';
            document.head.appendChild(link);
            // JS laden
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js';
            script.onload = resolve;
            script.onerror = function () {
                _croppieLoaded = null;
                reject(new Error('Croppie konnte nicht geladen werden.'));
            };
            document.head.appendChild(script);
        });
        return _croppieLoaded;
    }

    function parseRatio(ratioStr) {
        if (!ratioStr || ratioStr === 'free') return null;
        var parts = ratioStr.split(':');
        if (parts.length !== 2) return null;
        return parseFloat(parts[0]) / parseFloat(parts[1]);
    }

    function openCropDialog(mediaInput, ratioStr) {
        var filename = mediaInput.value;
        if (!filename) return;

        var imageUrl = 'index.php?rex_media_type=rex_media_large&rex_media_file=' + encodeURIComponent(filename);
        var ratio = parseRatio(ratioStr);
        var field = mediaInput.closest('.fields-media-field');
        var cropInput = field ? field.querySelector('.fields-crop-data') : null;

        // Bestehende Crop-Daten laden
        var existingCrop = {};
        if (cropInput && cropInput.value) {
            try { existingCrop = JSON.parse(cropInput.value); } catch (ex) { /* ignore */ }
        }

        loadCroppie().then(function () {
            // Modal erstellen
            var overlay = document.createElement('div');
            overlay.className = 'fields-crop-overlay';
            overlay.innerHTML =
                '<div class="fields-crop-modal">' +
                '  <div class="fields-crop-header">' +
                '    <h4>Bildausschnitt wählen</h4>' +
                '    <button type="button" class="close fields-crop-cancel">&times;</button>' +
                '  </div>' +
                '  <div class="fields-crop-body">' +
                '    <div class="fields-crop-container"></div>' +
                '  </div>' +
                '  <div class="fields-crop-footer">' +
                '    <button type="button" class="btn btn-default fields-crop-cancel">Abbrechen</button>' +
                '    <button type="button" class="btn btn-default fields-crop-reset">Zurücksetzen</button>' +
                '    <button type="button" class="btn btn-primary fields-crop-apply">Übernehmen</button>' +
                '  </div>' +
                '</div>';
            document.body.appendChild(overlay);

            var container = overlay.querySelector('.fields-crop-container');

            // Viewport-Größe berechnen
            var vpWidth = 300;
            var vpHeight = ratio ? Math.round(vpWidth / ratio) : 300;
            if (vpHeight > 400) {
                vpHeight = 400;
                vpWidth = Math.round(vpHeight * ratio);
            }

            var croppie = new Croppie(container, {
                url: imageUrl,
                viewport: {
                    width: vpWidth,
                    height: vpHeight,
                    type: 'square'
                },
                boundary: {
                    width: Math.max(vpWidth + 100, 450),
                    height: Math.max(vpHeight + 100, 450)
                },
                enableOrientation: false,
                enableZoom: true,
                enforceBoundary: false
            });

            // Übernehmen
            overlay.querySelector('.fields-crop-apply').addEventListener('click', function () {
                croppie.result({
                    type: 'rawcanvas',
                    size: 'viewport',
                    format: 'png',
                    quality: 1
                }).then(function () {
                    var data = croppie.get();
                    // CSS-freundliche Crop-Daten berechnen
                    // data.points = [x1, y1, x2, y2] in Originalbild-Pixeln
                    // data.zoom = aktueller Zoom-Faktor
                    var points = data.points;
                    var cropX = parseInt(points[0], 10);
                    var cropY = parseInt(points[1], 10);
                    var cropW = parseInt(points[2], 10) - cropX;
                    var cropH = parseInt(points[3], 10) - cropY;

                    // Lade das originale Bild für Dimension
                    var tmpImg = new Image();
                    tmpImg.onload = function () {
                        var origW = tmpImg.naturalWidth;
                        var origH = tmpImg.naturalHeight;

                        // CSS object-position in Prozent (basierend auf Crop-Center)
                        var centerX = cropX + cropW / 2;
                        var centerY = cropY + cropH / 2;
                        var posX = origW > 0 ? Math.round((centerX / origW) * 100) : 50;
                        var posY = origH > 0 ? Math.round((centerY / origH) * 100) : 50;

                        var cropResult = {
                            x: cropX,
                            y: cropY,
                            width: cropW,
                            height: cropH,
                            x_pct: parseFloat((origW > 0 ? (cropX / origW * 100) : 0).toFixed(4)),
                            y_pct: parseFloat((origH > 0 ? (cropY / origH * 100) : 0).toFixed(4)),
                            width_pct: parseFloat((origW > 0 ? (cropW / origW * 100) : 100).toFixed(4)),
                            height_pct: parseFloat((origH > 0 ? (cropH / origH * 100) : 100).toFixed(4)),
                            posX: posX,
                            posY: posY,
                            ratio: ratioStr,
                            zoom: data.zoom
                        };

                        if (cropInput) {
                            cropInput.value = JSON.stringify(cropResult);
                        }

                        // Preview aktualisieren mit Crop-Darstellung
                        var previewDiv = field ? field.querySelector('.fields-media-preview') : null;
                        if (previewDiv) {
                            previewDiv.innerHTML = '<img src="' + imageUrl + '" alt="" ' +
                                'style="max-height:80px; object-fit:cover; object-position:' +
                                posX + '% ' + posY + '%;" />';
                        }

                        // Hauptwert aktualisieren
                        var wrapper = mediaInput.closest('.fields-contacts-repeater');
                        if (wrapper) updateContactsValue(wrapper);

                        // Modal schließen
                        croppie.destroy();
                        overlay.remove();
                    };
                    tmpImg.src = imageUrl;
                });
            });

            // Zurücksetzen
            overlay.querySelector('.fields-crop-reset').addEventListener('click', function () {
                if (cropInput) cropInput.value = '{}';
                var previewDiv = field ? field.querySelector('.fields-media-preview') : null;
                if (previewDiv) {
                    previewDiv.innerHTML = '<img src="' + imageUrl + '" alt="" style="max-height:80px;" />';
                }
                var wrapper = mediaInput.closest('.fields-contacts-repeater');
                if (wrapper) updateContactsValue(wrapper);
                croppie.destroy();
                overlay.remove();
            });

            // Abbrechen
            overlay.querySelectorAll('.fields-crop-cancel').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    croppie.destroy();
                    overlay.remove();
                });
            });

            // Overlay-Klick = Abbrechen
            overlay.addEventListener('click', function (evt) {
                if (evt.target === overlay) {
                    croppie.destroy();
                    overlay.remove();
                }
            });
        }).catch(function (err) {
            alert(err.message || 'Croppie konnte nicht geladen werden.');
        });
    }

    function registerRepeaterSocialEvents() {
        // Contact social add/remove
        document.addEventListener('click', function (e) {
            var addBtn = e.target.closest('.fields-contact-social-add');
            if (addBtn) {
                var container = addBtn.previousElementSibling;
                if (!container) return;
                var html = '<div class="form-inline fields-contact-social-entry" style="margin-bottom: 5px;">' +
                    '<input type="text" class="form-control input-sm" style="width: 120px;" placeholder="Plattform" />' +
                    '<input type="url" class="form-control input-sm" style="width: calc(100% - 180px);" placeholder="URL" />' +
                    '<button type="button" class="btn btn-danger btn-xs fields-contact-social-remove">' +
                    '<i class="rex-icon fa-minus"></i></button></div>';
                container.insertAdjacentHTML('beforeend', html);
            }

            var removeBtn = e.target.closest('.fields-contact-social-remove');
            if (removeBtn) {
                removeBtn.closest('.fields-contact-social-entry').remove();
                var wrapper = removeBtn.closest('.fields-contacts-repeater');
                if (wrapper) updateContactsValue(wrapper);
            }
        });
    }

    // ============================================================
    // Opening Hours
    // ============================================================

    function registerOpeningHoursEvents() {
        // Status change
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('fields-oh-status')) return;
            var day = e.target.dataset.day;
            var times = e.target.closest('tr').querySelector('.fields-oh-times');
            if (times) {
                times.style.display = e.target.value === 'open' ? '' : 'none';
            }
            var wrapper = e.target.closest('.fields-opening-hours');
            if (wrapper) updateOpeningHoursValue(wrapper);
        });

        // Add time slot
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-oh-add-time')) return;
            var btn = e.target.closest('.fields-oh-add-time');
            var container = btn.closest('.fields-oh-times');
            var html = '<div class="fields-oh-timeslot"><div class="form-inline">' +
                '<input type="time" class="form-control input-sm fields-oh-open" value="09:00" />' +
                '<span> – </span>' +
                '<input type="time" class="form-control input-sm fields-oh-close" value="17:00" />' +
                '<button type="button" class="btn btn-danger btn-xs fields-oh-remove-time" title="Entfernen">' +
                '<i class="rex-icon fa-minus"></i></button></div></div>';
            btn.insertAdjacentHTML('beforebegin', html);
            var wrapper = btn.closest('.fields-opening-hours');
            if (wrapper) updateOpeningHoursValue(wrapper);
        });

        // Remove time slot
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-oh-remove-time')) return;
            var slot = e.target.closest('.fields-oh-timeslot');
            var wrapper = e.target.closest('.fields-opening-hours');
            if (slot) slot.remove();
            if (wrapper) updateOpeningHoursValue(wrapper);
        });

        // Special opening hours - add
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-oh-special-add')) return;
            var container = e.target.closest('.panel-body').querySelector('.fields-oh-special-entries');
            var html = '<div class="fields-oh-special-entry panel panel-default">' +
                '<div class="panel-body"><div class="row">' +
                '<div class="col-sm-3"><input type="text" class="form-control input-sm fields-oh-special-date" placeholder="YYYY-MM-DD" /></div>' +
                '<div class="col-sm-3"><input type="text" class="form-control input-sm fields-oh-special-name" placeholder="Bezeichnung" /></div>' +
                '<div class="col-sm-2"><select class="form-control input-sm fields-oh-special-status">' +
                '<option value="closed">Geschlossen</option><option value="open">Geöffnet</option></select></div>' +
                '<div class="col-sm-3 fields-oh-special-times" style="display:none;"></div>' +
                '<div class="col-sm-1"><button type="button" class="btn btn-danger btn-xs fields-oh-special-remove">' +
                '<i class="rex-icon fa-trash"></i></button></div></div></div></div>';
            container.insertAdjacentHTML('beforeend', html);
        });

        // Special - remove
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-oh-special-remove')) return;
            var entry = e.target.closest('.fields-oh-special-entry');
            var wrapper = e.target.closest('.fields-opening-hours');
            if (entry) entry.remove();
            if (wrapper) updateOpeningHoursValue(wrapper);
        });

        // Special - status change
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('fields-oh-special-status')) return;
            var times = e.target.closest('.row').querySelector('.fields-oh-special-times');
            if (times) {
                times.style.display = e.target.value === 'open' ? '' : 'none';
            }
        });
    }

    function updateOpeningHoursValue(wrapper) {
        var data = { regular: {}, special: [], note: '' };

        // Reguläre Zeiten
        wrapper.querySelectorAll('.fields-oh-day').forEach(function (row) {
            var day = row.dataset.day;
            var statusSelect = row.querySelector('.fields-oh-status');
            var status = statusSelect ? statusSelect.value : 'closed';
            var times = [];

            if (status === 'open') {
                row.querySelectorAll('.fields-oh-timeslot').forEach(function (slot) {
                    var open = slot.querySelector('.fields-oh-open');
                    var close = slot.querySelector('.fields-oh-close');
                    if (open && close) {
                        times.push({ open: open.value, close: close.value });
                    }
                });
            }

            data.regular[day] = { status: status, times: times };
        });

        // Sonderzeiten
        wrapper.querySelectorAll('.fields-oh-special-entry').forEach(function (entry) {
            var date = entry.querySelector('.fields-oh-special-date');
            var name = entry.querySelector('.fields-oh-special-name');
            var status = entry.querySelector('.fields-oh-special-status');

            if (date && date.value) {
                var special = {
                    date: date.value,
                    name: name ? name.value : '',
                    status: status ? status.value : 'closed',
                    times: []
                };
                data.special.push(special);
            }
        });

        // Hinweis
        var note = wrapper.querySelector('.fields-oh-note');
        if (note) {
            data.note = note.value;
        }

        var hidden = wrapper.querySelector('.fields-opening-hours-value');
        if (hidden) {
            hidden.value = JSON.stringify(data);
        }
    }

    // ============================================================
    // IBAN Live Validation
    // ============================================================

    function registerIbanEvents() {
        var debounceTimer = null;

        function runIbanCheck(wrapper, input) {
            var apiUrl = wrapper.dataset.apiUrl;
            var iban = input.value.replace(/\s+/g, '').toUpperCase();
            var statusEl = wrapper.querySelector('.fields-iban-status i');
            var resultEl = wrapper.closest('.form-group').querySelector('.fields-iban-result');

            if (iban.length < 5) {
                statusEl.className = 'rex-icon fa-question-circle text-muted';
                if (resultEl) resultEl.style.display = 'none';
                return;
            }

            statusEl.className = 'rex-icon fa-spinner fa-spin text-info';

            fetch(apiUrl + '&iban=' + encodeURIComponent(iban))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.valid) {
                        statusEl.className = 'rex-icon fa-check-circle text-success';
                        if (resultEl) {
                            resultEl.style.display = '';
                            var bankEl = resultEl.querySelector('.fields-iban-bank');
                            var bicEl = resultEl.querySelector('.fields-iban-bic');
                            if (bankEl && data.bank) bankEl.textContent = 'Bank: ' + data.bank;
                            if (bicEl && data.bic) bicEl.textContent = ' | BIC: ' + data.bic;
                        }
                    } else {
                        statusEl.className = 'rex-icon fa-times-circle text-danger';
                        if (resultEl) resultEl.style.display = 'none';
                    }
                })
                .catch(function () {
                    statusEl.className = 'rex-icon fa-exclamation-triangle text-warning';
                });
        }

        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('fields-iban-input')) return;

            clearTimeout(debounceTimer);
            var wrapper = e.target.closest('.fields-iban-wrapper');
            if (!wrapper) return;

            var input = e.target;
            var statusEl = wrapper.querySelector('.fields-iban-status i');
            statusEl.className = 'rex-icon fa-spinner fa-spin text-info';

            debounceTimer = setTimeout(function () {
                runIbanCheck(wrapper, input);
            }, 500);
        });

        // Manuelles Prüfen via Klick auf das Status-Icon
        document.addEventListener('click', function (e) {
            var statusBtn = e.target.closest('.fields-iban-status');
            if (!statusBtn) return;
            
            var wrapper = statusBtn.closest('.fields-iban-wrapper');
            if (!wrapper) return;

            var input = wrapper.querySelector('.fields-iban-input');
            if (input) {
                clearTimeout(debounceTimer);
                runIbanCheck(wrapper, input);
            }
        });
    }

    // ============================================================
    // Conditional Field Groups
    // ============================================================

    function initConditionalFields() {
        document.querySelectorAll('.fields-conditional-rule').forEach(function (rule) {
            var sourceField = rule.dataset.sourceField;
            var operator = rule.dataset.operator;
            var compareValue = rule.dataset.compareValue;
            var targetFields = JSON.parse(rule.dataset.targetFields || '[]');
            var action = rule.dataset.action;

            if (!sourceField || targetFields.length === 0) return;

            // Source Input finden: Primär über Wrapper ID (div[id$="-feldname"])
            var sourceWrapper = document.querySelector('div[id$="-' + sourceField + '"]');
            var sourceInput = null;

            if (sourceWrapper) {
                sourceInput = sourceWrapper.querySelector('input, select, textarea');
            }

            // Fallback für Sonderfälle: Direktes Input-Match falls Wrapper nicht greifbar
            if (!sourceInput) {
                sourceInput = document.querySelector('[name*="[' + sourceField + ']"]');
            }

            if (!sourceInput) return;

            function evaluate() {
                var val = sourceInput.type === 'checkbox' ? (sourceInput.checked ? '1' : '0') : sourceInput.value;
                var match = false;

                switch (operator) {
                    case '=': match = val === compareValue; break;
                    case '!=': match = val !== compareValue; break;
                    case '>': match = Number(val) > Number(compareValue); break;
                    case '<': match = Number(val) < Number(compareValue); break;
                    case 'contains': match = val.indexOf(compareValue) !== -1; break;
                    case 'empty': match = val === '' || val === null; break;
                    case '!empty': match = val !== '' && val !== null; break;
                    default: match = val === compareValue;
                }

                if (operator === 'switch') {
                    // Switch Mode: Vergleicht Target-Name/Klasse mit dem aktuellen Wert
                    targetFields.forEach(function (fieldName) {
                        fieldName = fieldName.trim();
                        if (!fieldName) return;
                        
                        var cleanTarget = fieldName.replace(/^[.#]/, '');
                        // Match wenn Target exakt Wert ist oder Target auf "-Wert" oder "_Wert" endet
                        var isMatch = (cleanTarget === val) || fieldName.endsWith('-' + val) || fieldName.endsWith('_' + val);
                        
                        // Toggle Logic for Switch
                        if (fieldName.startsWith('.') || fieldName.startsWith('#')) {
                            document.querySelectorAll(fieldName).forEach(function(el) {
                                el.style.display = isMatch ? '' : 'none';
                            });
                        } else {
                            var targetWrapper = document.querySelector('div[id$="-' + fieldName + '"]');
                            if (targetWrapper) {
                                targetWrapper.style.display = isMatch ? '' : 'none';
                            }
                        }
                    });
                    return; // Early exit für Switch Mode
                }

                var shouldShow = (action === 'show') ? match : !match;

                targetFields.forEach(function (fieldName) {
                    fieldName = fieldName.trim();
                    if (!fieldName) return;

                    // Support CSS Selectors (.class, #id)
                    if (fieldName.startsWith('.') || fieldName.startsWith('#')) {
                        document.querySelectorAll(fieldName).forEach(function(el) {
                            el.style.display = shouldShow ? '' : 'none';
                        });
                        return;
                    }

                    // Target Wrapper finden: Primär über Wrapper ID
                    var targetWrapper = document.querySelector('div[id$="-' + fieldName + '"]');

                        if (targetWrapper) {
                            targetWrapper.style.display = shouldShow ? '' : 'none';
                        }
                    }
                );
            }

            sourceInput.addEventListener('change', evaluate);
            sourceInput.addEventListener('input', evaluate);
            evaluate(); // Initial
        });
    }

    // ============================================================
    // Icon Picker
    // ============================================================

    // Font Awesome 5/6 Icons (Auswahl der gängigsten)
    var FA_ICONS = [
        'fa-home', 'fa-user', 'fa-users', 'fa-envelope', 'fa-phone', 'fa-mobile',
        'fa-map-marker', 'fa-clock', 'fa-calendar', 'fa-star', 'fa-heart', 'fa-comment',
        'fa-search', 'fa-cog', 'fa-cogs', 'fa-wrench', 'fa-lock', 'fa-unlock',
        'fa-key', 'fa-shield', 'fa-check', 'fa-times', 'fa-plus', 'fa-minus',
        'fa-arrow-up', 'fa-arrow-down', 'fa-arrow-left', 'fa-arrow-right',
        'fa-chevron-up', 'fa-chevron-down', 'fa-chevron-left', 'fa-chevron-right',
        'fa-file', 'fa-file-alt', 'fa-folder', 'fa-folder-open', 'fa-image', 'fa-camera',
        'fa-video', 'fa-music', 'fa-play', 'fa-pause', 'fa-stop', 'fa-volume-up',
        'fa-download', 'fa-upload', 'fa-cloud', 'fa-database', 'fa-server',
        'fa-desktop', 'fa-laptop', 'fa-tablet', 'fa-mobile-alt', 'fa-print',
        'fa-wifi', 'fa-bluetooth', 'fa-battery-full', 'fa-plug', 'fa-bolt',
        'fa-globe', 'fa-link', 'fa-unlink', 'fa-paperclip', 'fa-bookmark',
        'fa-tag', 'fa-tags', 'fa-flag', 'fa-map', 'fa-compass',
        'fa-chart-bar', 'fa-chart-line', 'fa-chart-pie', 'fa-chart-area',
        'fa-table', 'fa-list', 'fa-th', 'fa-th-large', 'fa-th-list',
        'fa-shopping-cart', 'fa-credit-card', 'fa-money-bill', 'fa-euro-sign',
        'fa-truck', 'fa-box', 'fa-gift', 'fa-trophy', 'fa-medal',
        'fa-graduation-cap', 'fa-book', 'fa-pen', 'fa-pencil-alt', 'fa-edit',
        'fa-trash', 'fa-trash-alt', 'fa-redo', 'fa-undo', 'fa-sync',
        'fa-eye', 'fa-eye-slash', 'fa-bell', 'fa-bell-slash', 'fa-lightbulb',
        'fa-sun', 'fa-moon', 'fa-snowflake', 'fa-fire', 'fa-water',
        'fa-leaf', 'fa-tree', 'fa-seedling', 'fa-paw', 'fa-bug',
        'fa-car', 'fa-bicycle', 'fa-bus', 'fa-train', 'fa-plane',
        'fa-ship', 'fa-rocket', 'fa-helicopter', 'fa-parking', 'fa-gas-pump',
        'fa-hospital', 'fa-ambulance', 'fa-heartbeat', 'fa-stethoscope', 'fa-pills',
        'fa-utensils', 'fa-coffee', 'fa-glass-martini', 'fa-beer', 'fa-wine-glass',
        'fa-building', 'fa-store', 'fa-industry', 'fa-church', 'fa-university',
        'fa-facebook', 'fa-twitter', 'fa-instagram', 'fa-linkedin', 'fa-youtube',
        'fa-github', 'fa-whatsapp', 'fa-telegram', 'fa-tiktok', 'fa-pinterest',
        'fa-xing', 'fa-discord', 'fa-slack', 'fa-reddit', 'fa-spotify',
        'fa-apple', 'fa-android', 'fa-windows', 'fa-linux', 'fa-chrome',
        'fa-info', 'fa-info-circle', 'fa-question', 'fa-question-circle',
        'fa-exclamation', 'fa-exclamation-circle', 'fa-exclamation-triangle',
        'fa-ban', 'fa-hand-paper', 'fa-thumbs-up', 'fa-thumbs-down', 'fa-smile',
    ];

    // UIkit Icons
    var UIKIT_ICONS = [
        'home', 'user', 'users', 'mail', 'phone', 'receiver',
        'location', 'clock', 'calendar', 'star', 'heart', 'comment',
        'search', 'settings', 'cog', 'lock', 'unlock',
        'check', 'close', 'plus', 'minus', 'plus-circle', 'minus-circle',
        'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right',
        'chevron-up', 'chevron-down', 'chevron-left', 'chevron-right',
        'file', 'file-text', 'folder', 'image', 'camera',
        'video-camera', 'play', 'play-circle',
        'download', 'upload', 'cloud-upload', 'cloud-download',
        'desktop', 'laptop', 'tablet', 'phone',
        'world', 'link', 'bookmark', 'tag',
        'cart', 'credit-card', 'bag',
        'table', 'list', 'grid', 'thumbnails',
        'pencil', 'trash', 'refresh', 'copy', 'move',
        'eye', 'eye-slash', 'bell', 'bolt',
        'commenting', 'comments', 'hashtag', 'rss',
        'paint-bucket', 'code', 'database', 'server',
        'print', 'pull', 'push',
        'happy', 'lifesaver', 'warning', 'info',
        'question', 'ban', 'social',
        'facebook', 'twitter', 'instagram', 'linkedin',
        'youtube', 'github', 'whatsapp', 'tiktok', 'pinterest',
        'xing', 'discord', 'reddit', 'spotify',
    ];

    function registerIconPickerEvents() {
        // Open picker
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-icon-pick')) return;
            var wrapper = e.target.closest('.fields-icon-picker');
            if (!wrapper) return;
            var modal = wrapper.querySelector('.fields-icon-modal');
            if (!modal) return;

            var isVisible = modal.style.display !== 'none';
            modal.style.display = isVisible ? 'none' : '';

            if (!isVisible) {
                // Load icons
                var enabledSets = JSON.parse(wrapper.dataset.enabledSets || '["fontawesome"]');
                var activeSet = enabledSets[0] || 'fontawesome';
                renderIcons(wrapper, activeSet);
            }
        });

        // Tab switch
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-icon-tab')) return;
            var btn = e.target.closest('.fields-icon-tab');
            var wrapper = btn.closest('.fields-icon-picker');
            if (!wrapper) return;

            wrapper.querySelectorAll('.fields-icon-tab').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            renderIcons(wrapper, btn.dataset.set);
        });

        // Search
        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('fields-icon-search')) return;
            var wrapper = e.target.closest('.fields-icon-picker');
            if (!wrapper) return;
            var activeTab = wrapper.querySelector('.fields-icon-tab.active');
            var set = activeTab ? activeTab.dataset.set : 'fontawesome';
            renderIcons(wrapper, set, e.target.value.toLowerCase());
        });

        // Select icon
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-icon-item')) return;
            var item = e.target.closest('.fields-icon-item');
            var wrapper = item.closest('.fields-icon-picker');
            if (!wrapper) return;

            var iconClass = item.dataset.icon;
            var input = wrapper.querySelector('.fields-icon-value');
            var preview = wrapper.querySelector('.fields-icon-preview');
            var modal = wrapper.querySelector('.fields-icon-modal');

            if (input) input.value = iconClass;
            if (preview) preview.innerHTML = '<i class="' + iconClass + '"></i>';
            if (modal) modal.style.display = 'none';

            // Show clear button
            var clearBtn = wrapper.querySelector('.fields-icon-clear');
            if (!clearBtn) {
                var btnGroup = wrapper.querySelector('.input-group-btn');
                if (btnGroup) {
                    btnGroup.insertAdjacentHTML('beforeend',
                        '<button type="button" class="btn btn-default fields-icon-clear" title="Auswahl entfernen">' +
                        '<i class="rex-icon fa-times"></i></button>');
                }
            }
        });

        // Clear
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fields-icon-clear')) return;
            var wrapper = e.target.closest('.fields-icon-picker');
            if (!wrapper) return;

            var input = wrapper.querySelector('.fields-icon-value');
            var preview = wrapper.querySelector('.fields-icon-preview');
            if (input) input.value = '';
            if (preview) preview.innerHTML = '<i class="rex-icon fa-image text-muted"></i>';
        });
    }

    function renderIcons(wrapper, set, filter) {
        var grid = wrapper.querySelector('.fields-icon-grid');
        if (!grid) return;

        var icons = [];
        if (set === 'uikit') {
            if (typeof rex !== 'undefined' && rex.fields_icons && rex.fields_icons.uikit) {
                icons = rex.fields_icons.uikit;
            } else {
                icons = UIKIT_ICONS;
            }
        } else {
            if (typeof rex !== 'undefined' && rex.fields_icons && rex.fields_icons.fa) {
                icons = rex.fields_icons.fa;
            } else {
                icons = FA_ICONS;
            }
        }
        var html = '';

        icons.forEach(function (icon) {
            var iconName = icon.replace('fa-', '').replace('uk-icon-', '');
            if (filter && iconName.indexOf(filter) === -1) return;

            var iconClass, displayIcon;
            if (set === 'uikit') {
                iconClass = 'uk-icon-' + icon;
                displayIcon = '<span uk-icon="icon: ' + icon + '"></span>';
            } else {
                iconClass = 'fa ' + icon;
                displayIcon = '<i class="fa ' + icon + '"></i>';
            }

            html += '<button type="button" class="btn btn-default btn-sm fields-icon-item" ' +
                'data-icon="' + iconClass + '" title="' + iconName + '" ' +
                'style="width:40px;height:40px;margin:2px;font-size:16px;">' +
                displayIcon + '</button>';
        });

        if (html === '') {
            html = '<p class="text-muted text-center">Keine Icons gefunden</p>';
        }

        grid.innerHTML = html;
    }

    // ============================================================
    // Init
    // ============================================================

    // DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init on pjax (REDAXO Backend)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('rex:ready', init);
    }

})();
