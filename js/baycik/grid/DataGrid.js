require([
    "dojo/_base/declare",
    "dijit/_Widget",
    "dijit/_Templated",
    "dojo/text!baycik/grid/DataGrid.html",
    "dojo/query",
    "dojo/dom-class",
    "dojo/_base/lang",
    "dojo/_base/json"
],
function (
	declare,
	_Widget,
	_Templated,
	template,
	query,
	domClass,
	lang,
	json
	) {
    return declare("baycik.grid.DataGrid", [_Widget, _Templated], {
	templateString: template,
	widgetsInTemplate: false,
	standartTools: {
	    "space": {
		img: 'space.png',
		command: 'doNone()',
		tip: ' '
	    },
	    "download": {
		img: 'xls_download.png',
		command: 'fileDownload()',
		tip: 'Скачать .xls файл'
	    },
	    "print": {
		img: 'print.png',
		command: 'print()',
		tip: 'Напечатать'
	    },
	    "add": {
		img: 'add.png',
		command: 'insertRow()',
		tip: 'Добавить строку'
	    },
	    "delete": {
		img: 'delete.png',
		command: 'deleteSelected()',
		tip: 'Удалить выделенное'
	    },
	    "upload": {
		img: 'xls_upload.png',
		command: 'fileUpload()',
		tip: 'Загрузить файл данных'
	    }
	},
	constructor: function () {
	    this.request = {
		page: 1,
		limit: 30
	    };
	    this.identifier = '';
	    this._items = [];
	    this._selection = [];
	    this._structure = {};
	},
	startup: function () {
	    this.inherited(arguments);
	    this.onStructure(this.request);
	},
	addToRequest: function (token) {
	    this.request = lang.mixin(this.request, token);
	},
	/////////////////////////////////
	//TOOLS
	/////////////////////////////////
	onStructure: function () {
	},
	onRequest: function () {
	},
	onPrint: function () {
	},
	onDownload: function () {
	},
	onUpload: function () {
	},
	onUpdate: false,
	onSelect: function () {
	},
	onDeselect: function () {
	},
	onAction: function () {
	},
	onDelete: function () {
	},
	onInsert: function () {
	},
	deleteSelected: function () {
	    if (this._structure.readonly || !this.onDelete || this._selection.length < 1 || !confirm("Удалить выделенные строки?"))
		return;
	    var deleteIds = [];
	    var items = this.getSelected();
	    if (items.length) {
		for (var i in items) {
		    var obj = {};
		    obj[this.identifier] = items[i][this.identifier];
		    deleteIds.push(obj);
		}
	    }
	    var delIds = json.toJson(deleteIds);
	    this.onDelete(lang.mixin(lang.clone(this.request), {
		delIds: delIds
	    }));
	    //this._reload();
	},
	print: function () {
	    if (!this.onPrint)
		return;
	    this.onPrint(lang.clone(this.request));
	},
	insertRow: function () {
	    if (this._structure.readonly || !this.onInsert)
		return;
	    var value = null;
	    var label = '';
	    var ident = this._structure['identifier'];
	    for (var i in this._structure.columns) {
		if (this._structure.columns[i].field == ident) {
		    label = this._structure.columns[i].name || ident;
		}
	    }
	    value = prompt("Введите значение для ключа '" + label + "'");
	    if (value == null)
		return;
	    var insert = "{\"" + ident + "\":\"" + value + "\"}";
	    this.onInsert(lang.mixin(lang.clone(this.request), {
		newrow: insert
	    }));
	    query('input', this.innerGrid).forEach(function (node) {
		if (node.name == ident)
		    node.value = value;
	    });
	    this._filterGrid();
	},
	_updateCell: function (cell) {
	    if (this._structure.readonly || !cell || !this.onUpdate)
		return;
	    var field = cell.getAttribute('data-field');
	    for (var i in this._structure.columns)
		if (this._structure.columns[i].field == field && this._structure.columns[i].readonly)
		    return;
	    var rowNode = cell.parentNode;
	    var rowIndex = parseInt(rowNode.id);
	    var oldval = this._items[rowIndex][field];
	    cell.style.backgroundColor = 'red';
	    var newval = prompt('Введите новое значение.', oldval);
	    cell.style.backgroundColor = '';
	    if (newval == null || newval == oldval)
		return;
	    var key = '{"' + this.identifier + '":"' + this._items[rowIndex][this.identifier] + '"}';
	    var value = '{"' + field + '":"' + newval + '"}';
	    this.onUpdate(lang.mixin(lang.clone(this.request), {
		key: key,
		value: value
	    }));
	    this._reload();
	},
	fileUpload: function () {
	    if (!this.onUpload)
		return;
	    this.onUpload(lang.clone(this.request));
	},
	fileDownload: function () {
	    if (!this.onDownload)
		return;
	    this.onDownload(lang.clone(this.request));
	},
	/////////////////////////////////
	//PAGING AND STATUSBAR
	/////////////////////////////////
	_prevPage: function () {
	    this.request.page--;
	    if (this.request.page < 1)
		this.request.page = parseInt(this.totalpages.innerHTML);
	    this._reload();
	},
	_nextPage: function () {
	    this.request.page++;
	    if (this.request.page > parseInt(this.totalpages.innerHTML))
		this.request.page = 1;
	    this._reload();
	},
	_goto: function () {
	    var page = prompt("Укажите номер страницы", this.request.page);
	    if (page && page > 0 && this.request.page < parseInt(this.totalpages.innerHTML)) {
		this.request.page = page;
		this._reload();
	    }
	},
	_setlimit: function () {
	    var limit = prompt("Укажите колличество строк на странице", this.request.limit);
	    if (limit) {
		this.request.limit = limit;
		this._reload();
	    }
	},
	_status: function (msg) {
	    if (msg) {
		this.messagebar.innerHTML = msg;
		this.messagebar.style.display = 'block';
		this.statusbar.style.display = 'none';
		var self = this;
		clearTimeout(this.statusclock);
		this.statusclock = setTimeout(function () {
		    self._status("");
		}, 2000);
	    } else {
		this.messagebar.style.display = 'none';
		//if( !this._structure.hidestatus )
		this.statusbar.style.display = 'block';
	    }
	},
	_reload: function () {
	    this.onRequest(this.request);
	    this.throbberoff.style.display = 'none';
	    this.throbberon.style.display = 'inline';
	},
	loadGrid: function () {
	    this._reload();
	},
	_reset: function () {
	    this.request.page = 1;
	    query('input', this.innerGrid).forEach(function (node, index, arr) {
		node.value = '';
	    });
	    this._filterGrid();
	},
	_filterGrid: function () {
	    var filter = {};
	    query('input', this.innerGrid).forEach(function (node, index, arr) {
		if (node.value)
		    filter[node.name] = node.value;
	    });
	    this.request.filter = json.toJson(filter);
	    this.request.page = 1;
	    this._reload();
	},
	_initFilter: function () {
	    var self = this;
	    clearTimeout(this.filterclock);
	    this.filterclock = setTimeout(function () {
		self._filterGrid();
	    }, 500);
	},
	/////////////////////////////////
	//RENDERING
	/////////////////////////////////
	buildToolbar: function (toolnames, extra) {
	    if (this._structure.hidetools)
		return;
	    var html = '';
	    for (var i in toolnames) {
		var tool = this.standartTools[toolnames[i]] || extra[toolnames[i]];
		if (tool) {
		    html += "<div class='baycikGridTool' onclick='dijit.byId(\"" + this.id + "\")." + tool.command + "'><img src='img/grid/" + tool['img'] + "'></div>";
		}
	    }
	    this.toolbar.innerHTML = html;
	},
	buildTable: function (columns) {
	    var header = '';
	    var filter = '';
	    var table = '<table class="baycikGridTable" style="{{table_style}}"><thead><tr>{{header}}</tr><tr>{{filter}}</tr></thead><tbody></tbody></table>';
	    for (var i in columns) {
		var column = columns[i];
		header += '<th> ' + column.name + ' </th>';
		filter += '<th><input type="text" name="' + column.field + '" size="1" /></th>';
	    }
	    if (this._structure.hidefilter)
		filter = '';
	    this.innerGrid.innerHTML = table.replace("{{header}}", header).replace("{{filter}}", filter).replace("{{table_style}}", this._structure.table_style || '');
	    var self = this;
	    this.innerGrid.firstChild.onclick = function (e) {
		self._tableClick(e);
	    };
	    this.innerGrid.firstChild.ondblclick = function (e) {
		self._tableDblClick(e);
	    };
	    this.innerGrid.firstChild.onkeypress = function (e) {
		self._tableKeyPress(e);
	    };
	},
	setStructure: function (structure) {
	    this.identifier = structure.identifier;
	    this._structure = structure;
	    this.buildTable(structure.columns);
	    this.buildToolbar(structure.toolnames, structure.extratools);
	    this.gridname.innerHTML = structure.grid_name || '';
	    if (this._structure.hidestatus)
		this.statusbar.style.visibility = 'hidden';
	    delete this.request.filter;
	    this.onRequest(this.request);
	},
	setData: function (data) {
	    this.totalrows.innerHTML = data.total_rows || '';
	    this.totalpages.innerHTML = data.total_pages || '';
	    this.currentpage.innerHTML = data.page || '';
	    this._clearSelection();
	    this.onDeselect();
	    this._status(data.items ? "" : "Ничего не найдено!");
	    this._items = data.items;
	    var rowsHtml = this._drawRows(this._items);
	    query('tbody', this.innerGrid).forEach(function (node) {
		node.innerHTML = rowsHtml;
	    });
	    this.throbberoff.style.display = 'inline';
	    this.throbberon.style.display = 'none';
	},
	_drawRows: function (items) {
	    var rowsHtml = '';
	    for (var i in items) {
		var row = '<tr id="' + i + '_' + this.id + '">';
		for (var k in this._structure.columns) {
		    var column = this._structure.columns[k];
		    var cellData = items[i][column.field];
		    var title = '';
		    var style = [];
		    if (cellData && column.type == 'tooltip') {
			var tokens = cellData.match(/(\w+)(\s?)(.*)/);
			if (tokens && tokens[1]) {
			    cellData = '<img src="img/' + tokens[1] + '.png" data-function="' + tokens[1] + '" width="16" height="16">';
			    title = 'title="' + tokens[3] + '"';
			    column.align = "center";
			}
		    }
		    if (column.width) {
			style.push('max-width:' + column.width);
		    }
		    if (column.minWidth) {
			style.push('min-width:' + column.minWidth);
		    }
		    if (column.align){
			style.push('text-align:' + column.align);
		    }
		    if ( column.distinct && items[i-1] && items[i-1][column.field]===cellData ){
			cellData='';
		    }
		    var calculated = this.calculateCell({
			i: i,
			field: column.field,
			cellData: cellData,
			rowData: items[i],
			style: style
		    });
		    row += '<td ' + title + ' data-field="' + column.field + '" style="' + calculated.style.join(";") + '">' + (calculated.cellData || '') + '</td>';
		}
		row += '</tr>';
		rowsHtml += row;
	    }
	    return rowsHtml;
	},
	calculateCell: function (cell) {
	    return cell;
	},
	/////////////////////////////////
	//EVENTS
	/////////////////////////////////
	_getDataFromCell: function (cell) {
	    var rowIndex = parseInt(cell.parentNode.id);
	    return {
		rowData: this._items[rowIndex],
		field: cell.getAttribute('data-field')
	    };
	},
	_tableDblClick: function (e) {
	    this._updateCell(e.target);

	    var cell = e.target;
	    var data = this._getDataFromCell(cell);
	    this.onDblClick(data, e);
	},
	_tableClick: function (e) {
	    this._selectRow(e);
	    var img = e.target;
	    var action = img.getAttribute('data-function');
	    if (action) {
		var data = this._getDataFromCell(img.parentNode);
		data.action = action;
		this.onAction(data, e);
	    }
	},
	_tableKeyPress: function (e) {
	    this._initFilter();
	},
	/////////////////////////////////
	//SELECTION
	/////////////////////////////////
	_selectRow: function (e) {
	    var rowNode = e.target.parentNode;
	    var rowIndex = parseInt(rowNode.id);
	    if (!rowNode || isNaN(rowIndex))
		return; //proseed only if node is grid's row
	    if (e.ctrlKey && this._structure.selectionMode !== 'single') {
		if (this._isSelected(rowIndex))
		    this._removeSelection(rowIndex);
		else
		    this._addSelection(rowIndex);
	    } else if (e.shiftKey && this._structure.selectionMode !== 'single') {
		var firstIndex = this._selection[0];
		this._clearSelection();
		if (firstIndex >= 0 && rowIndex - firstIndex) {
		    this._addSelection(firstIndex);
		    for (var i = Math.min(rowIndex, firstIndex); i <= Math.max(rowIndex, firstIndex); i++)
			this._addSelection(i);
		}
	    } else {
		this._clearSelection();
		this._addSelection(rowIndex);
	    }
	    this.onSelect(this.getSelected());
	},
	_addSelection: function (index) {
	    this._selection.push(index);
	    domClass.add(index + '_' + this.id, 'baycikGridSelectedRow');
	},
	_removeSelection: function (index) {
	    for (var i in this._selection)
		if (this._selection[i] == index)
		    this._selection.splice(i, 1);
	    domClass.remove(index + '_' + this.id, 'baycikGridSelectedRow');
	},
	_clearSelection: function () {
	    this._selection = [];
	    query('.baycikGridSelectedRow', this.innerGrid).forEach(function (node, index, arr) {
		domClass.remove(node.id, 'baycikGridSelectedRow');
	    });
	},
	_isSelected: function (index) {
	    for (var i in this._selection)
		if (this._selection[i] == index)
		    return true;
	    return false;
	},
	getSelected: function () {
	    var selected = [];
	    for (var i in this._selection)
		selected.push(this._items[this._selection[i]]);
	    return selected;
	}
    });
});