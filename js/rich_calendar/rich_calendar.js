/*==============================================================================

                                 Rich Calendar 1.0
                                 =================
                    Copyright (c) 2007-2008 Vyacheslav Smolin


Author:
-------
Vyacheslav Smolin (http://www.richarea.com, http://html2xhtml.richarea.com,
re@richarea.com)

About the script:
-----------------
Rich Calendar is 100% JavaScript calendar script. No pop-up windows.
Skinnable and multilingual. Multiple calendar instances on one page.
Allows to embed calendar objects in html document or position them absolutely
using flexible horizontal and vertical alignment options.
Supports any date formats. Language dependant date formats. User-defined
behaviour possible.
Could be associated with an element (eg text field) to read/write date from/to.
Pop-up mode (calendar closes on mouse click outside it).

Requirements:
-------------
Rich Calendar works in IE, Mozilla-based browsers such as Firefox, Opera 9+,
and Safari 3.0.

Usage:
------
Please see example.html.

Demo:
-----
http://www.richarea.com/demo/rich_calendar/

License:
--------
Free. Copyright information must stay intact.
We'd appreciate if you place a direct link to us somewhere on your site.

==============================================================================*/

// Rich Calendar
RichCalendar = function(target_obj, show_time) {

	// value
	this.value = '';

	// format
	this.format = '%Y-%m-%d';

	// Week Day to start with (0 - Sunday, 1 - Monday, etc...)
	this.start_week_day = 1;

	// iframe object to show calendar object in
	this.iframe_obj = null;

	// path to calendar css and js files
	this.lib_path = 'rich_calendar/';

	// DOM object to take/set date from/to
	this.target_obj = target_obj;

	// show time
	this.show_time = show_time;

	// function called when calendar value changes
	this.user_onchange_handler = null;

	// function called when data choice is cancelled
	this.user_onclose_handler = null;

	// function called when mouse clicked outside calendar with auto_close set
	// to true after it is closed
	this.user_onautoclose_handler = null;

	// default language
	this.default_lang = 'en';

	// language
	this.language = 'en';

	// current date
	this.date = new Date();
/*
this.date.setFullYear(2008);
this.date.setMonth(1);
this.date.setDate(29);
*/
//this.date.setMonth(11);
//this.date.setDate(31);

	// calendar skin name
	this.skin = '';

	// calendar closes automatically on click outside it
	this.auto_close = true;

	// element which value is taken to initilize calendar and where calendar
	// returns date if user defined function to return date is not specified
	this.value_el = null;

	// specifies calendar positioning - absolute by default
	this.position = null;

}

RichCalendar.is_ie = /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent);

// Static functions
RichCalendar.get_iframe_styles = function() {
var i;
var j;

	var styles = document.styleSheets;
	var sheets_num = styles.length;

	var style_text = '';
    for (i=0; i<sheets_num; i++) {
		if (RichCalendar.is_ie) {
			// take all calendar styles in IE as cannot take text of each rule
			if (/rich_calendar.css$/.test(styles[i].href)) {
				style_text += styles[i].cssText;
				break;
			}
		} else {
			var rules = null;
			try {
				if (RichCalendar.is_ie) {
					rules = styles[i].rules;
				} else {
					rules = styles[i].cssRules;
				}
			} catch(error) {
				continue;
			}

			if (rules != null) {
				rules_num = rules.length;

				for (j=0; j<rules_num; j++) {
					var rule_value = rules[j].selectorText;
					if (/rc_iframe/.test(rule_value)) {
						style_text += rules[j].cssText;
					}
				}
			}
		}
	}

	return style_text;
}


RichCalendar.attach_event = function(obj, event, handler) {

	if (obj.addEventListener) {
		obj.addEventListener(event, handler, false);
	} else {
		if (obj.attachEvent) {
			obj.attachEvent('on'+event, handler);
		}
	}
}


RichCalendar.detach_event = function(obj, event, handler) {

	if (obj.removeEventListener) {
		obj.removeEventListener(event, handler, false);
	} else {
		if (obj.detachEvent) {
			obj.detachEvent('on'+event, handler);
		}
	}
}


// add event handlers to object obj
RichCalendar.attach_events = function(obj) {
	RichCalendar.attach_event(obj, 'click', RichCalendar.onclick);
	RichCalendar.attach_event(obj, 'mouseover', RichCalendar.onmouseover);
	RichCalendar.attach_event(obj, 'mouseout', RichCalendar.onmouseout);
}


// remove event handlers set to object obj
RichCalendar.detach_events = function(obj) {
	RichCalendar.detach_event(obj, 'click', RichCalendar.onclick);
	RichCalendar.detach_event(obj, 'mouseover', RichCalendar.onmouseover);
	RichCalendar.detach_event(obj, 'mouseout', RichCalendar.onmouseout);
}


