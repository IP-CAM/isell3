<?php
require_once 'Data.php';
class Utils extends Data{
    public function sendEmail( $to, $subject, $body, $file, $copy='share', $encoding='utf-8' ){
        if( !BAY_SMTP_SERVER || !BAY_SMTP_USER || !BAY_SMTP_PASS || !BAY_SMTP_SENDER_MAIL ){
                $this->Base->msg("Настройки для отправки email не установленны!");
                return false;
        }
        if( !$to ){
                $this->Base->msg('Не указанны получатели письма!');
                return false;
        }
        $sender_mail=BAY_SMTP_SENDER_MAIL;
        $recep=explode(',',$to);
        /*
         * $copy==share add to recep 
         * $copy==private add to recep 
         * $copy==nocopy add nothing to recep
         */
        if( $copy=='share' ){
                $recep[]=$sender_mail;
        }
        else if( $copy=='private' ){
                $recep[]=$sender_mail=BAY_SMTP_PRIVATE_MAIL;
        }
        try{
            require_once 'application/libraries/swift/swift_required.php';
            $message = Swift_Message::newInstance()
              ->setFrom(array($sender_mail => iconv('windows-1251', 'utf-8',  BAY_SMTP_SENDER_NAME) ))
              ->setSubject($subject)
              ->setTo($recep)
              ->setBody($body,'text/plain',$encoding)
              ->setEncoder(Swift_Encoding::get8BitEncoding());
            if( $file ){
                $attachment = Swift_Attachment::newInstance()
                  ->setFilename($file['name'])
                  ->setBody($file['data'])
                  ;
                $message->attach($attachment);
            }
            $transport = Swift_SmtpTransport::newInstance(BAY_SMTP_SERVER, 587)
              ->setUsername(BAY_SMTP_USER)
              ->setPassword(BAY_SMTP_PASS)
              ->setTimeout(10);
              ;
            $mailer = Swift_Mailer::newInstance($transport);

            if ( !$mailer->send($message,$failures) ){
                $this->Base->msg("Сообщение не было отправленно на:\n".implode("\n",$failures));
                return false;
            }
        }
        catch( Exception $error_string ){
            $this->Base->msg("Ошибка связи с SMTP сервером {BAY_SMTP_SERVER!}\n\nПроверьте соединение с Интернетом.");
            $this->Base->msg($error_string);
            return false;
        }
        return true;
    }
    public function sendSms( $number, $delete_me, $body ){
        if( !BAY_SMS_SENDER || !BAY_SMS_USER || !BAY_SMS_PASS ){
            $this->Base->msg("Настройки для отправки смс не установленны");
            return false;
        }
        if( !in_array('https', stream_get_wrappers()) ){
            $this->Base->msg("Sms can not be sent. https is not available");
            return false;
        }
        try{
            if( time()-$this->Base->svar('smsSessionTime')*1>25*60 ){
                $this->Base->svar('smsSessionTime',time());
                $sid=json_decode(file_get_contents("https://integrationapi.net/rest/User/SessionId?login=".BAY_SMS_USER."&password=".BAY_SMS_PASS));
                $this->Base->svar('smsSessionId',$sid);
            }
            $post_vars=array(
                'sessionId'=>$this->Base->svar('smsSessionId'),
                'sourceAddress'=>BAY_SMS_SENDER,
                'destinationAddress'=>$number,
                'data'=>$body
                );
            $opts = array(
                'http'=>array(
                        'method'=>"POST",
                        'content'=>http_build_query($post_vars)
                        )
            );
            $msg_ids=  json_decode(
                        file_get_contents('https://integrationapi.net/rest/Sms/Send', false, stream_context_create($opts))
                        );
            if( !$msg_ids[0] )
                    return false;
        }catch( Exception $e ){
            $this->Base->svar('smsSessionTime',0);
            /*
             * Make smsSid expire to try again
             */
            return false;
        }
        return true;
    }
    public function fill_length( $word, $length, $delim=' ' ){
        $chars=preg_split('/(?<!^)(?!$)/u', $word ); 
        return array_pad($chars,mb_strlen($chars,'UTF-8')-$length,$delim);
    }
    public function getAllDetails( $comp ){
        $all="$comp[company_name] \n$comp[company_jaddress]";
        if( $comp['company_phone'] )
                $all.=", тел.:$comp[company_phone]";
        if( $comp['company_bank_account'] )
                $all.=", Р/р:$comp[company_bank_account]";
        if( $comp['company_bank_name'] )
                $all.=" в $comp[company_bank_name]";
        if( $comp['company_bank_id'] )
                $all.=" МФО:$comp[company_bank_id]";
        if( $comp['company_vat_id'] )
                $all.=", IПН:$comp[company_vat_id]";
        if( $comp['company_vat_licence_id'] )
                $all.=" Номер свiдоцтва:$comp[company_vat_licence_id]";
        if( $comp['company_code'] )
                $all.=" ЄДРПОУ:$comp[company_code]";
        $all.=" $comp[web]";
        if( $comp['email'] )
                $all.=" E-mail:$comp[email]";
        return $all;
    }
	public function spellNumber( $number, $units, $ret_number=false ){
		$hundreds_i=$this->getNumberPosition($number,100,1);
		$tens_i=$this->getNumberPosition($number,10,1);
		$ones_i=$this->getNumberPosition($number,1,1);
		if( !($hundreds_i||$tens_i||$ones_i) && !$ret_number )return '';
		if( $ones_i==1 && $tens_i!=1 )
			$unit=$units[0];
		else if( $ones_i>1 && $ones_i<5 )
			$unit=$units[1];
		else
			$unit=$units[2];
		if( $ret_number ){
			if($number<10)return "0$number $unit";
			return "$number $unit";
		}
			
		$ones=array("","одна","дві","три","чотири","п'ять","шість","сім","вісім","дев'ять");
		$tens=array("","десять","двадцять","тридцять","сорок","п'ятдесят","шістдесят","сімдесят","вісімдесят","дев'яносто");
		$teens=array("","одинадцять","дванадцять","тринадцять","чотирнадцять","п'ятнадцять","шістнадцять","сімнадцять","вісімнадцять","дев'ятнадцять");
		$hundreds=array("","сто","двісті","триста","чотириста","п'ятсот","шістсот","сімсот","вісімсот","дев'ятсот");
		
		if($tens_i==1)
			return "$hundreds[$hundreds_i] $teens[$ones_i] $unit";
		else
			return "$hundreds[$hundreds_i] $tens[$tens_i] $ones[$ones_i] $unit";
	}
	public function getNumberPosition( $number, $position, $range=1 ){//DEPRECATED
		$number-=$position*10*$range*floor($number/$position/10/$range);
		return floor($number/$position);
	}
	private function getNumberChunk( $number, $position, $length ){
		$point_pos=  strpos($number, '.')-1;
		return substr($number, $position-$point_pos, $length);
	}
	public function spellAmount( $number, $unit=NULL, $return_cents=true ){
		if( !$unit ){
			$unit[0]=array('копійка','копійки','копійок');
			$unit[1]=array('гривня','гривні','гривень');
			$unit[2]=array('тисяча','тисячи','тисяч');
			$unit[3]=array('мільон','мільони','мільонів');
		}
		$millions=$this->getNumberPosition($number,1000000,100);
		$thousands=$this->getNumberPosition($number,1000,100);
		$ones=$this->getNumberPosition($number,1,100);
		$cents=$this->getNumberPosition($number*100,1,10);

		$str=$this->spellNumber($millions,$unit[3]).' '.$this->spellNumber($thousands,$unit[2]).' '.$this->spellNumber($ones,$unit[1]).' '.$this->spellNumber($cents,$unit[0],$return_cents);
		$str=trim($str);
		return mb_strtoupper(mb_substr($str,0,1,'utf-8'),'utf-8').mb_substr($str,1,mb_strlen($str)-1,'utf-8');
	}
	public function getLocalDate( $tstamp ){
		$time=strtotime($tstamp);
		$months=array("січня","лютого","березня","квітня","травня","червня","липня","серпня","вересня","жовтня","листопада","грудня");
		return date('d',$time).' '.$months[date('m',$time)-1].' '.date('Y',$time);
	}
//	public function makeViewOut( $view, $head, $entries, $footer ){
//		$footer['total_spell']=$this->spellAmount($footer['total']);
//		//die($footer['total']);
//		//
//		$footer['vatless']=number_format($footer['vatless'],2,',','');
//		$footer['vat']=number_format($footer['vat'],2,',','');
//		$footer['total']=number_format($footer['total'],2,',','');
//		
//		$active=$this->Base->_acomp;
//		$passive=$this->Base->_pcomp;
//		$active['cvli']=$this->fill_length($active['company_vat_licence_id'],10);
//		$passive['cvli']=$this->fill_length($passive['company_vat_licence_id'],10);
//		$active['cvi']=$this->fill_length($active['company_vat_id'],12);
//		$passive['cvi']=$this->fill_length($passive['company_vat_id'],12);
//		if( !$passive['company_agreement_date'] && !$passive['company_agreement_num'] ){
//			$passive['company_agreement_date']=$view['tstamp'];
//			$passive['company_agreement_num']='-';
//		}
//		$passive['ag_date']=date('dmY',strtotime($passive['company_agreement_date']));
//		$passive['ag_date_dot']=date('d.m.Y',strtotime($passive['company_agreement_date']));
//		$view['a']=$active;
//		$view['p']=$passive;
//		
//		$view['a']['all']=$this->getAllDetails( $view['a'] );
//		$view['p']['all']=$this->getAllDetails( $view['p'] );
//		$view['a']['allbr']=str_replace("\n",'<br>',$view['a']['all']);
//		$view['p']['allbr']=str_replace("\n",'<br>',$view['p']['all']);
//		$view['user_sign']=$this->Base->svar('user_sign');
//		$view['user_position']=$this->Base->svar('user_position');
//		
//		$view['loc_date']=$this->getLocalDate($view['tstamp']);
//		$view['vat_percent']=20;
//		$view['date']=date('dmY',strtotime($view['tstamp']));
//		$view['date_dot']=date('d.m.Y',strtotime($view['tstamp']));
//		$view['extra']=json_decode($view['view_efield_values']);
//		$view['view_num_fill']=$this->fill_length($view['view_num'],7);
//		$view['entries_num']=count($entries['rows']);
//		$view['head']=$head;
//		$view['entries']=$entries['rows'];
//		$view['footer']=$footer;
//		return $view;
//	}
	
