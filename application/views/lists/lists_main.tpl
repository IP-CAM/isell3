<script type="text/javascript">
    dojo.require("dijit.Calendar");
    dojo.require("baycik.grid.DataGrid");
    ListsJs = {};
    ListsJs.init = function () {
        ListsJs.events.init();
    }
    ListsJs.destroy = function () {
        data_grid.destroy();
        table_list.destroy();
    }

    ListsJs.events = {};
    ListsJs.events.init = function () {
        this.selectedLabel = '';
        this.selectedDate = '';
        //Connector.addRequest({mod:'Lists',rq:'ActiveEventDates'},function(cont){
        //	ListsJs.events.activeDates=cont;

        //});
        ListsJs.events.initCalendar();
        ListsJs.events.loadActiveDates();
    };
    ListsJs.events.initEventList = function () {
        if (ListsJs.events.data_grid) {
            ListsJs.events.data_grid._reload();
            return;
        }
        ListsJs.events.data_grid = new baycik.grid.DataGrid({}, 'event_grid');
        with(ListsJs.events.data_grid) {
            request.mod = 'Data';
            request.table_name = 'event_list';
            onStructure = function (request) {
                request.mod = 'Data';
                request.rq = 'GridStructure';
                Connector.addRequest(request, this, 'setStructure');
            };
            onRequest = function (request) {
                request.mod = 'Lists';
                request.rq = 'EventList';
                request.label = ListsJs.events.selectedLabel;
                request.date = ListsJs.events.selectedDay;
                Connector.addRequest(request, function (cont) {
                    ListsJs.events.activeDates = cont['activeDates'];
                    ListsJs.events.data_grid.setData(cont);
                });
            };
            startup();
        }
        ListsJs.events.data_grid.onPrint = function (request) {
            request.mod = 'Lists';
            request.rq = 'EventListViewOut';
            request.label = ListsJs.events.selectedLabel;
            request.date = ListsJs.events.selectedDay;
            Acc.printOut(request);
        };
        ListsJs.events.data_grid.onDownload = function (request) {
            request.mod = 'Data';
            request.rq = 'GridOut';
            request.out_type = '.xls';
            request.limit = 0;
            Connector.addRedirection(request);
        }
        ListsJs.events.data_grid.onDblClick = function (e) {
            var selected = this.getSelected()[0];
            var fvalue = {};
            var callback = function (fvalue) {
                ListsJs.events.selectedLabel = fvalue.event_label || ListsJs.events.selectedLabel;
                ListsJs.events.selectedDay = fvalue.event_date || ListsJs.events.selectedDay;
                ListsJs.events.calendar.set('value', dojo.date.locale.parse(ListsJs.events.selectedDay, {
                    datePattern: "yyyy-MM-dd",
                    selector: "date"
                }));
                ListsJs.events.loadActiveDates();
                ListsJs.events.initLabelList();
            };
            var getvars = selected;
            getvars.tpl = 'dialogs/eventdialog.html';
            Acc.showPopup(fvalue, callback, getvars, {
                w: 680,
                h: 320
            })
        }
        ListsJs.events.data_grid.onDelete = function (request) {
            request.mod = 'Data';
            request.rq = 'DeleteGridRow';
            Connector.addRequest(request);
            ListsJs.events.data_grid.loadGrid();
        };
        ListsJs.events.data_grid.addEvent=function(){
            ListsJs.events.addEvent();
        };
    };
    ListsJs.events.addEvent = function () {
        var fvalue = {};
        var callback = function (fvalue) {
            ListsJs.events.selectedLabel = fvalue.event_label || ListsJs.events.selectedLabel;
            ListsJs.events.selectedDay = fvalue.event_date || ListsJs.events.selectedDay;
            ListsJs.events.calendar.set('value', dojo.date.locale.parse(ListsJs.events.selectedDay, {
                datePattern: "yyyy-MM-dd",
                selector: "date"
            }));
            ListsJs.events.loadActiveDates();
            ListsJs.events.initLabelList();
        };

        var dt = ListsJs.events.selectedDay.match(/(\d{4})-(\d{2})-(\d{2})/);
        var getvars = {
            event_id: 0
        };
        getvars.event_label = ListsJs.events.selectedLabel;
        getvars.event_date = dt[3] + '.' + dt[2] + '.' + dt[1];
        getvars.tpl = 'dialogs/eventdialog.html';
        Acc.showPopup(fvalue, callback, getvars, {
            w: 680,
            h: 320
        });
    };
    ListsJs.events.initLabelList = function () {
        var request = {
            mod: 'Lists',
            rq: 'GetEventLabels',
            selectedDay: ListsJs.events.selectedDay
        };
        Connector.addRequest(request, function (cont) {
            Acc.renderTpl('event_label_list', {
                label_list: cont
            });
            dojo.query("#event_label_list .event_label").forEach(function (node, index, arr) {
                if (index == 0)
                    ListsJs.events.firstLabel = node;
                if (node.title == ListsJs.events.selectedLabel) {
                    ListsJs.events.selectLabel(node);
                    ListsJs.events.firstLabel = ''; //Label is found so no need to select first one
                }
            });
            if (ListsJs.events.firstLabel) //!ListsJs.events.selectedLabel && 
                ListsJs.events.selectLabel(ListsJs.events.firstLabel);
        });
    };
    ListsJs.events.selectLabel = function (node) {
        dojo.query("#event_label_list .event_label_selected").forEach(function (node, index, arr) {
            dojo.removeClass(node, "event_label_selected");
        });
        dojo.addClass(node, "event_label_selected");
        this.selectedLabel = node.title;
        ListsJs.events.initEventList();
    };
    ListsJs.events.addActiveDate = function (date) {
        ListsJs.events.activeDates.push({
            "event_date": date
        });
        ListsJs.events.calendar._populateGrid();
    };
    ListsJs.events.loadActiveDates = function (date) {
        Connector.addRequest({
            mod: 'Lists',
            rq: 'ActiveEventDates'
        }, function (cont) {
            ListsJs.events.activeDates = cont;
            ListsJs.events.calendar._populateGrid();
        });
    };
    ListsJs.events.initCalendar = function () {
        this.calendar = new dijit.Calendar({}, 'event_calendar');
        this.calendar.getClassForDate = function (date) {
            var day = dojo.date.locale.format(date, {
                datePattern: "yyyy-MM-dd",
                selector: "date"
            });
            for (var i in ListsJs.events.activeDates) {
                if (day == ListsJs.events.activeDates[i]['event_date'])
                    return 'date_has_event';
            };
            return;
        };
        this.calendar.onChange = function (date) {
            ListsJs.events.selectedDay = dojo.date.locale.format(date, {
                datePattern: "yyyy-MM-dd",
                selector: "date"
            });
            ListsJs.events.initLabelList();
        };
        this.calendar.set('value', new Date());
    };