// calendar onclick event handler
RichCalendar.onclick = function(e) {

//alert(e + ' => ' + e.srcElement + ' => ' + e.target + ' => ' + window.event);
//for (var i in e) alert(i + ' => ' + e[i]);

var event = RichCalendar.get_event(e);
var obj = RichCalendar.get_target_object(e);

	if (!obj) return;

var cal = obj.calendar;

var cur_year = cal.date.getFullYear();
var cur_month = cal.date.getMonth();
var cur_day = cal.date.getDate();

//alert(obj.rc_object_code);
	switch (obj.rc_object_code) {
		case 'day':
//			alert(obj.day_num);
			cal.date.setDate(obj.day_num);
			break;
		case 'prev_year':
			// determine number of days in prev year
			cal.date.setDate(1);
			cal.date.setFullYear(cur_year-1);
			var month_days = RichCalendar.get_month_days(cal.date);

			// prevent jumping to next month
			if (cur_day > month_days) {
				cal.date.setDate(month_days);
			} else {
				cal.date.setDate(cur_day);
			}

			cal.show_date();
			break;
		case 'prev_month':
			// determine number of days in prev month
			cal.date.setDate(1);
			cal.date.setMonth(cur_month-1);
			var month_days = RichCalendar.get_month_days(cal.date);

			// prevent jumping to next month
			if (cur_day > month_days) {
				cal.date.setDate(month_days);
			} else {
				cal.date.setDate(cur_day);
			}

			cal.show_date();
			break;
		case 'next_month':
			// determine number of days in prev month
			cal.date.setDate(1);
			cal.date.setMonth(cur_month+1);
			var month_days = RichCalendar.get_month_days(cal.date);

			// prevent jumping to next month
			if (cur_day > month_days) {
				cal.date.setDate(month_days);
			} else {
				cal.date.setDate(cur_day);
			}

			cal.show_date();
			break;
		case 'next_year':
			// determine number of days in next year
			cal.date.setDate(1);
			cal.date.setFullYear(cur_year+1);
			var month_days = RichCalendar.get_month_days(cal.date);

			// prevent jumping to next month
			if (cur_day > month_days) {
				cal.date.setDate(month_days);
			} else {
				cal.date.setDate(cur_day);
			}

			cal.show_date();
			break;
		case 'today':
			var today = new Date();
			today.setHours(cal.date.getHours());
			today.setMinutes(cal.date.getMinutes());
			today.setSeconds(cal.date.getSeconds());

			cal.date = today;
			cal.show_date();

			break;
		case 'clear':
			// handle clear request
			if (cal.value_el) {
				cal.value_el.value = '';
			}
			break;
		case 'close':
			// handle close request
			cal.onclose_handler();
			break;
		case 'week_day':
//alert(obj.innerHTML);
			cal.start_week_day = obj.week_day_num;
			cal.show_date();
			break;
		default:
			break;
	}


	// handle close request
	if (obj.rc_object_code != 'week_day') {
		cal.onchange_handler(obj.rc_object_code);
	}

	// handle date change


	// hide all other auto closing calendars
	RichCalendar.hide_auto_close(cal);

}


// calendar onmouseover event handler
RichCalendar.onmouseover = function(e) {

//alert(e + ' => ' + e.srcElement + ' => ' + e.target + ' => ' + window.event);
//for (var i in e) alert(i + ' => ' + e[i]);

var event = RichCalendar.get_event(e);
var obj = RichCalendar.get_target_object(e);

	if (!obj) return;

var cal = obj.calendar;

var cur_year = cal.date.getFullYear();
var cur_month = cal.date.getMonth();
var cur_day = cal.date.getDate();

	switch (obj.rc_object_code) {
		case 'day':
			var date = new Date(cal.date);
			date.setDate(obj.day_num);
			cal.set_footer_text(cal.get_formatted_date(cal.text('footerDateFormat'), date));

			// highlight day cell and its row
			RichCalendar.add_class(obj, "rc_highlight");
			RichCalendar.add_class(obj.parentNode, "rc_highlight");

			break;
		case 'clear':
		case 'today':
		case 'close':
		case 'prev_year':
		case 'prev_month':
		case 'next_month':
		case 'next_year':
			cal.set_footer_text(cal.text(obj.rc_object_code));
			break;
		case 'week_day':
			if (obj.week_day_num != cal.start_week_day) {
				var day_names = cal.text("dayNames");
				var name = day_names[obj.week_day_num];
				var text = cal.text("make_first");
				text = text.replace("%s", name);
			} else {
				var text = cal.text('footerDefaultText');
			}
			cal.set_footer_text(text);
			break;
		default:
			cal.set_footer_text(cal.text('footerDefaultText'));
			break;
	}

}


// calendar onmouseout event handler
RichCalendar.onmouseout = function(e) {

//alert(e + ' => ' + e.srcElement + ' => ' + e.target + ' => ' + window.event);
//for (var i in e) alert(i + ' => ' + e[i]);

var event = RichCalendar.get_event(e);
var obj = RichCalendar.get_target_object(e);

	if (!obj) return;

var cal = obj.calendar;

	cal.set_footer_text(cal.text('footerDefaultText'));

	// un-highlight day cell and its row
	RichCalendar.remove_class(obj, "rc_highlight");
	RichCalendar.remove_class(obj.parentNode, "rc_highlight");

}


// document onmousedown event handler
RichCalendar.document_onmousedown = function(e) {
var event = RichCalendar.get_event(e);
var obj = RichCalendar.get_target_object(e);

	if (!obj) return;

var el = obj;
var cal = null;

	while (el) {
		if (el.className && el.className.match(/^rc_iframe_body/) &&
			el.tagName.toUpperCase() == 'BODY') {

			cal = el.calendar;
			break;
		}
		el = el.parentNode;
	}

	// close all not active calendars
	RichCalendar.hide_auto_close(cal);

}


