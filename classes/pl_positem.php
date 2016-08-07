<?
require_once('abstractitem.php');
require_once('positem.php');
require_once('pl_pospriceitem.php');

require_once('price_kind_item.php');

require_once('round_define.class.php');
require_once('currency/currency_solver.class.php');

//������� �����-�����
class PlPosItem extends AbstractItem{
	public $_round_define;
	public $_cs;
	
		
	//��������� ���� ����
	protected function init(){
		$this->tablename='pl_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
		$this->_round_define=new RoundDefine;
		$this->_cs=new CurrencySolver;
	}
	
	
	//������� ������ � ��������� ������������
	
	//�������� 
	public function Add($params){
		$code=false;
		
		
		$test_item=$this->GetItemByFields(array('position_id'=>$params['position_id']));
		if($test_item===false){
			if(isset($params['position_id'])) $params['id']=$params['position_id'];
			
			$code=AbstractItem::Add($params);	
		}
		
		
		return $code;
	}
	
	
	//�������
	public function Del($id){
		
		
		if($this->CanDelete($id)){
		 
		  
		 
		 
		  $ns= new NonSet('delete from pl_discount_maxval where pl_position_id="'.$id.'"');
		  $ns= new NonSet('delete from  pl_position_price where pl_position_id="'.$id.'"');
		  
		  $ns= new NonSet('delete from  pl_position where pl_position_id in(select id from catalog_position where parent_id="'.$id.'")');
		  
		  
		  AbstractItem::Del($id);
		}
	}	
	
	
	
	
	//�������� ����������� ��������
	public function CanDelete($id){
		$can_delete=true;
		
		$set=new mysqlSet('select count(*) from kp_position as p inner join kp as b on b.id=p.kp_id where p.position_id="'.$id.'" and (b.is_confirmed_price=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		
		$set=new mysqlSet('select count(*) from bill_position as p inner join bill as b on b.id=p.bill_id where p.pl_position_id="'.$id.'" and (b.is_confirmed_price=1 or b.is_confirmed_shipping=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		
		$set=new mysqlSet('select count(*) from sh_i_position as p inner join sh_i as b on b.id=p.sh_i_id where p.pl_position_id="'.$id.'" and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0) $can_delete=$can_delete&&false;
		
		$set=new mysqlSet('select count(*) from acceptance_position as p inner join acceptance as b on b.id=p.acceptance_id where p.pl_position_id="'.$id.'"  and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		
		return $can_delete;
	}
	//
	
	
	
		//�������� �� ���� � ���� ���������
	public function GetItemById($id,$mode=0){
		if($id===NULL) return false;
		
		
		$this->item=AbstractItem::GetItemById($id,$mode);
		
		//������������� ���������� ���� �� ���-��
		if($this->item!==false){
			$arr=array();
			foreach($this->item as $k=>$v){
				if(is_numeric($k)) continue;
				$arr[$k]=$v;	
			}
			
			//�������� ���� �� ������������
			
			$_pos=new PosItem;
			
			$pos=$_pos->GetItemById($this->item['position_id'],$mode);
			//print_r($pos);
			
			
			if($pos!==false){
				foreach($pos as $kk=>$vv){
					if(is_numeric($k)) continue;
					if(($kk=='id')) continue;
					
					//echo "$kk=$vv ";
					$arr[$kk]=$vv;	
				}
			}
			
			//�������� ����������� �� ������?
			
			
			$this->item=$arr;
			
		}
		
		return $this->item;
		
		
	}
	
	//��������� ������� ����� �� ������ �����
	public function GetItemByFields($params){
		
		$this->item=AbstractItem::GetItemByFields($params);
		
		//������������� ���������� ���� �� ���-��
		if($this->item!==false){
			$arr=array();
			foreach($this->item as $k=>$v){
				if(is_numeric($k)) continue;
				$arr[$k]=$v;	
			}
			
			//�������� ���� �� ������������
			
			$_pos=new PosItem;
			
			$pos=$_pos->GetItemById($this->item['position_id'],$mode);
			//print_r($pos);
			
			
			if($pos!==false){
				foreach($pos as $kk=>$vv){
					if(is_numeric($k)) continue;
					if(($kk=='id')) continue;
					
					//echo "$kk=$vv ";
					$arr[$kk]=$vv;	
				}
			}
			
			$this->item=$arr;
			
		}
		
		return $this->item;
		
	}
	
	
	//��������� �������� �������� ���� ����-�� � �����
	public function DispatchCalcPriceF($id, $currency_id=CURRENCY_DEFAULT_ID, $price=NULL, $item=NULL, $discount_id=NULL, $discount_value=NULL, $discount_rub_or_percent=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $extra_charges=0){
		if($item===NULL) $item=$this->GetItemById($id);
	 
		
		if($item['parent_id']==0) {
			//echo '����<br>';
			return $this->CalcPriceF($id, $currency_id, $price, $item, $discount_id, $discount_value, $discount_rub_or_percent, $price_kind_id, $price_kind,$extra_charges);
		}else{
			 //echo 'option<br>';
			 return $this-> CalcOptionPriceF($id, $currency_id, $price, $item, $discount_id, $discount_value, $discount_rub_or_percent, $price_kind_id, $price_kind,$extra_charges);
		}
	
	}
	
	
	//������� �������� ���� ������������, ��� ����� ����� ������ �����.
	function CalcPriceF($id, $currency_id=CURRENCY_DEFAULT_ID, $price=NULL, $item=NULL, $discount_id=NULL, $discount_value=NULL, $discount_rub_or_percent=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL,$extra_charges=0){
		
	
		
		if($item===NULL) $item=$this->GetItemById($id);
		
			
		$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		 
		 
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		if($price_kind['is_calc_price']==0){
			
			
			$_pi=new PlPositionPriceItem;
			$pi=$_pi->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
			
			
			
			
			$price=$pi['price'];
			//var_dump(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
			
			
			
			$price=$price - $price*$item['discount_base']/100;
			$price=$price  - $price*$item['discount_add']/100;
			
			
		}else{
			//���������� ������� ��� ����������� ����	
			
			//����� ������� ����, �� ��� ����� ���������
			$_pi=new PlPositionPriceItem;
			$pi=$_pi->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$base_pki['id']));
			$price=$pi['price'];
			
			$price=$price - $price*$item['discount_base']/100 ;
			$price=$price - $price*$item['discount_add']/100;
			
			
			//�������� ������ � ��. � ���� ddpm
			if($price_kind_id==1){
				
				//��������� ����� ������� ��� ����������
				$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
				
				if($calculation_form_id==1){
				
					$nds=$this->CalcNDS($price,  $id,  $currency_id,  $item,  3, NULL, false);
				
					$insur=$this->CalcInsur($price, $nds, $id, $currency_id,  $item, 3, NULL, false);	
					
					$comission=$this->CalcComission($price, $nds, $id, $currency_id,  $item, 3, NULL, false);
					
					$price= $price + $item['delivery_ddpm'] + $nds + $item['duty_ddpm'] + $item['svh_broker'] + $insur + $comission;
				}elseif($calculation_form_id==2){
					
					$customs_value=$this->CalcCustomsValue($price, $id,$currency_id, $item, 3, NULL, false, $rates);
					
					
					$delivery_rub=$this->CalcDeliveryRub($id, $currency_id, $item, 3, NULL, $rates, false);
					
					$nds=$this->CalcNDS($price,  $id,  $currency_id,  $item,  3, NULL, false);
				
					$insur=$this->CalcInsur($price, $nds, $id, $currency_id,  $item, 3, NULL, false);	
					
					//$comission=$this->CalcComission($price, $nds, $id, $currency_id,  $item, 3, NULL, false);
					
					
					$price=$price+$item['delivery_value']+$delivery_rub+ $nds + $item['duty_ddpm'] + $item['svh_broker'] + $insur /*+ $comission*/ +$customs_value+$item['broker_costs'];
				}
			}
			
			
			
			//� ������� ���� ��������� ������ ��������������, ��� ����� ��������� ����
			if($price_kind_id==1) $fieldname='profit_ddpm';
			elseif($price_kind_id==2) $fieldname='profit_exw';
			else $fieldname='profit_ddpm';
			
			$price=$price+ $price*$item[$fieldname]/100;
			
			//� ��������� ���� ��������� �������������� �������
			$price+=$extra_charges;
			
			
			//� ��������� ���� ��������� ������
			if($discount_id!==NULL){
			if($discount_rub_or_percent==0) $price-=(float)$discount_value;
				else $price-=$price*((float)$discount_value/100);
				
			}else{
				if($item['discount_rub_or_percent']==0) $price-=(float)$item['discount_value'];
				else $price-=$price*((float)$item['discount_value']/100);
			}
			
		}
		
		 
		//echo $price;
		 
		 return round($price, $digits);
		
			
	}
	
	
	
	
	function CalcOptionPriceF($id, $currency_id=CURRENCY_DEFAULT_ID, $price=NULL, $item=NULL, $discount_id=NULL, $discount_value=NULL, $discount_rub_or_percent=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL,$extra_charges){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		 
		 $_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		 
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		//���� ��� - �� ���� ����� �� ��������
		$_pki=new PriceKindItem; $_pi=new PlPositionPriceItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById( $price_kind_id);//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		/*if($item['is_install']==1){
			 $pr=$_pi->GetItemByFields(array('currency_id'=>$currency_id, 'price_kind_id'=>$base_pki['id'], 'pl_position_id'=>$id));
			$price=$pr['price'];
					 
		}else*/
		if($price_kind['is_calc_price']==0){
			
			$pi=$_pi->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
			$price=$pi['price'];
			
			//����� ����� ������������ ����-��, �� ���� ����� ������� ������
			$parent=$this->GetItemById($item['parent_id']);
			
			
			//var_dump(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
			
			$price=$price - $price*$parent['discount_base']/100;
			$price=$price - $price*$parent['discount_add']/100;
		}else{
			
			//����� ������� ����, �� ��� ����� ���������
			$_pi=new PlPositionPriceItem;
			$pi=$_pi->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$base_pki['id']));
			$price=$pi['price'];
			
			//echo 'base is: '.$price;
			
			//����� ����� ������������ ����-��, �� ���� ����� ������� ������
			$parent=$this->GetItemById($item['parent_id']);
			
			
			 
			$price=$price - $price*$parent['discount_base']/100 ;
			
			$price=$price - $price*$parent['discount_add']/100;
			
			//echo 'affected is: '.$price;
			
			
			//�������� ������ � ��. � ���� ddpm
			if($price_kind_id==1){
				
					//��������� ����� ������� ��� ����������
				$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
				
				if($calculation_form_id==1){
				
					$nds=$this->CalcOptionNDS($price,  $id,  $currency_id,  $item,  3, NULL, false);
				
					$insur=$this->CalcOptionInsur($price, $nds, $id, $currency_id,  $item, 3, NULL, false);	
					
					$comission=$this->CalcOptionComission($price, $nds, $id, $currency_id,  $item, 3, NULL, false);
					
					$price= $price +  $nds + $insur + $comission;	
				}else{
					$customs_value=$this->CalcOptionCustomsValue($price, $id, $currency_id, $item, 3, NULL, false);
					
					$nds=$this->CalcOptionNDS($price,  $id,  $currency_id,  $item,  3, NULL, false);
				
					$insur=$this->CalcOptionInsur($price, $nds, $id, $currency_id,  $item, 3, NULL, false);	
					
					//$comission=$this->CalcOptionComission($price, $nds, $id, $currency_id,  $item, 3, NULL, false);
					
					$price= $price +  $nds + $insur + /*$comission+*/$customs_value;	
				}
			}
			
			
			
			//� ������� ���� ��������� ������ ��������������, ��� ����� ��������� ����
			if($price_kind_id==1) $fieldname='profit_ddpm';
			elseif($price_kind_id==2) $fieldname='profit_exw';
			else $fieldname='profit_ddpm';
			
			//����� ���
			if($item['is_install']!=1) $price=$price+ $price*$parent[$fieldname]/100;
			/*if($item['is_install']==1) {
				die();
				echo $price;
			}*/
			
			//echo 'start is: '.$price;
			
			//� ��������� ���� ��������� �����. �������:
			$price+=$extra_charges;
			
			
			//� ��������� ���� ��������� ������... ���� �� �������� � ����� ��, �.�. ������ �� ��������� � ������� �.�.
			//�� ��������� � ����  ����� ������ ���������, ���� ����� - ��� ���:
			//($item['is_install']==1)&&($price_kind['is_calc_price']==1)&&($discount_id==1) - �� ���������!
			
			if(($item['is_install']==1)&&($price_kind['is_calc_price']==1)){
				//echo 'PNR!!!!!!!!!';
				//���� ������ ��������� - �� ���������
				
				//���� ������ ���-�� - �������� � ������ �������
				if($discount_id!==NULL){
				 
					if($discount_id==2){
						if($discount_rub_or_percent==0) $price-=(float)$discount_value;
						else $price-=$price*((float)$discount_value/100);
					}
				 
				}else{
					if($parent['discount_id']==2){ 
						if($item['discount_rub_or_percent']==0) $price-=(float)$item['discount_value'];
						else $price-=$price*((float)$item['discount_value']/100);
					}
				}	
				
				
				
			}else{
				//Not PNR
				//echo 'NOT PNR!!!!!!!!!';
				//�������� � ������� ������
				if($discount_id!==NULL){
				 
					
					if($discount_rub_or_percent==0) $price-=(float)$discount_value;
					else $price-=$price*((float)$discount_value/100);
				 
				}else{
					 
					if($parent['discount_rub_or_percent']==0) $price-=(float)$parent['discount_value'];
					else $price-=$price*((float)$parent['discount_value']/100);
					 
				}	
			}
			
			
			 
		
		}
		
		
		
