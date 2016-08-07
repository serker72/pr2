<?
require_once('currency_sync.class.php');
require_once('currency_rates.class.php'); 

//работа с курсами валют
class CurrencySolver extends AbstractGroup{
	public $_rates_item;
	public $_sync_item;
	
	
	 
	//установка всех имен
	protected function init(){
	 
		$this->pagename='currency_rates.php';		
		
		$this->_rates_item=new CurrencyRatesItem;
		$this->_sync_item=new CurrencySyncItem;
		
	}
	
	
	//получить актуальные курсы
	public function GetActual(){
		/*
		pdate=
		pdate_formatted=
		currencies=
			2=>
			nominal=1
			rates=46.66
			3=>
			nominal=1
			rates=35.55
		*/
		
		
		$sql='select * from currency_syncs order by id desc limit 1';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		
		$sql='select p.*, pc.signature  from currency_rates as p left join pl_currency as pc on p.currency_id=pc.id where p.sync_id="'.$f['id'].'" order by currency_id asc';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$currencies=array();
		for($i=0; $i<$rc; $i++){
			$g=mysqli_fetch_array($rs);
			$currencies[$g['currency_id']]=array(
				'nominal'=>$g['nominal'], 
				'rates'=>$g['rates'], 
				'currency_id'=>$g['currency_id'], 
				'signature'=>$g['signature']);
		}
		
		//добавим рубль!
		$currencies[1]=array('nominal'=>1, 'rates'=>1, 'currency_id'=>1 );
		
		return array(
			'pdate'=>$f['pdate'],
			'pdate_formatted'=>date('d.m.Y H:i:s', $f['pdate']),
			'currencies'=>$currencies
			);
			
			
	}
	
	
	//получить курсы на дату
	public function GetToDate($pdate='01.01.2015'){
		/*
		pdate=
		pdate_formatted=
		currencies=
			2=>
			nominal=1
			rates=46.66
			3=>
			nominal=1
			rates=35.55
		*/
		
		
		$sql='select * from currency_syncs where pdate<="'.(DateFromDMY($pdate)+24*60*60-1).'" order by id desc limit 1';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		
		$sql='select p.*, pc.signature  from currency_rates as p left join pl_currency as pc on p.currency_id=pc.id where p.sync_id="'.$f['id'].'" order by currency_id asc';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$currencies=array();
		for($i=0; $i<$rc; $i++){
			$g=mysqli_fetch_array($rs);
			$currencies[$g['currency_id']]=array(
				'nominal'=>$g['nominal'], 
				'rates'=>$g['rates'], 
				'currency_id'=>$g['currency_id'], 
				'signature'=>$g['signature']);
		}
		
		//добавим рубль!
		$currencies[1]=array('nominal'=>1, 'rates'=>1, 'currency_id'=>1 );
		
		return array(
			'pdate'=>$f['pdate'],
			'pdate_formatted'=>date('d.m.Y H:i:s', $f['pdate']),
			'currencies'=>$currencies
			);
			
			
	}
	
	
	
	
	//конвертаци€: статический метод
	public static function Convert($value, $rates, $from_id, $to_id){
		$val=0;
		
		try{
			//перевод в рубли
			$val=$value*$rates['currencies'][$from_id]['rates']/$rates['currencies'][$from_id]['nominal']; //$value/$rates['currencies']['
			
			//перевод в заданную валюту
			$val=$val*$rates['currencies'][$to_id]['nominal']/$rates['currencies'][$to_id]['rates'];
				
		}catch(Exception $e) {}
		
		return $val;
	}
	
	
	//получить все имеющиес€ курсы
	public function GetItems($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE,
		$can_refresh_rates=false
	){
		$txt='';
		
		$sm=new SmartyAdm;
		
		
		$sql='select p.*, 
			
			ra2.rates as ra2_rates, cu2.name as cu2_name, cu2.signature as cu2_signature,
			ra3.rates as ra3_rates, cu3.name as cu3_name, cu3.signature as cu3_signature
			
		 from currency_syncs as p 
			left join  currency_rates as ra2 on ra2.sync_id=p.id and ra2.currency_id=2
			left join  pl_currency as cu2 on ra2.currency_id=cu2.id
			
			
			left join  currency_rates as ra3 on ra3.sync_id=p.id and ra3.currency_id=3
			left join  pl_currency as cu3 on ra3.currency_id=cu3.id
		 ';
		
		$sql_count='select count(*) 
		 from currency_syncs as p 
			left join  currency_rates as ra2 on ra2.sync_id=p.id and ra2.currency_id=2
			left join  pl_currency as cu2 on ra2.currency_id=cu2.id
			
			
			left join  currency_rates as ra3 on ra3.sync_id=p.id and ra3.currency_id=3
			left join  pl_currency as cu3 on ra3.currency_id=cu3.id
		 ';
		
		 
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$_pi=new OrgItem;
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		
		$au=new AuthUser();
		 
		$sm->assign('can_refresh_rates', $can_refresh_rates);
		
		//ссылка дл€ кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	//запись
	public function Put($force=false){
		
		
		if(!$force){
			//проверить, были ли уже записи от сегодн€
			//если были - не записывать!
			$sql='select count(*) from currency_syncs where pdate between "'.datefromdmy(date('d.m.Y')).'" and  "'.(datefromdmy(date('d.m.Y'))+24*60*60-1).'"  ';
			$set=new mysqlset($sql);
			$rs=$set->getResult();
			$f=mysqli_fetch_array($rs);
			if(intval($f[0])>0) return;
		}
		
		
		//получим курсы валют
		//если вернулось null или хот€ бы в одном из результатов  'курс не найден' - уходим
		
		$result=$this->getCourse();
		
		$can_sync=true;
		
		if($result===NULL) $can_sync=$can_sync&&false;
		
		foreach($result as $k=>$v) if($v['value']=='курс не найден')  $can_sync=$can_sync&&false;
		
		if($can_sync){
			//echo 'was not';
			
			//занесем что получилось	
			
			$sync_params=array('pdate'=>time());
			$sync_id=$this->_sync_item->Add($sync_params);
			
			foreach($result as $k=>$v){
				$rates_params=array();
				$rates_params['sync_id']=$sync_id;
				$rates_params['currency_id']=$v['inner_id'];
				$rates_params['nominal']=$v['nominal'];
				$rates_params['rates']=	$v['value'];
				
				$this->_rates_item->Add($rates_params);
			}
			
		}
		
			
	}
	
	
	
	
	
	
	
	/************
* getCourse()
* ѕолучение курса валют
* $currency - массив с кодами валют
* ¬озвращает массив со значени€ми курсов заданных валют
* (ключ каждого элемента массива - название валюты на русском €зыке)
*  оды валют можно найти здесь: http://www.cbr.ru/scripts/XML_val.asp?d=0
* Ќапример:
* ƒоллар —Ўј - R01235
* ≈вро - R01239
* ‘унт стерлингов - R01035
*************/
	protected function getCourse($currency = array(
								array('inner_id'=>3, 'bank_code'=>'R01235'),
								array('inner_id'=>2, 'bank_code'=>'R01239'))){
		  $value = array();
		  /*$request = "GET http://www.cbr.ru/scripts/XML_daily.asp HTTP/1.0\r\n"
				  ."Host: www.cbr.ru\r\n"
				  ."Referer: http://www.classpersonal.ru\r\n"
				  ."Cookie: income=1\r\n\r\n";*/
		  $request = "GET http://www.cbr.ru/scripts/XML_daily.asp HTTP/1.0\r\n"
				  ."Host: www.cbr.ru\r\n"
				  ."Referer: http://www.gydex.ru\r\n"
				  ."Cookie: income=1\r\n\r\n";
		  $fp = fsockopen('www.cbr.ru',80,$errno,$errstr,10);
		  if(!$fp) return null;
		  $xml_respose = '';
		  fwrite($fp,$request);
		  while(!feof($fp)) $xml_respose.= fgets($fp,1024);
		  fclose($fp);
		  $xml_respose = substr($xml_respose,strpos($xml_respose,'<?xml'));
		  $xml = DOMDocument::loadXML($xml_respose);
		  $val=Array();
		  if($xml && is_array($currency)){
			  $xpc = new DOMXPath($xml);
			  foreach($currency as $curr){
				  $cur=$curr['bank_code'];
				  $nominal = 1;
				  $name = '';
				  
				  $res = $xpc->query('/ValCurs/Valute[@ID="'.$cur.'"]/Nominal/text()');
				  if($res->length) $nominal = floatval(str_replace(',','.',$res->item(0)->data));
							  
				  //$res = $xpc->query('/ValCurs/Valute[@ID="'.$cur.'"]/Name/text()');
				  $res = $xpc->query('/ValCurs/Valute[@ID="'.$cur.'"]/CharCode/text()');
				  if($res->length) $name = $res->item(0)->data;
				  
				  $res = $xpc->query('/ValCurs/Valute[@ID="'.$cur.'"]/Value/text()');
				  if($res->length) $value[$name] = floatval(str_replace(',','.',$res->item(0)->data))/$nominal;
				  else $value[$name] = 'курс не найден';
				  
				  
				  $res = $xpc->query('/ValCurs/@Date');
				  //var_dump($res->item(0));
				  if($res->length) $date = $res->item(0)->value; //->data;
				  
				  
				  $val[]=Array(
					  'inner_id'=>$curr['inner_id'],
					  'nominal'=>$nominal,
					  'name' => $name,
					  'value'=>$value[$name],
					  'date'=>$date
				  );
			  }
		  }else return null;
		  return $val;
	  }
	
}
?>