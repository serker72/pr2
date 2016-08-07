<?
require_once('sh_i_posgroup.php');
require_once('sh_i_group.php');
require_once('billitem.php');
require_once('maxformer.php');

// класс для редактирования позиций раcпоряжения на основе позиций счета по заданным объектам и участкам
class ShIPrepare extends BillPosGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i_position';
		$this->pagename='view.php';		
		$this->subkeyname='sh_i_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $storage_id, $sector_id){
		$arr=Array();
		
		$_bill=new BillItem;
		$bill=$_bill->GetItemById($id);
		$_bpf=new BillPosPMFormer;
		
		$_mf=new MaxFormer;
		$sql='select p.quantity, p.id as p_id, p.bill_id, p.komplekt_ved_pos_id, p.position_id as id, p.position_id as position_id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.price, p.price_pm, p.total,
					 pd.id as dimension_id, p.komplekt_ved_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 p.storage_id, ss.name as storage_name,
					 p.sector_id, sec.name as sector_name
		
		from bill_position as p 
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join storage as ss on p.storage_id=ss.id
			left join sector as sec on p.sector_id=sec.id
		where p.bill_id="'.$id.'" 
			and p.storage_id="'.$storage_id.'"
			and p.sector_id="'.$sector_id.'"
			
		order by position_name asc, id asc';
		
		//echo $sql;
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
			$f['has_pm']=(int)($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			
			$f['price_pm']=$pm['price_pm'];
			$f['cost']=$pm['cost'];
			$f['total']=$pm['total'];
			
			//обнулим незаполненный плюс/минус
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			
			//рассчитаем ндс
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			
			$f['max_quantity']=$_mf->MaxForBill($bill['komplekt_ved_id'], $f['id']); 
			$f['quantity_confirmed']=$_mf->MaxInKomplekt($bill['komplekt_ved_id'], $f['id']);
			$f['in_rasp']=$_mf->MaxInShI($id, $f['id'],NULL,$storage_id,$sector_id);
			 
			
			if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
			else $f['komplekt_ved_name']='-';
			
			$f['hash']=md5($f['position_id'].'_'.$f['komplekt_ved_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//список позиций
	/*public function GetItemsByIdArr($id,$current_id=0,$do_find_max=false){
		//id - это айди заявки
		//отнесем его в общий массив
		
		$kompl_ids=array(); 
		$_kompl=new BillItem;
		$kompl=$_kompl->GetItemById($id);
		
		
		//print_r($kompl_ids);
		
		
		$mf=new MaxFormer;
		
		$arr=array();
		$set=new MysqlSet('select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id, ss.name as storage_name, kp.sector_id
		from '.$this->tablename.' as p 
		inner join catalog_position as pos on p.position_id=pos.id 
		left join catalog_dimension as dim on pos.dimension_id=dim.id 
		left join storage as ss on p.storage_id=ss.id
		left join komplekt_ved as kp on kp.id=p.komplekt_ved_id
		where '.$this->subkeyname.'="'.$id.'" order by position_name asc, id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$in_free=$mf->InFree($id,$f['position_id']);
			
			if($do_find_max){
				$f['in_bills']=$mf->InBills($id,$f['position_id'])-$mf->InSh($id,$f['position_id']);
				$f['in_sh']=$mf->InSh($id,$f['position_id'])-$mf->InAcc($id,$f['position_id']);
				$f['in_free']=$in_free;
				$f['in_pol']=$mf->InAcc($id,$f['position_id']);
			}else{
				$f['in_bills']='-';
				$f['in_sh']='-';
				$f['in_free']='-';
				$f['in_pol']='-';
			}
			
			$arr[]=$f;
			
			
			
		}
		
		return $arr;
	}*/
	
	
	
	
}
?>