SeqConnector = function (processorUrl) {
    this.processorUrl = processorUrl || './';
    this.pending = false;
    this.sequence = [];
    this.currRequestObject = {};
};
require([
        "dojo/request/xhr",
        "dojo/json"
    ],
    function (
        xhr,
        JSON
    ) {
        SeqConnector.prototype.addRedirection = function (request) {
            var query = this.serialize(request);
            location.href = this.processorUrl + '?' + query;
        };
        SeqConnector.prototype.addCss = function (path) {
            var css = document.createElement("link");
            css.rel = "stylesheet";
            css.type = "text/css";
            css.href = path;
            document.getElementsByTagName("head")[0].appendChild(css);
        };
        SeqConnector.prototype.addScript = function (path, listener) {
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.src = path;
            script.async = true;
            script.charset = 'utf-8';
            script.onload = script.onreadystatechange = function () {
                if ((!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                    listener();
                }
            };
            document.getElementsByTagName("head")[0].appendChild(script);
        };
        SeqConnector.prototype.serialize = function (obj) {
            var str = [];
            for (var p in obj)
                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
            return str.join("&");
        };
        SeqConnector.prototype.addRequest = function (post_vars, listener, handler_name) {
            var requestObject = {
                post_vars: post_vars,
                listener: listener,
                handler_name: handler_name
            };
            this._addToSequence(requestObject);
        };
        SeqConnector.prototype.sendRequest = function (post_vars, listener, handler_name) {
            var requestObject = {
                post_vars: post_vars,
                listener: listener,
                handler_name: handler_name
            };
            this._postRequest(requestObject);
        };


        SeqConnector.prototype._addToSequence = function (requestObject) {
            this.sequence.push(requestObject);
            this._postNextRequest();
        };
        SeqConnector.prototype._postNextRequest = function () {
            if (this.pending === true || this.sequence.length === 0)
                return;
            this.pending = true;
            this.currRequestObject = this.sequence.shift();
            this._postRequest(this.currRequestObject, 'next');
        };
        SeqConnector.prototype._postRequest = function (requestObject, advance) {
            var _this = this;
	    var url=this.processorUrl;
	    
	    var post_vars=$.extend({}, requestObject.post_vars);
	    if( post_vars.mod ){
		url+='Proc'+post_vars.mod;
		delete post_vars.mod;
		if( post_vars.rq ){
		    url+='/on'+post_vars.rq;
		    delete post_vars.rq;
		}
	    }
	    else if( post_vars.tpl ){
		url+='page/'+post_vars.tpl;
		requestObject.method='GET';
		delete post_vars.tpl;
	    }
	    
            var promise=xhr(url, {
                method: requestObject.method||'POST',
                data: post_vars
            })
	    promise.response.then(function (response) {
                if (advance) {
                    _this.pending = false;
                    _this._postNextRequest();
                }
                try {
		    var responseObject={};
		    responseObject.type=response.getHeader('X-isell-type');
		    if( response.getHeader('X-isell-msg') ){
			responseObject.msg= decodeURIComponent(response.getHeader('X-isell-msg'));
		    }
		    if( response.getHeader('X-isell-format')=='json' ){
			responseObject.content = JSON.parse(response.text);
		    }
		    else{
			responseObject.content=response.text;
		    }
                } catch (e) {
                    console.log(e);
                    alert("Unexpected server response:\n\n" + response.text);
                    return;
                }
                _this._onResponse(responseObject, requestObject);
            });
        };
        SeqConnector.prototype._onResponse = function (response, requestObject) {
            var commit_handler = true;
            switch (response.type) {
            case 'wrn':
                alert(response.content);
                break;
            case 'error':
                alert('Програмная ошибка сервера:\n\n' + response.content);
                commit_handler = false;
                break;
            case 'dialog':
                this._loadDialog(response.content, response.msg);
                this.freezeSequence();
		return;
                break;
            case 'confirm':
                if (confirm(response.content)) {
                    requestObject.post_vars._confirmed = 1;
                    this._addToSequence(requestObject);
                    delete requestObject.post_vars._confirmed;
                    commit_handler = false;
                }
                break;
            };
            if ( response.msg ){
                alert( response.msg );
	    }
            if (commit_handler){
                this._commitResponseHandler(requestObject, response.content );
	    }
            this._postNextRequest();
        };
        SeqConnector.prototype._commitResponseHandler = function (requestObject, response) {
	    if( requestObject.handler_name ){
		requestObject.listener[requestObject.handler_name](response);
	    }
	    else{
		requestObject.listener && requestObject.listener(response);
	    }
        };
        SeqConnector.prototype._loadDialog = function (html, msg) {
	    var dialog = $("<div id='SeqDialog'></div>").html(html);
	    $("#SeqDialog script").each(function() { eval(this.text);} );
	    $('body').append(dialog);
	    $("#SeqDialogMsg").html(msg);
        };
        SeqConnector.prototype.freezeSequence = function () {
	    this.freezedRequestObject=this.currRequestObject;
            this.freezed = true;
        };
        SeqConnector.prototype.unfreezeSequence = function () {
	    if(this.freezed){
		this.freezed = false;
		$("#SeqDialog").remove();
		this._addToSequence(this.freezedRequestObject);
		this._postNextRequest();
	    }
        };
   }
);