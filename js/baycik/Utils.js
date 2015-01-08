Utils = function () {
    this.tpl_cache = new Array();
    this.title = '';
    this.subtitle = '';
    this.app_title = '';
    Utils.prototype.currentInstance = this;
};
require([
    "dojo/dom",
    "dijit/registry",
    "dojo/on",
    "dojo/query",
    "dojo/parser",
    "dojox/dtl/_base",
    "dojox/dtl/Context",
    "dojo/io-query",
    "dojo/_base/lang",
    "dojo/hash",
    "dojo/topic",
    "dojo/ready"
],
function (
    dom,
    registry,
    on,
    query,
    parser,
    dtl,
    Context,
    ioQuery,
    lang,
    hash,
    topic,
    ready
) {
    ready(function () {
        if (Utils.prototype.currentInstance.appReady)
            Utils.prototype.currentInstance.appReady();
        window.dom=Utils.prototype.currentInstance.dom=dom;
        window.on=Utils.prototype.currentInstance.on=on;
        window.registry=Utils.prototype.currentInstance.registry=registry;
        window.query=Utils.prototype.currentInstance.query=query;
    });
    ////////////////////////
    //HASH CONTROL
    ////////////////////////
    Utils.prototype.topic = topic;
    Utils.prototype.topic.subscribe("/dojo/hashchange", function () {
        var hashObj = Utils.prototype.appGetState();
        Utils.prototype.topic.publish("/app/statechange", hashObj);
    });
    Utils.prototype.appGetState = function () {
        var hashCode = hash(); //.replace(/\//img,'&').replace(/:/img,'=');
        return ioQuery.queryToObject(hashCode);
    };
    Utils.prototype.appSetState = function (hashObj) {
        var hashCode = ioQuery.objectToQuery(hashObj);
        //hashCode=hashCode.replace(/&/img,'/').replace(/=/img,':');
        hash(hashCode);
    };
    Utils.prototype.appUpdateState = function (update) {
        var state = lang.mixin(this.appGetState(), update);
        this.appSetState(state);
    };
    ////////////////////
    //MODULE SETUP
    ////////////////////
    Utils.prototype.setTitle = function (title) {
        this.title = title;
        dom.byId("module_title").innerHTML = '<span style="color:#b09"><b>' + this.app_title + '</b></span> - ' + this.title;
        document.title = this.title + ' / ' + this.app_title;
    };
    Utils.prototype.setSubTitle = function (subtitle) {
        this.subtitle = ' / ' + subtitle;
        dom.byId("module_title").innerHTML = '<span style="color:#b09"><b>' + this.app_title + '</b></span> - ' + this.title + '<span style="color:#666">' + this.subtitle + '</span>';
    };
    Utils.prototype.selectModuleButton = function (mod_name) {
        if ( !dom.byId(mod_name + 'Button') ) return;
        query('.ModuleButtonSelected').removeClass('ModuleButtonSelected');
        query('#' + mod_name + 'Button').addClass('ModuleButtonSelected');
    };
    Utils.prototype.buildModuleDOM = function (module_data) {
        if (!module_data) return;
        this.module_data = module_data;
        this.renderTpl('module_menu', {
            menuitems: this.module_data
        });
        //this.appSetState({
        //    mod: this.module_data[0].name
        //});
    };
    Utils.prototype.loadModule = function ( mod_name ) {
        if ( this.currentModule === mod_name ) return;
        this.unloadModule(this.currentModule);
        this.currentModule = mod_name;
        this.selectModuleButton(this.currentModule);

        var container = dom.byId('holder' + this.currentModule);
        if (!container) {
            var container = document.createElement('div');
            container.id = 'holder' + mod_name;
            dom.byId('ModuleContainer').appendChild(container);
        }
        container.style.display = 'block';
        for (var i in this.module_data) {
            if (this.module_data[i].name === this.currentModule)
                this.setTitle(this.module_data[i].label);
        }
        if (container.innerHTML === '') {
            var _this = this;
            Connector.addRequest({
                mod: this.currentModule
            }, function (response) {
                _this.setUpModule(mod_name, response);
            });
        }
    };
    Utils.prototype.unloadModule = function (mod_name) {
        if (!mod_name) return; //firstTime
        var container = dom.byId('holder' + mod_name);
        container.style.display = 'none';
    };
    Utils.prototype.setUpModule = function (mod_name, html) {
        this.setInnerHTML('holder' + mod_name, html);
        setTimeout(function(){
            if (window[mod_name + 'Js'] && window[mod_name + 'Js'].init)
                window[mod_name + 'Js'].init();            
        },0);
    };
    //////////////////////
    //DOM FUNCTIONS
    //////////////////////
    Utils.prototype.setInnerHTML = function (ele_id, content, notparse) {
        if (!ele_id) return;
        var ele = dom.byId(ele_id);
        ele.innerHTML = this.splitContent(content);
        if (notparse !== "notparse") {
            //setTimeout(function(){
            var widgets = registry.findWidgets(ele);
            widgets.forEach(function (w) {
                w.destroyRecursive(true);
                console.log("destroyed: " + w.id);
            });
            parser.parse(ele);
            //},1000);
        }
    };
    Utils.prototype.splitContent = function (raw_html) {
        if (!raw_html)
            return;
        var scriptExpr = "<script[^>]*>(.|\s|\n|\r)*?</script>";
        var matches = raw_html.match(new RegExp(scriptExpr, "img"));
        if (matches) {
            setTimeout(function () {
                for (var i = 0; i < matches.length; i++)
                    eval(matches[i].replace(/<script[^>]*>[\s\r\n]*(<\!--)?|(-->)?[\s\r\n]*<\/script>/img, ""));
            }, 0);
        }
        return raw_html.replace(new RegExp(scriptExpr, "img"), "");
    };
    Utils.prototype.renderTpl = function (node_id, data) {
        var tplNode = dom.byId(node_id);
        if (!tplNode) return;
        if (!this.tpl_cache[node_id])
            this.tpl_cache[node_id] = unescape(tplNode.innerHTML).replace(/<\!--|-->/img, "");
        if (data) {
            var template = new dtl.Template(this.tpl_cache[node_id]);
            tplNode.innerHTML = template.render(new Context(data));
            tplNode.className = tplNode.className.replace('hidden', '');
        } else //Reset if no data
            tplNode.innerHTML = this.tpl_cache[node_id];
        parser.parse(tplNode);
    };
    Utils.prototype.resetTpl=function( node_id ){
        if( this.tpl_cache[node_id] ){
            delete this.tpl_cache[node_id];
        }
    }

    /////////////////////
    //UTILS
    /////////////////////
    Utils.prototype.pend=function( callback ){
        setTimeout(callback,0);
    };
    Utils.prototype.showPopup = function (fvalue, callback, getvars, size) {
        if (!fvalue) return;
        var href = './popup.php?' + ioQuery.objectToQuery(getvars); //Connector.serialize(getvars);
        if (window.popupDialog) {
            popupDialog.close();
        }
        if (!size) {
            size = {
                w: 400,
                h: 300
            };
        }
        window.popupfvalue = fvalue;
        window.popupcallback = callback;
        window.popupDialog = window.open(href, 'dialog', 'width=' + size.w + ',height=' + size.h + ',left=400,top=300');
        window.popupDialog.fvalue = fvalue;
        window.popupDialog.callback = callback;
    };
    Utils.prototype.printOut = function (request) {
        var href = './?' + ioQuery.objectToQuery(request);
        if (window.printerWindow)
            window.printerWindow.close();
        window.printerWindow = window.open(href, '_new');
        window.printerWindow.onlyPrint = true;
    };
    Utils.prototype.initModTabs = function (HostObject, id) {
        var _Utils = this;
        HostObject.selectTab = function (tab) {
            if (!tab.name)
                tab.name = tab.get('content');
            HostObject.active_script = HostObject[tab.name];
            if (!HostObject.active_script)
                return;

            if (tab.html_loaded && !HostObject.active_script.nocache) {
                if (HostObject.active_script.focus)
                    HostObject.active_script.focus();
                return;
            }
            tab.set('content', 'Loading...');

            var tpl_path = HostObject.active_script.tpl;
            if( typeof tpl_path==='object' ){
                var request=tpl_path;
            }
            else{
                var request={tpl: tpl_path};
            }
            Connector.addRequest(request, function (content) {
                tab.set('content', _Utils.splitContent(content));
                setTimeout(function(){
                    HostObject.active_script.init && HostObject.active_script.init();  
                },0);
            });
            tab.html_loaded = true;
        };
        HostObject.tabs = registry.byId(id);
        HostObject.selectTab(HostObject.tabs.getChildren()[0]);
        HostObject.tabs.watch("selectedChildWidget", function (name, oldval, newval) {
            HostObject.selectTab(newval);
        });
    };
});