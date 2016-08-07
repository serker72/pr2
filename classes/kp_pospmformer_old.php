<?
require_once('abstractitem.php');
require_once('pl_curritem.php');
require_once('pl_positem.php');
require_once('pl_pospriceitem.php');
require_once('price_kind_item.php');

//�������� ���������� ��� � ��������� ��
class KpPosPMFormer{
	
	
	
	
	
	//������� ����� ��������� �� ��������� ��������
	public function CalcCost(&$positions){
		$cost=0;
		
		//����� ������� �������
		$main_id=0; $main_index=-1;
		
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				 $main_id=$v['pl_position_id'];	
				 $main_index=$k;
				 break;
			}
		}
		//
		//������� ����������� ���� ��. ������� �� �������
		$price_f_main=$this->CalcPriceFMain($main_index, $positions);
		//echo $price_f_main; die();
		
		//������� ����������� ���� �� ������� �������  �� �������
		$this->CalcPriceFNotMain($main_index, $positions);
		
		$cost=$this->CalcAllCost($main_index, $positions);
		
		
		
		if($v['price_kind_id']==1){
			//�������� ��������� �������� (���� � ��� �����-���� - ddpm)
			//����� ����� ����� ��������� - $cost
			
			//��������� �������� ddpm  $pl['delivery_ddpm']
			//� ��������� ���� ������� $positions
			
			//������ ��������� �������� ddpm
			$_pl=new PlPosItem;
			$pl=$_pl->GetItemById($v['pl_position_id']);
			$pl['delivery_ddpm'];
			
			
			//var_dump( $pl['delivery_ddpm']);
			
			//����� ������� ��������� �������� �� �������
			$this->ApplyDeliveryDDPM($cost, $pl['delivery_ddpm'], $positions);
			
		}else{
			
		
		}
		
		
		//����� ����� ���������
		$cost=$this->CalcAllCost($main_index, $positions);
		
		//������ � ����� ��������� ������� ������� ����� ��������� ����� ��
		$positions[$main_index]['total']=$cost;
	
		
		//������������� ������ - ������� ������� � ������
		
		$old_array=$positions;
		$new_array=array();
		
		$new_array[]=$positions[$main_index];
		
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
				$new_array[]=$v;
			}
			
		}
		
		
			
		
		
		$positions=$new_array;
		
		return $cost;	
	}
	
	
	
	//��������� �������� DDPM
	protected function ApplyDeliveryDDPM($cost, $ddpm, &$positions){
		//�������� 	cost, total � �������
		foreach($positions as $k=>$v){
			
			$x=$ddpm*$v['cost']/$cost;
			
			//echo "������� $v[pl_position_id]  ���� $v[cost] ������� $x ����� ".($positions[$k]['cost']+$x)."<br />";
			
			$positions[$k]['cost']=round($positions[$k]['cost']+$x,2);
			$positions[$k]['total']=round($positions[$k]['total']+$x,2);
		}
	}
	
	
	
	
	
	public function CalcPriceFMain($index, &$positions){

		$price=(float)$positions[$index]['price'];
		//echo $price.' ';
		
		 
			if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
		
		$positions[$index]['price_f']=round($price, 2);
		
		//$cost_main= $price*$positions[$index]['quantity'];
		$positions[$index]['cost']=round($price*$positions[$index]['quantity'], 2);
		$positions[$index]['total']=round($price*$positions[$index]['quantity'], 2);
		
		
			
		return round($price, 2);
		
	}
	
	public function CalcPriceFNotMain($index, &$positions){
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
			  $price=(float)$positions[$k]['price'];
			  
			 /* echo '<pre>';
			  print_r($v);
			  echo '</pre>';
			  */
			  
			  //�� ��������� � ����  ����� ������ ���������, ���� ����� - ��� ���:	
			  if(!isset($v['is_install'])) {
				  $_pl=new PlPosItem;
				  $pl=$_pl->GetItemById($v['id']);
				  $v['is_install']=$pl['is_install'];
			  }
			  if(!isset($v['is_calc_price'])) {
				  $_pl=new PriceKindItem;
				  $pl=$_pl->GetItemById($v['price_kind_id']);
				  $v['is_calc_price']=$pl['is_calc_price'];
			  }
			  
			  
			  if(!(($v['is_install']==1)&&($v['is_calc_price']==1))){
		 		//not PNR - �������� ��� ������
			  	if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			  	else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
			  }else{
				  //echo 'PNR!!!!!!!!!!!';	
				  //  ���� ������ ������������ - �������� ��
				  if($v['pl_discount_id']==2){
					  if($v['pl_discount_rub_or_percent']==0) $price-=(float)$v['pl_discount_value'];
			  		  else $price-=$price*((float)$v['pl_discount_value']/100);
					  //echo $price;
				  }
			  }
			  $positions[$k]['price_f']=round($price, 2);	
			  $positions[$k]['price_pm']=round($price, 2);	
			  
			  $positions[$k]['cost']=round($price*$positions[$k]['quantity'], 2);
			  $positions[$k]['total']=round($price*$positions[$k]['quantity'], 2);
			  
			  //echo "price=$price; total= ".$positions[$k]['total']."<br>";		
			}
		}
	}
	
	
	
	
	
	protected function CalcAllCost($index, &$positions){
		$sum=0;
		
		$sum+=$positions[$index]['total'];
		
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
				$sum+=$positions[$k]['total'];
		
			}
		}
		
		return $sum;	
	}
	
	
	public function CalcNDS(array $positions){
		$cost=0;
		
		$cost=sprintf("%.2f",($this->CalcCost($positions)-$this->CalcCost($positions)/((100+NDS)/100)));
		
		return $cost;	
	}
	
	
	public function GetCurrency($positions){
		//����� ������� �������
		$main_id=0; $main_index=-1;
		
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				 $main_id=$v['pl_position_id'];	
				 $main_index=$k;
				 break;
			}
		}
		
		if(!isset($positions[$main_index]['signature'])){
			$_curr=new PlCurrItem;
			$currency=$_curr->GetItemById($positions[$main_index]['currency_id']);
			$signature=$currency['signature'];
		}else $signature=$positions[$main_index]['signature'];
		
		$currency=array('currency_id'=>$positions[$main_index]['currency_id'],
						'signature'=>$signature);
		return $currency;
	}
	
}
?>