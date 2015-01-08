<div>
<div style="visibility:hidden" dojoAttachpoint="rootNode">
    <table cellpadding="0" cellspacing="0">
        <tr id="{{id}}_tool_bar">
          <td align="right">
            <!--{% for tool in tableStruct.tools %}-->
            <span style="width:30px;height:30px;"><a href="javascript:dijit.byId('{{id}}').{{tool.command}}" title="{{tool.tip}}"><img id="{% if tool.id %}{{id}}{{tool.id}}{% else %}{{id}}_tool{{forloop.counter0}}{% endif %}" src="js/baycik/datatable/img/{{tool.img}}" border="0" /></a></span>
            <!--{% endfor %}-->
          </td>
        </tr>
        <tr id="{{id}}_status_bar">
            <td class="data_table_status">
          	<div id="{{id}}_message" style="background-color:#FF6"></div>
            <div id="{{id}}_status"><div style="float:left;"><img src="js/baycik/datatable/img/mini_trash.png" dojoAttachEvent="onclick: resetFilter" /> <b>"{{tableStruct.table_name}}"</b></div><div style="float:right">&nbsp;<span title="Количество строк" style="cursor:pointer" dojoAttachevent="onclick: setPageLimit">Σ{{tableData.total_rows}}</span> <img src="js/baycik/datatable/img/leftarrow.png" vspace="0" align="absmiddle" border="0" dojoAttachEvent="onclick: prevPage" style="cursor:pointer"/><span dojoAttachEvent="onclick: gotoPage" style="cursor:pointer"><b>{{tableData.page}}</b>/{{tableData.total_pages}}</span><img src="js/baycik/datatable/img/rightarrow.png" vspace="0" align="absmiddle" border="0" style="cursor:pointer" dojoAttachEvent="onclick: nextPage" /><span style="cursor:pointer" title="Обновить" dojoAttachEvent="onclick: loadTableData">    <span style="width:16px;dispaly:none;" id="{{id}}_loading"><img src='js/baycik/datatable/img/loading_16.gif' align="absmiddle" /></span><span style="width:16px;display:inline;" id="{{id}}_reload"><img src='js/baycik/datatable/img/reload_16.png' align="absmiddle" /></span>  </span></div></div>
            </td>
        </tr>
       <tr>
        	<td align="center">
            	<form action="#" onsubmit="return false" name="{{id}}_form">
                <table id="{{id}}_data_rows" class="data_table" border="0" cellspacing="0" cellpadding="0" onmouseout="dijit.byId('{{id}}').hilightRow()">
                    <thead><tr><th class="data_table_del" style="cursor:pointer" dojoAttachEvent="onclick: selectAll" title="Выбрать все строчки">x</th><!--{% for col in tableStruct.columns %}--><th dojoAttachEvent="onclick: sortColumn" class="{% if col.is_key %}data_table_key{% endif %}" width="{{ col.width|default:'5' }}"><!--{{ col.name|default:"-" }}--></th><!--{% endfor %}--></tr></thead>
                    <tbody><tr id="{{id}}_search_bar"><td class="data_table_del" style="background:none #F0DEEA;height:10px;"></td><!--{% for col in tableStruct.columns %}--><td style="padding:1px;height:10px;"><input type="text" name="{{col.field}}" dojoAttachEvent="onkeyup: onFilter" size="1" style="width:100%;margin:0px;border:none;" /></td><!--{% endfor %}--></tr></tbody>
                    <tbody><!--{% for row in tableData.rows %}--><tr class="data_table_row" dojoAttachEvent="onclick: selectRow,onmouseover: hilightRow"><td><!--{{forloop.counter}}--></td><!--{% for cell in row %}--><td ondblclick="dijit.byId('{{id}}').makeEditor(this,'{{forloop.parentloop.counter0}}','{{forloop.counter0}}');"><!--{{cell|default:''}}--></td><!--{% endfor %}--></tr><!--{% endfor %}--></tbody>
                </table>
				</form>
			</td>
		</tr>
	</table>
</div>
</div>