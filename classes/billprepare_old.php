<?
require_once('komplposgroup.php');
require_once('komplgroup.php');
require_once('komplitem.php');
require_once('maxformer.php');

// класс для редактирования позиций счета на основе позиций заявки + доступных заявок
class BillPrepare extends KomplPosGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_pos';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsByIdArr($id,$current_id=0,$do_find_max=false){
		//id - это айди заявки
		//отнесем его в общий массив
		
		$kompl_ids=array(); 
		$_kompl=new KomplItem;
		$kompl=$_kompl->GetItemById($id);
		
		//найти все АКТИВНЫЕ заявки на сегодня
		$_komplgr=new KomplGroup;
		$kg=$_komplgr->ShowActiveArr($id, $kompl['org_id']);
		foreach($kg as $k=>$v){
			if($v['id']==$id) continue;
			$kompl_ids[]=$v['id'];
		}
		//
		//print_r($kompl_ids);
		
		//добавить к активным заявкам принудительно - все заявки по данному счету
		$set=new MysqlSet('select distinct komplekt_ved_id from bill_position where bill_id="'.$current_id.'"');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if(!in_array($f[0],$kompl_ids)) $kompl_ids[]=$f[0];
		}
		
		
		//подгрузим услуги - заявка не нужна
		
		
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
		
		
		$mf=new MaxFormer;
		
		$arr=array();
		$set=new MysqlSet('select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id, ss.name as storage_name, kp.sector_id, pos.group_id
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
			//$f['is_current']=(bool)($f['id']==$current_id);
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
			if(in_array($f['group_id'],$arg)) $f['is_usl']=1;
			else $f['is_usl']=0;
			
			//var_dump($f); почему не выводится quantity_confirmed????
			$arr[]=$f;
			
			//echo $in_free; echo '   '; echo count($kompl_ids); echo ' zzz ';
			
			if(($in_free>0)&&(count($kompl_ids)>0)){
				
			/*	echo '<br />'.'select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id, ss.name as storage_name, kp.sector_id
				from '.$this->tablename.' as p 
				inner join catalog_position as pos on p.position_id=pos.id 
				left join catalog_dimension as dim on pos.dimension_id=dim.id 
				left join storage as ss on p.storage_id=ss.id
				left join komplekt_ved as kp on kp.id=p.komplekt_ved_id
				where p.position_id="'.$f['position_id'].'" and p.'.$this->subkeyname.' in('.implode(', ',$kompl_ids).') order by position_name asc, id asc'.'<br>';*/
				
				//	поискать позицию в других заявках. список заявок нам известен
				$set1=new MysqlSet('select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id, ss.name as storage_name, kp.sector_id, pos.group_id
				from '.$this->tablename.' as p 
				inner join catalog_position as pos on p.position_id=pos.id 
				left join catalog_dimension as dim on pos.dimension_id=dim.id 
				left join storage as ss on p.storage_id=ss.id
				left join komplekt_ved as kp on kp.id=p.komplekt_ved_id
				where p.position_id="'.$f['position_id'].'" and '.$this->subkeyname.' in('.implode(', ',$kompl_ids).') order by position_name asc, id asc');
				
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				for($j=0; $j<$rc1; $j++){
				  $g=mysqli_fetch_array($rs1);
				  foreach($g as $k1=>$v1) $g[$k1]=stripslashes($v1);
				  
				  //echo $rc1;
				  
				  if($current_id==0) $_c_id=NULL;
				  else $_c_id=$current_id;
				  
				  //echo 'try';
				  $in_free1=$mf->InFree($g['komplekt_ved_id'],$g['position_id'],$_c_id);
					
					
				   //echo $current_id.' '.$g['komplekt_ved_id'].' '.$g['position_name'].' '.$in_free1.'<br>';
				  	
				  if($do_find_max){
					  $g['in_bills']=$mf->InBills($g['komplekt_ved_id'],$g['position_id'])-$mf->InSh($g['komplekt_ved_id'],$g['position_id']);
					  $g['in_sh']=$mf->InSh($g['komplekt_ved_id'],$g['position_id'])-$mf->InAcc($g['komplekt_ved_id'],$g['position_id']);
					  $g['in_free']=$in_free;
					  $g['in_pol']=$mf->InAcc($g['komplekt_ved_id'],$g['position_id']);
				  }else{
					  $g['in_bills']='-';
					  $g['in_sh']='-';
					  $g['in_free']='-';
					  $g['in_pol']='-';
				  }
				  if(in_array($g['group_id'],$arg)) $g['is_usl']=1;
				  else $g['is_usl']=0;
				 
				  if($in_free1>0) $arr[]=$g;
				}
						
			}
			
		}
		
		//составим список "в заявке" для исключения...
		$pos_ids=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['position_id'],$pos_ids)) $pos_ids[]=$v['position_id'];	
		}
		
		//подгрузим услуги, доставка не нужна
		if(count($arg)>0){
		  $some_sql='select pos.id as position_id, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id, ss.id as storage_id, ss.name as storage_name, kp.sector_id, pos.group_id
		  from  catalog_position as pos
		  left join catalog_dimension as dim on pos.dimension_id=dim.id 
		  left join storage as ss on '.$kompl['storage_id'].'=ss.id
		  left join komplekt_ved as kp on kp.id='.$id.'
		  where pos.group_id in ('.implode(', ',$arg).')';
		  
		  if(count($pos_ids)>0) $some_sql.=' and pos.id not in('.implode(', ',$pos_ids).')';
		  $some_sql.=' order by position_name asc, position_id asc';
		  
		  
		  //echo $some_sql;
		  $set=new MysqlSet($some_sql);
		  
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  //$f['is_current']=(bool)($f['id']==$current_id);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  $f['komplekt_ved_id']=0;
			  
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
			  
			  $f['is_usl']=1;
			  
			  $arr[]=$f;
			  
		  }
		}
		
		return $arr;
	}
	
	
	
	
}
?>