// hide all calendars that should autoclose except cal and remove
// them from RichCalendar.active_calendars
RichCalendar.hide_auto_close = function(cal) {
var active_cals = [];
var i;

	for (i=0; i<RichCalendar.active_calendars.length; i++) {
		var cur_cal = RichCalendar.active_calendars[i];
		if (cur_cal.auto_close && cur_cal != cal) {

			cur_cal.hide();

			if (cur_cal.user_onautoclose_handler) {
				cur_cal.user_onautoclose_handler(this);
			}

		} else {
			active_cals[active_cals.length] = cur_cal;
		}
	}

	RichCalendar.active_calendars = active_cals;
}


// remove calendar cal from list RichCalendar.active_calendars of active
// calendars
RichCalendar.make_inactive = function(cal) {
var active_cals = [];
var i;

	for (i=0; i<RichCalendar.active_calendars.length; i++) {
		var cur_cal = RichCalendar.active_calendars[i];
		if (cur_cal != cal) {
			active_cals[active_cals.length] = cur_cal;
		}
	}

	RichCalendar.active_calendars = active_cals;
}


// returns event object
RichCalendar.get_event = function(e) {

	return e||window.event;

}


// returns event target object
RichCalendar.get_target_object = function(e) {

	return e.target?e.target:(e.srcElement?e.srcElement:window.event.srcElement);

}


// returns skin suffics for skin class name
RichCalendar.skin_suffix = function(skin) {
	return (skin != '')?('_' + skin):'';
}


// return number of days in month
RichCalendar.get_month_days = function(date, month) {
var year = date.getFullYear();

	if (month) {
		month = parseInt(month);
		if (month <= 0 || month > 11) month = null;
	}

	if (!month) {
		month = date.getMonth();
	}

	if (month==1 && RichCalendar.is_leap_year(year)) {
		return 29;
	} else {
//alert(month + ' -> ' + RichCalendar.month_days[month]);
		return RichCalendar.month_days[month];
	}

}


// return true if year is a leap year
RichCalendar.is_leap_year = function(year) {
	return (year%4==0 && year%100!=0 || year%400==0) ? true : false;
}


// return day of the year
RichCalendar.get_day_of_year = function(date) {
var now = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0);
var year_start = new Date(date.getFullYear(), 0, 0, 0, 0, 0);

// milliseconds in day
var day_in_msecs = 24*60*60*1000;

	return Math.floor((now - year_start) / day_in_msecs);

}


// add class to element
RichCalendar.add_class = function(el, class_name) {

	RichCalendar.remove_class(el, class_name);
	el.className += " " + class_name;

}


// remove class from element
RichCalendar.remove_class = function(el, class_name) {

	if (!el || !el.className) return

	var new_class_parts = [];
	var class_parts = String(el.className).split(" ");
	var i;
	for (i=0; i<class_parts.length; i++) {
		if (class_parts[i] != "" && class_parts[i] != class_name) {
			new_class_parts[new_class_parts.length] = class_parts[i];
		}
	}

	el.className = new_class_parts.join(" ");
}


// return position of object obj; dont go above stop_obj in DOM structure
RichCalendar.get_obj_pos = function(obj, stop_obj){
	var pos = Array(0,0);

	if (!obj) return pos;

	var iniObj = obj;

	while (obj && stop_obj != obj) {

		pos[0] += obj.offsetLeft;
		pos[1] += obj.offsetTop;

		if (obj != iniObj) {
			pos[0] += parseInt(RichCalendar.get_style(obj, "borderTopWidth"), 10) || 0;
			pos[1] += parseInt(RichCalendar.get_style(obj, "borderLeftWidth"), 10) || 0;
		}

		obj = obj.offsetParent;

	}


	var obj = iniObj;

	while (obj && stop_obj != obj && obj.tagName.toLowerCase() != 'body') {
		pos[0] -= obj.scrollLeft;
		pos[1] -= obj.scrollTop;

		obj = obj.parentNode;
	}

	return pos;
}

// return current style value
RichCalendar.get_style = function (el, name) {
var view = document.defaultView;

	if (view && view.getComputedStyle) {
		var st = view.getComputedStyle(el, "");
		return st[name];
	}

//		if (document.defaultView.getComputedStyle(obj, '').getPropertyValue('position') == 'absolute') break;

	var v;
	if (v = el.currentStyle) {
		return v[name];
	}

	if (v = el.style[name]) {
		return v;
	}

}

// array of text data in various languages
RichCalendar.rc_lang_data = [];

// number of days in months
RichCalendar.month_days = [31,28,31,30,31,30,31,31,30,31,30,31];

// currently shown calendars
RichCalendar.active_calendars = [];

// true if all document handlers are set
RichCalendar.handlert_set = false;


// Calendar API


