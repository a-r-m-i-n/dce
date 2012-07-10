var codemirrorEditors = codemirrorEditors || [];

/**
 * Initializes the CodeMirror editor for given textarea.
 *
 * @param textarea
 * @param string mode
 * @return void
 */
function initCodeMirrorEditor(textarea, mode) {
	if (mode === undefined) mode = 'xml';

	var editor = CodeMirror.fromTextArea(textarea, {
			mode: {
				name: mode
			},
			indentUnit: 4,
			tabSize: 4,
			lineNumbers: true,
			indentWithTabs: true
		}
	);

		// Set width of codemirror to avoid frame scrollbars
	var windowWidth = $$('#typo3-inner-docbody > h2')[0].getWidth();
	editor.getWrapperElement().style.width = windowWidth - 40 + 'px';
	codemirrorEditors.push(editor);

	window.setTimeout(function(){
		editor.refresh();
	},1);

		// Provides available variables and add them to codemirror on select
	var variables = $(textarea.id.replace(/.*?_(.*)/gi, 'variables_$1'));
	if (variables) {
		$(variables).onchange = function() {
			if (this.value) {
				if (this.value.match(/^v:/)) {
					editor.replaceSelection('{' + this.value.replace(/.*?:(.*)/gi, '$1') + '}');
				} else if (this.value.match(/^f:/)) {
					editor.replaceSelection(this.value.replace(/.*?:([\s\S]*)/gi, '$1'));
				}
				editor.focus();
				this.value = '';
			}
		}
	}
		// Provides some "ready to start" templates and add them to codemirror on select
	var templates = $(textarea.id.replace(/.*?_(.*)/gi, 'templates_$1'));
	if (templates) {
		$(templates).onchange = function() {
			if (this.value) {
				editor.setValue(this.value);
				editor.focus();
				this.value = '';
			}
		}
	}

		// Prevents a visibility bug in codemirror if it is initialized on a hidden tab page
	$$('.typo3-dyntabmenu a').each(function(){
		this.onclick = function(){
			for (var i = 0; i < codemirrorEditors.length; i++) {
				codemirrorEditors[i].refresh();
			}
		};
	});
}
