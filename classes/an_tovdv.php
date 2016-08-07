<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('storageitem.php');
require_once('sectoritem.php');
require_once('storagegroup.php');
require_once('sectorgroup.php');
require_once('storagesector.php');
require_once('orgitem.php');
require_once('opfitem.php');

class AnTovdv{
	public function ShowData($position_id, $org_id, $storage_id, $sector_id, $pdate1, $pdate2, $template, DBDecorator $dec,$pagename='files.php', $manager=''){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
		
		
		$sm=new SmartyAdm;
		$_org=new OrgItem;
		$_opf=new OpfItem;
		$org=$_org->getitembyid($org_id);
		$opf=$_opf->GetItemById($org['opf_id']);
		
		$sm->assign('org', stripslashes($opf['name'].' '.$org['full_name']));
		
		$sm->assign('manager',$manager);
		
		$sm->assign('supplier_name',$supplier['name']);
		
		$sm->assign('dogovor','№'.$supplier['contract_no'].' от '.$supplier['contract_pdate']);
		
		$sm->assign('period',date("d.m.Y H:i:s",$pdate1).' - '.date("d.m.Y H:i:s",$pdate2));
		
		$alls=array();
		
		if(!(($position_id==0)&&($storage_id==0)&&($sector_id==0))){
		
		  //$filter='where  ';
		  $_filter=array();
		 //  $_filter[]=' s.is_confirmed=1 ';
		 // $_filter[]=' (s.pdate between "'.$pdate1.'" and "'.$pdate2.'") ';
		  if($position_id>0) $_filter[]=' position_id="'.$position_id.'"';
		  if($storage_id>0) $_filter[]=' storage_id="'.$storage_id.'"';
		  if($sector_id>0) $_filter[]='  sector_id="'.$sector_id.'"';
		  $filter=implode(' and ',$_filter);
		
		  $sql='select distinct a.* from storage as a where a.is_active=1  
		  ';
		  if($storage_id>0) $sql.=' and id="'.$storage_id.'"';
		  $sql.=' and(
		  /*распоряжения на приемку по складу*/
		  id in(
		  	select distinct storage_id from bill where org_id="'.$org_id.'" and id in(select distinct bill_id from sh_i where is_confirmed=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")
			';
			//if($position_id>0) $sql.=' and id in(select distinct bill_id from interstore_position where position_id="'.$position_id.'")';
			if($sector_id>0) $sql.=' and sector_id="'.$sector_id.'" ';
			$sql.=')	
		  )
		  /*межсклад - отправка*/
		  or id in(
		  	select distinct sender_storage_id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		  if($storage_id>0) $sql.=' and sender_storage_id="'.$storage_id.'" ';
		  if($sector_id>0) $sql.=' and sender_sector_id="'.$sector_id.'" ';
		  if($position_id>0) $sql.=' and id in(select distinct interstore_id from interstore_position where position_id="'.$position_id.'")';
		  $sql.='
		  )
		  /*межсклад-приемка */
		  or id in(
		  	select distinct receiver_storage_id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		  if($storage_id>0) $sql.=' and receiver_storage_id="'.$storage_id.'" ';
		  if($sector_id>0) $sql.=' and receiver_sector_id="'.$sector_id.'" ';
		  if($position_id>0) $sql.=' and id in(select distinct interstore_id from interstore_position where position_id="'.$position_id.'")';
		  $sql.='
		  )
		  
		  )';
		  
		  
		  $sql.='order by a.name asc';	  
			  
		  
		  //echo $sql;
		  
		  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  $prihod=0;
		  $rashod=0;
		  
		  $alls=array();
		  $total_quantity=0; $total_pm=0; $total_marja=0;
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			 // print_r($f);
			  
			  $sectors=array();
			  //по складу получить все участки
			  $sql1='
			  select b.* from sector as b inner join storage_sector as bb on b.id=bb.sector_id where bb.storage_id="'.$f['id'].'" and b.is_active=1 ';
			  
			  if($sector_id>0) $sql1.=' and sector_id="'.$sector_id.'" ';
			  
			  $sql1.=' order by b.name asc';
			  $set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
			  $rs1=$set1->GetResult();
			  $rc1=$set1->GetResultNumRows();
			  for($j=0; $j<$rc1; $j++){
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				
				$items=array();
				//получить: 1 - приемку 2 - межсклад отдачу; 3 - межсклад-приемку; 4 - межсклад-списание
				//приемка
				$sql2='select *, sum(quantity) as s_q from sh_i_position where 
				sh_i_id in(select id from sh_i where is_confirmed=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and bill_id in(select id from bill where org_id="'.$org_id.'" and sector_id="'.$g['id'].'" and storage_id="'.$f[0].'"))';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
  					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=1;
					$items[]=$h;
					$prihod+=(int)$h['s_q'];
				  //print_r($h);
				}
				//print_r($g);
				
				//2 отдача межсклад
				$sql2='select *, sum(quantity) as s_q from interstore_position where 
				
				interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=0 and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and sender_sector_id="'.$g['id'].'" and sender_storage_id="'.$f[0].'")';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
  					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=2;
					$items[]=$h;
					$rashod+=(int)$h['s_q'];
				  //print_r($h);
				}
				
