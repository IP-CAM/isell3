var App = {};
App.flash = function (msg, type) {
    if (type === 'error') {
	$("#appStatus").html(msg);
	$("#appStatus").window({
	    title: 'Ошибка',
	    width: 800,
	    height: 300
	});
	$("#appStatus").window('move',{top:0});
    }
    else if( type === 'alert' ){
	$.messager.alert('Внимание!',msg,'error');
    }
    else {
	clearTimeout(App.flashClock);
	App.flashClock=setTimeout(function(){
	    $.messager.show({title: 'Сообщение', msg: App.msg, showType: 'show'});
	    App.msg='';
	},300);
	App.msg=(App.msg||'')+'<br>'+msg;
    }
};
App.loadModule = function (path, data) {
    var id = path.replace(/\//g, '_');
    var handler = $.Deferred();
    App[id] = {};
    $("#" + id).load( path + '.html', function () {
	App[id].data=data;
	App[id].handler=handler;
	App[id].init ? App[id].init(data,handler) : '';
 	$.parser.parse("#" + id);//for easy ui
    });
    return handler.promise();
};
App.loadWindow = function (path, data) {
    var id = path.replace(/\//g, '_');
    if (!$('#' + id).length) {
	$('#appWindowContainer').append('<div id="' + id + '" class="app_window"></div>');
    }
    return App.loadModule(path, data);
};
App.init = function () {
    //App.loadModule('appMenu');
    //App.loadWindow('catalog/company_list');
    //App.loadWindow('catalog/company_form');
    //App.loadWindow('page/dialog/move_doc');
    App.loadBg();
};
$(App.init);


//////////////////////////////////////////////////
//AJAX SETUP
//////////////////////////////////////////////////
$.ajaxSetup({
    cache: true
});
$(document).ajaxComplete(function (event, xhr, settings) {
    $(document).css('cursor','');
    if (xhr.responseText.indexOf('<h4>A PHP Error was encountered</h4>') > -1 || xhr.responseText.indexOf('xdebug-error') > -1) {
	App.flash("<h3>url: "+settings.url+"</h3>"+xhr.responseText, 'error');
    }
    if( xhr.getResponseHeader('X-isell-msg') ){
	var msg=decodeURIComponent(xhr.getResponseHeader('X-isell-msg').replace(/\+/g,  " "));
	if( xhr.getResponseHeader('X-isell-type')=='error' ){
	    App.flash( msg, 'error' );
	}
	else{
	    App.flash( msg );
	}
    }
});
$(document).ajaxError(function (event, xhr, settings) {
    App.flash("<h3>url: "+settings.url+"</h3>"+xhr.responseText, 'error');
});
$(document).ajaxSend(function () {
    $(document).css('cursor','wait');
});
//////////////////////////////////////////////////
//UTILS
//////////////////////////////////////////////////
App.uri = function () {
    var args = Array.prototype.slice.call(arguments);
    return args.join('/');
};
App.toIso=function(dmY){
    if(dmY instanceof Date){
	return dmY.getFullYear() + '-' + String("0"+dmY.getMonth() + 1).slice(-2) +'-' + String("0"+dmY.getDate()).slice(-2);
    }
    return dmY.replace(/[^\d]/g,'').replace(/^[\d]{4}(\d\d)$/,"20$1").replace(/^(\d\d)(\d\d)(\d\d\d\d)$/,"$3-$2-$1");
};
App.toDmy=function(iso){
    if(iso instanceof Date){
	iso=App.toIso(iso);
    }
    return iso.replace(/^(\d\d\d\d)-(\d\d)-(\d\d)$/,"$3.$2.$1");
};
App.today=function(){
    return App.toDmy( new Date() );
};
App.setupForm=function(fquery,fvalue){
    if( !fquery || !fvalue ){
	return false;
    }
    $(fquery+" input,"+fquery+" textarea,"+fquery+" select").each(function( i,element ){
	$(element).val( fvalue[element.name] );
	if( $(element).attr('type')==='hidden' ){
	    return true;
	}
	if( $(element).attr('title') ){
	    $(element).wrap( '<div class="inp_group"><label></label></div>' );
	    $(element).before( "<b>"+element.title+": </b>" );
	}
	if( $(element).attr('type')==='checkbox' && fvalue[element.name]*1 ){
	    $(element).attr('checked','checked');
	}
    });
    return $(fquery+" input,"+fquery+" textarea,"+fquery+" select");
};
App.collectForm=function(fquery){
    var fvalue = {};
    $(fquery+" input,"+fquery+" textarea,"+fquery+" select").each(function( i,element ){
	if( element.name ){
	    fvalue[element.name]=$(element).val();
	    if( $(element).attr('type')==='checkbox' ){
		fvalue[element.name]=$(element).is(':checked');
	    }
	}
    });
    return fvalue;
};
App.cookie=function(cname,cvalue){
    if( cvalue===undefined ){
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)===' ') c = c.substring(1);
            if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
        }
        return "";        
    }
    else{
        var d = new Date();
        d.setTime(d.getTime() + (365*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;     
    }
};
App.loadBg=function(){
    if( App.cookie('bg').length>10 ){
        $("body").css('background', 'url("' + App.cookie('bg') + '") repeat scroll center top #ffffff');
        $("body").css('background-size', '100%');
    }
};
App.setBg=function(){
    App.cookie('bg',prompt("Введите интернет адрес изображения заднего плана!\n\nНапример\n http://7-themes.com/data_images/out/68/7005391-sport-cars-wallpapers.jpg"));   
    App.loadBg();
}
$.fn.datebox.defaults.formatter = function (date) {
    return App.toDmy(date);
}
$.fn.datebox.defaults.parser = function (s) {
    var date=s.replace(/[^\d]/g,'').replace(/^[\d]{4}(\d\d)$/,"20$1").replace(/^(\d\d)(\d\d)(\d\d\d\d)$/,"$2/$1/$3");
    var t = Date.parse(date);
    if (!isNaN(t)) {
	return new Date(t);
    } else {
	return new Date();
    }
}
