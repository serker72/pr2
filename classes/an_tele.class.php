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


//отчет ТЕЛЕ - лиды
class AnTele_Leads extends AnTele_Abstract{
	//установка всех имен
	protected function init(){
		$this->tablename='lead';
		$this->_instance=new  Lead_Item;
		$this->_group_instance=new Lead_Group;
		//$this->_filegroup_instance=new TenderFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TenderFileItem(1))); нужен отдельный метод для инициализации!
		
		$this->_fileitem_instance=new LeadFileItem(1);
		
		$this->_suppliers=new Lead_SupplierGroup;
		$this->_historygroup_instance=new Lead_HistoryGroup;
		$this->_eqsgroup=new Lead_EqTypeGroup;
		$this->_kindsgroup=new LeadKindGroup;

		
		$this->_fzgroup=new Tender_FZGroup;
		
		$this->edit_pagename='ed_lead.php'; $this->file_pagename='lead_file.html'; $this->history_pagename='lead_lenta_file.html';
		
	}
	
	
	//получить запрос для отчета
	protected function GainSql(){
		$this->_sql='select distinct p.*,
		s.name as status_name ,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		 eq.name as eq_name, kind.name as kind_name,
		 tender.code as tender_code,
		 
		 cur.name as currency_name, cur.signature as currency_signature,
		 prod.name as producer_name,
		 lf.name as fail_name,
		 tender.fail_reason as tfail_reason, tf.name as tfail_name 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				 left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join lead_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				 left join tender_eq_types as eq on eq.id=p.eq_type_id
				left join lead_kind as kind on kind.id=p.kind_id
				left join tender as tender on tender.id=p.tender_id
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join pl_producer as prod on p.producer_id=prod.id
				left join lead_fail as lf on p.fail_reason_id=lf.id 
				
				left join lead_fail as tf on tender.fail_reason_id=tf.id 
				
					 
				 ';
				
	}
}



//отчет ТЕЛЕ - тендеры
class AnTele_Tenders extends AnTele_Abstract{
	//установка всех имен
	protected function init(){
		$this->tablename='tender';
		$this->_instance=new Tender_Item;
		$this->_group_instance=new Tender_Group;
		//$this->_filegroup_instance=new TenderFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TenderFileItem(1))); нужен отдельный метод для инициализации!
		
		$this->_fileitem_instance=new TenderFileItem(1);
		
		$this->_suppliers=new Tender_SupplierGroup;
		$this->_historygroup_instance=new Tender_HistoryGroup;
		$this->_eqsgroup=new Tender_EqTypeGroup;
		$this->_kindsgroup=new TenderKindGroup;
		$this->_fzgroup=new Tender_FZGroup;
		
		$this->edit_pagename='ed_tender.php'; $this->file_pagename='tender_file.html'; $this->history_pagename='tender_lenta_file.html';
		
	}
	
	
	//получить запрос для отчета
	protected function GainSql(){
		$this->_sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		eq.name as eq_name, kind.name as kind_name,
		cur.name as currency_name, cur.signature as currency_signature,
		fz.name as fz_name,
		lf.name as fail_name
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join tender_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
				left join tender_kind as kind on kind.id=p.kind_id
				
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join tender_fz as fz on p.fz_id=fz.id
				left join lead_fail as lf on p.fail_reason_id=lf.id
					 
				 ';
	}
}


//абстрактный отчет ТЕЛЕ
class AnTele_Abstract{
	
	protected $tablename;
	protected $_instance;
	protected $_group_instance;
	protected $_historygroup_instance;
	protected $_filegroup_instance;
	protected $_fileitem_instance;
	protected $_suppliers;
	
	protected $_eqsgroup, $_kindsgroup, $_fzgroup;
	
	protected $_sql;
	protected $edit_pagename, $file_pagename, $history_pagename;
	
	public function __construct(){
		$this->init();
	}
	
