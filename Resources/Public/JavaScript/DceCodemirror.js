define([
    "./Contrib/codemirror/lib/codemirror",
    "./Contrib/codemirror/mode/htmlmixed/htmlmixed"
], function (Codemirror) {

    var storage = {
        codemirrorCycle: 0,
        codemirrorEditors: []
    };

    var initCodemirrorEditor = function (textareaSelector, mode) {
        var textarea = document.querySelector(textareaSelector);

        // Do not init the same textarea twice
        if (textarea.dataset.codemirrorCycle) {
            return;
        }

        var editor = Codemirror.fromTextArea(textarea, {
            mode: mode,
            htmlMode: true,
            indentUnit: 4,
            tabSize: 4,
            lineNumbers: true,
            indentWithTabs: true,
            styleActiveLine: true
        });

        storage.codemirrorCycle++;
        storage.codemirrorEditors.push(editor);
        textarea.dataset.codemirrorCycle = storage.codemirrorCycle;

        setTimeout(function () {
            editor.refresh();
        }, 100);

        document.addEventListener('click', function (event) {
            editor.refresh();
        });


        var availableTemplates = textarea.closest('#dceConfigurationWizard').querySelector('.availableTemplates');
        if (availableTemplates) {
            availableTemplates.addEventListener('change', function (event) {
                if (this.value) {
                    var editorId = textarea.dataset.codemirrorCycle;
                    var editor = storage.codemirrorEditors[editorId - 1];

                    editor.setValue($(this).val());
                    editor.focus();
                    this.value = '';
                }
            });
        }

        var availableVariables = textarea.closest('#dceConfigurationWizard').querySelector('.availableVariables');
        if (availableVariables) {
            availableVariables.addEventListener('change', function (event) {
                if (this.value) {
                    var editorId = textarea.dataset.codemirrorCycle;
                    var editor = storage.codemirrorEditors[editorId - 1];

                    if (this.value.match(/^v:/)) {
                        editor.replaceSelection('{' + this.value.replace(/.*?:(.*)/gi, '$1') + '}');
                    } else if (this.value.match(/^f:/)) {
                        editor.replaceSelection(this.value.replace(/.*?:([\s\S]*)/gi, '$1'));
                    }
                    editor.focus();
                    this.value = '';
                }
            });
        }
    };

    return {initCodeMirrorEditor: initCodemirrorEditor};
});
