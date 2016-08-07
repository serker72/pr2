<?
/************
* getCourse()
* Получение курса валют
* $currency - массив с кодами валют
* Возвращает массив со значениями курсов заданных валют
* (ключ каждого элемента массива - название валюты на русском языке)
* Коды валют можно найти здесь: http://www.cbr.ru/scripts/XML_val.asp?d=0
* Например:
* Доллар США - R01235
* Евро - R01239
* Фунт стерлингов - R01035
*************/
function getCourse($currency = array(
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

//print_r( getCourse());

?>