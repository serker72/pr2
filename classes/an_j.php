<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('storagegroup.php');
require_once('sectorgroup.php');
require_once('opfitem.php');
require_once('bdetailsgroup.php');

require_once('wfitem.php');

class AnJ{

	public function ShowData($pdate1, $pdate2, $storage_id,$sector_id, $org_id, $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $limited_sector=NULL, $_extended_limited_sector_pairs=NULL, &$alls,$only_active_sectors=0,$only_active_storages=0){
		
		$pdate2+=24*60*60-1;
		
		$_bpm=new BillPosPMFormer;
	
		$_bd=new BDetailsGroup;
		
		$_au=new AuthUser;
		//$_res=$_au->Auth();
		$_ai=new WfItem;
		
		$sm=new SmartyAdm;
		
		
	   	$alls=array();
		$total=0;
		
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$mode_flt='';
		
		if($limited_sector!==NULL){
			$sector_flt=' and p.sender_sector_id in('.implode(', ',$limited_sector).')';
			
		}else{
			if(is_array($sector_id)&&(count($sector_id)>0)){
			$sector_flt=' and p.sender_sector_id in('.implode(', ',$sector_id).') ';	
		
		}
			
		}
		if(is_array($storage_id)&&(count($storage_id)>0)){
			$storage_flt=' and p.sender_storage_id in('.implode(', ',$storage_id).') ';	
				
		}
		
		
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		//фильтр секр. участка
		$ss_filter='';
		if(is_array($extended_limited_sector_pairs)){
			$_t_arr=array();
			foreach($extended_limited_sector_pairs as $k=>$v)	{
				$_t_arr[]=' (p.sender_sector_id="'.$v[0].'" and p.sender_storage_id="'.$v[1].'") ';
			}
			
			$ss_filter=implode(' or ',$_t_arr);
			unset($_t_arr);
		}
		if(strlen($ss_filter)>0){
			$ss_filter=' and ('.$ss_filter.')';
		}
		
		
		
		
		//найти все банки организации
		$sql='select p.*,
					sr.name as sender_storage_name,
					sc.name as sender_sector_name,
					rr.name as receiver_storage_name,
					rc.name as receiver_sector_name,
					u.name_s as confirmed_fill_wf_name, u.login as confirmed_fill_wf_login,
					us.name_s as confirmed_name, us.login as confirmed_login,  p.confirm_pdate as confirm_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					
					par.id as par_id,
					par.pdate as par_pdate,
					
					parent_u.name_s as parent_confirmed_wf_name, parent_u.login as parent_confirmed_wf_login,
					parent_us.name_s as parent_confirmed_name, parent_us.login as parent_confirmed_login,  
					
					parent_sr.name as parent_sender_storage_name,
					parent_sc.name as parent_sender_sector_name,
					parent_rr.name as parent_receiver_storage_name,
					parent_rc.name as parent_receiver_sector_name
					
					
				from interstore as p
					inner join interstore as par on par.id=p.interstore_id
					
					left join storage as sr on p.sender_storage_id=sr.id
					left join sector as sc on p.sender_sector_id=sc.id
					left join storage as rr on p.receiver_storage_id=rr.id
					left join sector as rc on p.receiver_sector_id=rc.id
					
					left join storage as parent_sr on par.sender_storage_id=parent_sr.id
					left join sector as parent_sc on par.sender_sector_id=parent_sc.id
					left join storage as parent_rr on par.receiver_storage_id=parent_rr.id
					left join sector as parent_rc on par.receiver_sector_id=parent_rc.id
				
					
					
					left join user as u on p.user_confirm_fill_wf_id=u.id
					left join user as us on p.user_confirm_id=us.id
					left join user as mn on p.manager_id=mn.id
					
					left join user as parent_u on par.user_confirm_wf_id=parent_u.id
					left join user as parent_us on par.user_confirm_id=parent_us.id
					
				where p.is_or_writeoff="1" and p.org_id="'.$org_id.'"
				and (p.pdate between "'.$pdate1.'" and "'.$pdate2.'")
				and p.is_j=1 
				'.$storage_flt.' '.$sector_flt.' '.$db_flt.'  '.$ss_filter.'
		';
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		if($do_it){
			//echo $sql;
		  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  for($i=0; $i<$rc; $i++){
			  
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			 if($f['pdate']==0) $f['pdate']='-';
			else $f['pdate']=date("d.m.Y",$f['pdate']);	
			  
			  $positions=$_ai->GetPositionsArr($f['id']);
			
			  $rep_positions=array();
			  foreach($positions as $k=>$v){
				  
					  $rep_positions[]=$v;	
				  
			  }
			  $f['positions']=$rep_positions;
			 
			  
			 $alls[]=$f;
		  }
		}
	   $sm->assign('items',$alls);
		
	//	print_r($alls);
	   
	   
	   
	   
	   //заполним шаблон полями
		$current_storage='';
		//$current_bank_id='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			

			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			//if($v->GetName()=='position_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
	   
	   
	//склады
		$_storages=new StorageGroup;
		$sgs=$_storages->GetItemsArr(0,$only_active_storages);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$storage_names_selected=array(); //massiv dlya pe4atnoy versii
		$storage_html='';
		foreach($sgs as $k=>$v){
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
			if(in_array($v['id'],$storage_id)) $storage_names_selected[]=$v['name'];
			
			$class='';
			if($v['is_active']==0) $class='class="inactive"';
		
		
		
			if(in_array($v['id'],$storage_id)) $storage_html.='<option value="'.$v['id'].'" selected="selected"  '.$class.'>'.$v['name'].'</option>';	
			else $storage_html.='<option value="'.$v['id'].'" '.$class.'>'.$v['name'].'</option>';	
		}
		$sm->assign('storage_ids',$sender_storage_ids);
		
		$sm->assign('storage_html',$storage_html);
		$sm->assign('storage_names',$sender_storage_names);
		$sm->assign('storage_names_selected',$storage_names_selected);
		
		
		$sm->assign('storage_id',$current_storage);
		
		//участки
		$_storages=new SectorGroup;
		$sgs=$_storages->GetItemsArr(0,$only_active_sectors);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$sector_names_selected=array();
		$sector_html='';
		foreach($sgs as $k=>$v){
			if(($limited_sector!==NULL)&&(!in_array($v['id'],$limited_sector))) continue;
			
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
			if(in_array($v['id'],$sector_id)) $sector_names_selected[]=$v['name'];
			
			
			$class='';
			if($v['is_active']==0) $class='class="inactive"';
		
		
		
			if(in_array($v['id'],$sector_id)) $sector_html.='<option value="'.$v['id'].'" selected="selected"  '.$class.'>'.$v['name'].'</option>';	
			else $sector_html.='<option value="'.$v['id'].'" '.$class.'>'.$v['name'].'</option>';	
		}
		$sm->assign('sector_ids',$sender_storage_ids);
		$sm->assign('sector_names',$sender_storage_names);
		$sm->assign('sector_html', $sector_html);
		$sm->assign('sector_names_selected',$sector_names_selected);
		
		$sm->assign('sector_id',$current_sector);
	   
	   
	   
	   
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		
		
		$sm->assign('can_print',$can_print);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>