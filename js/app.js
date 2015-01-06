var App = {
    module_path:'./isell/'
};
App.flash = function (msg, type) {
    if (type === 'error') {
	$("#appStatus").html(msg);
	$("#appStatus").window('open');
	$("#appStatus").window('move',{top:0});
    }
    else if( type === 'alert' ){
	$.messager.alert('Warning!',msg,'error');
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
App.uri = function () {
    var args = Array.prototype.slice.call(arguments);
    return args.join('/');
};
App.loadModule = function (path, data) {
    var id = path.replace('/', '_');
    var handler = $.Deferred();
    App[id] = {};
    $("#" + id).load(App.module_path+path + '.html', function () {
	$.parser.parse("#" + id);//for easy ui
	App[id].init ? App[id].init(data,handler) : '';
    });
    return handler.promise();
};
App.loadWindow = function (path, data) {
    var id = path.replace('/', '_');
    if (!$('#' + id).length) {
	$('#appWindowContainer').append('<div id="' + id + '" class="app_window"><div>Loading...</div></div>');
    }
    return App.loadModule(path, data);
};
App.setupForm=function(id,fvalue,handler){
    if( !id || !fvalue || !handler ){
	return false;
    }
    $("#"+id+" input,#"+id+" textarea,#"+id+" select").each(function( i,element ){
	$(element).attr('value',fvalue[element.name]||'');
	$(element).wrap( '<div class="inp_group"><label></label></div>' );
	$(element).before( "<b>"+element.title+": </b>" );
    });
    $("#"+id+" input,#"+id+" textarea,#"+id+" select").change(function(e){
	handler(e.currentTarget,e);
    });
};
App.init = function () {
    //App.loadModule('appMenu');
    App.loadWindow('catalog/company_list');
    App.loadWindow('catalog/company_form');
};
$(App.init);



$.ajaxSetup({
    cache: true
});

$(document).ajaxComplete(function (event, xhr, settings) {
    $(document).css('cursor','');
    if (xhr.responseText.indexOf('<h4>A PHP Error was encountered</h4>') > -1 || xhr.responseText.indexOf('xdebug-error') > -1) {
	App.flash("<h3>url: "+settings.url+"</h3>"+xhr.responseText, 'error');
    }
    if(xhr.getResponseHeader('iSell-Message')){
	App.flash( decodeURIComponent(xhr.getResponseHeader('iSell-Message').replace(/\+/g,  " ")), 'error' );
    }
});
$(document).ajaxError(function (event, xhr, settings) {
    App.flash("<h3>url: "+settings.url+"</h3>"+xhr.responseText, 'error');
});
$(document).ajaxSend(function () {
    $(document).css('cursor','wait');
    //App.flash("Triggered ajaxSend handler.");
});
$(document).ajaxSuccess(function () {
    //App.flash("Triggered ajaxSuccess handler.");
});