	//установка всех имен
	protected function init(){
		$this->tablename='tender';
		$this->_instance=new Tender_Item;
		$this->_group_instance=new Tender_Group;
		//$this->_filegroup_instance=new TenderFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new TenderFileItem(1))); нужен отдельный метод для инициализации!
		
		$this->_fileitem_instance=new TenderFileItem(1);
		
		$this->_suppliers=new Tender_SupplierGroup;
		$this->_historygroup_instance=new Tender_HistoryGroup;
		$this->_eqsgroup=new Tender_EqTypeGroup;
		$this->_kindsgroup=new TenderKindGroup;
		$this->_fzgroup=new Tender_FZGroup;
		
		$this->edit_pagename='ed_tender.php'; $this->file_pagename='tender_file.html'; $this->history_pagename='tender_lenta_file.html';
	}
	
	
	//получить запрос для отчета
	protected function GainSql(){
		$this->_sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		eq.name as eq_name, kind.name as kind_name,
		cur.name as currency_name, cur.signature as currency_signature,
		fz.name as fz_name 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join tender_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
				left join tender_kind as kind on kind.id=p.kind_id
				
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join tender_fz as fz on p.fz_id=fz.id
					 
				 ';
	}
	
	
	
	
	
	
	
	

	public function ShowData(  $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $can_edit=false, &$alls, $result=NULL){
		
		 
		 
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
	 
		$_otv_arr=array(); $_sups=array(); $_prods=array();
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0;
			$fields=$dec->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_content') $has_content=$v->GetValue();
				
				 if($v->GetName()=='print') $print=$v->GetValue();
				  if($v->GetName()=='prefix') $prefix=$v->GetValue();
			}
			 
		  
			    $this->GainSql();
				$sql=$this->_sql;
				
