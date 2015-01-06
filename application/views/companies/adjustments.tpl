<script type="text/javascript">
    require([
	"dojo/dom",
	"dijit/registry",
	"dojo/on",
	"dojo/data/ItemFileReadStore",
	"dijit/form/Select",
	"baycik/grid/DataGrid",
	"dojo/domReady!"
    ],
	    function (
		    dom,
		    registry,
		    on,
		    ItemFileReadStore,
		    DataGrid,
		    Select
		    ) {
		CompaniesJs.adjustments.init = function () {
		    Connector.addRequest({mod:'Companies',rq:'Adjustments'}, function (content) {
			content.dis = CompaniesJs.adjustments.makePriceListLink(content.discounts);
			Acc.renderTpl('comp_adj', content);
			CompaniesJs.adjustments.initAdjForm(content);

		    });
		    CompaniesJs.adjustments.initCompStats();
		}
		CompaniesJs.adjustments.focus = function () {
		    this.init();
		}
		CompaniesJs.adjustments.makePriceListLink = function (discounts) {
		    var DiscountString = [];
		    for (var i in discounts) {
			if (!discounts[i]['discount'])
			    continue;
			DiscountString.push(discounts[i]['branch_id'] + ':' + discounts[i]['discount']);
		    }
		    return DiscountString.join(';');
		};
		CompaniesJs.adjustments.initAdjForm = function (fvalue) {
		    if (!CompaniesJs.adjustments.staffStore) {
			var staff_list = fvalue.staffList;
			staff_list.splice(0, 0,{user_id:'',full_name:'-'});
			CompaniesJs.adjustments.staffStore = new ItemFileReadStore({data: {
				identifier: 'user_id',
				label: 'full_name',
				items: staff_list
			    }});
			registry.byId('comp_manager_id').setStore(CompaniesJs.adjustments.staffStore);
		    }
		    fvalue.is_supplier = fvalue.is_supplier == 1 ? true : false;
		    this.suppressUpdate = true;
		    registry.byId('comp_adj_form').set('value', fvalue);
		    setTimeout(function () {
			CompaniesJs.adjustments.suppressUpdate = false;
		    }, 0);
		}
		CompaniesJs.adjustments.update_dis = function (branch_id, value) {
			var discount = (100 + value) / 100;
			var request ={};
			request.mod = 'Companies';
			request.rq = 'UpdateDiscount';
			request.branch_id = branch_id;
			request.discount = value;
			Connector.addRequest(request);
			CompaniesJs.adjustments.clockId = setTimeout(function (){CompaniesJs.adjustments.init()}, 2000
			);
		    }
		    CompaniesJs.adjustments.update_adj = function (field_name, field_value) {
			if (this.suppressUpdate)
			    return;
			var request ={};
			request.mod = 'Companies';
			request.rq = 'DetailUpdate';
			request.field_name = field_name;
			request.field_value = field_value;
			Connector.addRequest(request);
		    }
		    CompaniesJs.adjustments.postponeRefresh = function () {
			clearTimeout(CompaniesJs.adjustments.clockId);
		    }
		    CompaniesJs.adjustments.storeLastFocus = function (ele) {
			this.lastFocus = ele;
		    }
		    CompaniesJs.adjustments.initCompStats = function () {
			Connector.addRequest({mod:'Companies',rq:'GetSellStats'}, function (resp) {
			    if (resp.pay && resp.pay_mval) {
				var max_px = 250;
				for (var i in resp.pay) {
				    resp.pay[i].pwidth = parseInt(resp.pay[i].p * max_px / resp.pay_mval);
				    resp.pay[i].swidth = parseInt(resp.pay[i].s * max_px / resp.pay_mval);
				}
			    }
			    Acc.renderTpl('comp_sell_stats', resp);
			});
		    }
		});
