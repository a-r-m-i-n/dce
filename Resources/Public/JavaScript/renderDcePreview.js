document.observe("dom:loaded", function() {
	var placeholder = $('dcePreviewIframePlaceholder');
	if (!placeholder) return;

	var dyntabmenu = $$('.typo3-TCEforms .typo3-dyntabmenu')[0];
	var tds = $(dyntabmenu).getElementsBySelector('td:last');
	var lastTd = tds.pop();
	$(lastTd).observe('click', function(){
		var iFrame = new Element('iframe', {id: 'dcePreviewIframe', frameborder: '0', src: $(placeholder).readAttribute('data-src')});
		$(placeholder).insert({before: iFrame});
		$(placeholder).remove();
	});

	if ($(lastTd).hasClassName('tabact')) {
		$(lastTd).simulate('click');
	}
});


/**
 * Event.simulate(@element, eventName[, options]) -> Element
 *
 * - @element: element to fire event on
 * - eventName: name of event to fire (only MouseEvents and HTMLEvents interfaces are supported)
 * - options: optional object to fine-tune event properties - pointerX, pointerY, ctrlKey, etc.
 *
 *    $('foo').simulate('click'); // => fires "click" event on an element with id=foo
 *
 **/
(function(){var a={HTMLEvents:/^(?:load|unload|abort|error|select|change|submit|reset|focus|blur|resize|scroll)$/,MouseEvents:/^(?:click|mouse(?:down|up|over|move|out))$/};var b={pointerX:0,pointerY:0,button:0,ctrlKey:false,altKey:false,shiftKey:false,metaKey:false,bubbles:true,cancelable:true};Event.simulate=function(c,d){var e=Object.extend(b,arguments[2]||{});var f,g=null;c=$(c);for(var h in a){if(a[h].test(d)){g=h;break}}if(!g)throw new SyntaxError("Only HTMLEvents and MouseEvents interfaces are supported");if(document.createEvent){f=document.createEvent(g);if(g=="HTMLEvents"){f.initEvent(d,e.bubbles,e.cancelable)}else{f.initMouseEvent(d,e.bubbles,e.cancelable,document.defaultView,e.button,e.pointerX,e.pointerY,e.pointerX,e.pointerY,e.ctrlKey,e.altKey,e.shiftKey,e.metaKey,e.button,c)}c.dispatchEvent(f)}else{e.clientX=e.pointerX;e.clientY=e.pointerY;f=Object.extend(document.createEventObject(),e);c.fireEvent("on"+d,f)}return c};Element.addMethods({simulate:Event.simulate})})()