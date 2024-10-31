/**
 * Package main JavaScript
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @version 1.0.6.1
 */
/**
 * Globals
 */
var mouseAbsX=0, mouseAbsY=0, this_viewsz = Array(), last_viewsz = Array(), this_pagedim = Array(), last_pagedim = Array(), this_scroll = Array(), last_scroll = Array();

//alert(window.location.protocol + '//' + window.location.hostname + '/');

// 1.0.6.1 ~ beg: removed php stuff
var ajaxURL = window.location.protocol + '//' + window.location.hostname + '/wp-content/plugins/myeasybackup/ajax_ro.php';
var imgURL = window.location.protocol + '//' + window.location.hostname + '/wp-content/plugins/myeasybackup/img/';
// 1.0.6.1 ~ end

//alert('ajaxURL\n'+ajaxURL+'imgURL\n'+imgURL);

/**
 * Returns an array with the page dimensions [width, height]
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function getPageDimensions() {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	var body = document.getElementsByTagName('body')[0];
	var bodyOffsetWidth = 0, bodyOffsetHeight = 0, bodyScrollWidth = 0, bodyScrollHeight = 0, this_pagedim = [0,0];

	if(typeof document.documentElement!='undefined' && typeof document.documentElement.scrollWidth!='undefined')
	{
		this_pagedim[0] = document.documentElement.scrollWidth;
		this_pagedim[1] = document.documentElement.scrollHeight;
	}
	bodyOffsetWidth  = body.offsetWidth;
	bodyOffsetHeight = body.offsetHeight;
	bodyScrollWidth  = body.scrollWidth;
	bodyScrollHeight = body.scrollHeight;

	if(bodyOffsetWidth>this_pagedim[0])  { this_pagedim[0] = bodyOffsetWidth; }
	if(bodyOffsetHeight>this_pagedim[1]) { this_pagedim[1] = bodyOffsetHeight; }
	if(bodyScrollWidth>this_pagedim[0])  { this_pagedim[0] = bodyScrollWidth; }
	if(bodyScrollHeight>this_pagedim[1]) { this_pagedim[1] = bodyScrollHeight; }

	return this_pagedim;
}

/**
 * Returns an array with the page scrolling positions[horizontal, vertical]
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function getScrollingPosition() {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	var position=[0,0];

	if(typeof window.pageYOffset!='undefined')				{ position=[window.pageXOffset,window.pageYOffset]; }
	if(typeof document.documentElement.scrollTop!='undefined' &&
			  document.documentElement.scrollTop>0)			{ position=[document.documentElement.scrollLeft,document.documentElement.scrollTop]; }
	else if(typeof document.body.scrollTop!='undefined')	{ position=[document.body.scrollLeft,document.body.scrollTop]; }
	return position;
}

/**
 * Returns an array with the view dimensions [width, height]
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~
function getViewportSize() {
//~~~~~~~~~~~~~~~~~~~~~~~~~~
	var size=[0,0];

	if (typeof window.innerWidth!='undefined')			{ size=[window.innerWidth, window.innerHeight]; }
	else if (typeof document.documentElement!='undefined' &&
			 typeof document.documentElement.clientWidth!='undefined' &&
			 document.documentElement.clientWidth!=0)	{ size=[document.documentElement.clientWidth,document.documentElement.clientHeight]; }
	else 												{ size=[document.getElementsByTagName('body')[0].clientWidth,document.getElementsByTagName('body')[0].clientHeight]; }
	return size;
}

/**
 * Changes what is needed to change when the browser window change dimensions
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~
function _checkWindowSize() {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~

	this_viewsz  = getViewportSize();
	this_scroll  = getScrollingPosition();
	this_pagedim = getPageDimensions();

	if(	this_viewsz[0]  == last_viewsz[0]  && this_viewsz[1]  == last_viewsz[1]
		&&
		this_scroll[0]  == last_scroll[0]  && this_scroll[1]  == last_scroll[1]
		&&
		this_pagedim[0] == last_pagedim[0] && this_pagedim[1] == last_pagedim[1] )
	{
		return false;	// window size is NOT changed and user is not scrolling
	}

	//
	//	keep the popWin messages in a visible area
	//

	var el = document.getElementById('myeasybackup_popWin');
	if(el)
	{
//window.status = ' (0) '
//	+'w:'+el.style.width+' h:'+el.style.height
//;

///		el.style.paddingTop = (this_scroll[1]+50)+'px';
el.style.top = (this_scroll[1])+'px';
		//el.style.width = this_viewsz[0]+'px';
		//el.style.height = (this_viewsz[1]-50)+'px';
///		el.style.width = this_pagedim[0]+'px';
///		el.style.height = (this_pagedim[1]-50)+'px';
	}

	//
	//	keep the ae_prompt messages in a visible area
	//
	el = document.getElementById('aep_ovrl');
	if(el)
	{
		//el.style.width = this_viewsz[0]+'px';
		//el.style.height = this_viewsz[1]+'px';
		el.style.width = this_pagedim[0]+'px';
		el.style.height = this_pagedim[1]+'px';
	}

	el = document.getElementById('aep_ww');
	if(el)
	{
		el.style.top = (this_scroll[1])+'px';
	}

	//
	//	IE fixes for tables width
	//
	el = document.getElementById('meb-h2-settings');
	if(el)
	{
		//meb_settings_ie8fix();
	}

//window.status = ' (1) '
//	+'viewsz:'+this_viewsz[1]+'|'
//	+'scroll:'+this_scroll[1]+'|'
//	+'pagedim:'+this_pagedim[1]+'|'
//	+'w:'+el.style.width+' h:'+el.style.height
//;

	last_viewsz = this_viewsz; last_scroll = this_scroll; last_pagedim = this_pagedim;
	return true;
}

/**
 * Loader for functions
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function addLoadListener(fn) {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if(typeof window.addEventListener!='undefined')			{ window.addEventListener('load',fn,false); }
	else if(typeof document.addEventListener!='undefined')	{ document.addEventListener('load',fn,false); }
	else if(typeof window.attachEvent!='undefined')			{ window.attachEvent('onload',fn); }
	else { var oldfn=window.onload; if(typeof window.onload!='function') { window.onload=fn; } else { window.onload=function() {oldfn();fn();}; } }
}

/**
 * Shows a message
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function set_waiting_message(typ) {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

//alert('set_waiting_message!');

	if(typ!='dr')       //	0.9.1
	{
		document.body.style.cursor = 'wait';
	}

	var popWin = document.getElementById('myeasybackup_popWin');
	if(popWin)
	{
		if(typ=='up')	//	0.0.5
		{
			document.getElementById('wait_upload').style.display='block';	//	0.0.6
		}
		else if(typ=='dr')	//	0.9.1
		{
			document.getElementById('drftp_list').style.display='block';
		}
		else
		{
			document.getElementById('wait_backup').style.display='block';	//	0.0.6
		}

		var this_pagedim = getPageDimensions();
		popWin.style.top    = '0px';
		popWin.style.left   = '0px';
		popWin.style.width  = this_pagedim[0] + 'px';
		popWin.style.height = this_pagedim[1] + 'px';
		popWin.style.display= 'block';
	}
	else
	{
		setTimeout("set_waiting_message('" + typ +"');", 250);
		return;
	}
}

/**
 * Hides the message element
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function hide_waiting_message() {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	//	@since 0.0.5
	//
	document.body.style.cursor = 'default';
	var popWin = document.getElementById('myeasybackup_popWin');
	if(popWin)
	{
		popWin.style.display= 'none';
		var el = document.getElementById('drftp_vars').innerHTML = '';  // 0.9.1
	}
	else
	{
		setTimeout('hide_waiting_message()', 250);
		return;
	}
}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function meb_settings_ie8fix() {
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	var contents_width = document.getElementById('meb-h2-settings').offsetWidth - 20;

	document.getElementById('meb-settings-tbl').style.width=contents_width+'px';
	document.getElementById('meb-settings-tbl-l').style.width=(contents_width/2)+'px';
	document.getElementById('meb-settings-tbl-r').style.width=(contents_width/2)+'px';

	document.getElementById('sys_options').style.width=(contents_width-4)+'px';
	document.getElementById('meb-settings-tbl-lsys').style.width=((contents_width-4)/2)+'px';
	document.getElementById('meb-settings-tbl-rsys').style.width=((contents_width-4)/2)+'px';

	document.getElementById('zip_options').style.width=(contents_width-4)+'px';
	document.getElementById('meb-settings-tbl-lzip').style.width=((contents_width-4)/2)+'px';
	document.getElementById('meb-settings-tbl-rzip').style.width=((contents_width-4)/2)+'px';

	document.getElementById('tar_options').style.width=(contents_width-4)+'px';
	document.getElementById('meb-settings-tbl-ltar').style.width=((contents_width-4)/2)+'px';
	document.getElementById('meb-settings-tbl-rtar').style.width=((contents_width-4)/2)+'px';

	document.getElementById('php_options').style.width=(contents_width-4)+'px';
	document.getElementById('meb-settings-tbl-lphp').style.width=((contents_width-4)/2)+'px';
	document.getElementById('meb-settings-tbl-rphp').style.width=((contents_width-4)/2)+'px';
}


////~~~~~~~~~~~~~~~~~~~~~~~~
//function mouseMove(e) {
////~~~~~~~~~~~~~~~~~~~~~~~~
//	if(!e) { var e = window.event; }
//
//	if(document.all)
//	{
//		//	browser is IE
//		//
//		mouseAbsX = event.clientX + this_scroll[0];
//		mouseAbsY = event.clientY + this_scroll[1];
//	}
//	else
//	{
//		//	browser is NOT IE
//		//
//		mouseAbsX = e.pageX;
//		mouseAbsY = e.pageY;
//	}
//window.status=mouseAbsX+','+mouseAbsY;
//}
//
//document.onmousemove = mouseMove;

addLoadListener(function() { setInterval("_checkWindowSize()",250); } );

/*
	http://www.anyexample.com/webdev/javascript/ie7_javascript_prompt%28%29_alternative.xml
*/
// This is variable for storing callback function
var ae_cb = null;

// this is a simple function-shortcut
// to avoid using lengthy document.getElementById
function ae$(a) { return document.getElementById(a); }

// This is a main ae_prompt function
// it saves function callback
// and sets up dialog
function ae_prompt(cb, q, a) {
	ae_cb = cb;
	//ae$('aep_t').innerHTML = document.domain + ' question:';
	ae$('aep_t').innerHTML = document.domain;
	ae$('aep_prompt').innerHTML = q;
	ae$('aep_text').value = a;
	ae$('aep_ovrl').style.display = ae$('aep_ww').style.display = '';
	ae$('aep_text').focus();
	ae$('aep_text').select();
}

// This function is called when user presses OK(m=0) or Cancel(m=1) button
// in the dialog. You should not call this function directly.
function ae_clk(m) {
	// hide dialog layers
	ae$('aep_ovrl').style.display = ae$('aep_ww').style.display = 'none';
	if (!m)
		ae_cb(null);  // user pressed cancel, call callback with null
	else
		ae_cb(ae$('aep_text').value); // user pressed OK
}
