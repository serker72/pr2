<?
require_once('abstractitem.php');

//абстрактный элемент
class BillPosPMFormer{
	
	
	//считаем и возвращаем массив из цен +/-, стоимости и стоимости +/-
	public function Form($price, $quantity, $has_pm, $plus_or_minus, $value, $rub_or_percent,$price_pm, $total){
		$v=array();
		
		
		/*
		$v['price_pm']=0;
			$v['cost']=0;
			$v['total']=0;*/
		
		
		/*if($has_pm){
			
			$slag=0;
			if($rub_or_percent==0){
				$slag=$value;
			}else{
				$slag=$price*$value/100.0;
			}
			
			if($plus_or_minus==1){ //$slag=-1.0*$slag;
				$v['price_pm']=$price-$slag;
			}else{
				$v['price_pm']=$price+$slag;
			}
			
			$v['cost']=$price*$quantity;
			
			$v['total']=$v['price_pm']*$quantity;
		}else{
			$v['price_pm']=$price;
			$v['cost']=$price*$quantity;
			$v['total']=$v['cost'];
			
		}*/
		
		
		$v['price_pm']=$price_pm;
			$v['cost']=$price*$quantity;
			$v['total']=$total;
		
			
		return $v;
	}
	
	
	
	public function FormDiscount($price, $quantity, $has_pm, $plus_or_minus, $value, $rub_or_percent,$price_pm, $total, $discount_value, $discount_rub_or_percent){
		$v=array();
		
		$slag=0;
		$slag_un=0;
		if($has_pm){
			
			$slag=0;
			if($rub_or_percent==0){
				$slag=$value;
			}else{
				$slag=$price*$value/100.0;
			}
			
			$slag_un=$slag;
			if($plus_or_minus==1){ //$slag=-1.0*$slag;
				//$v['price_pm']=$price-$slag;
				$slag=-1.0*$slag;
			}else{
				//$v['price_pm']=$price+$slag;
			}
			
			$v['price_pm']=$price+$slag;
			
			$v['cost']=$price*$quantity;
			
			$v['total']=$v['price_pm']*$quantity;
		}else{
			$v['price_pm']=$price;
			$v['cost']=$price*$quantity;
			$v['total']=$v['cost'];
			
		}
		
		if($discount_rub_or_percent==0){
			//рубли	
			$v['discount_amount']=$discount_value;
		}else{
			//%
			$v['discount_amount']=$discount_value*$slag_un/100.0;
		}
		
		
		
		return $v;
	}
	
	
	
	
	
	//считаем общую стоимость по введенным позици€м
	public function CalcCost(array $positions, $changed_totals=NULL){
		$cost=0;
		foreach($positions as $k=>$v){
			
			
			//Ё“ќ —ƒ≈ЋјЌќ ƒЋя ѕ–≈ƒ¬ј–»“≈Ћ№Ќќ√ќ јЌјЋ»«ј —”ћћџ ѕќ—“”ѕЋ≈Ќ»я при смене +/- в счете
			//перебрать changed_totals, если нашли совпадение по всем трем параметрам - то тотал считаем как прайс_пм из changed_totals*кол-во  
			
			$was_in=false; $was_in_index=-1;		
			if($changed_totals!==NULL){
				//echo 'zzzzzzzzzzzzzzzz';
				foreach($changed_totals as $kk=>$vv){
					//print_r($vv); print_r($v);
					
					if(($v['position_id']==$vv['position_id'])
					&&($v['pl_position_id']==$vv['pl_position_id'])
					&&($v['pl_discount_id']==$vv['pl_discount_id'])
					&&($v['pl_discount_value']==$vv['pl_discount_value'])
					&&($v['pl_discount_rub_or_percent']==$vv['pl_discount_rub_or_percent'])
					&&($v['kp_id']==$vv['kp_id'])
					&&($v['out_bill_id']==$vv['out_bill_id'])
					){
						$was_in=true;
						$was_in_index=$kk;
						break;	
					}
				}
			}
			
			
			
			if(($changed_totals!==NULL)&&($was_in)){
				
				//var_dump( $changed_totals[$was_in_index]['price_pm']);
				
				$cost+=$changed_totals[$was_in_index]['price_pm']*$v['quantity'];
				
			}else {
				$stru=$this->Form($v['price_f'],$v['quantity'],$v['has_pm'],$v['plus_or_minus'],$v['value'],$v['rub_or_percent'],$v['price_pm'],$v['total']);
			
				$cost+=$stru['total'];
			}
				
		}
		
		return $cost;	
	}
	
	public function CalcNDS(array $positions){
		$cost=0;
		
		$cost=sprintf("%.2f",($this->CalcCost($positions)-$this->CalcCost($positions)/((100+NDS)/100)));
		
		return $cost;	
	}
	
}
?>