define([
    "jquery",
    "../../../../typo3conf/ext/dce/Resources/Public/JavaScript/Contrib/codemirror/lib/codemirror",
    "../../../../typo3conf/ext/dce/Resources/Public/JavaScript/Contrib/codemirror/mode/htmlmixed/htmlmixed"
], function ($, Codemirror) {

    var storage = {
        codemirrorCycle: 0,
        codemirrorEditors: []
    };

    var initCodemirrorEditor = function ($textarea, mode) {
        var textarea = $($textarea);
        var editor = Codemirror.fromTextArea(textarea.get(0), {
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
        textarea.data('codemirrorCycle', storage.codemirrorCycle);

        setTimeout(function () {
            editor.refresh();
        }, 100);

        $(document).on('click', function () {
            editor.refresh();
        });

        textarea.closest('#dceConfigurationWizard').find('.availableTemplates').change(function () {
            if ($(this).val()) {
                var textarea = $(this).next('div').find('textarea').eq(0);
                var editorId = textarea.data('codemirrorCycle');
                var editor = storage.codemirrorEditors[editorId - 1];

                editor.setValue($(this).val());
                editor.focus();
                $(this).val('');
            }
        });

        textarea.closest('#dceConfigurationWizard').find('.availableVariables').change(function () {
            if ($(this).val()) {
                var textarea = $(this).next('div').find('textarea').eq(0);
                var editorId = textarea.data('codemirrorCycle');
                var editor = storage.codemirrorEditors[editorId - 1];

                if ($(this).val().match(/^v:/)) {
                    editor.replaceSelection('{' + $(this).val().replace(/.*?:(.*)/gi, '$1') + '}');
                } else if ($(this).val().match(/^f:/)) {
                    editor.replaceSelection($(this).val().replace(/.*?:([\s\S]*)/gi, '$1'));
                }
                editor.focus();
                $(this).val('');
            }
        });
    };

    return {initCodeMirrorEditor: initCodemirrorEditor};
});