		//return round($price, -1);
		 return round($price, $digits);
		
			
	}
	
	
	//������� �������
	function CalcCustomsValue($price_f, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true, $rates=NULL){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		if($rates===NULL) $rates=$this->_cs->GetActual();
		
		 
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		if($price_kind['is_calc_price']==0){
			
			$price=$price_f;  
			
			//$delivery_rub=$this->CalcDeliveryRub($id, $currency_id, $item, 3, NULL, $rates, false);
			$price=($price_f+$this->CalcInsur($price_f,0,$id,$currency_id,$item,NULL,3,false,$rates)+$item['delivery_value'])*($item['customs']/100);
			 
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round){
		  return round($price,0);	
		 
		}else return $price;
		
			
	}
	
	
	//������� ������� ��� �����
	function CalcOptionCustomsValue($price_f, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		
		
		 
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		/*if($item['is_install']==1){
			$price=0;
		}else*/
		if($price_kind['is_calc_price']==0){
			 
			//����� ����� ������������ ����-��, �� ���� ����� ������� ������
			$parent=$this->GetItemById($item['parent_id']);
			
			
			//var_dump(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
			
			
			 
			$price=$price_f; 
			
			$price=($price_f+$this->CalcOptionInsur($price_f,0, $id, $currency_id, $item,NULL, 3,false,$rates))*($parent['customs']/100); 
			 
			//$price=($price_f)*0.18;
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round)
		   return round($price,0);	
		else return $price;
		
			
	}
	
	
	
	
	//������� ��� ��� ������������
	// ��� ����� ����� ������ �����.
	function CalcNDS($price_f, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true, $rates=NULL){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		if($rates===NULL) $rates=$this->_cs->GetActual();
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
		
		if($price_kind['is_calc_price']==0){
			
			$price=$price_f;  
			
			if($calculation_form_id==1) {
			
				$delivery_rub=$this->CalcDeliveryRub($id, $currency_id, $item, 3, NULL, $rates, false);
			 
				$price=($price_f/*+0.7*($item['delivery_ddpm']+$item['delivery_value']+$delivery_rub)*/)*0.18;
			}else{
			
				$price=($price_f+$this->CalcInsur($price_f,0,$id,$currency_id,$item,3,NULL,false,$rates)+ $this->CalcCustomsValue($price_f,$id,$currency_id,$item,3,NULL,false,$rates)+$item['delivery_value'])*0.18;
				
//				echo "price is:".$price_f." insur is:".$this->CalcInsur($price_f,0,$id,$currency_id,$item,3,NULL,false,$rates)." customs:". $this->CalcCustomsValue($price_f,$id,$currency_id,$item,3,NULL,false,$rates)."<br>";
				
			}
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round){
		  return round($price,0);	
		 
		}else return $price;
		
			
	}
	
	//������� ��� ��� �����
	function CalcOptionNDS($price_f, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
		
		/*if($item['is_install']==1){
			$price=0;
		}else*/
		if($price_kind['is_calc_price']==0){
			 
			//����� ����� ������������ ����-��, �� ���� ����� ������� ������
			//$parent=$this->GetItemById($item['parent_id']);
			
			
			//var_dump(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$price_kind_id));
			
			
			 
			$price=$price_f;  
			 
			if($calculation_form_id==1) {
				$price=($price_f)*0.18;
			}else{
				$price=($price_f+$this->CalcOptionInsur($price_f,0,$id,$currency_id,$item,3,NULL,false,$rates)+ $this->CalcOptionCustomsValue($price_f,$id,$currency_id,$item,3,NULL,false,$rates)+$item['delivery_value'])*0.18;	
			}
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round)
		   return round($price,0);	
		else return $price;
		
			
	}
	
	
	//������� ��������� ��� ������������
	// ��� ����� ����� ������ �����.
	function CalcInsur($price_f, $nds, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true, $rates=NULL){
		if($item===NULL) $item=$this->GetItemById($id);
		if($rates===NULL) $rates=$this->_cs->GetActual();
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
		
		if($price_kind['is_calc_price']==0){
			 
			if($calculation_form_id==1){
				$delivery_rub=$this->CalcDeliveryRub($id, $currency_id, $item, 3, NULL, $rates, false); 
			 
				$price=$price_f+$nds+$item['delivery_ddpm']+$item['delivery_value']+$delivery_rub+$item['duty_ddpm']+$item['svh_broker'];  
			
			
			
			 	$price=$price*0.002;
			}else{
				 $price=$price_f*1.1;	
				 $price=$price*0.001;
				 
			}
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round)
		 return round($price, 0);
		 
		else return $price;	
	}
	
	//������� ��������� ���   ����� 
	function CalcOptionInsur($price_f, $nds, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true){
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
		
		
		/*if($item['is_install']==1){
			$price=0;
		}else*/
		if($price_kind['is_calc_price']==0){
			
			//����� ����� ������������ ����-��, �� ���� ����� ������� ������
			//$parent=$this->GetItemById($item['parent_id']);
			
			if($calculation_form_id==1){
			 
				$price=$price_f+$nds;  
			
			 	$price=$price*0.002;
			}else{
				 $price=$price_f*1.1; 
				 $price=$price*0.001;
			}
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round)
		 return round($price, 0);
		 		
		else return $price;		
	}
	
	
	
	//�������� ��� ������-��
	function CalcComission($price_f, $nds, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true, $rates){
		if($item===NULL) $item=$this->GetItemById($id);
		if($rates===NULL) $rates=$this->_cs->GetActual();
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		if($price_kind['is_calc_price']==0){
			
			
			$delivery_rub=$this->CalcDeliveryRub($id, $currency_id, $item, 3, NULL, $rates, false);
			 
			$price=$price_f+$nds+$item['delivery_ddpm']+$item['delivery_value']+$delivery_rub+$item['duty_ddpm']+$item['svh_broker'];  
			
			$price=$price*0.01;
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round) 
		 return round($price, 0);
		 	
		else return $price;		
	}
	
	//�������� ��� �����
	function CalcOptionComission($price_f, $nds, $id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $do_round=true){
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		//���� $price_kind - ���������, �� ����� �������, � �� ������� �������� ���������
		//���� $price_kind - ������� - �� �������� ����� ����� ������� - ����� ������� ������
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );//$item['price_kind_id']);
		
		if($price_kind['is_calc_price']==1){
			//��� ����� ������� ��� ���?????
			$base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		}else{
			$base_pki=$price_kind;	
		}
		
		$price=0;
		
		/*if($item['is_install']==1){
			$price=0;
		}else*/
		if($price_kind['is_calc_price']==0){
			 
			$price=$price_f+$nds;  
			
			$price=$price*0.01;
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round)
		 return round($price, 0);
		 		
		else return $price;		
	}
	
	
	
	//������� �������� �������� � �������� ������
	// ��� ����� ����� ������ �����.
	function CalcDeliveryRub($id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL, $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $rates=NULL, $do_round=true){
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id );
		
		
		if($rates===NULL) $rates=$this->_cs->GetActual();
		
		
		
		$price=0;
		
		if($price_kind['is_calc_price']==0){
			 
			$price=$price+ CurrencySolver::Convert( $item['delivery_rub'], $rates, 1, $currency_id);  
			
			 
		}else{
			//���������� ������� ��� ����������� ����	
			
			
			
		}
		
		 
		
		if($do_round)
		 return round($price, 0);
		 
		else return $price;	
	}
	
	
	
	
	//��������� �������� ���� ������������ � ����� ExW (DDPM)
	public function DispatchCalcPrice($id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL,  $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $base_pki=NULL, $pi=NULL,$extra_charges=0){
		if($item===NULL) $item=$this->GetItemById($id);
		if($item['parent_id']==0) return $this->CalcPrice($id, $currency_id,  $item,  $price_kind_id, $price_kind, $base_pki=NULL, $pi=NULL,$extra_charges);
		else return $this->CalcOptionPrice($id, $currency_id,  $item,  $price_kind_id, $price_kind, $base_pki=NULL, $pi=NULL,$extra_charges);
	}
	
	
	////������� ���� ������������ ExW (DDPM), ��� ����� ����� ������ �����.
	function CalcPrice($id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL,  $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $base_pki=NULL, $pi=NULL,$extra_charges=0){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id);
		
		$price=0;
		
		//����� ������� ����, ������ �� ��� ������� ������.
		if($base_pki===NULL) $base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0,'group_id'=>$price_kind['group_id']));
		
		//var_dump($item['price_kind_id']);
		
	    //������� ����
		$_pi=new PlPositionPriceItem;
		if($pi===NULL) $pi=$_pi->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$base_pki['id']));
		$price=$pi['price'];
		
		$price=$price- $price*$item['discount_base']/100;
		
		$price=$price- $price*$item['discount_add']/100;
		
		
		
		
		//���� ����= ddpm - ����� ��� ������ � ������� � ��������� ��
		if($price_kind_id==1){
			//��������� ����� ������� ��� ����������
			$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
			
			if($calculation_form_id==1){
			
				$nds=$this->CalcNDS($price,  $id, $currency_id, $item,   3,   NULL, false);
				
				$insur=$this->CalcInsur($price, $nds, $id, $currency_id, $item, 3, NULL, false);	
				
				$comission=$this->CalcComission($price, $nds, $id, $currency_id, $item, 3, NULL, false);
				
				$price= $price + $item['delivery_ddpm'] + $nds + $item['duty_ddpm'] + $item['svh_broker'] + $insur + $comission;
			}elseif($calculation_form_id==2){
					
					$delivery_rub=$this->CalcDeliveryRub($id, $currency_id, $item, 3, NULL, $rates, false);
					
					//echo $delivery_rub;
					
					$customs_value=$this->CalcCustomsValue($price, $id, $currency_id, $item,3,NULL,false, $rates);
					
					$nds=$this->CalcNDS($price,  $id, $currency_id, $item,   3,   NULL, false);
				
					$insur=$this->CalcInsur($price, $nds, $id, $currency_id, $item, 3, NULL, false);	
					
					//$comission=$this->CalcComission($price, $nds, $id, $currency_id, $item, 3, NULL, false);
					
					$price=$price+$item['delivery_value']+$delivery_rub + $nds + $item['duty_ddpm'] + $item['svh_broker'] + $insur +/* $comission+ */$customs_value+$item['broker_costs'];
			}
			
			
		}
		
		
		
		
		//��������� � ��� ��������������
		if($price_kind_id==1) $fieldname='profit_ddpm';
		elseif($price_kind_id==2) $fieldname='profit_exw';
		else $fieldname='profit_ddpm';
		
		$price=$price+ $price*$item[$fieldname]/100;
		
		
		//�������� ���. �������
		$price+=$extra_charges; 
		
		
		//echo 'the base';
		//return round($price, -1);
		  
		return round($price, $digits);
		 
		
	}
	
	
	////������� ���� ������������ ExW (DDPM)  ��� �����  
	function CalcOptionPrice($id, $currency_id=CURRENCY_DEFAULT_ID,  $item=NULL,  $price_kind_id=PRICE_KIND_DEFAULT_ID, $price_kind=NULL, $base_pki=NULL, $pi=NULL,$extra_charges=0){
		if($item===NULL) $item=$this->GetItemById($id);
		
		$digits=$this->_round_define->DefineDigits($item['group_id']);
		
		$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		$_pki=new PriceKindItem;
		if($price_kind===NULL) $price_kind=$_pki->GetItemById($price_kind_id); //$item['price_kind_id']);
		
		$price=0;
		
		//����� ����� ������������ ����-��, �� ���� ����� ������� ������
		$parent=$this->GetItemById($item['parent_id']);
			
			
		
		
		//����� ������� ����, ������ �� ��� ������� ������.
		if($base_pki===NULL) $base_pki=$_pki->GetItemByFields(array('is_calc_price'=>0, 'group_id'=>$price_kind['group_id']));
		
	 
		$_pi=new PlPositionPriceItem;
		if($pi===NULL) $pi=$_pi->GetItemByFields(array('pl_position_id'=>$id, 'currency_id'=>$currency_id, 'price_kind_id'=>$base_pki['id']));
		$price=$pi['price'];
		
		
		/*if($item['is_install']==1){
			 
		}else{
		*/	
			$price=$price-  $price*$parent['discount_base']/100 ;
			$price=$price-  $price*$parent['discount_add']/100;
			
			
			
			//���� ����= ddpm - ����� ��� ������ � ������� � ��������� ��
			if($price_kind_id==1){
				
				//��������� ����� ������� ��� ����������
				$calculation_form_id=$this->GetCalculationFormId($item['producer_id']);
				
				if($calculation_form_id==1){
					$nds=$this->CalcOptionNDS($price,  $id,  $currency_id,  $item,  3, NULL, false);
					
					$insur=$this->CalcOptionInsur($price, $nds, $id, $currency_id,  $item, 3, NULL, false);	
					
					$comission=$this->CalcOptionComission($price, $nds, $id, $currency_id,  $item, 3, NULL, false);
					
					$price= $price +  $nds + $insur + $comission;
				}elseif($calculation_form_id==2){
					
					$customs_value=$this->CalcOptionCustomsValue($price, $id, $currency_id, $item, 3, NULL,false);
					
					$nds=$this->CalcOptionNDS($price,  $id,  $currency_id,  $item,  3, NULL, false);
					
					$insur=$this->CalcOptionInsur($price, $nds, $id, $currency_id,  $item, 3, NULL, false);	
					
					//$comission=$this->CalcOptionComission($price, $nds, $id, $currency_id,  $item, 3, NULL, false);
					
					$price= $price +  $nds + $insur + /*$comission+*/ $customs_value;
				}
				
			//}
			
			
			
			
			//��������� � ��� ��������������
			if($price_kind_id==1) $fieldname='profit_ddpm';
			elseif($price_kind_id==2) $fieldname='profit_exw';
			else $fieldname='profit_ddpm';
			
			//����� ���
			if($item['is_install']!=1) $price=$price+ $price*$parent[$fieldname]/100;
			
			//�������� ���. �������
			$price+=$extra_charges; 
			 
		}
		
		//return round($price, -1);
		 return round($price, $digits);
		
	}
	
	
	 
	//�����, ������������ ���� ����� ������� ��������
	public function GetCalculationFormId($producer_id){
		$sql='select p.delivery_form_id from	
		pl_producer as p
		 
		where p.id="'.$producer_id.'"';
		
		 $set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			
		return $f[0];	
	}
}
?>