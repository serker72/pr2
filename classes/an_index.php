<?
 
require_once('orgitem.php');
require_once('opfitem.php');

require_once('array_sorter.php');
 
//отчеты для главной страницы
class AnIndex{

	public function ShowDataKP($pdate1, $pdate2, $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Число док-тов'){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		
		$sql='select count(*) from kp
		
		as p
			inner join supplier as sp on sp.id=p.supplier_id
			
			inner join user as u on u.id=p.user_manager_id
			inner join kp_position as pp on pp.kp_id=p.id
			
			inner join catalog_position as cat on pp.position_id=cat.id and cat.parent_id=0
			inner join pl_producer as prod on prod.id=cat.producer_id
			
		
		 where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" and (p.pdate between "'.$pdate1.'" and "'.$pdate2.'")';
	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		
		//$sm->assign('total', $total);
	 
		/*echo date('d.m.Y', $pdate1);
		echo date('d.m.Y', $pdate2);
		 
		echo $sql;*/
		
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='sp.full_name';
				$field_value_name='sp.full_name';
			break;
			case 1:
				$field_name='cat.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='cat.id';
				$field_value_name='cat.name';
			break;
			
			case 3:
				$field_name='p.user_manager_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		
		$sql='select distinct '.$field_value_name.' from
			kp as p
			inner join supplier as sp on sp.id=p.supplier_id
			
			inner join user as u on u.id=p.user_manager_id
			inner join kp_position as pp on pp.kp_id=p.id
			
			inner join catalog_position as cat on pp.position_id=cat.id and cat.parent_id=0
			inner join pl_producer as prod on prod.id=cat.producer_id
			
		where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$alls['total']=$total;
		
		$_si=new abstractitem;
		$_si->SetTableName('supplier');
		$_opf=new OpfItem;
		
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select count(distinct p.id)  from 
			kp as p
			inner join supplier as sp on sp.id=p.supplier_id
			
			inner join user as u on u.id=p.user_manager_id
			inner join kp_position as pp on pp.kp_id=p.id
			
			inner join catalog_position as cat on pp.position_id=cat.id and cat.parent_id=0
			inner join pl_producer as prod on prod.id=cat.producer_id
			
		where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
				
			
			$name=$f[0];
			if($divide==0){
				$si=$_si->GetItemByFields(array('full_name'=>$f[0]));
				//var_dump($si);
				$opf=$_opf->GetItemById($si['opf_id']);
				 
				$name.=', '.$opf['name'];	
			}
			
			$items[]=array(
				'name'=>$name,
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	
		}
		
		
		$items=ArraySorter::SortArr($items, 'value',1);
		
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		 
		$sm->assign('title', $title);	
		return $sm->fetch($template);
	}
	
	
	
	
	public function ShowDataDog($pdate11,  $pdate12, $period,  $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Число док-тов'){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		
		
		 // and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"'
		
		
		
		
		
		//$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		$sql='select count(*) from   
		 plan_fact_fact as p
			 
			inner join supplier as sp on sp.id=p.supplier_id
			 
			inner join user as u on u.id=p.user_id
			  
			inner join pl_producer as prod on prod.id=p.producer_id
			
		
		where p.is_confirmed=1 and p.org_id="'.$org_id.'" and (p.month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and p.year="'.date('Y', $pdate12).'"';
	
	//echo $sql.'<br>';
	
	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		$alls['total']=$total;
		
		  
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='sp.full_name';
				$field_value_name='sp.full_name';
			break;
			case 1:
				$field_name='p.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='p.eq_name';
				$field_value_name='p.eq_name';
			break;
			
			case 3:
				$field_name='p.user_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		 
		$sql='select distinct '.$field_value_name.' from
			 plan_fact_fact as p
			 
			inner join supplier as sp on sp.id=p.supplier_id
			 
			inner join user as u on u.id=p.user_id
			  
			inner join pl_producer as prod on prod.id=p.producer_id
			
		where p.is_confirmed=1 and p.org_id="'.$org_id.'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $pdate12).'"
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_si=new abstractitem;
		$_si->SetTableName('supplier');
		$_opf=new OpfItem;
		
		
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select count(distinct p.id)  from 
			 plan_fact_fact as p
			 
			 inner join supplier as sp on sp.id=p.supplier_id
			inner join user as u on u.id=p.user_id
			  
			inner join pl_producer as prod on prod.id=p.producer_id
			
		where p.is_confirmed=1 and p.org_id="'.$org_id.'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $pdate12).'" and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
				
			
			$name=$f[0];
			if($divide==0){
				$si=$_si->GetItemByFields(array('full_name'=>$f[0]));
				//var_dump($si);
				$opf=$_opf->GetItemById($si['opf_id']);
				 
				$name.=', '.$opf['name'];	
			}
			
			$items[]=array(
				'name'=>$name,
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	 
		}
		
		$items=ArraySorter::SortArr($items, 'value',1);
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		$sm->assign('title',$title);
		 
			
		return $sm->fetch($template);
	}
	
	
}
?>