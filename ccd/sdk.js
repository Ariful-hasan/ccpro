var baseUrl;
var isJquueryLoadedBySdk = false;
function loadjscssfile(filename, filetype){
    if (filetype=="js"){ //if filename is a external JavaScript file
        var fileref=document.createElement('script')
        fileref.setAttribute("type","text/javascript")
        fileref.setAttribute("src", filename)
    }
    else if (filetype=="css"){ //if filename is an external CSS file
        var fileref=document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref!="undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}




var obj = document.querySelectorAll('[chat-tab=true]');
if(obj.length==1) {
	obj[0].style.display='none';
	
	var href = obj[0].getAttribute("href");
	var href_ary = href.split("?");
	var imagePath = href_ary[0];
	var tabText = obj[0].innerHTML;
	
	imagePath = imagePath.replace("chat.php","");
	imagePath = imagePath.replace("http://","");
	imagePath = imagePath.replace("https://","");
	baseUrl = href_ary[0];//atag.origin+atag.pathname;
	baseUrl = baseUrl.replace("chat.php","");
	baseUrl = baseUrl.replace("http:","");
	baseUrl = baseUrl.replace("https:","");
	loadjscssfile(imagePath+'ccd/gchat.css', 'css');
	if(!window.jQuery) {// || document.write('<script src="https://www.gtalkhome.com/theme/main/js/jquery.js"><\/script>')
		loadjscssfile(baseUrl+"ccd/jquery.min.js", "js");
		isJquueryLoadedBySdk = true;
	}
	
	var layout="orange";
	var args = atob(href_ary[1]).split("&");
	for(i=0; i<args.length; i++) {
		var argN = args[i].split("=");
		if(argN[0]=="layout") {
			layout = argN[1];
		}
	}

	var html ="<a class='gcc-chat-button "+layout+"' href='javascript:void(0)' rel='"+href+"' onClick='return chat_window(this.rel);'>\
		<div class='live_chat_inner'>\
			<div class='lcbs-left'><img align='texttop' class='live_chat' src='"+imagePath+"ccd/chat.png'></div>\
			<div class='lcbs-right'>"+tabText+"</div>\
		</div>\
	</a>";
	//document.body.innerHTML += html;
	setTimeout("appendtobody()",3000);
	
}
function appendtobody() {
	//jQuery.noConflict();
	var $jqChat = isJquueryLoadedBySdk ? jQuery.noConflict() : jQuery;
	
	$jqChat("body").append(html);
}

setCookie("user_arival_timestamp",Math.floor(Date.now() / 1000), 1);

var newwindow = null;
var chat_window_width = typeof chat_window_width=='undefined' ? 380 : chat_window_width;
var chat_window_height = typeof chat_window_height=='undefined' ? 520 : chat_window_height;
function chat_window(url) {
	if ((newwindow == null) || (newwindow.closed)) {
		
		var chat_start_time = Math.floor(Date.now() / 1000);
		var user_arival_timestamp = getCookie("user_arival_timestamp");
		var time_difference = chat_start_time - user_arival_timestamp;

		var left = screen.width-chat_window_width;
		var top = screen.height-chat_window_height;
		var url_ary = url.split("?");
		var weburl = getLocation(window.location.href);
		var hostname = weburl.hostname;
		var wurl = window.location.href;
		wurl = wurl.replace(hostname,"");
		wurl = wurl.replace("http://","");
		wurl = wurl.replace("https://","");
		var args = atob(url_ary[1])+"&url="+encodeURIComponent(wurl)+"&user_arival_duration="+time_difference;
		url = url_ary[0]+"?"+btoa(args);
		
		newwindow=window.open(url,'_blank','width='+chat_window_width+',height='+chat_window_height+',left='+left+',top='+top);
		newwindow.focus();
		setCookie("proactive_chat_status",1);
    } else {
        newwindow.focus();    
    }
	return false;
}

var getLocation = function(href) {
    var l = document.createElement("a");
    l.href = href;
    return l;
};

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
} 

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
}

var obj = document.querySelectorAll('[proactive-chat=true]');
if(obj.length==1) {
	var href = obj[0].getAttribute("href");
	var href_ary = href.split("?");
	var args_str = atob(href_ary[1]);
	//var args_ary = args_str.split("&");
	//for(i=0; i<args_ary.length; i++) {
	//	console.log(args_ary[i]);
	//}
	var proactive_chat_window_interval = typeof proactive_chat_interval=='undefined' ? 60 : proactive_chat_interval;//seconds
	var proactive_chat_status = parseInt(getCookie("proactive_chat_status"));
	if(!proactive_chat_status) {
		setTimeout(function(){ShowProactiveChatWindow(args_str)}, proactive_chat_window_interval*1000);
		//var atag = document.getElementById('g_live_chat');
		baseUrl = href_ary[0];//atag.origin+atag.pathname;
		baseUrl = baseUrl.replace("chat.php","");
		baseUrl = baseUrl.replace("http:","");
		baseUrl = baseUrl.replace("https:","");
		loadjscssfile(baseUrl+"ccd/sdk.css", "css");
		
		
	}
}