	function getPrintPage( $doc_view_id, $out_type ){
		ob_start();
		$this->Base->LoadClass('Document');
		$view=$this->Base->Document->getViewOut($doc_view_id);
		if( !$view ){
			die('Образ под таким номером не найден!');
		}
		$view['a']['all']=str_replace("\n",'<br>',$view['a']['all']);
		$view['p']['all']=str_replace("\n",'<br>',$view['p']['all']);
		
		require_once 'lib/rain/rain.tpl.class.php';
		$tpl = new RainTPL();
		$tpl->configure( 'tpl_dir', 'tpl/companies/doc_tpls/' );
		$tpl->configure( 'cache_dir', 'tpl/companies/doc_tpls/tmp/' );
		$tpl->assign('v',$view);
		if( $out_type=='html_file' ){
			header('Content-type: text/html; charset=utf-8;');
			header("Content-Disposition: attachment; filename=\"$view[view_tpl]_№$view[view_num].html\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
			header("Pragma: public");
			$html=$tpl->draw($view['view_tpl'],true);
			require 'tpl/companies/doc_tpls/document_wrapper.php';
		}
		else if( $out_type=="xml" ){
			header('Content-type: text/xml; charset=windows-1251;');
			header("Content-Disposition: attachment; filename=\"$view[view_tpl]_№$view[view_num].xml\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
			$tpl->configure( 'tpl_ext', 'xml' );
			$html=$tpl->draw('podatkova_nakladna',true);
			$html=iconv('utf-8','windows-1251',$html);
			echo $html;
		}
		else {
			$show_controls=true;
			header('Content-type: text/html; charset=utf-8;');
			$html=$tpl->draw($view['view_tpl'],true);
			require 'tpl/companies/doc_tpls/document_wrapper.php';
		}
		
		$output=ob_get_contents();
		ob_end_clean();
		return $output;
	}
}
?>