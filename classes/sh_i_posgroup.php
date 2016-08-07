<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('maxformer.php');

require_once('billitem.php');
require_once('sh_i_item.php');
require_once('posgroupgroup.php');

require_once('pl_dismaxvalgroup.php');


// абстрактная группа
class ShIPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i_position';
		$this->pagename='view.php';		
		$this->subkeyname='sh_i_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $dec2=NULL, $for_an_onway=false, $sh=NULL){
		$arr=array();
		
		$db_flt='';
		
		if($dec2!==NULL){
		  $db_flt=$dec2->GenFltSql(' and ');
		  if(strlen($db_flt)>0){
			  $db_flt=' and '.$db_flt;
		  //	$sql_count.=' and '.$db_flt;	
		  }
		}
		
		
		$_bpf=new BillPosPMFormer;
		$_pdm=new PlDisMaxValGroup;
		
			
		$sql='select p.id as p_id, p.sh_i_id,  p.position_id as id, p.position_id as position_id,
					p.pl_position_id as pl_position_id,
					 p.pl_discount_id, p.pl_discount_value, p.pl_discount_rub_or_percent,
					 d.name,
					  p.kp_id as kp_id, kp.code as kp_code,
		
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_f, p.price_pm, p.total, 
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 cg.group_id			 
		
		from '.$this->tablename.' as p 
			left join sh_i_position_pm as pm on pm.sh_i_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join pl_discount as d on p.pl_discount_id=d.id
			left join catalog_position as cg on cg.id=p.position_id
			left join kp as kp on kp.id=p.kp_id
		where p.'.$this->subkeyname.'="'.$id.'" '.$db_flt.' order by position_name asc, id asc';
		
		
		
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_mf=new MaxFormer;
		$_sh=new ShIItem;
		if( $sh===NULL) $sh=$_sh->GetItemById($id);
		
		
		
		$_pgg=new PosGroupGroup;
		$arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		$arg=array();
		$arg[]=SERVICE_CODE;
		foreach($arc as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
			$f['has_pm']=($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			
			$f['price_pm']=$pm['price_pm'];
			$f['cost']=round($f['price_f']*$f['quantity'],2);
			$f['total']=$pm['total'];
			
			//обнулим незаполненный плюс/минус
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			
			if($f['discount_rub_or_percent']=="") $f['discount_rub_or_percent']=0;
			if($f['discount_value']=="") $f['discount_value']=0;
			
			
			
			$f['in_acc']=round($_mf->MaxInAcc($sh['bill_id'], $f['position_id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'],0, $id,NULL,$f['kp_id']), 3);
			
			//round($_mf->MaxInAcc($sh['bill_id'],$f['position_id'],0,$id,NULL,NULL,$f['komplekt_ved_id']),3);
			
			
			if($for_an_onway){
				if(((float)$f['quantity']-(float)$f['in_acc'])<=0){	
					continue;
				}
			}
			
			$f['in_bill']=round($_mf->MaxInBill($sh['bill_id'], $f['position_id'],  $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'],  $f['pl_discount_rub_or_percent'],NULL,$f['kp_id']),3);
			
			$f['not_in_bill']=round($f['in_bill']- $_mf->MaxInShI($sh['bill_id'], $f['position_id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'], NULL,NULL,$f['kp_id']),3);
			
			
			
			
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			
			
			//также получить набор макс. скидок для позиции
			$max_vals=array();
			$max_vals=$_pdm->GetItemsByIdArr($f['pl_position_id']);
			$f['discs1']=$max_vals;
			
			$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent'].'_'.$f['kp_id']);
			
			
			if(in_array($f['group_id'],$arg)) $f['is_usl']=1;
			else $f['is_usl']=0;
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>