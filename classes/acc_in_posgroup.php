<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('maxformer.php');
require_once('acc_item.php');
require_once('acc_in_item.php');
require_once('billpositem.php');

require_once('pl_dismaxvalgroup.php');


// абстрактная группа
class AccInPosGroup extends AbstractGroup {
	protected static $uslugi;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance_position';
		$this->pagename='view.php';		
		$this->subkeyname='acceptance_id';	
		$this->vis_name='is_shown';		
		
		if(self::$uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		  self::$uslugi/*$this->uslugi*/=array();
		  self::$uslugi/*$this->uslugi*/[]=SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$uslugi/*$this->uslugi*/)) self::$uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$uslugi/*$this->uslugi*/))  self::$uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0,$has_goods=true,$has_usl=true, $show_statiscits=true, $show_boundaries=true){
		$arr=array();
		
		
		
		$_bpf=new BillPosPMFormer;
		
		$_pdm=new PlDisMaxValGroup;
		
		
		$sql='select p.id as p_id, p.acceptance_id,  p.position_id as id,  p.position_id as position_id,
					p.pl_position_id as pl_position_id,
					 p.pl_discount_id, p.pl_discount_value, p.pl_discount_rub_or_percent,
					 p.out_bill_id,
					 d.name,
					 
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_f, p.price_pm, p.total,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,			 
					 cat.group_id,
					 ob.code as out_bill_code
		from '.$this->tablename.' as p 
			left join acceptance_position_pm as pm on pm.acceptance_position_id=p.id
			left join pl_discount as d on p.pl_discount_id=d.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join catalog_position as cat on cat.id=p.position_id
			left join bill as ob on ob.id=p.out_bill_id
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, p.out_bill_id asc, id asc';
		
		//echo $sql.'<br>';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_bpi=new BillPosItem;
		$_ac=new AccItem;
		$_mf=new MaxFormer;
		$ac=$_ac->GetItemById($id);
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//echo $f['p_id'].' ';
			
			//исключить позиции с услугами
			if(!$has_usl){
				if($this->IsUsl($f['group_id'])) continue;	
			}
			
			//исключить позиции с товарами
			if(!$has_goods){
				if(!$this->IsUsl($f['group_id'])) continue;		
			}
			
			
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
			$f['has_pm']=($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price_f'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			//$f['cost']=round($f['price_f']*$f['quantity'],2);
			$f['price_pm']=$pm['price_pm'];
			
			
			//получить из счета цену_пм как окр. до 10 знаков ст-ть/кол-во по счету
			$bpi=$_bpi->GetItemByFields(array('bill_id'=>$ac['bill_id'], 'position_id'=>$f['id'], 
			'pl_position_id'=>$ac['pl_position_id'], 
			'pl_discount_id'=>$ac['pl_discount_id'],
			'pl_discount_value'=>$ac['pl_discount_value'],
				
				'pl_discount_rub_or_percent'=>$f['pl_discount_rub_or_percent'],
				'out_bill_id'=>$f['out_bill_id']
				));	
			if($bpi!==false){
				//устраняем потерю копеек при округлении - берем цену из счета с макс. точностью до 10 знаков!
				$some_price_pm=round($bpi['total']/$bpi['quantity'],10);
				
				$f['price_pm_unf']=$some_price_pm;	
			}else{
				$f['price_pm_unf']=	$pm['price_pm'];
			}
			
			
			$f['cost']=$pm['cost'];
			$f['total']=$pm['total'];
			
			//обнулим незаполненный плюс/минус
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			
			if($show_statiscits){
			 //	echo ' zzzzZZZ ';
			  $f['in_bill']=round($_mf->MaxInBill($ac['bill_id'], $f['id'],  $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'],$f['out_bill_id']),3);
				  $f['in_rasp']=round($_mf->MaxInShI($ac['bill_id'], $f['id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'], $ac['sh_i_id'],$f['out_bill_id']),3);
				  $f['in_acc']=round($_mf->MaxInAcc($ac['bill_id'],  $f['position_id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'], $id, $ac['sh_i_id'],$f['out_bill_id']),3);
			}
			
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			
			
			
			//добавлены для контроля редактирования числа позиций поступления
			//если это поступление по распоряжению на отгрузку...
			if(($ac['inventory_id']==0)&&($ac['interstore_id']==0)){
				if($show_statiscits){
				  $f['max_quantity']=$_mf->MaxForAcc($ac['sh_i_id'], $f['id'], $id,   $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'],  $f['pl_discount_rub_or_percent'],  $f['out_bill_id']);
			  
				  //всего в соответствующей строке счета
				  $f['max_bill_quantity']=$_mf->MaxInBill($ac['bill_id'],$f['id'],   $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'],$f['out_bill_id']);
				  
				  //всего в соотв. строке заявки
				  //$f['max_komplekt_quantity']=$_mf->MaxInKomplekt($f['komplekt_ved_id'],$f['id']);
				}
			}elseif($ac['inventory_id']!=0){
				
				if($show_statiscits){
				//если это поступление по распоряжению на инвентаризацию
				
					$f['max_quantity']=$_mf->MaxForAcc($ac['sh_i_id'], $f['id'], $id,   $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'],$f['out_bill_id']);
			  
				  //всего в соответствующей строке счета
				  $f['max_bill_quantity']=$_mf->MaxInBill($ac['bill_id'],$f['id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'],$f['out_bill_id']);
				  
				  //всего в соотв. строке заявки
				//  $f['max_komplekt_quantity']=$f['max_quantity'];
				  
				}
			
			}/*elseif($ac['interstore_id']!=0){
				
				if($show_statiscits){
				//если это поступление по распоряжению на межсклад
					
				  $f['max_quantity']=$_mf->MaxForAccIs($ac['interstore_id'], $f['id'], $f['p_id'], $f['komplekt_ved_id']);
					
				  //всего в соответствующей строке счета
				  $f['max_bill_quantity']=$f['max_quantity']; 
				 
				  //всего в соотв. строке заявки
				  $f['max_komplekt_quantity']=$f['max_quantity'];
				  
				}
			
			}*/
		  
			 
			
			$f['is_usl']=(int)$this->IsUsl($f['group_id']);
			
			
			
			
			$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent'].'_'.$f['out_bill_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//принадлежит ли данная категория категории услуг
	protected function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
}
?>