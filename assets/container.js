(function () {
    'use strict';

    // 1. Helper: Vanilla JS Equivalent von jQuery .nextUntil()
    // Findet alle folgenden Geschwister-Elemente, bis der Selektor matcht
    const getNextSiblingsUntil = (elem, selector) => {
        let siblings = [];
        let next = elem.nextElementSibling;
        
        while (next) {
            // Wenn das nächste Element dem Selektor entspricht, stoppen wir (exclusive)
            if (next.nodeType === 1 && next.matches(selector)) {
                break;
            }
            siblings.push(next);
            next = next.nextElementSibling;
        }
        return siblings;
    };

    // 2. Main Logic: Container initialisieren
    const initFieldsContainers = (scope) => {
        scope = scope || document;
        
        // Finde alle Marker im aktuellen Scope
        const markers = scope.querySelectorAll('.fields-container-marker');
        
        // Set verwenden, um Marker zu tracken, die bereits verarbeitet wurden (Schutz vor Doppel-Wrap)
        // Hinweis: Da wir das DOM verändern, ist querySelectorAll zwar statisch, 
        // aber wir markieren die Elemente trotzdem.
        
        markers.forEach(marker => {
            if (marker.classList.contains('js-processed')) return;
            marker.classList.add('js-processed');

            // Optional: Heuristik für YForm Wrapper
            // Falls der Marker in einem div.yform-element alleine steht, 
            // wollen wir eigentlich ab diesem Wrapper sammeln, nicht ab dem input selbst.
            let startNode = marker;
            if (marker.parentElement && 
                marker.parentElement.children.length === 1 && 
                marker.tagName === 'INPUT') {
                // Der Marker ist das einzige Kind (z.B. in einem Wrapper-Div)
                startNode = marker.parentElement;
            }

            // Sammle alle Elemente bis zum nächsten Marker (oder bis zum Ende)
            // Beachte: Der Selektor muss auch auf Wrapper matchen, falls wir die Heuristik nutzen würden.
            // Vereinfachung: Wir suchen nach .fields-container-marker im DOM, aber nextUntil schaut auf Siblings.
            // Da Wrapper Siblings sind, stoppt nextUntil korrekt am nächsten Wrapper, 
            // WENN der nächste Wrapper AUCH den Marker enthält? Nein.
            // YForm flach: Marker <input> -> Sibling -> Sibling -> Marker <input>
            // Hier funktioniert der einfache Selector.
            
            const siblings = getNextSiblingsUntil(startNode, '.fields-container-marker, .fields-container-marker-wrapper');
            
            if (siblings.length > 0) {
                // Wrapper erstellen
                const wrapper = document.createElement('div');
                wrapper.className = 'fields-container-item';
                
                // Metadaten übertragen
                if (marker.dataset.type) {
                    wrapper.classList.add('fields-type-' + marker.dataset.type);
                    wrapper.dataset.type = marker.dataset.type;
                }
                if (marker.dataset.group) {
                    wrapper.dataset.group = marker.dataset.group;
                }
                if (marker.dataset.label) {
                    wrapper.dataset.label = marker.dataset.label;
                }

                // Wrapper im DOM platzieren (nach dem Marker/StartNode)
                startNode.parentNode.insertBefore(wrapper, startNode.nextSibling);

                // Elemente in den Wrapper verschieben
                siblings.forEach(el => wrapper.appendChild(el));
            }
        });

        // 3. Grouping Logic (Pass 2)
        // Benachbarte Container mit gleicher Gruppe zusammenfassen (z.B. für Tabs)
        const containers = scope.querySelectorAll('.fields-container-item');
        let currentGroupWrapper = null;
        let currentGroup = null;

        containers.forEach(container => {
            const group = container.dataset.group;
            
            // Wenn das Item eine Gruppe hat
            if (group) {
                // Wenn wir schon in dieser Gruppe sind und sie fortlaufend ist
                if (currentGroupWrapper && currentGroup === group && container.previousElementSibling === currentGroupWrapper) {
                    // Füge diesen Container zum existierenden Gruppen-Wrapper hinzu
                    currentGroupWrapper.appendChild(container);
                } else {
                    // Neue Gruppe starten
                    currentGroupWrapper = document.createElement('div');
                    currentGroupWrapper.className = 'fields-container-group fields-group-' + group;
                    
                    // Typ vom ersten Kind übernehmen (z.B. für CSS 'uk-switcher' oder 'tabs')
                    if (container.dataset.type) {
                        currentGroupWrapper.classList.add('group-type-' + container.dataset.type);
                        currentGroupWrapper.dataset.type = container.dataset.type;
                    }

                    // Vor dem aktuellen Container einfügen
                    container.parentNode.insertBefore(currentGroupWrapper, container);
                    
                    // Container hineinverschieben
                    currentGroupWrapper.appendChild(container);
                    currentGroup = group;
                }
            } else {
                // Container ohne Gruppe unterbricht die Sequenz
                currentGroupWrapper = null;
                currentGroup = null;
            }
        });
    };

    // 4. Initialization via REDAXO Event (oder DOMContentLoaded)
    const init = () => {
        // Initialer Run
        initFieldsContainers(document);

        // Hook für AJAX (YForm Popups, Blocks etc.)
        if (window.jQuery) {
            $(document).on('rex:ready', function (e, container) {
                // container ist hier entweder ein HTMLElement oder jQuery Objekt
                let domContainer = container instanceof jQuery ? container[0] : container;
                initFieldsContainers(domContainer);
            });
        }
    };

    // Start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