// show calendar inside/before/after (defined by argument position) element el
// if any specified or in the point specified (if any)
RichCalendar.prototype.show = function(x, y, el, position) {

	if (!this.value_el) {
		this.value_el = el;
	}

	this.position = position;

	this.iframe_obj = document.createElement('IFRAME');
	this.iframe_obj.className = 'rc_calendar'+RichCalendar.skin_suffix(this.skin);
	this.iframe_obj.setAttribute('scrolling', 'no');
	this.iframe_obj.setAttribute('src','javascript:false;');
	this.iframe_obj.calendar = this;


	// relative positioning
	if (this.is_relative_position(position)) {
		switch (position) {
			case "before":
				if (el.parentNode) {
					el.parentNode.insertBefore(this.iframe_obj, el);
				}
				break;
			case "after":
				if (el.parentNode) {
					el.parentNode.insertBefore(this.iframe_obj, el.nextSibling);
				}
				break;
			case "child":
			default:
				el.appendChild(this.iframe_obj);
				this.position = 'child';
				break;
		}

	} else { // absolute positioning

		this.iframe_obj.style.position = 'absolute';

		// move the iframe to the position specified
		var left = parseInt(x);
		var top = parseInt(y);
		if (typeof(x) == 'number' && typeof(y) == 'number') {
			this.iframe_obj.style.left = x + 'px';
			this.iframe_obj.style.top = y + 'px';
		}
		this.iframe_obj.style.border = '1px solid #000000';
		this.iframe_obj.value = this.value;

		document.body.appendChild(this.iframe_obj);

	}

	// styles to add to iframe
	var iframe_styles = RichCalendar.get_iframe_styles();

	// put calendar content into the iframe
	var iframe_content = '' +
'<html>' +
'<head>' +
'<style type="text/css">'+iframe_styles+'</style>' +
'</head>' +
'<body class="rc_iframe_body' + RichCalendar.skin_suffix(this.skin) + '" id="rc_body">' +
'</body></html>' +
	'';


	this.iframe_doc = this.iframe_obj.contentWindow.document;
	this.iframe_doc.open();
	this.iframe_doc.write(iframe_content);
	this.iframe_doc.close();

	RichCalendar.attach_event(this.iframe_doc, 'mousedown', RichCalendar.document_onmousedown);



	this.body_obj = this.iframe_doc.getElementById('rc_body');
	this.body_obj.calendar = this;
	// main table
	this.table_obj = this.iframe_doc.createElement('TABLE');
	this.table_obj.className = 'rc_table';
	this.table_obj.setAttribute('id', 'rc_iframe_table');
	this.table_obj.cellSpacing = 0;
	this.table_obj.cellPadding = 0;
	// store reference to the calendar
	this.table_obj.calendar = this;

	// header row
	this.head_tr = this.table_obj.insertRow(0);
	this.head_tr.className = 'rc_head_tr';

	this.clear_td = this.head_tr.insertCell(0);
	this.clear_td.innerHTML = 'c';
	this.clear_td.rc_object_code = 'clear';
	this.clear_td.calendar = this;
	RichCalendar.attach_events(this.clear_td);

//this.clear_td.className = 'rc_head_tr';
	this.head_td = this.head_tr.insertCell(1);
//this.head_td.className = 'rc_head_tr';
	this.head_td.colSpan = 5;
//this.head_td.innerHTML = 'asdf';
	this.close_td = this.head_tr.insertCell(2);
	this.close_td.innerHTML = 'x';
	this.close_td.rc_object_code = 'close';
	this.close_td.calendar = this;
	RichCalendar.attach_events(this.close_td);
//this.close_td.className = 'rc_head_tr';

	// navigation row
	this.nav_tr = this.table_obj.insertRow(1);
	this.nav_tr.className = 'rc_nav_tr';

	this.prev_year_td = this.nav_tr.insertCell(0);
	this.prev_year_td.innerHTML = '&#x00ab;';
	this.prev_year_td.rc_object_code = 'prev_year';
	this.prev_year_td.calendar = this;
	RichCalendar.attach_events(this.prev_year_td);

	this.prev_month_td = this.nav_tr.insertCell(1);
	this.prev_month_td.innerHTML = '&#x2039;';
	this.prev_month_td.rc_object_code = 'prev_month';
	this.prev_month_td.calendar = this;
	RichCalendar.attach_events(this.prev_month_td);

	this.today_td = this.nav_tr.insertCell(2);
	this.today_td.colSpan = 3;
	this.today_td.innerHTML = this.text('today');
	this.today_td.rc_object_code = 'today';
	this.today_td.calendar = this;
	RichCalendar.attach_events(this.today_td);

	this.next_month_td = this.nav_tr.insertCell(3);
	this.next_month_td.innerHTML = '&#x203a;';
	this.next_month_td.rc_object_code = 'next_month';
	this.next_month_td.calendar = this;
	RichCalendar.attach_events(this.next_month_td);

	this.next_year_td = this.nav_tr.insertCell(4);
	this.next_year_td.innerHTML = '&#x00bb;';
	this.next_year_td.rc_object_code = 'next_year';
	this.next_year_td.calendar = this;
	RichCalendar.attach_events(this.next_year_td);

	// weekdays row
	this.wd_tr = this.table_obj.insertRow(2);
	this.wd_tr.className = 'rc_wd_tr';

	var i;
//	var day_names = this.text('dayNamesShort');
	for (i=0; i<7; i++) {
//		var wd = (i+this.start_week_day)%7;

		var td = this.wd_tr.insertCell(i);
		td.rc_object_code = 'week_day';
		td.calendar = this;
		RichCalendar.attach_events(td);
//		td.innerHTML = day_names[wd];

//		if (typeof(weekend_days[wd]) != "undefined") {
//			td.className = "rc_weekend_head";
//		}
	}


	// calendar rows (initially create min number of rows necessary - 4)
	var rows_num = 4;
	var row_indx;
	var cell_indx;
	this.cal_tr = [];

	for (row_indx=0; row_indx<rows_num; row_indx++) {
		this.create_cal_row(row_indx);
/*
		this.cal_tr[row_indx] = this.table_obj.insertRow(3+row_indx);
		this.cal_tr[row_indx].className = 'rc_cal_tr';

		for (cell_indx=0; cell_indx<7; cell_indx++) {
			var td = this.cal_tr[row_indx].insertCell(cell_indx);
			td.innerHTML = row_indx + '-' + cell_indx;
		}
*/
	}


	if (this.show_time) {
		// create time row if necessary
		this.time_tr = this.table_obj.insertRow(rows_num+3);
		this.time_tr.className = 'rc_time_tr';
		var td = this.time_tr.insertCell(0);
		td.colSpan = 2;
		td.innerHTML = this.text('time') + ':';
	
		var td = this.time_tr.insertCell(1);
		td.colSpan = 3;

		this.hours_obj = this.createElement('INPUT', td);
		this.hours_obj.className = 'rc_hours';
		this.hours_obj.setAttribute('size', 1);
		this.hours_obj.setAttribute('maxlength', 2);

		this.colon_span = this.createElement('SPAN', td);
		this.colon_span.className = 'rc_colon_span';
		this.colon_span.innerHTML = '&nbsp;:&nbsp;';

		this.mins_obj = this.createElement('INPUT', td);
		this.mins_obj.className = 'rc_mins';
		this.mins_obj.setAttribute('size', 1);
		this.mins_obj.setAttribute('maxlength', 2);
	
		var td = this.time_tr.insertCell(2);
		td.colSpan = 2;
		td.innerHTML = '&nbsp;';
	}

	// footer row

	this.footer_tr = this.table_obj.insertRow(rows_num+3+(this.show_time?1:0));
	this.footer_tr.className = 'rc_footer_tr';
	this.footer_td = this.footer_tr.insertCell(0);
	this.footer_td.colSpan = 7;
	this.footer_td.innerHTML = this.text('footerDefaultText');


	this.body_obj.appendChild(this.table_obj);

	// create a DIV element to determine size of calendar
	this.size_div = document.createElement('DIV');
	this.size_div.className = this.body_obj.className;
	this.size_div.style.position = "absolute";
	this.size_div.style.left = "-1000px";
	this.size_div.style.top = "-1000px";
	document.body.appendChild(this.size_div);


	// show current date in calendar
	this.show_date();


	// set document handlers if not set yet
	if (!RichCalendar.handlers_set) {
		RichCalendar.attach_event(document, 'mousedown', RichCalendar.document_onmousedown);
		RichCalendar.handlers_set = true;
	}

	// store this calendar in array of active calendars
	RichCalendar.active_calendars[RichCalendar.active_calendars.length] = this;

//alert(this.body_obj.innerHTML);
}


