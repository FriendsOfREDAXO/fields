/**
 * Fields AddOn - Conditional Field Groups (Frontend)
 *
 * Blendet Felder in YForm-Frontend-Formularen abhängig vom Wert eines
 * anderen Feldes ein/aus. Wertet die vom Feldtyp `fields_conditional`
 * gerenderten `.fields-conditional-rule`-Marker aus - rein clientseitig,
 * ohne serverseitige Abhängigkeiten.
 */
(function () {
    'use strict';

    function initConditionalFields() {
        var rules = document.querySelectorAll('.fields-conditional-rule');

        rules.forEach(function (rule) {
            var sourceField = rule.dataset.sourceField;
            var operator = rule.dataset.operator;
            var compareValue = rule.dataset.compareValue;
            var targetFields = JSON.parse(rule.dataset.targetFields || '[]');
            var action = rule.dataset.action;

            if (!sourceField || targetFields.length === 0) {
                return;
            }

            var ruleScope = rule.closest('form') || document;
            var sourceWrapper = ruleScope.querySelector('div[id$="-' + sourceField + '"]');
            var sourceInput = null;

            if (sourceWrapper) {
                sourceInput = sourceWrapper.querySelector('input[type=checkbox], input[type=radio]')
                           || sourceWrapper.querySelector('select, textarea')
                           || sourceWrapper.querySelector('input:not([type=hidden])')
                           || sourceWrapper.querySelector('input');
            }

            if (!sourceInput) {
                sourceInput = ruleScope.querySelector('[name*="[' + sourceField + ']"]');
                if (sourceInput && sourceInput.type === 'hidden') {
                    var betterInput = ruleScope.querySelector('[name*="[' + sourceField + ']"]:not([type=hidden])');
                    if (betterInput) sourceInput = betterInput;
                }
            }

            if (!sourceInput) {
                return;
            }

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
                    targetFields.forEach(function (fieldName) {
                        fieldName = fieldName.trim();
                        if (!fieldName) return;

                        var cleanTarget = fieldName.replace(/^[.#]/, '');
                        var isMatch = (cleanTarget === val) || fieldName.endsWith('-' + val) || fieldName.endsWith('_' + val);

                        if (fieldName.startsWith('.') || fieldName.startsWith('#')) {
                            document.querySelectorAll(fieldName).forEach(function (el) {
                                el.style.display = isMatch ? '' : 'none';
                            });
                        } else {
                            var targetWrapper = document.querySelector('div[id$="-' + fieldName + '"]');
                            if (targetWrapper) {
                                targetWrapper.style.display = isMatch ? '' : 'none';
                            }
                        }
                    });
                    return;
                }

                var shouldShow = (action === 'show') ? match : !match;

                targetFields.forEach(function (fieldName) {
                    fieldName = fieldName.trim();
                    if (!fieldName) return;

                    if (fieldName.startsWith('.') || fieldName.startsWith('#')) {
                        document.querySelectorAll(fieldName).forEach(function (el) {
                            el.style.display = shouldShow ? '' : 'none';
                        });
                        return;
                    }

                    var targetWrapper = document.querySelector('div[id$="-' + fieldName + '"]');
                    if (targetWrapper) {
                        targetWrapper.style.display = shouldShow ? '' : 'none';
                    }
                });
            }

            sourceInput.addEventListener('change', evaluate);
            sourceInput.addEventListener('input', evaluate);
            evaluate();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConditionalFields);
    } else {
        initConditionalFields();
    }
})();
