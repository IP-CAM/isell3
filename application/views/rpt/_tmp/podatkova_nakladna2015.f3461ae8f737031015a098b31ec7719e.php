<?php if(!class_exists('raintpl')){exit;}?><?php echo '<?xml  version="1.0" encoding="windows-1251" ?>'; ?>
<DECLAR xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="J1201006.xsd">
  <DECLARHEAD>
    <TIN><?php echo $v["a"]["company_code"];?></TIN>
    <C_DOC><?php echo $v["c_doc"];?></C_DOC>
    <C_DOC_SUB><?php echo $v["c_doc_sub"];?></C_DOC_SUB>
    <C_DOC_VER><?php echo $v["c_doc_ver"];?></C_DOC_VER>
    <C_DOC_TYPE><?php echo $v["c_doc_type"];?></C_DOC_TYPE>
    <C_DOC_CNT><?php echo $v["c_doc_cnt"];?></C_DOC_CNT>
    <C_REG><?php echo $v["c_reg"];?></C_REG>
    <C_RAJ><?php echo $v["c_raj"];?></C_RAJ>
    <PERIOD_MONTH><?php echo $v["period_month"];?></PERIOD_MONTH>
    <PERIOD_TYPE><?php echo $v["period_type"];?></PERIOD_TYPE>
    <PERIOD_YEAR><?php echo $v["period_year"];?></PERIOD_YEAR>
    <C_STI_ORIG><?php echo $v["c_sti_orig"];?></C_STI_ORIG>
    <C_DOC_STAN><?php echo $v["c_doc_stan"];?></C_DOC_STAN>
    <LINKED_DOCS xsi:nil="true" />
    <D_FILL><?php echo str_replace('.','',$v["date_dot"]); ?></D_FILL>
    <SOFTWARE>iSell</SOFTWARE>
  </DECLARHEAD>
  <DECLARBODY>
    <HTYPR><?php echo $v["extra"]->type_of_reason;?></HTYPR>
    <HORIG1><?php echo $v["stay_at_seller"];?></HORIG1>
    <HFILL><?php echo str_replace('.','',$v["date_dot"]); ?></HFILL>
    <HNUM><?php echo $v["view_num"];?></HNUM>
    <HNUM1 xsi:nil="true" />
    <HNUM2 xsi:nil="true" />
    <HNAMESEL><?php echo $v["a"]["company_name"];?></HNAMESEL>
    <HNAMEBUY><?php echo $v["p"]["company_name"];?></HNAMEBUY>
    <HKSEL><?php echo $v["a"]["company_vat_id"];?></HKSEL>
    <HKBUY><?php echo $v["p"]["company_vat_id"];?></HKBUY>
    <HLOCSEL><?php echo $v["a"]["company_jaddress"];?></HLOCSEL>
    <HLOCBUY><?php echo $v["p"]["company_jaddress"];?></HLOCBUY>
    <HTELSEL><?php echo $v["a"]["company_phone"];?></HTELSEL>
    <HTELBUY><?php echo $v["p"]["company_phone"];?></HTELBUY>
    <H01G1S>Договір поставки</H01G1S>
    <H01G2D><?php echo str_replace('.','',$v["p"]["ag_date_dot"]); ?></H01G2D>
    <H01G3S><?php echo $v["p"]["company_agreement_num"];?></H01G3S>
    <H02G1S>Оплата з поточного рахунку</H02G1S>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG2D ROWNUM="<?php echo $counter1+1;?>"><?php echo str_replace('.','',$v["date_dot"]); ?></RXXXXG2D>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG3S ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["2"];?></RXXXXG3S>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG4 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["3"];?></RXXXXG4>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG4S ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["5"];?></RXXXXG4S>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG5 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["4"];?></RXXXXG5>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG6 ROWNUM="<?php echo $counter1+1;?>"><?php echo str_replace(',','.',$value1["6"]); ?></RXXXXG6>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG7 ROWNUM="<?php echo $counter1+1;?>"><?php echo str_replace(',','.',$value1["7"]); ?></RXXXXG7>
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG8 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG9 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["entries"]) && is_array($v["entries"]) && sizeof($v["entries"]) ) foreach( $v["entries"] as $key1 => $value1 ){ $counter1++; ?>
    <RXXXXG10 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
	<R01G7><?php echo str_replace(',','.',$v["footer"]["vatless"]); ?></R01G7>
    <R01G8>0.00</R01G8>
    <R01G9>0.00</R01G9>
    <R01G10>0.00</R01G10>
    <R01G11><?php echo str_replace(',','.',$v["footer"]["vatless"]); ?></R01G11>
    <R02G11 xsi:nil="true" />
    <R03G7><?php echo str_replace(',','.',$v["footer"]["vat"]); ?></R03G7>
    <R03G8 xsi:nil="true" />
    <R03G9 xsi:nil="true" />
    <R03G10S xsi:nil="true" />
    <R03G11><?php echo str_replace(',','.',$v["footer"]["vat"]); ?></R03G11>
    <R04G7><?php echo str_replace(',','.',$v["footer"]["total"]); ?></R04G7>
    <R04G8>0.00</R04G8>
    <R04G9>0.00</R04G9>
    <R04G10>0.00</R04G10>
    <R04G11><?php echo str_replace(',','.',$v["footer"]["total"]); ?></R04G11>
    <R003G10S xsi:nil="true" />
    <H10G1S><?php echo $v["extra"]->sign;?></H10G1S>
  </DECLARBODY>
</DECLAR>