import { Collapse } from 'bootstrap';

/**
 * TYPO3 14 fallback for DCE FlexForm section containers.
 *
 * TYPO3 always gets the click first. DCE only intervenes when the native
 * FormEngine handler did not change the collapse state. No click is cancelled
 * or stopped, so FAL, crop and other FormEngine controls remain untouched.
 */
const sectionSelector = '.t3js-flex-section';
const sectionContentSelector = ':scope > .t3js-flex-section-content';
const sectionToggleSelector = '[data-bs-toggle="collapse"], .t3js-flex-section-toggle';

const toggleAllSelectors = [
    '.t3js-flex-section-toggle-all',
    '.t3js-flex-container-toggle-all',
    '.t3js-flex-control-toggle-all',
    '[data-action="toggle-all"]',
    '[data-action="toggleAll"]',
].join(',');

function getDirectSectionContent(section) {
    return section.querySelector(sectionContentSelector);
}

function getExpandedState(content) {
    return content.classList.contains('show');
}

function getDirectSectionToggle(target) {
    const toggle = target.closest(sectionToggleSelector);
    if (!toggle) {
        return null;
    }

    const heading = toggle.closest('.panel-heading');
    const section = heading?.parentElement;

    if (!heading || !section?.matches(sectionSelector)) {
        return null;
    }

    const content = getDirectSectionContent(section);
    if (!content) {
        return null;
    }

    // A valid section toggle must target the direct section content. This avoids
    // matching nested FAL/IRRE collapse controls inside the section body.
    const targetSelector = toggle.getAttribute('data-bs-target') || toggle.getAttribute('href');
    if (targetSelector?.startsWith('#') && content.id && targetSelector.slice(1) !== content.id) {
        return null;
    }

    return { toggle, section, content };
}

function findSectionScope(button) {
    let scope = button.parentElement;

    while (scope && scope !== document.body) {
        const sections = Array.from(scope.children).filter((child) => child.matches?.(sectionSelector));
        if (sections.length > 0) {
            return { scope, sections };
        }

        const nestedSections = Array.from(scope.querySelectorAll(sectionSelector));
        if (nestedSections.length > 0) {
            return { scope, sections: nestedSections };
        }

        scope = scope.parentElement;
    }

    return null;
}

function isToggleAllControl(button) {
    if (button.matches(toggleAllSelectors)) {
        return true;
    }

    const className = String(button.className || '').toLowerCase();
    const label = [
        button.getAttribute('title'),
        button.getAttribute('aria-label'),
        button.textContent,
    ].filter(Boolean).join(' ').toLowerCase();

    return className.includes('toggle-all')
        || label.includes('alle umschalten')
        || label.includes('toggle all');
}

document.addEventListener('click', (event) => {
    const sectionToggle = getDirectSectionToggle(event.target);
    if (sectionToggle) {
        const { content } = sectionToggle;
        const stateBefore = getExpandedState(content);

        window.setTimeout(() => {
            // TYPO3 handled the click: do nothing.
            if (getExpandedState(content) !== stateBefore) {
                return;
            }

            // TYPO3 did not react: use Bootstrap as a fallback.
            Collapse.getOrCreateInstance(content, { toggle: false }).toggle();
        }, 0);
        return;
    }

    const button = event.target.closest('button, a, [role="button"]');
    if (!button || button.closest(sectionSelector) || !isToggleAllControl(button)) {
        return;
    }

    const sectionScope = findSectionScope(button);
    if (!sectionScope) {
        return;
    }

    const contents = sectionScope.sections
        .map(getDirectSectionContent)
        .filter((content) => content instanceof HTMLElement);

    if (contents.length === 0) {
        return;
    }

    const statesBefore = contents.map(getExpandedState);

    window.setTimeout(() => {
        const statesAfter = contents.map(getExpandedState);
        const nativeHandlerChangedState = statesAfter.some((state, index) => state !== statesBefore[index]);

        if (nativeHandlerChangedState) {
            return;
        }

        const shouldOpen = statesBefore.some((state) => !state);
        contents.forEach((content) => {
            const collapse = Collapse.getOrCreateInstance(content, { toggle: false });
            if (shouldOpen) {
                collapse.show();
            } else {
                collapse.hide();
            }

        });
    }, 0);
});

/**
 * TYPO3 14 fallback for persisted FAL/IRRE records rendered in DCE FlexForms.
 *
 * The Core receives the click first. Only if the target panel did not change
 * its state does this fallback toggle it via the imported Bootstrap Collapse API.
 */
document.addEventListener('click', (event) => {
    const button = event.target.closest(
        '.form-irre-object > .panel-heading .panel-button[data-bs-toggle="collapse"]'
    );

    if (!button) {
        return;
    }

    const targetSelector = button.getAttribute('data-bs-target');
    if (!targetSelector || !targetSelector.startsWith('#')) {
        return;
    }

    const targetId = targetSelector.slice(1);
    const target = document.getElementById(targetId);

    if (!(target instanceof HTMLElement)) {
        return;
    }

    const wasOpen = target.classList.contains('show');

    window.setTimeout(() => {
        const isOpen = target.classList.contains('show');

        // TYPO3 handled the click successfully.
        if (isOpen !== wasOpen) {
            return;
        }

        Collapse.getOrCreateInstance(target, { toggle: false }).toggle();
    }, 0);
});

/**
 * Synchronize caret state for all Bootstrap collapse targets used by DCE
 * sections and FAL/IRRE records.
 *
 * This listens to Bootstrap lifecycle events and therefore works for native
 * TYPO3 handling as well as for the DCE fallback.
 */
function synchronizeCollapseTrigger(target, expanded) {
    if (!(target instanceof HTMLElement) || !target.id) {
        return;
    }

    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach((trigger) => {
        const targetSelector = trigger.getAttribute('data-bs-target');
        const href = trigger.getAttribute('href');
        const referencedId = targetSelector?.startsWith('#')
            ? targetSelector.slice(1)
            : (href?.startsWith('#') ? href.slice(1) : null);

        if (referencedId === target.id) {
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            trigger.classList.toggle('collapsed', !expanded);
        }
    });
}

document.addEventListener('shown.bs.collapse', (event) => {
    synchronizeCollapseTrigger(event.target, true);
});

document.addEventListener('hidden.bs.collapse', (event) => {
    synchronizeCollapseTrigger(event.target, false);
});
