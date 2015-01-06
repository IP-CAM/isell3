dojo.provide('baycik.tree.Tree');
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");

dojo.declare("baycik.tree.Tree",  [ dijit._Widget, dijit._Templated],
{
	templateString:"<div><div id='${id}_tree'></div></div>",
	request: {},
	
	onRequest:function(){alert('onRequest is not set')},
	onInsert:function(){alert('onInsert is not set')},
	onUpdate:function(){alert('onUpdate is not set')},
	onDelete:function(){alert('onDelete is not set')},
	onSelect:function(){},
		
	startup: function(){
		this.loadTreeData();
	},
	postCreate: function(){
	},
	setupTree: function(){
		if( this.tree )this.tree.destructor();
		
		this.tree=new dhtmlXTreeObject( this.id+'_tree' ,"100%","100%",0);
		this.tree.setImagePath(this.imgPath || "js/dhtmlx/tree/imgs/csh_vista/");
		this.rootId=0;
		this.tree.enableTreeLines(false);
		this.tree.enableDragAndDrop(true);
				
		this.tree.loadJSONObject = function(json, afterCall) {
			if (!this.parsCount)this.callEvent("onXLS", [this, null]);
			this.xmlstate = 1;
			this._p =  new JsonPointer(json);
			this._parse(this._p);
			if (afterCall)afterCall()
		};
		
		var _this=this;
		this.tree.attachEvent("onDrag",function( branch_id, parent_id ){_this.onUpdateItem( branch_id, parent_id );return true});
		this.tree.attachEvent("onDblClick",function(e){_this.onUpdateLabel(e)});
		this.tree.attachEvent("onClick",function(e){_this.onClickItem(e)});
	},
	setData: function( treeObject ){
		var _this=this;
		var openTopBranch=function(){
			if( treeObject.item.length==1 ){
				_this.tree.openItem(treeObject.item[0].id);
			};
		}
		this.setupTree();
		
		this.tree.loadJSONObject( treeObject, openTopBranch );
	},
	loadTreeData: function(){
		this.onRequest( this.request );
	},
	clearSelection:function(){
		if( this.tree.locked )return;
		
		this.onSelect( {branch_id:0,branch_data:{}} );
		this.tree._unselectItems()
	},
	deleteSelected: function(){
		if( this.tree.locked )return;
		
		var branch_id=this.tree.getSelectedItemId();
		if(!branch_id || !confirm('Вы уверенны, что хотите удалить?'))return;
		this.edited_branch_id=branch_id;
		
		var text=this.tree.getItemText(branch_id);
		this.tree.setItemText(branch_id,'<strike>'+text+'</strike>');
		
		this.onDelete( dojo.mixin(this.request,{branch_id:branch_id}) );
	},
	insertNew: function( extra_params ){
		if( this.tree.locked )return;
		var branch_id=this.tree.getSelectedItemId();
		var parent_id=branch_id?branch_id:0;
		
		var init_text=extra_params&&extra_params.text?extra_params.text:'Новая ветка';
		var text=prompt('Введите название ветки',init_text);
		if( text==null )return;
		this.tree.insertNewChild(parent_id,-1,'<b>'+text+'</b>');

		var request=dojo.mixin(dojo.clone(this.request),extra_params,{parent_id:parent_id,text:text});
		this.onInsert( request );
		this.tree.locked=true;
		if( parent_id==0 )
			this.refreshAfterUpdate=true;
	},
	
	
	onInsertReturn:function( new_branch_id ){
		this.tree.locked=false;
		if( new_branch_id==-1 ){//Insert fails
			this.tree.deleteItem(-1);
			return;
		}
		this.tree.changeItemId(-1,new_branch_id);
		
		var text=this.tree.getItemText(new_branch_id).replace(/<\w+>|<\/\w+>/gi, "");
		this.tree.setItemText(new_branch_id,text);
		
		//"ADDED TOP BRANCH NOT APPEAR" WORKAROUND
		if( this.refreshAfterUpdate ){
			this.loadTreeData();
			this.refreshAfterUpdate=false;
		}
	},
	onUpdateReturn:function( success ){//returned value should be an item object!!!
		this.tree.locked=false;
		var branch_id=this.edited_branch_id;
		if( success ){
			var text=this.tree.getItemText(branch_id).replace(/<\w+>|<\/\w+>/gi, "");
			this.tree.setItemText(branch_id,text);
		}
		else
			this.loadTreeData();
	},
	onDeleteReturn:function( success ){
		this.tree.locked=false;
		var branch_id=this.edited_branch_id;
		if( success ){
			this.tree.deleteItem(branch_id);
		}
		else {
			var text=this.tree.getItemText(branch_id).replace(/<\w+>|<\/\w+>/gi, "");
			this.tree.setItemText(branch_id,text);
		}
	},
	onUpdateItem:function( branch_id, parent_id, extra_params ){
		if( this.tree.locked )return;
		
		var text=this.tree.getItemText(branch_id);
		var request={branch_id:branch_id,parent_id:parent_id,text:text};
		
		this.edited_branch_id=branch_id;
		this.onUpdate( dojo.mixin( this.request, request, extra_params ) );
		
		this.tree.setItemText(branch_id,'<b>'+text+'</b>');
		this.tree.locked=true;
		return true;
	},
	onUpdateLabel:function(){
		if( this.tree.locked )return;
		
		var branch_id=this.tree.getSelectedItemId();
		var parent_id=this.tree.getParentId(branch_id);
		
		var text=prompt('Введите название ветки',this.tree.getSelectedItemText());
		if( text==null )return;
		this.tree.setItemText(branch_id,text);
		this.onUpdateItem( branch_id, parent_id );
		return false;
	},
	onClickItem:function( branch_id ){
		//if( this.tree.getOpenState(branch_id)==-1 )this.tree.openItem(branch_id);
		//else this.tree.closeAllItems(branch_id);
		this.onSelect( {branch_id:branch_id,branch_data:this.tree.getUserData(branch_id,'branch_data')} );
	}

});