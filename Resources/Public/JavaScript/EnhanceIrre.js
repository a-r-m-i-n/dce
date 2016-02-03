(function($){
	require([
		"../typo3conf/ext/dce/Resources/Public/JavaScript/Contrib/js.cookie.js"
	], function(Cookie) {
		if (Cookie.get('dceActiveIrreItem')) {
			$('#' + Cookie.get('dceActiveIrreItem')).find('.form-irre-header').trigger('click');
		}

		$(document).on('click', '.form-irre-header', function(){
			var panel = $(this).closest('.panel');
			var panelWasClosed = panel.hasClass('panel-collapsed');
			if (panelWasClosed) {
				// This click opened the panel, so store the current open field
				Cookie.set('dceActiveIrreItem', panel.attr('id'));
			} else {
				// This click closed the panel
				Cookie.remove('dceActiveIrreItem');
			}
		});
	});
})(TYPO3.jQuery);