// hide calendar (destroy iframe object)
RichCalendar.prototype.hide = function() {
	if (this.iframe_obj) {
		this.iframe_obj.parentNode.removeChild(this.iframe_obj);
		this.iframe_obj = null;
	}

	RichCalendar.make_inactive(this);
}


// show calendar inside/before/after (defined by argument position) element el
// ie relative to element el or 
RichCalendar.prototype.show_at_element = function(el, position) {

	if (typeof(el) != "object" || !el) return;

	// relative positioning
	if (this.is_relative_position(position)) {
		this.show(null, null, el, position);
		return;
	}
/*
	switch (position) {
		case "before":
		case "after":
		case "child":
			this.show(null, null, el, position);
			return;
		default:
			break;
	}
*/

	// absolute positioning
	var el_pos = RichCalendar.get_obj_pos(el);
	// negative coordinates to make calendar invisible for a while
	// as cannot determine right coordinates right now (calendar size is not
	// known yet right after this.show worked)
	var x = -1000;
	var y = -1000;

	this.show(x, y, el, position);


	// fix position (need to do this later then calendar is shown as
	// size of calendar could change in this.show(x, y)
//	var cal = this;
//	window.setTimeout(function(){cal.fix_position(el, position)}, 5);

}

// fix position of calendar
RichCalendar.prototype.fix_position = function(el) {
var position = this.position;

	if (this.is_relative_position(position)) {
		return;
	}

	if (!el) {
		el = this.value_el;
	}

//	alert(el.getAttribute("id") + " => " + position);

	var aligns = String(position).split("-");
	if (aligns.length == 2) {

		var el_pos = RichCalendar.get_obj_pos(el);
//alert(el_pos + ' => ' + el.offsetHeight);
		var x = el_pos[0];
		var y = el_pos[1] + el.offsetHeight;

		// iframe border thikness
		var border_width = parseInt(this.iframe_obj.style.borderWidth);

		var cal_width = parseInt(this.iframe_obj.width) + 2*border_width;
		var cal_height = parseInt(this.iframe_obj.height) + 2*border_width;

//alert('!: ' + cal_width + ' => ' + cal_height);
		// horizontal alignment
		switch (aligns[0]) {
			case "left":
				x -= cal_width;
				break;
			case "center":
				x += (el.offsetWidth - cal_width) / 2;
				break;
			case "right":
				x += el.offsetWidth;
				break;
			case "adj_right":
				x += el.offsetWidth - cal_width;
				break;
			default:
				break;
		}

		// vertical alignment
		switch (aligns[1]) {
			case "top":
				y -= el.offsetHeight + cal_height;
				break;
			case "center":
				y += (el.offsetHeight - cal_height) / 2 - el.offsetHeight;
				break;
			case "bottom":
				break;
			case "adj_bottom":
				y -= cal_height;
				break;
			default:
				break;
		}

		this.iframe_obj.style.left = x + 'px';
		this.iframe_obj.style.top = y + 'px';

		this.iframe_obj.style.visibility = 'visible';
	}

}


