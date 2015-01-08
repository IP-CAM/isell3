function JsonPointer(data, parent) {
    this.d = data;
    this.dp = parent;
};
JsonPointer.prototype = {
    text: function() {
        var afff = function(n) {
            var p = [];
            for (var i = 0; i < n.length; i++)
                p.push("{" + sfff(n[i]) + "}");
            return p.join(",")
            };
        var sfff = function(n) {
            var p = [];
            for (var a in n)
                if (typeof(n[a]) == "object") {
                if (a.length)
                    p.push('"' + a + '":[' + afff(n[a]) + "]");
                else
                    p.push('"' + a + '":{' + sfff(n[a]) + "}")
                } else
                p.push('"' + a + '":"' + n[a] + '"');
            return p.join(",")
            };
        return "{" + sfff(this.d) + "}"
    },
    get: function(name) {
        return this.d[name]
        },
    exists: function() {
        return !! this.d
    },
    content: function() {
        return this.d.content
    },
    each: function(name, f, t) {
        var a = this.d[name];
        var c = new JsonPointer();
        if (a)
            for (var i = 0; i < a.length; i++) {
            c.d = a[i];
            f.apply(t, [c, i])
            }
    },
    get_all: function() {
        return this.d
    },
    sub: function(name) {
        return new JsonPointer(this.d[name], this.d)
        },
    sub_exists: function(name) {
        return !! this.d[name]
        },
    each_x: function(name, rule, f, t, i) {
        var a = this.d[name];
        var c = new JsonPointer(0, this.d);
        if (a)
            for (i = i || 0; i < a.length; i++)
            if (a[i][rule]) {
            c.d = a[i];
            if (f.apply(t, [c, i]) == -1)
                return
        }
    },
    up: function(name) {
        return new JsonPointer(this.d.parentNode, this.d)
        },
    set: function(name, val) {
        this.d[name] = val
    },
    clone: function(name) {
        return new JsonPointer(this.d, this.dp)
        },
    through: function(name, rule, v, f, t) {
        var a = this.d[name];
        if (a.length)
            for (var i = 0; i < a.length; i++) {
            if (a[i][rule] != null && a[i][rule] != "" && (!v || a[i][rule] == v)) {
                var c = new JsonPointer(a[i], this.d);
                f.apply(t, [c, i])
                };
            var w = this.d;
            this.d = a[i];
            if (this.sub_exists(name))
                this.through(name, rule, v, f, t);
            this.d = w
        }
    }
};
require([
	"dojo/_base/declare",
	"dijit/_Widget",
	"dijit/_Templated"
],
function(
	declare,
	_Widget,
	_Templated
) {
return declare("baycik.tree.TreeWidget", [_Widget, _Templated], {

	templateString:"<div><div id='${id}_tree'></div></div>",
	
	onRequest:function(){alert('onRequest is not set')},
	onInsert:function(){alert('onInsert is not set')},
	onUpdate:function(){alert('onUpdate is not set')},
	onDelete:function(){alert('onDelete is not set')},
	onSelect:function(){},
		
	constructor: function(){
		this.request={};
		this.locked=true;
		this.alreadyLoaded=[];
	},
	startup: function(){
		this.loadTreeData();
	},
	postCreate: function(){
	},
	setupTree: function(){
		if( this.tree )this.tree.destructor();
		
		this.tree=new dhtmlXTreeObject( this.id+'_tree' ,"100%","100%",0);
		this.tree.setImagePath(this.imgPath || "js/dhtmlx/tree/imgs/csh_dhx_skyblue/");
		this.rootId=0;
		this.alreadyLoaded=new Array();
		this.tree.enableTreeLines(false);
		this.tree.enableDragAndDrop(true);
				
		this.tree.loadJSONObject = function(json, afterCall) {
			if (!this.parsCount)this.callEvent("onXLS", [this, null]);
			this.xmlstate = 1;
			this._p =  new JsonPointer(json,this.expectedParent);
			this._parse(this._p);
			if (afterCall)afterCall();
		};
		
		var _this=this;
		this.tree.attachEvent("onDrag",function( branch_id, parent_id ){_this.loadFolderContets( parent_id );_this.onUpdateItem( branch_id, parent_id );return true});
		this.tree.attachEvent("onDblClick",function(e){_this.onUpdateLabel(e)});
		this.tree.attachEvent("onClick",function(e){_this.onClickItem(e)});
		//this.tree.attachEvent("onSelect",function(id){_this.edited_branch_id=id});
	},
	setData: function( treeObject ){
		if( this.alreadyLoaded[treeObject.id]!=1 ){
			var _this=this;
			var openTopBranch=function(){
				return;
				if( treeObject.item.length==1 ){
					_this.tree.openItem(treeObject.item[0].id);
				};
			}
			if( !this.tree )this.setupTree();
			this.tree.loadJSONObject( treeObject, openTopBranch );
			this.locked=false;
		}
		this.alreadyLoaded[treeObject.id]=1;
	},
	loadTreeData: function(){
		if( this.alreadyLoaded[this.request.id]!=1 )//Redusing unnecessary traffic
			this.onRequest( this.request );
	},
	reload: function(){
		this.setupTree();
		this.loadTreeData();
	},
	clearSelection:function(){
		if( this.locked )return;
		this.tree._unselectItems()
		this.onSelect( {branch_id:0,branch_data:{}} );
	},
	deleteSelected: function(){
		if( this.locked )return;
		
		var branch_id=this.tree.getSelectedItemId();
		if(!branch_id || !confirm('Вы уверенны, что хотите удалить?'))return;
		this.edited_branch_id=branch_id;
		
		var text=this.tree.getItemText(branch_id);
		this.tree.setItemText(branch_id,'<strike>'+text+'</strike>');
		
		this.onDelete( dojo.mixin(this.request,{branch_id:branch_id}) );
	},
	insertNew: function( extra_params ){
		if( this.locked ){
			return false;
		}
		var branch_id=this.tree.getSelectedItemId();
		var parent_id=branch_id?branch_id:0;
		
		var init_text=extra_params&&extra_params.text?extra_params.text:'Новая ветка';
		var text=prompt('Введите название ветки',init_text);
		if( text==null )return;
		this.tree.insertNewChild(parent_id,-1,'<b>'+text+'</b>');

		var request=dojo.mixin(dojo.clone(this.request),extra_params,{parent_id:parent_id,text:text});
		this.onInsert( request );
		this.locked=true;
		if( parent_id==0 )
			this.refreshAfterUpdate=true;
	},
	
	
	onInsertReturn:function( new_branch_id ){
		if( new_branch_id==-1 ){//Insert fails
			this.tree.deleteItem(-1);
			return;
		}
		this.tree.changeItemId(-1,new_branch_id);
		
		var text=this.tree.getItemText(new_branch_id).replace(/<\w+>|<\/\w+>/gi, "");
		this.tree.setItemText(new_branch_id,text);
		this.locked=false;
		
		//"ADDED TOP BRANCH NOT APPEAR" WORKAROUND
		if( this.refreshAfterUpdate ){
			this.reload();
			this.refreshAfterUpdate=false;
		}
	},
	onDeleteReturn:function( success ){
		var branch_id=this.edited_branch_id;
		if( success ){
			this.tree.deleteItem(branch_id);
		}
		else {
			var text=this.tree.getItemText(branch_id).replace(/<\w+>|<\/\w+>/gi, "");
			this.tree.setItemText(branch_id,text);
		}
		this.locked=false;
	},
	onUpdateReturn:function( treeItem ){//returned value should be an item object!!!
		var branch_id=this.edited_branch_id;
		if( treeItem ){
			var text=this.tree.getItemText(branch_id).replace(/<\w+>|<\/\w+>/gi, "");
			this.tree.setItemText(branch_id,text);
			if( treeItem.parent_id!=this.tree.getParentId(branch_id) ){//undo dnd
				alert('Перетаскивание не удалось!');
				this.tree.selectItem(branch_id,false);
				this.tree.doCut();
				this.tree.doPaste(treeItem.parent_id);
			}
		}
		else
			this.reload();
		this.locked=false;
	},
	onUpdateItem:function( branch_id, parent_id, extra_params ){
		if( this.locked ){return false;
		}
		
		var text=this.tree.getItemText(branch_id);
		var request={branch_id:branch_id,parent_id:parent_id,text:text};
		
		this.edited_branch_id=branch_id;
		this.onUpdate( dojo.mixin( this.request, request, extra_params ) );
		
		this.tree.setItemText(branch_id,'<b>'+text+'</b>');
		this.locked=true;
		return true;
	},
	onUpdateLabel:function(){
		if( this.locked ){
			return false;
		}
		var branch_id=this.tree.getSelectedItemId();
		var parent_id=this.tree.getParentId(branch_id);
		var text=prompt('Введите название ветки',this.tree.getSelectedItemText());
		if( text==null )return;
		this.tree.setItemText(branch_id,text);
		this.onUpdateItem( branch_id, parent_id );
		return false;
	},
	loadFolderContets:function( branch_id ){
		if( !this.tree.getSubItems(branch_id) ){
			var old_id=this.request.id;
			this.request.id=branch_id;
			this.loadTreeData();
            this.request.id=old_id;
		}
	},
	onClickItem:function( branch_id ){
		if( this.locked ){
			return false;
		}
		if( this.tree.getOpenState(branch_id)==-1 )this.tree.openItem(branch_id);
		else this.tree.closeAllItems(branch_id);
		this.loadFolderContets( branch_id );
		this.onSelect( {branch_id:branch_id,branch_data:this.tree.getUserData(branch_id,'branch_data')} );
	}
  });
});