<script type="text/javascript">
dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.Button");

function beforeInit(){
	document.title="Переместить документ";
	if( fvalue.disable ){
		for( i in fvalue.disable )dijit.byId(fvalue.disable[i]).disabled=true;
	}
}
function beforeSubmit(){
	return true;
}
</script>
<div dojoType="dojo.data.ItemFileReadStore" jsId="companyStore" url="./?mod=Companies&rq=companieslist"></div>
<div dojoType="dijit.form.Form" id="EditorForm" jsId="EditorForm" encType="multipart/form-data" action="" method="post">
<input type="hidden" dojoType="dijit.form.TextBox" name="copy" id="copy" value="0" />
<table align="center">
        <tr>
            <td>Контрагент:<br />
                <input dojoType="dijit.form.FilteringSelect" store="companyStore" searchAttr="label" name="passive_company_id" id="passive_company_id" style="width:250px;">
            </td>
        </tr>
       <tr>
            <td align="center">
                <button dojoType="dijit.form.Button" id="moveDocButton" type="submit">Перенести</button>
                <button dojoType="dijit.form.Button" type="submit" onClick="document.getElementById('copy').value=1">Копировать</button>
                <button dojoType="dijit.form.Button" onclick="window.close()">Отменить</button>
            </td>
        </tr>
        <tr>
            <td align="center" colspan="2" height="50">&nbsp;</td>
        </tr>
    </table>
</div>