// return true if calendar is relatively positioned
RichCalendar.prototype.is_relative_position = function(position) {
	switch (position) {
		case "before":
		case "after":
		case "child":
			return true;
		default:
			return false;
	}
}


// creates an element in iframe
RichCalendar.prototype.createElement = function(tagName, parent) {

var el = this.iframe_doc.createElement(tagName);

	if (parent) {
		parent.appendChild(el);
	}

	return el;
}


// return text data desired
RichCalendar.prototype.text = function(name, language) {

	if (typeof(language) == "undefined") {
		language = this.language;
	}

	if (typeof(RichCalendar.rc_lang_data[language]) != "undefined") {
		return typeof(RichCalendar.rc_lang_data[language][name]) != "undefined"?RichCalendar.rc_lang_data[language][name]:'';
	}

	return typeof(RichCalendar.rc_lang_data[this.default_language][name]) != "undefined"?RichCalendar.rc_lang_data[this.default_language][name]:'';

}

// show date in calendar
RichCalendar.prototype.show_date = function() {
	// update week days row

	// numbers of weekend days
	var weekend_days = this.get_weekend_days();

	var i;
	var day_names = this.text('dayNamesShort');
	for (i=0; i<7; i++) {
		var wd = (i+this.start_week_day)%7;

		var td = this.wd_tr.cells[i];
		td.innerHTML = day_names[wd];

		if (typeof(weekend_days[wd]) != "undefined") {
			td.className = "rc_weekend_head";
		} else {
			td.className = "";
		}

//		td.rc_object_code = 'week_day';
//		td.calendar = this;
		td.week_day_num = wd;
//		RichCalendar.attach_events(td);
	}


var month_days = RichCalendar.get_month_days(this.date);
//	alert(month_days);

// first day of the same month and year as this.date
var date = new Date(this.date);
	date.setDate(1);
var week_day = (date.getDay()+7-this.start_week_day)%7+1;
//	alert(week_day);

// current data
var cur_year = this.date.getFullYear();
var cur_month = this.date.getMonth();
var cur_day = this.date.getDate();
//alert(cur_year + ' => ' + cur_month + ' => ' + cur_day);

// today
var today = new Date();
var today_year = today.getFullYear();
var today_month = today.getMonth();
var today_day = today.getDate();

// 

	var month_names = this.text('monthNames');
	this.head_td.innerHTML = month_names[cur_month] + ', ' + cur_year;


	var row;
	var day;
	var days = 0;
	var last_row;
	for (row=0; row<6; row++) {

		// all days are shown => just check if need to remove unused rows
		if (days == month_days) {
			if (this.cal_tr[last_row+1]) {
				this.cal_tr[last_row+1].parentNode.removeChild(this.cal_tr[last_row+1]);
				this.cal_tr[row] = null;
			}
			continue;
		}

		for (day=0; day<7; day++) {

			if (!this.cal_tr[row]) {
				this.create_cal_row(row);
			}

			var cur_tr = this.cal_tr[row];
			var cell = cur_tr.cells[day];
			cell.className = "";
			// should remove or IE attach the same event several times
			RichCalendar.detach_events(cell);

			if (row==0 && day+1 < week_day || days == month_days) {
				var td_text = '&nbsp;';

//				RichCalendar.detach_events(cell);
			} else {
				var day_num = days+1;
				var td_text = day_num;
				days++;

				cell.rc_object_code = 'day';
				cell.day_num = day_num;
				cell.calendar = this;
				RichCalendar.attach_events(cell);

				// hilight current date
				if (cur_day == day_num) {
					RichCalendar.add_class(cell, "rc_current");
				}

				// hilight today date
				if (day_num == today_day &&
					cur_month == today_month &&
					cur_year == today_year) {
					RichCalendar.add_class(cell, "rc_today");
				}


				var wd = (day+this.start_week_day)%7;

				// hilight weekend days
				if (typeof(weekend_days[wd]) != "undefined") {
					RichCalendar.add_class(cell, "rc_weekend_day");
				} else {
					RichCalendar.remove_class(cell, "rc_weekend_day");
				}

			}
			cell.innerHTML = td_text;

			if (days == month_days) {
				last_row = row;
			}
		}
	}


	// set time
	if (this.show_time && this.hours_obj && this.mins_obj) {
		var hours = this.date.getHours();
		if (hours < 10) hours = '0' + hours;
		var mins = this.date.getMinutes();
		if (mins < 10) mins = '0' + mins;

		this.hours_obj.value = hours;
		this.mins_obj.value = mins;
	}

	// change size of the iframe to fit to its content
/*
	var table_obj = this.iframe_doc.getElementById('rc_iframe_table');
	this.iframe_obj.width = table_obj.offsetWidth;
	this.iframe_obj.height = table_obj.offsetHeight;
*/
	var cal = this;
	window.setTimeout(function(){cal.fit_to_content()}, 1);

	// fix position (need to do this later then calendar is shown as
	// size of calendar could change in this.show(x, y)
	window.setTimeout(function(){cal.fix_position()}, 5);

}