				//3 приемка межсклад
				$sql2='select *, sum(quantity) as s_q from interstore_position where 
				
				interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=0 and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and receiver_sector_id="'.$g['id'].'" and receiver_storage_id="'.$f[0].'")';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
  					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=3;
					$items[]=$h;
					$prihod+=(int)$h['s_q'];
				  //print_r($h);
				}
					
				//4 списание 
				$sql2='select *, sum(quantity) as s_q from interstore_position where 
				
				interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=1 and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and sender_sector_id="'.$g['id'].'" and sender_storage_id="'.$f[0].'")';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
  					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=4;
					$items[]=$h;
					$rashod+=(int)$h['s_q'];
				  //print_r($h);
				}	
				
				//print_r($items);		 
				$g['items']=$items;
				$sectors[]=$g;
			  }
			  
			  $f['itemswith']=$sectors;
			  
			  //отдельно рассмотреть все без участков
			  if($sector_id==0){
				$items2=array();
				//получить: 1 - приемку 2 - межсклад отдачу; 3 - межсклад-приемку; 4 - межсклад-списание
				//приемка
				$sql2='select *, sum(quantity) as s_q from sh_i_position where 
				sh_i_id in(select id from sh_i where is_confirmed=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and bill_id in(select id from bill where org_id="'.$org_id.'" and sector_id="0" and storage_id="'.$f[0].'"))';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=1;
					$items2[]=$h;
					$prihod+=(int)$h['s_q'];
				  //print_r($h);
				}
				//print_r($g);
				
				//2 отдача межсклад
				$sql2='select *, sum(quantity) as s_q from interstore_position where 
				
				interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=0 and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and sender_sector_id="0" and sender_storage_id="'.$f[0].'")';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=2;
					$items2[]=$h;
					$rashod+=(int)$h['s_q'];
				  //print_r($h);
				}
				
				//3 приемка межсклад
				$sql2='select *, sum(quantity) as s_q from interstore_position where 
				
				interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=0 and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and receiver_sector_id="0" and receiver_storage_id="'.$f[0].'")';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=3;
					$items2[]=$h;
					$prihod+=(int)$h['s_q'];
				  //print_r($h);
				}
					
				//4 списание 
				$sql2='select *, sum(quantity) as s_q from interstore_position where 
				
				interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=1 and (pdate between "'.$pdate1.'" and "'.$pdate2.'") 
				and sender_sector_id="0" and sender_storage_id="'.$f[0].'")';
				if($position_id>0) $sql2.=' and position_id="'.$position_id.'" ';
				$sql2.=' group by position_id order by name';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					foreach($h as $kk=>$v) $h[$kk]=stripslashes($v);
					$h['operation_type']=4;
					$items2[]=$h;
					$rashod+=(int)$h['s_q'];
				  //print_r($h);
				}	
				
			  }
			  
			  $f['itemswo']=$items2;
			  
			  
			  $alls[]=$f;	
		  }
		  
		}
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';
		*/
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		//считать остатки, начальный и конечный
		$begin_ost=0;
		//нач. остаток
		$osts=$this->OstCalc($position_id, $storage_id, $sector_id, $pdate1, $org_id);
		
		$begin_ost=$osts;
		//kon. остаток
		$osts=$this->OstCalc($position_id, $storage_id, $sector_id, $pdate2, $org_id);
		$end_ost=$osts;
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='position_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//kontragent
		$as=new mysqlSet('select * from catalog_position order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_supplier==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('sg',$acts);
		
		
		
		//склады
		$_storages=new StorageGroup;
		$sgs=$_storages->GetItemsArr(0,1);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		
		foreach($sgs as $k=>$v){
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
		}
		$sm->assign('storage_ids',$sender_storage_ids);
		$sm->assign('storage_names',$sender_storage_names);
		
		
		$sm->assign('storage_id',$current_storage);
		
		
		$ssgr=new SectorGroup;
		$scs=$ssgr->GetItemsArr(0, 1);
	
		$sender_sector_ids=array('0');
		$sender_sector_names=array('-выберите-');
	
		foreach($scs as $k=>$v){
			$sender_sector_ids[]=$v['id'];
			$sender_sector_names[]=$v['name'];	
		}
		
		$sm->assign('sector_ids',$sender_sector_ids);
		$sm->assign('sector_id',$current_sector);
		$sm->assign('sector_names',$sender_sector_names);
			
		
		$sm->assign('begin_ost',$begin_ost);
		$sm->assign('end_ost',$end_ost);
		$sm->assign('prihod',$prihod);
		$sm->assign('rashod',$rashod);
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	public function OstCalc($position_id, $storage_id, $sector_id, $pdate, $org_id){
		$res=0;
		//1 сумма распоряжений на приемку
		$sql='select sum(quantity) from sh_i_position where sh_i_id in 
		(select id from sh_i where is_confirmed=1 and org_id="'.$org_id.'" and pdate<"'.$pdate.'"';
		
		if($storage_id>0) $sql.=' and bill_id in(select id from bill where org_id="'.$org_id.'" and storage_id="'.$storage_id.'")';
		if($sector_id>0) $sql.=' and bill_id in(select id from bill where org_id="'.$org_id.'" and sector_id="'.$sector_id.'")';
		$sql.=')';
		if($position_id>0) $sql.=' and position_id="'.$position_id.'"';
		
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$res+=(int)$f[0];
		
		//2 поступление межсклад утв.
		$sql='select sum(quantity) from interstore_position where interstore_id in(
		select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=0 and pdate<"'.$pdate.'"';
		if($storage_id>0) $sql.=' and receiver_storage_id="'.$storage_id.'" ';
		if($sector_id>0) $sql.=' and receiver_sector_id="'.$sector_id.'" ';
		$sql.=')';
		if($position_id>0) $sql.=' and position_id="'.$position_id.'"';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$res+=(int)$f[0];
		
		
		//3 выдача межсклад утв.
		$sql='select sum(quantity) from interstore_position where interstore_id in(
		select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=0 and pdate<"'.$pdate.'"';
		if($storage_id>0) $sql.=' and sender_storage_id="'.$storage_id.'" ';
		if($sector_id>0) $sql.=' and sender_sector_id="'.$sector_id.'" ';
		$sql.=')';
		if($position_id>0) $sql.=' and position_id="'.$position_id.'"';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$res-=(int)$f[0];
		
		//4 списание утв.
		$sql='select sum(quantity) from interstore_position where interstore_id in(
		select id from interstore where is_confirmed=1 and org_id="'.$org_id.'" and is_or_writeoff=1 and pdate<"'.$pdate.'"';
		if($storage_id>0) $sql.=' and sender_storage_id="'.$storage_id.'" ';
		if($sector_id>0) $sql.=' and sender_sector_id="'.$sector_id.'" ';
		$sql.=')';
		if($position_id>0) $sql.=' and position_id="'.$position_id.'"';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$res-=(int)$f[0];
		
		
		return $res;
		
	}
}
?>