/* global Mark, encodeURIComponent */

var App = {
    tplcache:{},
    //urlcache:{},
    handler:$.Deferred(),
    init: function () {
	App.loadBg();
    },
    flash:function (msg, type) {
	if (type === 'error') {
	    $("#appStatus").html(msg);
	    $("#appStatus").window({
		title: 'Ошибка',
		width: 800,
		height: 300
	    });
	    $("#appStatus").window('move', {top: 0});
	}
	else if (type === 'alert') {
	    $.messager.alert('Внимание!', msg, 'error');
	}
	else {
	    clearTimeout(App.flashClock);
	    App.flashClock = setTimeout(function () {
		$.messager.show({title: 'Сообщение', msg: App.msg, showType: 'show'});
		App.msg = '';
	    }, 300);
	    App.msg = (App.msg || '') + msg + '<br>';
	}
    },
    initTabs: function (tab_id) {
	$('#' + tab_id).tabs({
	    selected: App.cookie(tab_id) || 0,
	    onSelect: function (title, index) {
		var href = $('#' + tab_id).tabs('getTab', title).panel('options').href;
		var id = href.replace(/\//g, '_').replace('.html', '');
		App[id] && App[id].focus && App[id].focus();
		App.cookie(tab_id, title);		    
	    },
	    onLoad:function(panel){
		var href = panel.panel('options').href;
		if( href ){
		    var id = href.replace(/\//g, '_').replace('.html', '');
		    if( App[id] ){
			if( !$("#" + id).length ){
			    panel.wrapInner('<div id="'+id+'" style="padding:5px"></div>');
			    panel.css('padding','5px');
			}
			App[id].data={inpanel:true};
			App[id].node=$("#" + id);
			App[id].init && App[id].init();
			App[id].initAfter && App[id].initAfter();
		    }
		}
	    }
	});
    },
    initModule: function(id,data,handler){
	App[id].data = data;
	App[id].handler = handler;
	App[id].node = $("#" + id);
	App[id].init ? App[id].init(data, handler) : '';
	if( !App[id].parsed ){
	    $.parser.parse("#" + id);//for easy ui
	    App[id].parsed=true;
	}
	App[id].initAfter ? App[id].initAfter(data, handler) : '';
    },
    loadModule: function (path, data) {
	var id = path.replace(/\//g, '_');
	var handler = $.Deferred();
	if( App[id] ){
	    App.initModule(id,data,handler);
	} else {
	    App[id] = {};
	    $("#" + id).load(path + '.html', function () {
		App.initModule(id,data,handler);
	    });	    
	}
	return handler.promise();	
    },
    loadWindow: function (path, data) {
	var id = path.replace(/\//g, '_');
	if (!$('#' + id).length) {
	    $('#appWindowContainer').append('<div id="' + id + '" class="app_window"></div>');
	}
	return App.loadModule(path, data);
    }
};
$(App.init);
//////////////////////////////////////////////////
//UTILS
//////////////////////////////////////////////////
App.json=function( text ){
    try{
	return text===''?null:JSON.parse(text);
    }
    catch(e){
	console.log('isell-app-json-err: '+e+text);
	return null;
    }
};
//App.xhr={
//    wait:false,
//    sequence:[],
//    post:function( url, data, success, dataType ){
//	this.sequence.push({url:url,data:data,success:success,dataType:dataType});
//	this.next();
//    },
//    next:function(){
//	if( this.wait ){
//	    return;
//	}
//	this.send( this.sequence.shift() );
//    },
//    send:function( rq ){
//	this.wait=true;
//	$.post(rq.url,rq.data,rq.success.success,rq.dataType).always(function(){
//	    App.xhr.wait=false;
//	    App.xhr.next();
//	});
//    }
//};
App.uri = function () {
    var args = Array.prototype.slice.call(arguments);
    return args.map(encodeURIComponent).join('/');
};
App.toIso = function (dmY) {
    if (dmY instanceof Date) {
	return dmY.getFullYear() + '-' + String("0" + (dmY.getMonth() + 1)).slice(-2) + '-' + String("0" + dmY.getDate()).slice(-2);
    }
    return dmY ? dmY.replace(/[^\d]/g, '').replace(/^[\d]{4}(\d\d)$/, "20$1").replace(/^(\d\d)(\d\d)(\d\d\d\d)$/, "$3-$2-$1") : null;
};
App.toDmy = function (iso) {
    if (iso instanceof Date) {
	return String("0" + iso.getDate()).slice(-2) + '.' + String("0" + (iso.getMonth() + 1)).slice(-2) + '.' + iso.getFullYear();
    }
    return iso.replace(/^(\d\d\d\d)-(\d\d)-(\d\d)$/, "$3.$2.$1");
};
App.today = function () {
    return App.toDmy(new Date());
};
App.formatNum = function (num, mode) {
    if (num === undefined || mode === 'clear' && num * 1 === 0) {
	return '';
    }
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
};
App.formElements=function( fquery ){
    return $(fquery + " input," + fquery + " textarea," + fquery + " select");
};
App.setupForm = function ( fquery, fvalue, mode ) {
    if (!fquery) {
	return false;
    }
    fvalue=fvalue||{};
    App.formElements(fquery).each(function (i, element) {
	var value=fvalue[element.name] || ( mode==='use_inp_values'&&$(element).val() ?$(element).val():'');//Support for document header
	$(element).val(value);
	if ($(element).attr('type') === 'hidden') {
	    return true;
	}
	if ($(element).attr('title') && !$(element).attr('data-skip')) {
	    $(element).wrap('<div class="inp_group"><label></label></div>');
	    $(element).before("<b>" + element.title + ": </b>");
	}
	if ($(element).attr('type') === 'checkbox' && fvalue[element.name] * 1) {
	    $(element).attr('checked', 'checked');
	}
	$(element).attr('data-skip', 1);
    });
    return App.formElements(fquery);
};
App.collectForm = function (fquery) {
    var fvalue = {};
    App.formElements(fquery).each(function (i, element) {
	if (element.name) {
	    fvalue[element.name] = App.val(element);
	}
    });
    return fvalue;
};
App.val = function (element) {
    if ($(element).attr('type') === 'checkbox') {
	return $(element).is(':checked') ? 1 : 0;
    }
    return $(element).val();
};
App.cookie = function (cname, cvalue) {
    if (cvalue === undefined) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
	    var c = ca[i];
	    while (c.charAt(0) === ' ')
		c = c.substring(1);
	    if (c.indexOf(name) === 0)
		return c.substring(name.length, c.length);
	}
	return "";
    }
    else {
	var d = new Date();
	d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
    }
};
App.getUrlParent=function(){
    return location.href.split('/')[3];
};
App.loadBg = function () {
    if (localStorage.getItem('isell_bg'+App.getUrlParent())) {
	$("body").css('background', 'url("' + localStorage.getItem('isell_bg'+App.getUrlParent()) + '") repeat fixed center top');
	$("body").css('background-size', '100%');
    }
};
App.setBg = function () {
    App.loadWindow('page/dialog/background_setter');
};
App.datagrid = {
    tooltip: function (value, row) {
	if( !value ){
	    return '';
	}
	var parts = value.split(' ');
	var cmd = parts.shift();
	if (cmd)
	    return '<img src="img/' + cmd + '.png" title="' + parts.join(' ') + '">';
	else
	    return '';
    }
};
App.renderTpl=function( id, data ){
    if( !this.tplcache[id] ){
	this.tplcache[id]=$('#'+id).html();
    }
    //this.loadScript('js/markup.min.js',function(){
	$('#'+id).html( Mark.up(App.tplcache[id], data) );
    //});
    $('#'+id).removeClass('covert');
};
//App.loadScript = function (path,handler) {
//    if( this.urlcache[path] ){
//	handler();
//    }
//    else {
//	$.getScript(path,handler);
//	this.urlcache[path]=1;
//    }
//};




//////////////////////////////////////////////////
//AJAX SETUP
//////////////////////////////////////////////////
$.ajaxSetup({
    cache: true
});
$(document).ajaxComplete(function (event, xhr, settings) {
    $(document).css('cursor', '');
    var type = xhr.getResponseHeader('X-isell-type');
    var msg = xhr.getResponseHeader('X-isell-msg');
    if (msg) {
	var msg = decodeURIComponent(msg.replace(/\+/g, " "));
	if (type === 'error') {
	    App.flash(msg, 'error');
	}
	else {
	    App.flash(msg);
	}
    }
    else if (!type || type.indexOf('OK') === -1) {
	App.flash("<h3>url: " + settings.url + "</h3>" + xhr.responseText, 'error');
    }
});
$(document).ajaxError(function (event, xhr, settings) {
    var type = xhr.getResponseHeader('X-isell-type');
    if (type && type.indexOf('OK') > -1) {
	return;
    }
    App.flash("<h3>error url: " + settings.url + "</h3>" + xhr.responseText, 'error');
});
$(document).ajaxSend(function () {
    $(document).css('cursor', 'wait');
});
$.fn.datebox.defaults.formatter = function (date) {
    return App.toDmy(date);
};
$.fn.datebox.defaults.parser = function (input) {
    if (input instanceof Date) {
	return input;
    }
    if (!input) {
	return new Date("12/31/2999");
    }
    var date = input.replace(/[^\d]/g, '').replace(/^[\d]{4}(\d\d)$/, "20$1").replace(/^(\d\d)(\d\d)(\d\d\d\d)$/, "$2/$1/$3");
    var t = Date.parse(date);
    if (!isNaN(t)) {
	return new Date(t);
    } else {
	return new Date();
    }
};
