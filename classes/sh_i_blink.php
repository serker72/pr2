<?
require_once('abstractitem.php');


class ShIBlink{
	
	
	
	//обобщенная функция цвета и мигания
	public function OverallBlink($sh_i, $has_filter, &$color){
		$res=false;
		
		$color='black';
		
		
		
		if($has_filter&&($sh_i['pdate_shipping_plan']!=0)&&($sh_i['status_id']==2)){
			$now=DateFromdmy(date('d.m.Y'));	
			
			if($sh_i['pdate_shipping_plan']==$now){
				$color='green';
			}elseif($now>$sh_i['pdate_shipping_plan']){
				$color='red';
			}
			
		}
		
		
		
		return $res;
	}
}
?>