</script>
<table width="700" border="0">
    <tr>
	<td id="comp_adj" valign="top">
	    <table border="0" cellpadding="0" cellspacing="0" class="table">
		<tr style="background-image: url('img/thbg.jpg')">
		    <th width="130" align="center" nowrap="nowrap">Категория</th>
		    <th width="65" align="center" nowrap="nowrap">Скидка</th>
		    <th width="65" align="center" nowrap="nowrap">Надбавка</th>
		</tr>
		<!--{%for dis in discounts%}-->
		<tr>
		    <td><!--{{dis.label}}--></td>
		    <td align="right"><input type="text" value="{{dis.minus|default'0'}}" onchange="CompaniesJs.adjustments.update_dis('{{dis.branch_id}}', -this.value.match(/\d*.?\d+/))" onfocus="CompaniesJs.adjustments.postponeRefresh()" style="text-align:right" size="3" /> %</td>
		    <td align="right"><input type="text" value="{{dis.plus|default'0'}}" onchange="CompaniesJs.adjustments.update_dis('{{dis.branch_id}}', +this.value.match(/\d*.?\d+/))" onfocus="CompaniesJs.adjustments.postponeRefresh()" style="text-align:right" size="3" /> %</td>
		</tr>
		<!--{%endfor%}-->
	    </table>
	    <a href="http://plustrade.com.ua/v2/?plugin=PriceList&fn=html&dis={{dis}}" target="_new">Сделать прайс</a>
	</td>
	<td valign="top">
	    <div data-dojo-type="dijit/TitlePane" title="Настройки">
		<div data-dojo-type="dijit/form/Form" id="comp_adj_form">
		    <table>
			<tr>
			    <td>
				Отсрочка
			    </td>
			    <td>
				<input type="text" data-dojo-type="dijit/form/TextBox" name="deferment" onchange="CompaniesJs.adjustments.update_adj('deferment', this.value)"/>
			    </td>
			</tr>
			<tr>
			    <td>
				Лимит долга
			    </td>
			    <td>
				<input type="text" data-dojo-type="dijit/form/TextBox" name="debt_limit" onchange="CompaniesJs.adjustments.update_adj('debt_limit', this.value)"/>
			    </td>
			</tr>
			<tr>
			    <td>
				Менеджер
			    </td>
			    <td>
				<div data-dojo-type="dijit/form/Select" name="manager_id" id="comp_manager_id" onchange="CompaniesJs.adjustments.update_adj('manager_id', this.value)"></div>
			    </td>
			</tr>
			<tr>
			    <td>
				Поставщик
			    </td>
			    <td>
				<div data-dojo-type="dijit/form/CheckBox" value="1" name="is_supplier" id="comp_is_supplier" onChange="CompaniesJs.adjustments.update_adj('is_supplier', this.get('value') ? 1 : 0)"></div>
				<label for="comp_is_supplier">Обновлять закупочные цены</label>
			    </td>
			</tr>
			<tr>
			    <td>
				Бух счета
			    </td>
			    <td>
				<input type="text" data-dojo-type="dijit/form/TextBox" name="company_acc_list" onchange="CompaniesJs.adjustments.update_adj('company_acc_list', this.value)"/>
			    </td>
			</tr>
		    </table>
		</div>
	    </div>
	    <div data-dojo-type="dijit/TitlePane" title="Анализ">
		<div id="comp_sell_stats">
		    <!--{% for row in sell %}-->
		    <div>
			{{row.m|default:''}} 
			<div style="width:100px;display: inline-block">{{row.brand|default:''}}</div>
			<div style="width:50px;display: inline-block;text-align: right">{{row.s|default:''}}грн</div>
		    </div>
		    <!--{%endfor%}-->
		    <hr size="1">
		    <!--{% for row in pay %}-->
		    <div style="margin: 2px;" title="sell:{{row.s|default:''}}грн; pay:{{row.p|default:''}}грн">
			<div style="float: left;height: 12px;"><b>{{row.m|default:''}}</b></div>
			<div style="float: left;height: 12px;vertical-align: top;padding-top: 1px;">
			    <div style="width:{{row.pwidth|default:'0'}}px;background-color: #0f0;height: 5px;margin-bottom: 1px"></div>
			    <div style="width:{{row.swidth|default:'0'}}px;background-color: #f0f;height: 5px"></div>
			</div>
		    </div>
		    <div style="clear: both;height: 2px;"></div>
		    <!--{%endfor%}-->
		</div>
	    </div>
	</td>
    </tr>
</table>
