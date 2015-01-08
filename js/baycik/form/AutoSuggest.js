dojo.provide("baycik.form.AutoSuggest");
dojo.require("dijit._Widget");
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("baycik.form.AutoSuggest",  [dijit._Widget, dojox.dtl._DomTemplated],
{
	templatePath: dojo.moduleUrl('baycik.form.tpl','auto_suggest.tpl'),
	onRequest:function(){alert('onRequest is not set')},
	onChange:function(){},
	field:'',
	listwidth:'',
	width:'',
	minClueLength:-1,
	constructor:function(){
		this.suggData={};
		this.value='';
		this.prevValue='';
		this.prevQuery='';
		this.label='';
		this.identifier='';
		this.selectedIndex=-1;
		this.hidden=1;
		this.field=this.label;
	},
	postCreate:function(){
		var _this=this;
		setTimeout(function(){_this._init()},0);
	},
	focus:function(){
		dojo.byId(this.id+'input').focus();
	},
	_init:function(){
		this.input=dojo.byId(this.id+"input");
		this.connect(this.input,"onkeypress","_oninput" );
		this.connect(this.input,"onblur","_hideDelayed" );
		
		this.sugg_holder.style.width= this.listwidth?this.listwidth:'';
		this.input.style.width= this.width?this.width:'';
	},
	_oninput:function( e ){
		switch( e.charOrCode ){
			case dojo.keys.UP_ARROW://up
				if( this.input.value=='' )
					this.input.value=this.prevQuery;
				this._walkSuggestions(true);
				if( this.hidden )this._suggest();//suggest when
			break;
			case dojo.keys.DOWN_ARROW://down
				this._walkSuggestions(false);
				if( this.hidden )this._suggest();
			break;
			case dojo.keys.LEFT_ARROW://down
				if( this.input.value=='' )
					this.input.value=this.prevValue;
			break;
			case dojo.keys.ENTER://enter
				this._setSelectedValue();
				this._hideSuggestions();
			break;
			case dojo.keys.ESCAPE://escape
				this._hideSuggestions();
			break;
			default:
				if( typeof e.charOrCode == 'string' || e.charOrCode == 229 || e.charOrCode == 8 ){
					this._suggest();
				}
			break;
		}
	},
	_suggest:function(){
		clearTimeout(this._delayId);
		var _this=this;
		this._delayId=setTimeout(function(){ _this._getSuggData() },200);
	},
	setData:function( sd ){
		if( !sd.items )return;//When wrong clue must disappear ??? || sd.items.length==0
		this.suggData=sd;
		this.identifier=this.suggData.identifier;
		this.label=this.suggData.label;
		
		this.label_list=new Array();
		for( i in this.suggData.items ){
			this.label_list.push(this.suggData.items[i][this.label]);
		}
		this._selectSuggItem(-1);
		this._showSuggestions();
		this.render();
	},
	_getSuggData:function(){
		var clue=this.input.value;
		if( clue.length<=this.minClueLength )return;
		//this._hideSuggestions();
		this.onRequest( {clue:clue} );
	},
	_walkSuggestions:function( is_up ){
		if( this.hidden )return;
		var tmpIndex;
		if( is_up ){
			tmpIndex=this.selectedIndex-1;
			if( tmpIndex<0 ){
				tmpIndex=this.suggData.items.length-1;
			}
		}
		else{
			tmpIndex=this.selectedIndex+1;
			if( tmpIndex>this.suggData.items.length-1 ){
				tmpIndex=0;
			}
		}
		this._selectSuggItem(tmpIndex);
	},
	_selectSuggItem:function( index ){
		if( this.selectedIndex!=-1 && dojo.byId(this.id+'_sugg'+this.selectedIndex) )
			dojo.removeClass(dojo.byId(this.id+'_sugg'+this.selectedIndex),'asugg_hover');
		this.selectedIndex=index;
		if( this.selectedIndex!=-1 && dojo.byId(this.id+'_sugg'+this.selectedIndex) )
			dojo.addClass(dojo.byId(this.id+'_sugg'+this.selectedIndex),'asugg_hover');
	},
	_showSuggestions:function(){
		if( !this.sugg_holder )return;
		this.sugg_holder.style.display="block";
		this.hidden=0;
	},
	_hideSuggestions:function(){
		if( !this.sugg_holder )return;
		this.sugg_holder.style.display="none";
		this.hidden=1;
	},
	_hideDelayed:function(){
		var _this=this;
		setTimeout(function(){_this._hideSuggestions()},300);
	},
	_setSelectedValue:function(){
		this.value=this.suggData.items[this.selectedIndex];
		this.prevQuery=this.input.value;
		this.prevValue=this.input.value=this.value[this.field];
		this.onChange( this.value[this.identifier], this.value );
	}
});