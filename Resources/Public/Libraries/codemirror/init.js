var codeMirrorMode = codeMirrorMode || 'xml';

if (typeof(CodeMirror) === 'undefined') {
	var script = new Element('script', {
		type: 'text/javascript',
		src: '/typo3conf/ext/dce/Resources/Public/Libraries/codemirror/codemirror_custom.min.js'
	});
	$$('head')[0].appendChild(script);
}

watchForSymbol({
	symbol: 'CodeMirror',
	timeout: 5,
	onSuccess: function (symbol) {
		var editor = CodeMirror.fromTextArea(codearea, {
					mode: {
						name: codeMirrorMode
					},
					indentUnit: 4,
					tabSize: 4,
					lineNumbers: false,
					indentWithTabs: true
				}
		);
		parent.dce_editors.push(editor);

			// Provides available variables and add them to codemirror on select
		var variables = $(codearea.id.replace(/.*?_(.*)/gi, 'variables_$1'));
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
		var templates = $(codearea.id.replace(/.*?_(.*)/gi, 'templates_$1'));
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
				for (var i = 0; i < parent.dce_editors.length; i++) {
					parent.dce_editors[i].refresh();
				}
			};
		});
	}
});

// @see http://goo.gl/gAeau
function watchForSymbol(a){var b;if(!a||!a.symbol||!Object.isFunction(a.onSuccess)){throw"Missing required options"}a.onTimeout=a.onTimeout||Prototype.K;a.timeout=a.timeout||10;b=(new Date).getTime()+a.timeout*1e3;new PeriodicalExecuter(function(c){if(typeof window[a.symbol]!="undefined"){a.onSuccess(a.symbol);c.stop()}else if((new Date).getTime()>b){a.onTimeout(a.symbol);c.stop()}},.25)}