function ShowProactiveChatWindow(args_str) {

	var $jqChat = isJquueryLoadedBySdk ? jQuery.noConflict() : jQuery;

	$jqChat.ajax({
		type:"POST",
		url:baseUrl+"client_info.php?"+args_str+"&proactive_chat",
		//data:{page_id:page_id,site_key:site_key,user:user},
		dataType:"jsonp",
		success:function(resp) {
			if(resp.available>0) {
				var html = "<div id='proactive-chat-window'>";
				html += "<div class='info-header'>How can we help you!</div>";
				html += "<div class='info'>Customer care agents<br>are ready to answer your questions<div>";
				html += "<div class='buttons'><a class='chat-now' onclick='StartProactiveChat()' style='cursor:pointer; text-decoration: underline;'>Chat Now</a><a class='no-thanks' onclick='closeProactiveWindow()' style='cursor:pointer; text-decoration: underline;'>No Thanks</a><div>";
				html += "</div>";
				//document.body.innerHTML += html;
				$jqChat("body").append(html);
				//GetVisitorInfo(resp.available,resp.country,resp.region,resp.city,resp.postal_code,resp.ws_ip,resp.ws_port,resp.language,resp.service);
			}
		}
	});
	
}

function StartProactiveChat() {
	//var href = document.getElementById('g_live_chat').href;
	var obj = document.querySelectorAll('[proactive-chat=true]');
	if(obj.length==1) {
		var href = obj[0].getAttribute("href");
	
		document.getElementById('proactive-chat-window').remove();
		chat_window(href);
	
		setCookie("proactive_chat_status",1);
	}
}

function closeProactiveWindow()
{
	document.getElementById('proactive-chat-window').remove();
	setCookie("proactive_chat_status",1);
}









/*
function loadjscssfile(filename, filetype){
    if (filetype=="js"){ //if filename is a external JavaScript file
        var fileref=document.createElement('script')
        fileref.setAttribute("type","text/javascript")
        fileref.setAttribute("src", filename)
    }
    else if (filetype=="css"){ //if filename is an external CSS file
        var fileref=document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref!="undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}
var layout = document.getElementById("gchat-button").getAttribute("data-layout");
var user = document.getElementById("gchat-button").getAttribute("data-user");
var is_rnd = document.getElementById("gchat-button").getAttribute("is-rnd");
if(is_rnd==1) {
  baseUrl = "http://72.48.199.126/cloudcc/";
  //baseUrl = "http://192.168.10.67/cc/";
} else {
  if (window.location.protocol != "https:") baseUrl = "http://ccportal.gplex.com/";
  else baseUrl = "https://ccportal.gplex.com/";
}
loadjscssfile(baseUrl+"ccd/gchat.css", "css");

function StartChat() {
	var layout = document.getElementById("gchat-button").getAttribute("data-layout");
	var domain = document.getElementById("gchat-button").getAttribute("data-domain");
	var page_id = document.getElementById("gchat-button").getAttribute("data-page_id");
	var site_key = document.getElementById("gchat-button").getAttribute("data-site_key");
	var www_ip = document.getElementById("gchat-button").getAttribute("data-www_ip");
	var user = document.getElementById("gchat-button").getAttribute("data-user");
	var is_rnd = document.getElementById("gchat-button").getAttribute("data-is_rnd");
	
	//if(user=="AA") baseUrl = "http://64.5.49.34/cloudcc/";
	//else baseUrl = "http://ccportal.gplex.com";
	var width = 315;
	var height = 395;
	var left = screen.width-width;
	var top = screen.height-height;
	window.open(baseUrl+"chat.php?layout="+layout+"&domain="+domain+"&page_id="+page_id+"&site_key="+site_key+"&www_ip="+www_ip+"&user="+user+"&is_rnd="+is_rnd,"","width="+width+",height="+height+",resizable=No,left="+left+",top="+top);
}

function appendHtml(el, str) {
  //var div = document.createElement('div');
  //div.setAttribute("id", "gCCCustomerChatButton");
  //div.setAttribute("click", "StartChat()");
  //div.innerHTML = str;
  //while (div.children.length > 0) {
  //  el.appendChild(div.children[0]);
  //}
  document.body.innerHTML += str;
}

//var html = "<div id='gCCCustomerChatButton' onclick='StartChat()' class='"+layout+"'><span class='gcc-left'>Chat Now</span><span class='gcc-right'>+</span>";
/*var html = "<a href='javascript://' id='gchat-button' class='gcc-chat-button btn-disable'
		data-layout='orange' 
		data-domain='64.5.32.204:3030'
		data-page_id='"page-1234567890'
		data-site_key='86ada306e5cb27441e4c0e164f8ba048'
		data-www_ip='64.5.32.204'
		data-user='AA'
		onclick='StartChat()'
	>Live Chat</a>";
appendHtml(document.body, html); // "body" has two more children - h1 and span.
*/
