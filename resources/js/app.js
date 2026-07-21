import './bootstrap';
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Choices = Choices;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-enhance-select]').forEach((element) => {
        if (element.dataset.choicesReady === '1') {
            return;
        }

        try {
            const instance = new Choices(element, {
                allowHTML: false,
                shouldSort: false,
                searchEnabled: element.multiple || element.dataset.searchable === '1',
                itemSelectText: '',
                removeItemButton: element.multiple,
                placeholder: true,
                placeholderValue: element.dataset.placeholder || undefined,
                searchPlaceholderValue: element.dataset.searchPlaceholder || undefined,
            });
            element._choicesInstance = instance;
            element.dataset.choicesReady = '1';
        } catch (error) {
            // noop
        }
    });
});