				$db_flt=$dec->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' where '.$db_flt;
				}
				
				
				
				$ord_flt=$dec->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.=' order by '.$ord_flt;
				}			  
				  
				//echo  $sql.'<br><br>';  
				  
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				
				
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					
					
					if($f['pdate_placing']!=="") $f['pdate_placing']=DateFromYmd($f['pdate_placing']);
			
					if($f['pdate_claiming']!=="") $f['pdate_claiming']=DateFromYmd($f['pdate_claiming']);
					
					if($f['pdate_finish']!=="") $f['pdate_finish']=DateFromYmd($f['pdate_finish']);
					
					
					$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
					
					if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
					else $f['confirm_price_pdate']='-';
					
					 
					if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
					else $f['confirm_shipping_pdate']='-';
					
					
					if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
					else $f['fulfiled_pdate']='-';
					 
			 
				 
					$f['suppliers']=$this->_suppliers->GetItemsByIdArr($f['id']);	
					
				 
					
					
				 
				
					$f['max_price_formatted']=number_format($f['max_price'],2,'.',' ');//sprintf("", $f['max_price']);
			
					//history
					 
					$this->_historygroup_instance->ShowHistory(
						$f['id'],
						 'an_tele/'.$this->tablename.'_lenta.html', 
						 new DBDecorator(), 
						 $false, 
						 true,
						 false, 
						 $result,
						 false,
						 false, $history_data, false,false
						 );			
					$f['content']=$history_data; 
								 
					
					$filedec=new DBDEcorator;
				 
					$this->_filegroup_instance=NULL;
					if($this instanceof AnTele_Leads){
					
						$this->_filegroup_instance=new  LeadFileGroup(1,  $f['id'],  new FileDocFolderItem(1, $f['id'], $this->_fileitem_instance));
					}elseif($this instanceof AnTele_Tenders){
						$this->_filegroup_instance=new  TenderFileGroup(1,  $f['id'],  new FileDocFolderItem(1, $f['id'], $this->_fileitem_instance));
					}
					if($print==0) $_template='an_tele/'.$this->tablename.'incard_list.html';
					else $_template='an_tele/'.$this->tablename.'incard_list_print.html';
					$f['files']=$this->_filegroup_instance->ShowFiles($_template, $filedec,0,100000,$this->edit_pagename, $this->file_pagename, 'swfupl-js/sched_files.php',  
			 false, 
			 false,
			 false,
			0,
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,
			   $result,  
			   new DBDecorator, 'file_' 
			   );
						
					 
					//статистика
					if(($f['manager_id']!=0)&&!in_array($f['manager_id'], $_otv_arr)) $_otv_arr[]=$f['manager_id'];
					
					if(($f['producer_id']!=0)&&!in_array($f['producer_id'], $_prods)) $_prods[]=$f['producer_id'];
					foreach($f['suppliers'] as $k=>$v){
						if(!in_array($v['supplier_id'], $_sups)) $_sups[]=$v['supplier_id'];	
					}
					
					
					$alls[]=$f;
				}
				
			 
			 
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec->GetUris();
	   $user=''; $supplier=''; $city=''; $share_user=''; $producer='';  $tab_page='1'; $city='';  $country=''; $fo='';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
			if($v->GetName()=='manager_name') $user=$v->GetValue();
			if($v->GetName()=='supplier_name') $supplier=$v->GetValue();
			
			if($v->GetName()=='producer_name') $producer=$v->GetValue();
			 if($v->GetName()=='tab_page') $tab_page=$v->GetValue();
			
			 if($v->GetName()=='city') $city=$v->GetValue();
			if($v->GetName()=='country') $country=$v->GetValue();
			if($v->GetName()=='fo') $fo=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		 
		//вид, тип оборудования
		$sm->assign('kinds', $this->_kindsgroup->GetItemsArr());
		
		$sm->assign('eqs', $this->_eqsgroup->GetItemsArr());
		
		//фз
		$sm->assign('fzs', $this->_fzgroup->GetItemsArr());
		
		
		 
		//контрагент
		if(strlen($supplier)>0){
			$_ids=explode(';', $supplier);
			
			$sql='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id where s.id in('.implode(', ', $_ids).') order by s.full_name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f;
			}
			$sm->assign("our_suppliers", $our_users);
		 
		} 
		
		//продизводитель по ПЛ
		if(strlen($producer)>0){
			$_ids=explode(';', $producer);
			
			$sql='select * from pl_producer as s where s.id in('.implode(', ', $_ids).') order by s.name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f;
			}
			$sm->assign("our_producers", $our_users);
		 
		} 
		 
		 
		//менеджер
		if(strlen($user)>0){
				$_ids=explode(';', $user);
				
				//$sql='select * from user where id in('.implode(', ', $_ids).') order by name_s';
				$sql='select p.*, up.name as position_s from user as p
				left join user_position as up on up.id=p.position_id
				 where p.id in('.implode(', ', $_ids).') order by p.name_s';
				
				
				 
				 
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$our_users=array();
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					$our_users[]=$f;
				}
				$sm->assign("our_users", $our_users);
			 
			}
		
	 
		//город
		if(strlen($city)>0){
			$_ids=explode(';', $city);
			
			$sql='select c.*, r.name as region_name, o.name as okrug_name, sc.name as country_name
		
		 from sprav_city as c
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on c.country_id=sc.id
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
				if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
				
				
				$our_users[]=$f;
			}
			$sm->assign("our_cities", $our_users);
		 	 
		}
		
		//страна
		if(strlen($country)>0){
			$_ids=explode(';', $country);
			
			$sql='select c.* 
		
		 from sprav_country as c
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					 
				$our_users[]=$f;
			}
			$sm->assign("our_countries", $our_users);
		 	 
		}
		
		//фед. округ
		if(strlen($fo)>0){
			$_ids=explode(';', $fo);
			
			$sql='select c.* 
		 from sprav_district as c
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					 
				$our_users[]=$f;
			}
			$sm->assign("our_fos", $our_users);
		 	 
		}
	   
	   $link=$dec->GenFltUri('&',$prefix );
		$link=$pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1&tab_page='.$tab_page;
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
	  
	   
		//сколько записей
		$sm->assign('no', count($alls));
	 	
		//сколько ответственных
		$sm->assign('otv_no',count($_otv_arr));
		
		//сколько контрагентов
		$sm->assign('sup_no', count($_sups));
		
		//сколько пр-лей
		$sm->assign('prod_no', count($_prods));
		
		 
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		
			
		return $sm->fetch($template);
	}
	
	
	//получить список доступных документов
	public function GetAvailableIds($user_id){
		if($this instanceof AnTele_Leads) return $this->_group_instance->GetAvailableLeadIds($user_id);	
		elseif($this instanceof AnTele_Tenders) return $this->_group_instance->GetAvailableTenderIds($user_id);	
		else return array(-1);
	}
	
	
	
}
?>