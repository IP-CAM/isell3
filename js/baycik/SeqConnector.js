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
        SeqConnector.prototype.freeze = function () {
            this.freezed = true;
        };
        SeqConnector.prototype.unfreeze = function () {
            document.body.removeChild(document.getElementById('SeqDialog'));
            this.freezed = false;
        };
        SeqConnector.prototype.goon = function () {
            this.unfreeze();
            this._addToSequence(this.currRequestObject);
            this._postNextRequest();
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
	    
	    
	    
	    
            xhr(this.processorUrl, {
                method: 'POST',
                data: requestObject.post_vars
            }).then(function (response) {
                if (advance) {
                    _this.pending = false;
                    _this._postNextRequest();
                }
                try {
                    if (response !== '') { //Allow empty response
                        response = JSON.parse(response);
                    }
                } catch (e) {
                    console.log(e);
                    alert("Direct response:\n" + response);
                    return;
                }
                _this._onResponse(response, requestObject);
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
            case 'kick':
                this._loadDialog(response.content, response.msg);
                this.freeze();
                commit_handler = false;
                response.msg = '';
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
            if (this.freezed === true)
                return;
            if (response.msg)
                alert(response.msg);
            if (commit_handler)
                this._commitResponseHandler(requestObject, response.content == undefined ? response : response.content);
            this._postNextRequest();
        };
        SeqConnector.prototype._commitResponseHandler = function (requestObject, response) {
            if (typeof requestObject.listener === 'object')
                eval('requestObject.listener.' + requestObject.handler_name + '(response)');
            else if (typeof requestObject.listener === 'function')
                requestObject.listener(response);
        };
        SeqConnector.prototype._loadDialog = function (url, msg) {
            xhr(url).then(function (html) {
                var dialog = document.createElement('div');
                dialog.id = 'SeqDialog';
                dialog.innerHTML = html;
                document.body.appendChild(dialog);
                document.getElementById('SeqDialogMsg').innerHTML = msg;
            });
        };
    }
);