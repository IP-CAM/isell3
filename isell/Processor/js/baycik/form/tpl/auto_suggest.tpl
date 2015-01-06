<div>
	<style type="text/css">
        .asugg_item{
            background-color:#fff;
			padding:2px;
        }
        .asugg_hover{
            background-color:#cdf;
			cursor:default;
        }
		.asugg_holder{
			position:absolute;
			left:0px;top:0px;
			border:#999 1px solid;
			display:none;
			opacity:0.9;
		}
		.asugg_input{
			margin:0px;
			padding:0px;
		}
    </style>
    <input type="text" id="{{id}}input" class="asugg_input dijit dijitReset dijitLeft dijitTextBox" style="padding:1px;" autocomplete="off" title="Артикул" />
    <div style="position:relative;">
        <div dojoAttachPoint="sugg_holder" class="asugg_holder">
        <!--{% for row in suggData.items %}-->
        <div id="{{id}}_sugg{{forloop.counter0}}" class="asugg_item" onmouseover="dijit.byId('{{id}}')._selectSuggItem('{{forloop.counter0}}')" dojoAttachEvent="onclick:_setSelectedValue">
        <nobr><b>{{row.product_code}}</b> {{row.label}} <b><span style="color:blue;">[{{row.product_spack|default:''}}]</span> <span style="color:red">{{row.product_quantity|default:''}}</span></b></nobr>
        </div>
        <!--{% endfor %}-->
        </div>
    </div>
</div>
