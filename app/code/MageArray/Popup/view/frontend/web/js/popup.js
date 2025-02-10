/**
 * Created by NextBits.
 */

//your js code here

function canShow(id) {
	if( getCookie('die_popup' +id) == 1 || getCookie('unsetpopup'+id) == 1
		|| getCookie('one_time'  +id) == 1 ) {
		return false;
	} else {
		return true;
	}
}

function setCookies(name,value,day)
{
	var date = new Date();
	date.setTime(date.getTime()+(day*24*60*60*1000));
	var expires = "; expires="+date.toGMTString();
	document.cookie = name+"="+value+expires+"; path=/";
}

function deleteCookie(name)
{
	document.cookie = name + '=; Path=/; expires=T, 01 Jan 1970 00:00:01 GMT;';
	return true;
}

function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function setPopupTime(id,delay)
{
	var date = new Date();
	date.setTime(date.getTime()+(delay * 1000));
	var expires = "; expires="+date.toGMTString();
	document.cookie = "popup_time"+id+"="+"1"+expires+"; path=/";
}