import FormEngineValidation from"@typo3/backend/form-engine-validation.js";

const initDceCodeEditor = function (textarea) {
    const availableTemplates = textarea.closest('#dceConfigurationWizard').querySelector('.availableTemplates');
    if (availableTemplates) {
        availableTemplates.addEventListener('change', function (event) {
            if (this.value) {
                textarea.value = this.value;
                textarea.focus();
                this.value = '';
                FormEngineValidation.markFieldAsChanged(textarea);
            }
        });
    }

    const availableVariables = textarea.closest('#dceConfigurationWizard').querySelector('.availableVariables');
    if (availableVariables) {
        availableVariables.addEventListener('change', function (event) {
            if (this.value) {
                if (this.value.match(/^v:/)) {
                    insertCode(textarea, '{' + this.value.replace(/.*?:(.*)/gi, '$1') + '}')
                } else if (this.value.match(/^f:/)) {
                    insertCode(textarea, this.value.replace(/.*?:([\s\S]*)/gi, '$1'))
                }

                textarea.focus()
                this.value = '';
            }
        });
    }

    textarea.style.overflowY = 'hidden';
    textarea.style.boxSizing = 'border-box';

    textarea.addEventListener('change', function(event) {
        FormEngineValidation.markFieldAsChanged(event.target);
    });
    textarea.addEventListener('keyup', function() {
        adjustTextareaHeight(textarea);
    });

    indentCodeHandler(textarea);
    adjustTextareaHeight(textarea);
}

const insertCode = function(textarea, code) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;

    textarea.value = text.substring(0, start) + code + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + code.length;

    FormEngineValidation.markFieldAsChanged(textarea);
}

const indentCodeHandler = function(textarea) {
    textarea.addEventListener('keydown', function(event) {
        if (event.key === 'Tab') {
            event.preventDefault();

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const value = textarea.value;
            const selectedLinesStart = value.lastIndexOf('\n', start - 1) + 1;
            const selectedLinesEnd = end === value.length || value[end] === '\n' ? end : value.indexOf('\n', end);
            const selectedLines = value.substring(selectedLinesStart, selectedLinesEnd > -1 ? selectedLinesEnd : undefined);
            let newLines;

            if (event.shiftKey) {
                newLines = selectedLines.split('\n').map(line => line.startsWith('    ') ? line.substring(4) : line).join('\n');
            } else {
                newLines = selectedLines.split('\n').map(line => '    ' + line).join('\n');
            }

            if (selectedLines !== newLines) {
                const difference = newLines.length - selectedLines.length;
                textarea.value = value.substring(0, selectedLinesStart) + newLines + value.substring(selectedLinesEnd > -1 ? selectedLinesEnd : undefined);
                textarea.selectionStart = start + (selectedLinesStart === start ? difference : 0);
                textarea.selectionEnd = end + difference;
            }
        } else if (event.key === 'Enter') {
            event.preventDefault();

            const start = textarea.selectionStart;
            const value = textarea.value;
            const lineStart = value.lastIndexOf('\n', start - 1) + 1;
            const line = value.substring(lineStart, start);
            const indentation = line.match(/^(\s*)/)[0];

            const beforeCursor = value.substring(0, start);
            const afterCursor = value.substring(start);

            textarea.value = beforeCursor + '\n' + indentation + afterCursor;
            textarea.selectionStart = textarea.selectionEnd = start + indentation.length + 1;
        }
        FormEngineValidation.markFieldAsChanged(textarea);
    });
};

const adjustTextareaHeight = function(textarea) {
    let height;
    const lines = textarea.value.split('\n').length;
    const heightOffset = 15;
    const lineHeight = parseFloat(window.getComputedStyle(textarea).getPropertyValue('line-height'));

    if (isNaN(lineHeight)) {
        const fontSize = parseFloat(window.getComputedStyle(textarea).getPropertyValue('font-size'));
        height = (Math.ceil(fontSize * 1.2 * lines) + heightOffset) + 'px';
    } else {
        height = (lineHeight * lines + heightOffset) + 'px';
    }
    textarea.style.height = height;
};

const initEditor = function(element) {
    if (element.matches('textarea.dce-code-editor')) {
        initDceCodeEditor(element);
    }
}

document.querySelectorAll('textarea.dce-code-editor').forEach(initEditor);

const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    initEditor(node);
                    node.querySelectorAll('textarea.dce-code-editor').forEach(initEditor);
                }
            });
        }
    });
});
observer.observe(document.body, { childList: true, subtree: true });
