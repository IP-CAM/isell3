<?php if(!class_exists('raintpl')){exit;}?><style type="text/css">
	body, html, td{
		font-family:Arial;
		font-size:13px;
		line-height:normal;
	}
</style>
<div class="page">
<table width="718" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center"><table width="700" border="0" cellpadding="3" cellspacing="0">
      <tr>
        <td width="120" align="left" valign="top"><b><u>Постачальник</u></b></td>
        <td align="left">
        <?php echo $v["a"]["allbr"];?>
        </td>
      </tr>
      <tr>
        <td align="left" valign="top"><b><u>Одержувач</u></b></td>
        <td align="left">
        <?php echo $v["p"]["allbr"];?>
    </td>
      </tr>
      <tr>
        <td align="left" valign="top"><b><u>Платник</u></b></td>
        <td align="left">той самий</td>
      </tr>
      <?php if( $v["extra"]->sell_condition ){ ?>
      <tr>
        <td align="left" valign="top"><b><u>Умова продажу</u></b></td>
        <td align="left"><?php echo $v["extra"]->sell_condition;?></td>
      </tr>
      <?php } ?>
    </table></td>
  </tr>
  <tr>
    <td align="center"><table border="0" cellpadding="3" cellspacing="0">
      <tr>
        <td align="center">
        <span class="large"><b>Видаткова накладна № <?php echo $v["view_num"];?><br /> від <?php echo $v["loc_date"];?> р.</b></span>
        </td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td align="center">
    <table class="table_border" width="700" border="0" cellpadding="1" cellspacing="0">
      <tr class="big">
        <td width="15" align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>№</b></td>
        <td width="15" align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>Артикул</b></td>
        <td align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>Повна назва товару</b></td>
        <td width="50" align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>Од.вим.</b></td>
        <td width="50" align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>К-ть</b></td>
        <td width="95" align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>Цiна без ПДВ</b></td>
        <td width="100" align="center" nowrap="nowrap" bgcolor="#aaaaaa"><b>Сума без ПДВ</b></td>
      </tr>
      <?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
      <tr>
        <td align="right" valign="top"><?php echo $counter1+1;?></td>
        <td align="left" valign="top" nowrap="nowrap"><?php echo $value1["1"];?></td>
        <td align="left" valign="top"><?php echo $value1["2"];?></td>
        <td align="center" valign="top"><?php echo $value1["5"];?></td>
        <td align="right" valign="top"><?php echo $value1["4"];?></td>
        <td align="right" valign="top"><?php echo $value1["6"];?></td>
        <td align="right" valign="top"><?php echo $value1["7"];?></td>
      </tr>
      <?php } ?>
      <tr>
        <td colspan="6" align="right" style="border:none"><b>Разом без ПДВ: </b></td>
        <td align="right"><?php echo $v["footer"]["vatless"];?></td>
      </tr>
      <tr>
        <td colspan="6" align="right" style="border:none"><b>ПДВ: </b></td>
        <td align="right"><?php echo $v["footer"]["vat"];?></td>
      </tr>
      <tr>
        <td colspan="6" align="right" style="border:none"><b>Всього з ПДВ: </b></td>
        <td align="right"><?php echo $v["footer"]["total"];?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td align="center"><table width="700" border="0" cellspacing="0">
      <tr>
        <td width="350" align="left" valign="top"><b>Всього на суму:<br /><?php echo $v["footer"]["total_spell"];?><br /></b><b><br />
        ПДВ: <?php echo $v["footer"]["vat"];?><br />
          </b></td>
        <td align="right" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" valign="top">&nbsp;</td>
        <td align="right" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" valign="top"><table width="320" border="0" cellspacing="0">
          <tr>
            <td width="100"><b>Відвантажив(ла):</b></td>
            <td align="left" style="border-bottom:solid 1px #000">&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td align="center"><?php echo $v["user_sign"];?> <sup>*</sup></td>
          </tr>
          <tr>
            <td colspan="2"><small>* <?php echo $v["user_position"];?></small></td>
          </tr>
        </table></td>
        <td align="right" valign="bottom"><table width="320" border="0" cellspacing="0">
          <tr>
            <td width="100"><b>Отримав(ла):</b></td>
            <td align="left" style="border-bottom:solid 1px #000">&nbsp;</td>
          </tr>
          <tr>
            <td height="25" colspan="2">за дор. серії : <?php echo $v["extra"]->warrant;?></td>
            </tr>
        </table></td>
      </tr>
      <tr>
        <td align="left" valign="top">&nbsp;</td>
        <td align="right" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" valign="top"><?php if( $v["footer"]["total_weight"] ){ ?>Вага: <?php echo $v["footer"]["total_weight"];?>кг <?php } ?><?php if( $v["footer"]["total_volume"] ){ ?>Об'єм: <?php echo $v["footer"]["total_volume"];?>м<sup><small>3</small></sup><?php } ?></td>
        <td align="right" valign="bottom">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
</table> 
</div>