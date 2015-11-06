/*==============================================================================

                       DOMReady - window.onload alternative
                       ====================================
                       Copyright (c) 2007 Vyacheslav Smolin


Author:
-------
Vyacheslav Smolin (http://www.richarea.com, http://html2xhtml.richarea.com,
re@richarea.com)

About the script:
-----------------
The traditional approach to start working with DOM structure of a page is to
wait while page is loaded completely to be sure DOM is ready ;) This means that
window.onload event used for that will fire after all images and other content
is loaded.

If page contains a lot of such content then your functionality starting by
window.onload (eg menu) will be activated with a nonticable delay.

The smarter approach is not to wait when all the content your scripts do not
need will load, but use something like Mozilla's DOMContentLoaded event.

Browsers supported:
-------------
IE, Opera, Safari, Mozilla-based browsers (Firefox, Mozilla, etc).
For non-supported browsers window.onload is used.

Usage:
------
Please see example.html.

License:
--------
Free. Copyright information must stay intact.

Notes:
------
Dean Edwards's solution for IE has been used.
More info: http://dean.edwards.name/weblog/2005/09/busted/.

==============================================================================*/

var DOMReady = {

onDOMReadyHandler : function() {},

// returns true if listener is active, otherwise - false (that means that
// window.onload is used
listenDOMReady : function() {

var browser = navigator.userAgent;
var is_safari = /(safari|webkit)/i.test(browser);
var is_opera = /opera/i.test(browser);
var is_msie = /msie/i.test(browser);
var is_mozilla = /mozilla/i.test(browser) && !/(compatible|webkit)/i.test(browser);

	if (is_opera || is_mozilla){
		this.attachEvent(document, "DOMContentLoaded", this.onDOMReadyHandler);
		return true;
	}

	if (is_msie) {
		document.write('<script id="dr_ie_script" defer="true" src="https://javascript:false;"><\/script>');
		document.getElementById("dr_ie_script").onreadystatechange = function(){
			if (this.readyState == "complete") DOMReady.onDOMReadyHandler();
		};
		return true;
	}

	if (is_safari) {
		this.domReadyTimer = window.setInterval(function(){
			if (document.readyState == "loaded" ||
				document.readyState == "complete") {

				window.clearInterval(DOMReady.domReadyTimer);
				DOMReady.onDOMReadyHandler();

			}
		}, 10);

		return true;
	}


	// use onload event otherwise
	this.attachEvent(window, "load", DOMReady.onDOMReadyHandler);

	return false;
},

// timer (used with Safari)
domReadyTimer : null,

// set event handler
attachEvent : function(obj, event, handler) {

	if (obj.addEventListener) {
		obj.addEventListener(event, handler, false);
	} else {
		if (obj.attachEvent) {
			obj.attachEvent('on'+event, handler);
		}
	}
},


// remove event handler
detachEvent : function(obj, event, handler) {

	if (obj.removeEventListener) {
		obj.removeEventListener(event, handler, false);
	} else {
		if (obj.detachEvent) {
			obj.detachEvent('on'+event, handler);
		}
	}
}

};