// change size of the iframe to fit to its content
RichCalendar.prototype.fit_to_content = function() {
try {
	var table_obj = this.iframe_doc.getElementById('rc_iframe_table');
	this.iframe_obj.width = table_obj.offsetWidth;
	this.iframe_obj.height = table_obj.offsetHeight;

//alert(this.iframe_obj.width + ' => ' + this.iframe_obj.height);
	// sometimes IE return 0 values, so need to use another approach to
	// determine size of the calendar
	if (!parseInt(this.iframe_obj.width) || !parseInt(this.iframe_obj.height)) {
		this.size_div.innerHTML = this.body_obj.innerHTML;
//alert(this.size_div.offsetWidth + ' => ' + this.size_div.offsetHeight);
	this.iframe_obj.width = this.size_div.offsetWidth;
	this.iframe_obj.height = this.size_div.offsetHeight;
	}

}catch(e){}
}


// create calendar row
RichCalendar.prototype.create_cal_row = function(index) {
var row = this.table_obj.insertRow(3+index);
	row.className = 'rc_cal_tr';

	var cell_indx;
	for (cell_indx=0; cell_indx<7; cell_indx++) {
		var td = row.insertCell(cell_indx);
//		td.innerHTML = index + '-' + cell_indx;
	}

	this.cal_tr[index] = row;

	return row;
}


// changes calendar layout
RichCalendar.prototype.change_skin = function(skin) {
	if (!this.iframe_obj) return;

	var skin_suffix = RichCalendar.skin_suffix(skin);

	this.iframe_obj.className = 'rc_calendar' + skin_suffix;

	this.body_obj.className = 'rc_iframe_body' + skin_suffix;

	this.skin = skin;
}


// returns formatted date (chars recognized are alike used by PHP function date)
RichCalendar.prototype.get_formatted_date = function(format, date) {

	if (!date) date = this.date;
	if (!format) format = this.get_date_format();

	// set time
	if (this.show_time && this.hours_obj && this.mins_obj) {
		this.date.setHours(this.hours_obj.value);
		var mins = this.date.setMinutes(this.mins_obj.value);
	}

var y = date.getFullYear();
var m = date.getMonth();
var d = date.getDate();
var wd = date.getDay();
var hr = date.getHours();
var mins = date.getMinutes();
var secs = date.getSeconds();

var month_names_short = this.text('monthNamesShort');
var month_names = this.text('monthNames');
var day_names_short = this.text('dayNamesShort');
var day_names = this.text('dayNames');

var am = hr < 12 ? true : false;
var hr12 = hr > 12 ? hr - 12 : (hr == 0 ? 12 : hr);

var f = [];

	f["%a"] = am?'am':'pm';
	f["%A"] = am?'AM':'PM';
	f["%d"] = d < 10 ? '0'+d : d; // day of the month, 2 digits with leading zeroes (01 to 31)
	f["%D"] = day_names_short[wd]; // day of the week, textual, short, eg "Fri"
	f["%F"] = month_names[m]; // month, textual, long; eg "January"
	f["%h"] = hr12 < 10 ? '0' + hr12 : hr12; // hour, 12-hour format (01 to 12)
	f["%H"] = hr < 10 ? '0' + hr : hr; // hour, 24-hour format (00 to 23)
	f["%g"] = hr12; // hour, 12-hour format without leading zeros (1 to 12)
	f["%G"] = hr; // hour, 24-hour format without leading zeros (0 to 23)
	f["%i"] = mins < 10 ? '0' + mins : mins; // minutes (00 to 59)
	f["%j"] = d; // day of the month without leading zeros (1 to 31)
	f["%l"] = day_names[wd]; // day of the week, textual, long, eg "Friday"
	f["%L"] = RichCalendar.is_leap_year(y)?1:0; // 1 if leap year, otherwise - 0
	f["%m"] = m < 9 ? '0' + (m+1) : (m+1); //month (01 to 12)
	f["%n"] = m + 1; //month without leading zeros (1 to 12)
	f["%M"] = month_names_short[m]; // month, textual, short, eg "Jan"
	f["%s"] = secs < 10 ? '0' + secs : secs; // seconds (00 to 59)
	f["%t"] = RichCalendar.get_month_days(date); // number of days in the month (28 to 31)
	f["%w"] = wd; // day of the week, numeric (0, Sunday to 6, Saturday)
	f["%Y"] = y; // year, 4 digits, eg 2007
	f["%y"] = String(y).substr(2, 2); // year, 2 digits, eg "07"
	f["%z"] = RichCalendar.get_day_of_year(date); // day of the year (1 to 366)

	var parts = String(format).match(/%./g);
	var i;
	var f_date = format;
	for (i=0; i<parts.length; i++) {
		var value = f[parts[i]];
		if (typeof(value) != "undefined") {
			var re = new RegExp(parts[i], 'g');
			f_date = f_date.replace(re, value);
		}
	}

	return f_date;

}


