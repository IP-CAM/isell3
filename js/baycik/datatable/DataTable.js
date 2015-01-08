dojo.provide("baycik.datatable.DataTable");
dojo.require("dijit._Widget");
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("baycik.datatable.DataTable",  [ dijit._Widget, dojox.dtl._DomTemplated],
{
	
	templatePath: dojo.moduleUrl('baycik.datatable','data_table.tpl'),
	standartTools:{
		"space":{img:'space.png',command:'doNone()',tip:' '},
		"download":{img:'xls_download.png',command:'xlsDownload()',tip:'Скачать .xls файл'},
		"print":{img:'print.png',command:'print()',tip:'Напечатать'},
		"add":{img:'add.png',command:'insertRow()',tip:'Добавить строку'},
		"silentadd":{img:'add.png',command:'silentInsertRow()',tip:'Добавить строку'},
		"delete":{img:'delete.png',command:'deleteSelected()',tip:'Удалить выделенное'},
		"upload":{img:'xls_upload.png',command:'xlsUpload()',tip:'Отправить .xls файл'}
	},
	
	constructor: function(){
		this.edited_cell=null;
		this.request={page:1,limit:50};
		this.check_all=true;
		this.filter=new Array();
		this.customTools=[];
		this.selected_rows=[];
	},
	startup:function(){
		this.loadTableStructure();
	},
	
	calculateRow: function(){},
	doNone: function(){},
	onStructure: function(){this.setStatus('onStructure is not set')},
	onRequest: function(){this.setStatus('onRequest is not set')},
	onDelete: function(){this.setStatus('onDelete is not set')},
	onUpdate: function(){this.setStatus('onUpdate is not set')},
	onInsert: function(){this.setStatus('onInsert is not set')},
	onPrint: function(){this.setStatus('onPrint is not set')},
	onXlsDownload: function(){this.setStatus('onXlsDownload is not set')},
	onXlsUpload: function(){this.setStatus('onXlsUpload is not set')},
	onSelect: function(){},
	onDataSet: function(){},

	
	
	_startAnim:function(){
		if( dojo.byId(this.id+'_loading') ){
			dojo.byId(this.id+'_loading').style.display='inline';
			dojo.byId(this.id+'_reload').style.display='none';
		}
	},
	_stopAnim:function(){
		if( dojo.byId(this.id+'_loading') ){
			dojo.byId(this.id+'_loading').style.display='none';
			dojo.byId(this.id+'_reload').style.display='inline';
		}
	},
	nextPage:function(){
		this.request.page++;
		if( this.request.page>this.tableData.total_pages )this.request.page=1;
		this.loadTableData();
	},
	prevPage:function(){
		this.request.page--;
		if( this.request.page<1 )this.request.page=this.tableData.total_pages;
		this.loadTableData();
	},
	gotoPage:function(){
		var page=prompt('Номер страницы',this.request.page);
		if( page ){
			this.request.page=page;
			if( this.request.page>this.tableData.total_pages )this.request.page=1;
			if( this.request.page<1 )this.request.page=this.tableData.total_pages;
			this.loadTableData();
		}
	},
	setPageLimit:function(){
		var limit=prompt('Колличество строк в таблице',this.request.limit);
		if( limit && limit>0 ){
			this.request.limit=limit;
			this.loadTableData();
		}
	},
	sortColumn: function(e){
		for(var i in this.tableStruct.columns){
			if( e.target.innerHTML == this.tableStruct.columns[i].name )this.tableStruct.pos=i;
		}
		this.compile();
	},
	onFilter:function(e){
		if( this.filter[e.target.name]!=e.target.value ){
			if( e.keyCode!=13 )return   true;//enter key
			this.setFilter( e.target.name, e.target.value );
		}
	},
	resetFilter:function(){
		document[this.id+'_form'].reset();
		this.filter=[];
		this.request.page=1;
		this.loadTableData();
	},
	setFilter:function( col, val, set_input ){
		if( set_input ){
			document[this.id+'_form'][col].value=val;
		}
		this.filter[col]=val;
		this.request.page=1;

		clearTimeout(this.refreshDelay);
		var _this=this;
		this.refreshDelay=setTimeout( function(){_this.loadTableData()}, 0 );
	},
	restoreFilter:function(){
		var form=document[this.id+'_form'];
		for(i in this.filter){
			form[i].value=this.filter[i];
			form[i].focus();
		}
	},
	clearSelection: function( node ){
		var cleared=false;
		for( var i=0; i<this.selected_rows.length; i++ ){
			if( !node || this.selected_rows[i].node==node ){
				dojo.removeClass(this.selected_rows[i].node,'data_table_selected');
				this.selected_rows.splice(i,1);
				cleared=true;
				i--;
			}
		}
		return cleared;
	},
	getRowNum: function( node ){
		return node&&node.childNodes[0]?node.childNodes[0].innerHTML.match(/\d+/):0;
	},
	addSelection: function( node ){
		var row_num=this.getRowNum(node);
		if( row_num ){
			var data={};
			for( var i=0; i<this.tableStruct.columns.length; i++ )
				data[this.tableStruct.columns[i].field]=this.tableData.rows[row_num-1][i];
			
			dojo.addClass(node,'data_table_selected');
			this.selected_rows.push( {'data':data,'node':node,'row_num':row_num} );
			return data;
		}
	},
	seekField: function( field ){
		for( var i=0;i<this.tableData.rows.length;i++ ){
			if( this.tableData.rows[i][field.index]==field.value ){
				this.selectRowNum(i);
				return true;
			}
		}
		return false;
	},
	selectRowNum: function( row_num ){
		//alert(this.rowNodeRegister);
		this.selectRow( {target:{parentNode:this.rowNodeRegister[row_num]}} );
	},
	selectRow: function( e ){
		if( !e )return   true;
		var node=e.target.parentNode;
		var toolcmd='';
		if( e.target.tagName=='IMG' ){
			toolcmd=e.target.alt;
			node=e.target.parentNode.parentNode;
		}
		
		if( e.ctrlKey && this.tableStruct.multiselect ){
			if( this.clearSelection(node) )return   true;//Cancel if already selected
		}
		else {
			if( this.clearSelection(node) && !this.tableStruct.stikyselect && this.selected_rows.length==0 )
				return   true;//Cancel if already selected
			this.clearSelection();
		}
		this.hilightRow();
		var data=this.addSelection(node);
		data.toolcmd=toolcmd;
		this.onSelect(data,node);
	},
	hilightRow: function( e ){
		if( this.hilighted_row ){
			dojo.removeClass(this.hilighted_row,'data_table_hover');
		}
		this.hilighted_row=null;

		if( !e )return   true;
		var node=e.target.parentNode;
		var not_selected=true;
		for( var i=0; i<this.selected_rows.length; i++ )
			if( this.selected_rows[i].node==node )
				not_selected=false;
		
		if( not_selected ){
			if( node )dojo.addClass(node,'data_table_hover');
			this.hilighted_row=node;
		}
	},
	selectAll: function(){//select all delete bug
		if( this.tableStruct.readonly || !this.tableStruct.multiselect )return   true;
		var data_rows=dojo.byId(this.id+'_data_rows');
		for( var i in data_rows.rows ){
			if( !data_rows.rows[i] || !data_rows.rows[i].cells || data_rows.rows[i].className!='data_table_row' )continue;
			this.addSelection(data_rows.rows[i]);
		}
	},
	deleteSelected:function(){
		if( !this.selected_rows.length ){
			this.setStatus('Выберите удаляемые строчки');
			return;
		}
		if( !confirm('Вы уверенны что хотите удалить?') ){
			return;
		}
		
		var del_ids='';
		for( var i=0;i<this.selected_rows.length; i++ ){
			del_ids+=','+this.getRowId(this.selected_rows[i].row_num-1);
		}

		this._startAnim();
		this.request.delete_ids='['+del_ids.substr(1)+']';
		this.onDelete( this.request );
		this.loadTableData();

		delete this.request.delete_ids;
	},
	getRowId: function( row_index ){
		var row_ids='';
		for( var k in this.tableStruct.columns ){
			if( !this.tableStruct.columns[k].is_key )continue;
			
			if( row_index!=null ){
				var id_val=this.tableData.rows[row_index][k];
			}
			else{
				if(this.tableStruct.columns[k].hidden)continue;//skip asking hidden key
				var id_val=prompt('Введите значение ключа "'+this.tableStruct.columns[k].name+'"','');
				if( id_val==null || id_val=='' )return false;
			}
			row_ids+=',"'+id_val+'"';
		}
		if( row_ids.length ){
			return '['+row_ids.substr(1)+']';
		}
		this.setStatus('baycik.DataTable: No key found');
		return false;
	},
	makeEditor: function( ele,row_num,col_num ){
		this.onDblClick(this.tableData.rows[row_num],ele);
		if( this.tableStruct.readonly || this.tableStruct.columns[col_num].readonly || !ele || this.edited_cell==ele )return true;
		this.edited_row=row_num;
		this.edited_col=col_num;
		this.edited_cell=ele;
		
		var old_value=this.edited_cell.innerHTML;
		var new_value=prompt('Введите новое значение',old_value);
		if( new_value!=old_value && new_value!=null ){
			if( old_value==parseFloat(old_value) ){
				eval('new_value='+String(new_value).match( /[\(\d\.\)\*\/\+-]*/ ));//if cell is numeric do eval
			}
			this.updateCell( new_value );
		}
		this.edited_cell=null;
		return true;
	},
	updateCell: function ( new_value ){
		this._startAnim();
		this.request.update_id='['+this.getRowId(this.edited_row)+']';
		this.request.update_col=this.tableStruct.columns[this.edited_col].field;
		this.request.update_val=new_value;
		
		this.tableData.rows[this.edited_row][this.edited_col]=new_value;
		this.compile();
		
		this.onUpdate( this.request );
		delete this.request.update_col,this.request.update_id,this.request.value;
		this.loadTableData();
	},
	silentInsertRow: function(){
		this._startAnim();
		this.request.insert_id='';
		this.onInsert( this.request );
		delete this.request.insert_id;
		this.loadTableData();
	},
	insertRow: function( is_silent ){
		var insert_id=this.getRowId();
		if( !insert_id )return;
			
		this._startAnim();
		this.request.insert_id=insert_id;
		this.onInsert( this.request );
		
		var counter=0;
		var key_values=eval(insert_id);
		for( var i in this.tableStruct.columns ){
			if( !this.tableStruct.columns[i].is_key || this.tableStruct.columns[i].hidden )continue;
			
			var col=this.tableStruct.columns[i].field;
			var val=key_values[counter++];
			
			this.setFilter( col, val, true );
		}
		delete this.request.insert_id;
	},
	getTools: function( tableStruct ){
		var table_tools=new Array();
		if( tableStruct.toolnames ){
			for( var i=0; i<tableStruct.toolnames.length; i++ ){
				if( tableStruct.extratools && tableStruct.extratools[tableStruct.toolnames[i]] )
					table_tools.push( tableStruct.extratools[tableStruct.toolnames[i]] );
				else if( this.standartTools[tableStruct.toolnames[i]] )
					table_tools.push( this.standartTools[tableStruct.toolnames[i]] );
			}
		}
		//alert( table_tools.toSource() );
		return table_tools;
	},
	xlsDownload: function(){
		this.onXlsDownload( this.request );
	},
	xlsUpload: function(){
		this.onXlsUpload( this.request );
	},
	xlsUpload: function( button ){
		this.onXlsUpload( this.request, button );
	},
	print: function(){
		this.onPrint( this.request );
	},
	setStatus: function ( msg, permanent ){
		var status_node=dojo.byId(this.id+'_message');
		if( !status_node )return   true;
		
		if( !this.table_info_html ){
			this.table_info_html=status_node.innerHTML;
		}
		if( msg ){
			dojo.byId(this.id+'_message').style.display='block';
			dojo.byId(this.id+'_status').style.display='none';
			
			if(msg.length>160)msg=msg.substr(0,160)+' ...';
			status_node.innerHTML=msg;
			clearTimeout(this.status_timeout);
			if( permanent )return   true;
			var _this=this;
			this.status_timeout=setTimeout(function(){_this.setStatus()},4000);
		}
		else {
			dojo.byId(this.id+'_message').style.display='none';
			dojo.byId(this.id+'_status').style.display='block';
		}
	},
	resetTable: function(){
		this.request.page=1;
	},





	loadTableStructure:function(){
		this.onStructure( this.request );
	},
	setStructure: function( tableStruct ){
		if( !tableStruct.columns ){
			alert("Ошибка получения структуры таблицы");
			return   true;
		}
		if( !tableStruct.hidetools )tableStruct.tools=this.getTools(tableStruct);
		this.tableStruct=tableStruct;
		
		this.resetTable();
		this.loadTableData();
	},
	loadTableData:function(){
		var cols='',vals='';
		for( var i in this.filter ){
			//skip empty filter
			if( this.filter[i]=='' )continue;
			cols+=',"'+i+'"';
			vals+=',"'+this.filter[i].replace(/\"/,'\\"')+'"';
		}
		this.request.cols='['+cols.substr(1)+']';
		this.request.vals='['+vals.substr(1)+']';

		this._startAnim();
		this.onRequest( this.request ); 
	},
	setData: function( tableData ){
		this.tableData=tableData;
		
		this.setStatus("");
		if( !tableData || !tableData.rows || !tableData.rows.length ){
			this.setStatus("Ничего не найдено");
		}
		else {
			if( this.tableStruct.columns.length!=this.tableData.rows[0].length )
				this.setStatus('Counts of columns in structure and data are not same!');
		}
		
		this._stopAnim();
		
		if( !this.edited_cell )this.buildTable();
		
		//this.onDataSet()
		var _this=this;setTimeout(function(){_this.onDataSet()},100);
	},
	calculateColumns: function( table_dom_row ){
		if( table_dom_row.cells[0] )//Hide row id column
			table_dom_row.cells[0].style.display=this.tableStruct.showrowid?'':'none';

		for( var i=0; i<this.tableStruct.columns.length; i++ ){
			if( !table_dom_row.cells[i+1] )continue;
			var cell=table_dom_row.cells[i+1];
			var column=this.tableStruct.columns[i];
			
			if( column.hidden )
				cell.style.display='none';
			if( table_dom_row.className!='data_table_row' )//Content Rows
				continue;
			switch (column.type){
				case 'number':
					cell.style.textAlign='right';
				break;
				case 'string':
					cell.style.textAlign='left';
				break;
				case 'tooltip':
					if( !cell.innerHTML )continue;//Skip if tooltip is empty
					var tokens=cell.innerHTML.replace(/<\w+[^>]*>|<\/\w+>/gi, "").match(/(\w+)(\s?)(.*)/);
					if( !tokens || !tokens[1] )
						return   true;
					var type=tokens[1];
					var msg=tokens[3];
					cell.innerHTML='<img src="js/baycik/datatable/img/'+type+'.png" alt="'+type+'">';
					cell.title=msg;
				break;
				case 'short':
					var msg=cell.innerHTML;
					cell.innerHTML=msg.length>column.length?msg.substring(0,column.length-4)+'<span title="'+msg+'"> ...</span>':msg;
				break;
				case 'json':
					if( cell.innerHTML ){
						var json=dojo.fromJson(cell.innerHTML);
						if( json && json[column.property] ){
							cell.innerHTML=json[column.property];
						}
					}
				break;
			}
			switch (column.align){
				case 'right':
					cell.style.textAlign='right';
				break;
				case 'left':
					cell.style.textAlign='left';
				break;
				case 'center':
					cell.style.textAlign='center';
				break;
			}
		}
	},
	calculateTable: function(){
		this.rowNodeRegister=[];
		var data_rows=dojo.byId(this.id+'_data_rows');
		for( var i in data_rows.rows ){
			if( !data_rows.rows[i] || !data_rows.rows[i].cells )continue;
			var table_dom_row=data_rows.rows[i];
			
			this.calculateColumns(table_dom_row);
			if( table_dom_row.className!='data_table_row' )continue;//skip thead
			this.calculateRow(table_dom_row);
			this.rowNodeRegister.push(table_dom_row);
		}
	},
	buildTable: function(){
		this.compile();
		this.rootNode.style.visibility='visible';
	},
	//<table id="{{id}}_data_rows" class="data_table" border="0" cellspacing="0" cellpadding="0" onmouseout="dijit.byId(\'{{id}}\').hilightRow()"></table>
	alt_render:function(){
		if(!dojo.isFF)return   true;
		var tpl='<thead><tr><th class="data_table_del" style="cursor:pointer" onclick="dijit.byId(\'{{id}}\').selectAll(event)" title="Выбрать все строчки" width="5">x</th>{% for col in tableStruct.columns %}<th class="{% if col.is_key %}data_table_key{% endif %}" width="{{ col.width|default:\"5\" }}">{{ col.name|default:"-" }}</th>{% endfor %}</tr></thead><tbody><tr id="{{id}}_search_bar"><td class="data_table_del" style="background:none #F0DEEA;height:10px;"></td>{% for col in tableStruct.columns %}<td style="padding:1px;height:10px;"><input type="text" name="{{col.field}}" onkeyup="dijit.byId(\'{{id}}\').onFilter(event)" size="1" style="width:100%;margin:0px;border:none;" /></td>{% endfor %}</tr></tbody><tbody>{% for row in tableData.rows|dictsort:tableStruct.pos %}<tr class="data_table_row" onclick="dijit.byId(\'{{id}}\').selectRow(event)"onmouseover="dijit.byId(\'{{id}}\').hilightRow(event)"><td>{{forloop.counter}}</td>{% for cell in row %}<td ondblclick="dijit.byId(\'{{id}}\').makeEditor(this,\'{{forloop.parentloop.counter0}}\',\'{{forloop.counter0}}\');">{{cell|default:""}}</td>{% endfor %}</tr>{% endfor %}</tbody>';
		var template = new dojox.dtl.Template(tpl);
		var html=template.render(new dojox.dtl.Context(this));
		dojo.byId(this.id+'_data_rows').innerHTML=html;
		this.restoreFilter();
	},
	compile: function(){
		this.clearSelection();
		this.hilightRow();
		this.render();
		this.alt_render();
		this.calculateTable();
		if( this.tableStruct.hidestatus==1 &&  dojo.byId(this.id+'_search_bar') ){
			dojo.byId(this.id+'_status_bar').style.display='none';
			dojo.destroy(this.id+'_status_bar');
		}
		if( this.tableStruct.hidesearch==1 && dojo.byId(this.id+'_search_bar') ){
			dojo.byId(this.id+'_search_bar').style.display='none';
			dojo.destroy(this.id+'_search_bar');
		}
	}
});
