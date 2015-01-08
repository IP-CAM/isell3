<div>
    <link rel="stylesheet" type="text/css" href="js/baycik/datatable/data_table.css" />
<div style="visibility:hidden" dojoAttachpoint="rootNode">
    <table cellpadding="0" cellspacing="0">
        <tr id="{{id}}_status_bar">
            <td class="data_table_status" width="500">
              <span style="cursor:pointer;" title="Обновить" dojoAttachEvent="onclick: loadTableData">
              <span style="width:16px;display:none;float:right;padding:5px" id="{{id}}_loading"><img src='img/loading_16.gif' align="bottom" /></span>
              <span style="width:16px;display:inline;float:right;padding:5px" id="{{id}}_reload"><img src='img/reload_16.png' align="absmiddle" /></span>
              </span>
              <div id="{{id}}_status"></div>
              <div style="float:left;">
            	<form action="#" onsubmit="CompaniesJs.documents.suggFormSubmit(this);return false;" style="margin:0px;padding:0px">
                <table cellpadding="0" cellspacing="0">
                  <tr>
                    <td>
                    <div id="companies_doc_suggest"></div>
                    </td>
                    <td>
                    <input type="text" class="dijit dijitReset dijitLeft dijitTextBox" dojoAttachpoint="qty_input" style="text-align:right;width:30px;margin:0px;padding:1px;" title="Колличество">
                    </td>
                    <td>
                    <button type="submit" class="dijit dijitReset dijitInline dijitButton" style="border:1px #ccc solid">+</button>
                    </td>
                    <td>
                    </td>
                  </tr>
                </table>
                </form>
              </div>
              <div style="float:right" id="{{id}}_tool_bar">
                <!--{% for tool in tableStruct.tools %}-->
                <span style="width:55px;height:55px;"><a href="javascript:dijit.byId('{{id}}').{{tool.command}}" id="{% if tool.id %}{{id}}_{{tool.id}}{% else %}{{id}}_tool{{forloop.counter0}}{% endif %}" title="{{tool.tip}}"><img src="js/baycik/datatable/img/{{tool.img}}" border="0" /></a></span>
                <!--{% endfor %}-->
              </div>
            </td>
        </tr>
        <tr>
        	<td align="center">
                <table id="{{id}}_data_rows" class="data_table" width="500" border="0" cellspacing="0" cellpadding="0" onmouseout="dijit.byId('{{id}}').hilightRow()">
                    <thead><tr><th style="cursor:pointer" dojoAttachEvent="onclick: selectAll" title="Выбрать все строчки" width="10">x</th><!--{% for col in tableStruct.columns %}--><th dojoAttachEvent="onclick: sortColumn" class="{% if col.is_key %}data_table_key{% endif %}"><!--{{ col.name }}--></th><!--{% endfor %}--></tr></thead>
                    <tbody><tr id="{{id}}_search_bar"><td style="background:none #F0DEEA;height:10px;"></td><!--{% for col in tableStruct.columns %}--><td style="padding:1px;height:10px;"><input type="text" name="{{col.field}}" dojoAttachEvent="onkeyup: onFilter" size="1" style="width:100%;margin:0px;border:none;" /></td><!--{% endfor %}--></tr></tbody>
                    <tbody><!--{% for row in tableData.rows|dictsort:tableStruct.pos %}--> <tr class="data_table_row" dojoAttachEvent="onclick: selectRow,onmouseover: hilightRow"><td align="right"><!--{{forloop.counter}}--></td><!--{% for cell in row %}--><td ondblclick="dijit.byId('{{id}}').makeEditor(this,'{{forloop.parentloop.counter0}}','{{forloop.counter0}}');"><!--{{cell}}--></td><!--{% endfor %}--></tr><!--{% endfor %}--> </tbody>
                </table>
			</td>
		</tr>
        <tr>
            <td height="20">
          	<div id="{{id}}_message" style="background-color:#FF6" align="center"></div>
            </td>
        </tr>
	</table>
</div>
</div>