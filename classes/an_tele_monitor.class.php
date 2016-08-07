<?

require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');

require_once('tender.class.php');
require_once('tender_history_group.php');
require_once('tender_filegroup.php');
require_once('tender_fileitem.php');


require_once('lead.class.php');
require_once('lead_history_group.php');
require_once('lead_filegroup.php');
require_once('lead_fileitem.php');

 

//отчет что мониторить
class AnTenderMonitor{
	
 

	public function ShowData(  $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $can_edit=false, &$alls, $result=NULL, $limited_supplier=NULL){
		
		 
		 
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
	 
		$_otv_arr=array(); $_sups=array(); $_prods=array();
		
		if($do_it){
			
			$has_1=false; $has_2=false; $print=0; $prefix=0;
			$fields=$dec->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_1') $has_1=$v->GetValue();
				if($v->GetName()=='has_2') $has_2=$v->GetValue();
				
				 if($v->GetName()=='print') $print=$v->GetValue();
				  if($v->GetName()=='prefix') $prefix=$v->GetValue();
			}
			 
		  // $alls=array();
		   if($has_1) $alls[]=array('name'=>'По виду оборудования', 'kind_id'=>1, 'data'=>array());
		   if($has_2) $alls[]=array('name'=>'По контрагенту', 'kind_id'=>2, 'data'=>array());
		   foreach($alls as $kk=>$iter){
		  
			    $sql=$this->GainSql($iter['kind_id']);

				
				$db_flt=$dec->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				}
				
				if(($iter['kind_id']==2)&&($limited_supplier!==NULL)){
					$sql.=' and sup.id in('.implode(', ', $limited_supplier).') ';
				}
				
				/*$ord_flt=$dec->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.=' order by '.$ord_flt;
				}*/
				
				if($iter['kind_id']==1){
					$sql.=' order by eq.name asc';	
				}elseif($iter['kind_id']==2){
					$sql.=' order by sup.full_name asc';	
				}
				  
				//echo  $sql.'<br><br>';  
				  
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				
				
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					 
					
					$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
					
					$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
					
					 
					
					$alls[$kk]['data'][]=$f;
				}
		   }
			 
			 
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec->GetUris();
	   $user=''; $supplier=''; $city=''; $share_user=''; $producer='';  $tab_page='1';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
			if($v->GetName()=='manager_name') $user=$v->GetValue();
			if($v->GetName()=='supplier_name') $supplier=$v->GetValue();
			
			if($v->GetName()=='producer_name') $producer=$v->GetValue();
			 if($v->GetName()=='tab_page') $tab_page=$v->GetValue();
			
			 
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		 
		
	   
	   $link=$dec->GenFltUri('&',$prefix );
		$link=$pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1&tab_page='.$tab_page;
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
	  
	    
		
		$sm->assign('can_print',$can_print);
		
		//$sm->assign('can_edit',$can_edit);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		
			
		return $sm->fetch($template);
	}
	
	
	
	protected function GainSql($kind_id){
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		 
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		 
		
		eq.name as eq_name,  
		sup.full_name as supplier_name, opf.name as opf_name
		 
					 
				from tender_monitor as p
				left join document_status as s on s.id=p.status_id
				 
				left join user as up on up.id=p.user_confirm_id
				 
				
				 
				 
				left join supplier as sup on p.supplier_id=sup.id
				left join opf as opf on opf.id=sup.opf_id
				
				 
				
				left join user as cr on cr.id=p.created_id
				 
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
			where p.kind_id="'.$kind_id.'" 	 
				and p.is_confirmed=1
				 ';
				 
		 
		return $sql;	 
	}
	
}
?>