</script>
<table cellpadding="0" cellspacing="0" align="left">
  <tr>
    <td width="200" align="center" valign="top">
    <div id="event_calendar"></div>
    <div style="letter-spacing:3px;color:#06F">&nbsp;</div>
    <div id="event_label_list"><!--{% for row in label_list %}<div class="event_label" onclick="ListsJs.events.selectLabel(this);" title="{{row.event_label}}"><big>"{{row.event_label}}" ({{row.count}})</big></div>{% endfor %}--></div>
    </td>
    <td valign="top" align="center">
    	<div class="grid_wrapper">
            <div id="event_grid" style="padding:15px;"><h1>Выберите дату события в календаре</h1>
                <a href="javascript:ListsJs.events.addEvent()">
                    <img src="img/edit_add.png"><br> добавить новую запись
                </a>
            </div>
        </div>
    </td>
  </tr>
</table>
<style type="text/css">
	.grid_wrapper{
		padding:15px;
		min-height:500px;
		background:-moz-linear-gradient(top, #fff, #fff, #cdf);
		-background:url(img/tabbg.jpg) repeat-x bottom #ffffff;
		border:#999 solid 1px;
		box-shadow: 5px 5px 5px #bbb;
		border-radius:10px;
		min-width:800px;
	}
	#event_label_list .event_label{
		position:relative;
		left:1px;
		padding:7px;
		margin-bottom:5px;
		width:200px;
		background-color:#FFF;
		border:#ccc solid 1px;
		border-bottom-left-radius:5px;
		border-top-left-radius:5px;
		box-shadow: 0px 1px 3px #abc;
		background:-moz-linear-gradient(top, #fff, #eef);
		cursor:pointer;
	}
	#event_label_list .event_label_selected{
		border:#aaa solid 1px;
		border-right:#fff solid 1px;
		box-shadow: -2px 1px 3px #bbb;
		background:-moz-linear-gradient(left, #bfb, #fff);
		font-weight:bold;
	}
	.date_has_event{
		background-color:#bfb !important;
	}
</style>