// set footer content
RichCalendar.prototype.set_footer_text = function(text) {
	if (this.footer_td) {
		this.footer_td.innerHTML = text;
	}
}


// return array with keys - weekend days
RichCalendar.prototype.get_weekend_days = function() {
var weekend_days = this.text('weekend');
var weekend_parts = weekend_days.split(",");
var i;
var result = [];

	for (i=0; i<weekend_parts.length; i++) {
		result[weekend_parts[i]] = true;
	}

	return result;
}


// calendar on close handler; returns true if operation successfull
RichCalendar.prototype.onclose_handler = function() {

	if (this.user_onclose_handler) {
		this.user_onclose_handler(this);
	} else {
		this.hide();
	}

}


// calendar on change handler
RichCalendar.prototype.onchange_handler = function(object_code) {

	if (this.user_onchange_handler) {
		this.user_onchange_handler(this, object_code);
	} else {
		if (object_code == 'day') {

			if (this.value_el) this.value_el.value = this.get_formatted_date();

			if (this.auto_close) this.hide();

		} else {

		}
	}

}


// returns current date format
RichCalendar.prototype.get_date_format = function() {
	var lang_date_format = this.text('dateFormat');
	var format = lang_date_format?lang_date_format:this.format;

	if (this.show_time) {
		format += ' %H:%i';
	}

	return format;
}


// parses date from string str
RichCalendar.prototype.parse_date = function(str, format) {
	if (typeof(str) == "undefined") {
		return;
	}

	if (!format) format = this.get_date_format();


//alert(format);
var today = new Date();
var year = 0;
var month = -1;
var day = 0;
var hours = 0;
var mins = 0;
var seconds = 0;

var month_names = this.text('monthNames');
var short_month_names = this.text('monthNamesShort');

var en_month_names = this.text('monthNames', 'en');
var en_short_month_names = this.text('monthNamesShort', 'en');

//alert(month_names);
	// national chars are not recognized as symbols in regular expressions =>
	// replace them with english month names
	for (j=0; j<month_names.length; j++) {
		var re = new RegExp(month_names[j], 'gi');
		str = str.replace(re, en_month_names[j]);
	}
	for (j=0; j<short_month_names.length; j++) {
		var re = new RegExp(short_month_names[j], 'gi');
		str = str.replace(re, en_short_month_names[j]);
	}

var p = String(str).split(/\W+/g);
var f_p = String(format).match(/%./g);
var i;
var j;
var k;

//alert(p + ' => ' + f_p);
	for (i=0; i<f_p.length; i++) {

		if (!p[i]) continue;

		switch (f_p[i]) {
			case '%a': // am pm
			case '%A':
				if (/am/i.test(p[i]) && hours >= 12) {
					hours -= 12;
				} else {
					if (/pm/i.test(p[i]) && hours < 12) {
						hours += 12;
					}
				}
				break;
			case '%d':
			case '%j':
				day = parseInt(Number(p[i]));
				break;
			case '%F':
				for (j=0; j<en_month_names.length; j++) {
					if (en_month_names[j].toLowerCase() == p[i].toLowerCase()) {
						month = j;
						break;
					}
				}
				break;
			case '%h':
			case '%H':
			case '%g':
			case '%G':
				hours = parseInt(Number(p[i]));
				// to recognize this: 10pm
				if (/am/i.test(p[i]) && hours >= 12) {
					hours -= 12;
				} else {
					if (/pm/i.test(p[i]) && hours < 12) {
						hours += 12;
					}
				}
				break;
			case '%i':
				mins = parseInt(Number(p[i]));
				break;
			case '%m':
			case '%n':
				month = parseInt(Number(p[i]))-1;
				break;
			case '%M':
				for (j=0; j<en_short_month_names.length; j++) {
					if (en_short_month_names[j].toLowerCase() == p[i].toLowerCase()) {
						month = j;
						break;
					}
				}
				break;
			case '%s':
				seconds = parseInt(Number(p[i]));
				break;
			case '%Y':
				year = parseInt(Number(p[i]));
				break;
			case '%y':
				year = parseInt(p[i]);
				if (year < 100) {
					year += year + (year > 29 ? 1900 : 2000);
				}
				break;
			default:
				break;
		}

	}

	if (isNaN(year) || year <= 0) year = today.getFullYear();
	if (isNaN(month) || month < 0 || month > 11) month = today.getMonth();
	if (isNaN(day) || day <= 0 || day > 31) day = today.getDate();
	if (isNaN(hours) || hours < 0 || hours > 23) hours = today.getHours();
	if (isNaN(mins) || mins < 0 || mins > 59) mins = today.getMinutes();
	if (isNaN(seconds) || seconds < 0 || seconds > 59) seconds = today.getSeconds();

//alert(year + ' => ' + month + ' => ' + day + ' => ' + hours + ' => ' + mins + ' => ' + seconds);
	this.date = new Date(year, month, day, hours, mins, seconds);

}