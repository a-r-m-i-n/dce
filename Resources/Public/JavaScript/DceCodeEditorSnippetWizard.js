import { EditorState, Prec, StateEffect } from '@codemirror/state';
import { indentUnit } from '@codemirror/language';

const selector = 'select[data-dce-code-editor-snippet]';
const indentation = Prec.highest([
    indentUnit.of('    '),
    EditorState.tabSize.of(4),
]);
const configuredViews = new WeakSet();

const configureIndentation = function (codeEditor) {
    const view = codeEditor?.editorView;
    const isDceEditor = codeEditor?.closest('.form-wizards-wrap')?.querySelector(selector);
    if (!view || !isDceEditor || configuredViews.has(view)) {
        return;
    }

    view.dispatch({
        effects: StateEffect.appendConfig.of(indentation),
    });
    configuredViews.add(view);
};

const configureIndentationFromEvent = function (event) {
    const codeEditor = event.composedPath().find(node =>
        node instanceof HTMLElement && node.matches('typo3-t3editor-codemirror')
    );
    configureIndentation(codeEditor);
};

document.addEventListener('focusin', configureIndentationFromEvent, true);
document.addEventListener('keydown', configureIndentationFromEvent, true);

class DceCodeEditorSnippetWizard extends HTMLElement {
    connectedCallback() {
        const fieldWizardContainer = this.parentElement;
        const editorContainer = this.closest('.form-wizards-wrap')
            ?.querySelector(':scope > .form-wizards-item-element');
        if (!editorContainer || fieldWizardContainer === editorContainer) {
            return;
        }

        editorContainer.prepend(this);
        if (fieldWizardContainer.classList.contains('form-wizards-item-bottom')
            && !fieldWizardContainer.children.length
            && !fieldWizardContainer.textContent.trim()
        ) {
            fieldWizardContainer.remove();
        }
    }
}

if (!customElements.get('dce-code-editor-snippet-wizard')) {
    customElements.define('dce-code-editor-snippet-wizard', DceCodeEditorSnippetWizard);
}

document.addEventListener('change', function (event) {
    if (!(event.target instanceof HTMLSelectElement)
        || !event.target.matches(selector)
        || '0' === event.target.value
    ) {
        return;
    }

    const select = event.target;
    const codeEditor = select.closest('.form-wizards-wrap')?.querySelector('typo3-t3editor-codemirror');
    const textarea = codeEditor?.querySelector('textarea');
    if (!codeEditor || !textarea) {
        return;
    }

    let snippet = select.value;
    const replaceDocument = 'replace' === select.dataset.dceCodeEditorSnippet;
    if (!replaceDocument) {
        if (snippet.startsWith('v:')) {
            snippet = '{' + snippet.substring(2) + '}';
        } else if (snippet.startsWith('f:')) {
            snippet = snippet.substring(2);
        } else {
            select.value = '0';
            return;
        }
    }

    if (codeEditor.editorView) {
        configureIndentation(codeEditor);
        const view = codeEditor.editorView;
        const selection = view.state.selection.main;
        const from = replaceDocument ? 0 : selection.from;
        const to = replaceDocument ? view.state.doc.length : selection.to;

        view.dispatch({
            changes: { from, to, insert: snippet },
            selection: { anchor: from + snippet.length },
            scrollIntoView: true,
        });
        select.value = '0';
        view.focus();
        return;
    }

    const from = replaceDocument ? 0 : textarea.selectionStart;
    const to = replaceDocument ? textarea.value.length : textarea.selectionEnd;
    textarea.setRangeText(snippet, from, to, 'end');
    textarea.dispatchEvent(new Event('change', { bubbles: true }));
    select.value = '0';
    textarea.focus();
});
