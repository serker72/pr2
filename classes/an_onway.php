<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');

require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');

require_once('sh_i_in_item.php');


//отчет товары в пути

class AnOnway{
	
	public function ShowData($pdate_shipping_plan1, $pdate_shipping_plan2, $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$can_print=false,$do_show_data=true, DBDecorator $dec2){
		
			
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
		$_ai=new ShIInItem;
		
	
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$mode_flt='';
		
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		
		
		$sql='select p.*,
					
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					b.supplier_id, sp.full_name as supplier_name, sp.code, opf.name as supplier_opf
				from sh_i as p
					
					left join user as mn on p.manager_id=mn.id
					left join bill as b on p.bill_id=b.id
					left join supplier as sp on b.supplier_id=sp.id
					left join opf as opf on opf.id=sp.opf_id
					
				where p.org_id="'.$org_id.'" and (p.status_id=2 or p.status_id=7) and (p.pdate_shipping_plan between	"'.$pdate_shipping_plan1.'" and "'.$pdate_shipping_plan2.'") ';
		
		$sql.='  '.$storage_flt.' '.$sector_flt.' '.$db_flt.'  '.$ss_filter.' ';
		
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		if($do_show_data){
		
		//echo $sql;
		
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
	
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		    
			
			/*echo '<pre>';
			var_dump($f);
			echo '</pre>';
			
			*/
			
			
			if($f['pdate_shipping_plan']==0) $f['pdate_shipping_plan']='-';
			else $f['pdate_shipping_plan']=date("d.m.Y",$f['pdate_shipping_plan']);
			
			
			if($f['pdate']==0) $f['pdate']='-';
			else $f['pdate']=date("d.m.Y",$f['pdate']);
			
				
			//ввести фильтр по позициям...
			//добавить только те позиции, которые не завезли
			//если вдруг завезли все позиции - выкинуть заявку из списка
			$positions=$_ai->GetPositionsArr($f['id'], $dec2,true);
			
			$rep_positions=array();
			foreach($positions as $k=>$v){
				if(((float)$v['quantity']-(float)$v['in_acc'])>0){
					$rep_positions[]=$v;	
				}
			}
			
			if(count($rep_positions)==0) continue;
			
			$f['positions']=$rep_positions;
			
			
			
			/*echo '<pre>';
			var_dump($rep_positions);
			echo '</pre>';
		
			*/
			
			
			
			$alls[]=$f;
		}
		//var_dump($alls);		
		
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		
		$sortmode=0;
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		
		
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		
		
		$fields=$dec2->GetUris();
		foreach($fields as $k=>$v){
			
		
			if($v->GetName()=='dimension_id2') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group=$v->GetValue();
			if($v->GetName()=='three_group_id') $current_three_group=$v->GetValue();
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//единицы изм
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_dimension_id==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('dim',$acts);		
		
		
		//тов группы
		$as=new mysqlSet('select * from catalog_group where parent_group_id=0 order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		$gr_ids=array(); $gr_names=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_group_id==$f[0]); 
			$acts[]=$f;
			
			$gr_ids[]=$f['id'];
			$gr_names[]=$f['name'];
		}
		$sm->assign('group',$acts);
		
		
		
		//группы
		
		$sm->assign('group_ids',$gr_ids);
		$sm->assign('group_names',$gr_names);
		
		//подгруппы
		if($current_group_id>0){
			$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_group_id.'" order by name asc');
			$rs=$as->GetResult();
			$rc=$as->GetResultNumRows();
			$acts=array();
			$acts[]=array('name'=>'');
			$gr_ids=array(); $gr_names=array();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$f['is_current']=($current_two_group==$f[0]); 
				$acts[]=$f;
				
				$gr_ids[]=$f['id'];
				$gr_names[]=$f['name'];
			}
			$sm->assign('two_group',$acts);
			
			$sm->assign('two_group_ids',$gr_ids);
			$sm->assign('two_group_names',$gr_names);
			
			
			if($current_two_group>0){
				$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_two_group.'" order by name asc');
				$rs=$as->GetResult();
				$rc=$as->GetResultNumRows();
				$acts=array();
				$acts[]=array('name'=>'');
				$gr_ids=array(); $gr_names=array();
				
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					$f['is_current']=($current_three_group==$f[0]); 
					$acts[]=$f;
					
					$gr_ids[]=$f['id'];
					$gr_names[]=$f['name'];
				}
				$sm->assign('three_group',$acts);
				
				$sm->assign('three_group_ids',$gr_ids);
				$sm->assign('three_group_names',$gr_names);
			}
		}
		
		
		
		$sm->assign('period',date("d.m.Y H:i:s",$pdate_shipping_plan1).' - '.date("d.m.Y H:i:s",$pdate_shipping_plan2));
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		
		$sm->assign('do_it',$do_show_data);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
}
?>