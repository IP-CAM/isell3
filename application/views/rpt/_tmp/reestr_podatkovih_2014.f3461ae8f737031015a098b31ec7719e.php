<?php if(!class_exists('raintpl')){exit;}?><?php echo '<?xml  version="1.0" encoding="windows-1251" ?>'; ?>
<DECLAR xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="J1201507.xsd">
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
    <D_FILL><?php echo $v["today"];?></D_FILL>
    <SOFTWARE>iSell</SOFTWARE>
  </DECLARHEAD>
  <DECLARBODY>
    <HZ>1</HZ>
    <HNP>01</HNP>
    <HZY><?php echo $v["period_year"];?></HZY>
    <HZM><?php echo $v["period_month"];?></HZM>
    <HNAME><?php echo $v["a"]["company_name"];?></HNAME>
    <HNPDV><?php echo $v["a"]["company_vat_id"];?></HNPDV>
    <HNSPDV xsi:nil="true" />
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG1 ROWNUM="<?php echo $counter1+1;?>"><?php echo $counter1+1;?></T1RXXXXG1>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG2D ROWNUM="<?php echo $counter1+1;?>"><?php echo str_replace('.','',$value1["given"]); ?></T1RXXXXG2D>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG3S ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["doc_num"];?></T1RXXXXG3S>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG31 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG41S ROWNUM="<?php echo $counter1+1;?>">ПНП</T1RXXXXG41S>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG42S ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG43S ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG5S ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["company_name"];?></T1RXXXXG5S>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG6 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["company_vat_id"];?></T1RXXXXG6>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG7 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["total"];?></T1RXXXXG7>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG8 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["vatless"];?></T1RXXXXG8>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG9 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["vat"];?></T1RXXXXG9>
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG10 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG11 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG12 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["sell"]["entries"]["items"]) && is_array($v["sell"]["entries"]["items"]) && sizeof($v["sell"]["entries"]["items"]) ) foreach( $v["sell"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T1RXXXXG13 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
    <R011G7><?php echo str_replace(' ','',$v["sell"]["gtotal"]); ?></R011G7>
    <R011G8><?php echo str_replace(' ','',$v["sell"]["gvatless"]); ?></R011G8>
    <R011G9><?php echo str_replace(' ','',$v["sell"]["gvat"]); ?></R011G9>
    <R011G10 xsi:nil="true" />
    <R011G11 xsi:nil="true" />
    <R011G12 xsi:nil="true" />
    <R011G13 xsi:nil="true" />
    <R012G7 xsi:nil="true" />
    <R012G8>0.00</R012G8>
    <R012G9 xsi:nil="true" />
    <R012G10 xsi:nil="true" />
    <R012G11 xsi:nil="true" />
    <R012G12 xsi:nil="true" />
    <R012G13 xsi:nil="true" />
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG1 ROWNUM="<?php echo $counter1+1;?>"><?php echo $counter1+1;?></T2RXXXXG1>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG2D ROWNUM="<?php echo $counter1+1;?>"><?php echo substr($value1["given"],0,2); ?><?php echo $v["period_month"];?><?php echo $v["period_year"];?></T2RXXXXG2D>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG3D ROWNUM="<?php echo $counter1+1;?>"><?php echo str_replace('.','',$value1["given"]); ?></T2RXXXXG3D>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG4S ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["doc_num"];?></T2RXXXXG4S>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG41 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG51S ROWNUM="<?php echo $counter1+1;?>">ПНП</T2RXXXXG51S>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG52S ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG53S ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG6S ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["company_name"];?></T2RXXXXG6S>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG7 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["company_vat_id"];?></T2RXXXXG7>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG8 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["total"];?></T2RXXXXG8>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG9 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["vatless"];?></T2RXXXXG9>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG10 ROWNUM="<?php echo $counter1+1;?>"><?php echo $value1["vat"];?></T2RXXXXG10>
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG11 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG12 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG13 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG14 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG15 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
<?php $counter1=-1; if( isset($v["buy"]["entries"]["items"]) && is_array($v["buy"]["entries"]["items"]) && sizeof($v["buy"]["entries"]["items"]) ) foreach( $v["buy"]["entries"]["items"] as $key1 => $value1 ){ $counter1++; ?>
    <T2RXXXXG16 ROWNUM="<?php echo $counter1+1;?>" xsi:nil="true" />
<?php } ?>
    <R021G8><?php echo str_replace(' ','',$v["buy"]["gtotal"]); ?></R021G8>
    <R021G9><?php echo str_replace(' ','',$v["buy"]["gvatless"]); ?></R021G9>
    <R021G10><?php echo str_replace(' ','',$v["buy"]["gvat"]); ?></R021G10>
    <R021G11 xsi:nil="true" />
    <R021G12 xsi:nil="true" />
    <R021G13 xsi:nil="true" />
    <R021G14 xsi:nil="true" />
    <R021G15 xsi:nil="true" />
    <R021G16 xsi:nil="true" />
    <R022G8 xsi:nil="true" />
    <R022G9 xsi:nil="true" />
    <R022G10 xsi:nil="true" />
    <R022G11 xsi:nil="true" />
    <R022G12 xsi:nil="true" />
    <R022G13 xsi:nil="true" />
    <R022G14 xsi:nil="true" />
    <R022G15 xsi:nil="true" />
    <R022G16 xsi:nil="true" />
    <HFILL><?php echo $v["today"];?></HFILL>
    <HBOS><?php echo $v["pref"]["director_name"];?></HBOS>
    <HKBOS><?php echo $v["pref"]["director_tin"];?></HKBOS>
    <HBUH><?php echo $v["pref"]["accountant_name"];?></HBUH>
    <HKBUH><?php echo $v["pref"]["accountant_tin"];?></HKBUH>
  </DECLARBODY>
</DECLAR>