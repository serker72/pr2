<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/useritem.php');
require_once('classes/user_s_item.php');
//require_once('classes/user_d_item.php');

require_once('classes/search.php');
require_once('classes/kpgroup.php');

require_once('classes/memoallgroup.php');
require_once('classes/petitionallgroup.php');

require_once('classes/doc_in.class.php');
require_once('classes/doc_out.class.php');
require_once('classes/doc_vn.class.php');
require_once('classes/sched.class.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'GYDEX.поиск');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

 //журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Поиск по сайту');
 
//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	//die();
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	
	
	$_search=new Search;
	
	
	 
	//контрагенты
	if($au->user_rights->CheckAccess('w',91)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
                // KSK - 29.03.2016
                // добавляем условие выбора записей для отображения is_shown=1
		$_search->AddDoc( new Search_Supplier(
			'Контрагенты',
			'   from supplier as p 
			left join opf as po on p.opf_id=po.id
			left join supplier_contact as sc on sc.supplier_id=p.id and sc.is_shown=1
			left join user as crea on crea.id=p.created_id
			
			left join supplier_responsible_user as suresp on suresp.supplier_id=p.id
			left join user as resp on resp.id=suresp.user_id
			 ',
			 array('p.full_name','sc.name', 'crea.name_s', 'resp.name_s'),
			 array('Название','ФИО контакта', 'Создатель', 'Ответственный сотрудник'),
			 $decorator,
			  'supplier.php', 'action=1', 'id',
			  'suppliers.php', '', 'code'
			  ));
		
	
	}
	
	
	 
	//добавим КП
	if($au->user_rights->CheckAccess('w',695)){
		$kp_decorator=new DBDecorator;
		if(!$au->user_rights->CheckAccess('w',763)){
			//1. свои КП
			$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			$kp_decorator->AddEntry(new SqlEntry('p.user_manager_id',$result['id'], SqlEntry::E));
			
			 
			
			//2. КП подчиненных менеджеров руководителю отдела (если тек пол-ль =руководитель и есть такие менеджеры)
			$kps=new KpGroup;
			
			$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$kp_decorator->AddEntry(new SqlEntry('p.user_manager_id', NULL, SqlEntry::IN_VALUES, NULL,$kps->view_rules->GetManagers($result)));	
			
			
			
			//3. КП, подпадающие под правила из kp_rights
			//echo $log->view_rules->GetListSql($result);
			$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$kp_decorator->AddEntry(new SqlEntry('p.id','select kp_id from kp_position where position_id in('.$kps->view_rules->GetListSql($result).')', SqlEntry::IN_SQL));
			
			
			$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			
			
		}
		
		 
		
		//ограничения по сотруднику
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
			
		}
		
			
		$_search->AddDoc( new Search_KP(
			'Коммерческие предложения',
			'  from kp as p left join kp_position as pp on p.id=pp.kp_id 
			
			left join supplier as sp on p.supplier_id=sp.id
			left join opf as spo on spo.id=sp.opf_id 
			
			left join user as crea on p.manager_id=crea.id
			left join user as manager on p.user_manager_id=manager.id
					
			 left join kp_file as f on f.kp_id=p.id ',
			 array('p.code','sp.full_name', 'p.supplier_fio','pp.name', 'f.orig_name', 'f.text_contents', 'crea.name_s', 'manager.name_s'),
			 array('Номер','Контрагент', 'Кому', 'Позиция КП', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_kp.php', 'action=1', 'id',
			  'kps.php', '', 'code'
			  ));
		
		
	
	} 
	
	//договоры и приложения
	if($au->user_rights->CheckAccess('w',820)){
		$pf_decorator=new DBDecorator;
		$_rl=new RLMan;
		//отбор по себе и подчиненным сотр-кам
		if(!$au->user_rights->CheckAccess('w',784)){
			
			//если есть права просмотра ВСЕХ фактов по определенному поставщику - 
			if($au->user_rights->CheckAccess('w',845)){
				
				$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
				//var_dump($restricted_producers);
				$pf_decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
			}else{
			//иначе
			
				
				$pf_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
				
				$pf_decorator->AddEntry(new SqlEntry('p.user_id',$result['id'], SqlEntry::E));
				
				$pf_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
				
				$pf_decorator->AddEntry(new SqlEntry('p.user_id','select id from user where manager_id="'.$result['id'].'" and is_in_plan_fact_sales=1 and is_active=1', SqlEntry::IN_SQL));
				
				//если тек. сотр-к является руководителем отдела - добавить ему руководимых сотрудников
				$_pos=new UserPosItem;
				$pos=$_pos->Getitembyid($result['position_id']);
				if($pos['is_ruk_otd']){
					$pf_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
				
					$pf_decorator->AddEntry(new SqlEntry('p.user_id','select id from user where is_active=1 and is_in_plan_fact_sales=1 and department_id="'.$result['department_id'].'" and id<>"'.$result['id'].'"', SqlEntry::IN_SQL));
				}
				
				
				$pf_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			}
		}
		
		 
		
		//ограничения по сотруднику
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$pf_decorator->AddEntry(new SqlEntry('us.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));
			
		}

		$_search->AddDoc( new Search_Fact(
			'Договоры и приложения',
			'  from plan_fact_fact as p
						
						
						left join user as u on p.user_confirm_id=u.id
						left join pl_price_kind as pk on p.price_kind_id=pk.id
						
						left join user as us on p.user_id=us.id
						left join user as mn on p.posted_user_id=mn.id
						
						 
						left join document_status as st on st.id=p.status_id
						
						left join pl_producer as prod on prod.id=p.producer_id 
						 
						left join pl_currency as curr on curr.id=p.contract_currency_id 
						left join sprav_city as c on c.id=p.city_id	  ',
			 array('p.supplier_name','p.eq_name', 'p.contract_no',  'prod.name', 'c.name', 'us.name_s','mn.name_s'),
			 array('Контрагент','Оборудование', '№ договора','Производитель', 'Город', 'Создатель', 'Ответственный сотрудник'),
			 $pf_decorator,
			  'plan_fact_fact_opo.php', '', 'id',
			  'plan_fact_fact_opo.php', '', 'id'
			  ));
		
		
	
	}
	
	
	
	//прайс-лист
	 $_rl=new RLMan;

	if($au->user_rights->CheckAccess('w',600)){
		$pL_decorator=new DBDecorator;
		$pL_decorator->AddEntry(new SqlEntry('p.parent_id', 0, SqlEntry::E));	
		$pL_decorator->AddEntry(new SqlEntry('p.is_active', 1, SqlEntry::E));	
		$price_kind_id=3;
		
		//фильтроварть оборудование по закрытым подразделам, разделам, пр-лям
		$restricted_groups=array();
	//	$restricted_two_groups=array();
		$restricted_producers=array();
		
		
		$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
		$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
		
		if(count($restricted_producers)>0){
			$pL_decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
			 
		}
		
		if(count($restricted_groups)>0){
			$pL_decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
			 
		}
		
		
		
		$_search->AddDoc( new Search_PlPos(
			'Прайс-лист поставщика',
			'  from pl_position as pl
					inner join catalog_position as p on p.id=pl.position_id
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					left join pl_discount as dis on dis.id=pl.discount_id
					left join pl_position_price as plp on plp.pl_position_id=pl.id and plp.currency_id="'.CURRENCY_DEFAULT_ID.'" and plp.price_kind_id='.$price_kind_id.'
					left join pl_currency as curr on curr.id='.CURRENCY_DEFAULT_ID.'
					left join pl_price_kind as pr_kind on pr_kind.id='.$price_kind_id.'  
					left join catalog_position_file as f on f.position_id=p.id 
					
					',
					
			 array('p.name','p.name_en','f.orig_name', 'f.text_contents'),
			 array('Наименование','Наименование англ.', 'Имя прикрепленного файла', 'Содержание прикрепленного файла'),
			 $pL_decorator,
			  'ed_pl_position.php', 'action=1', 'id',
			  'pricelist.php', 'price_kind_id_1='.$price_kind_id, 'id_1'
			  ));
		
	}
	
	
	if($au->user_rights->CheckAccess('w',741)){
		$pL_decorator=new DBDecorator;
		$pL_decorator->AddEntry(new SqlEntry('p.parent_id', 0, SqlEntry::E));	
		$pL_decorator->AddEntry(new SqlEntry('p.is_active', 1, SqlEntry::E));	
		$price_kind_id=2;
		
		//фильтроварть оборудование по закрытым подразделам, разделам, пр-лям
		$restricted_groups=array();
	//	$restricted_two_groups=array();
		$restricted_producers=array();
		
		
		$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
		$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
		
		if(count($restricted_producers)>0){
			$pL_decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
			 
		}
		
		if(count($restricted_groups)>0){
			$pL_decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
			 
		}
		
		
		
		$_search->AddDoc( new Search_PlPos(
			'Прайс-лист ExW',
			'  from pl_position as pl
					inner join catalog_position as p on p.id=pl.position_id
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					left join pl_discount as dis on dis.id=pl.discount_id
					left join pl_position_price as plp on plp.pl_position_id=pl.id and plp.currency_id="'.CURRENCY_DEFAULT_ID.'" and plp.price_kind_id='.$price_kind_id.'
					left join pl_currency as curr on curr.id='.CURRENCY_DEFAULT_ID.'
					left join pl_price_kind as pr_kind on pr_kind.id='.$price_kind_id.'  
					left join catalog_position_file as f on f.position_id=p.id 
					
					',
					
			 array('p.name','p.name_en','f.orig_name', 'f.text_contents'),
			 array('Наименование','Наименование англ.', 'Имя прикрепленного файла', 'Содержание прикрепленного файла'),
			 $pL_decorator,
			  'ed_pl_position.php', 'action=1', 'id',
			  'pricelist.php', 'price_kind_id_1='.$price_kind_id, 'id_1'
			  ));
		
	}
	
	
	
	if($au->user_rights->CheckAccess('w',600)){
		$pL_decorator=new DBDecorator;
		$pL_decorator->AddEntry(new SqlEntry('p.parent_id', 0, SqlEntry::E));	
		$pL_decorator->AddEntry(new SqlEntry('p.is_active', 1, SqlEntry::E));	
		$price_kind_id=1;
		
		//фильтроварть оборудование по закрытым подразделам, разделам, пр-лям
		$restricted_groups=array();
	//	$restricted_two_groups=array();
		$restricted_producers=array();
		
		
		$restricted_groups=$_rl->GetBlockedItemsArr($result['id'],1,'w', 'catalog_group', 0);
		$restricted_producers=$_rl->GetBlockedItemsArr($result['id'],34,'w', 'pl_producer', 0);
		
		if(count($restricted_producers)>0){
			$pL_decorator->AddEntry(new SqlEntry('p.producer_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_producers));	
			 
		}
		
		if(count($restricted_groups)>0){
			$pL_decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_groups));
			 
		}
		
		
		
		$_search->AddDoc( new Search_PlPos(
			'Прайс-лист DDPM',
			'  from pl_position as pl
					inner join catalog_position as p on p.id=pl.position_id
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					left join pl_producer as pp on p.producer_id=pp.id
					left join pl_discount as dis on dis.id=pl.discount_id
					left join pl_position_price as plp on plp.pl_position_id=pl.id and plp.currency_id="'.CURRENCY_DEFAULT_ID.'" and plp.price_kind_id='.$price_kind_id.'
					left join pl_currency as curr on curr.id='.CURRENCY_DEFAULT_ID.'
					left join pl_price_kind as pr_kind on pr_kind.id='.$price_kind_id.'  
					left join catalog_position_file as f on f.position_id=p.id 
					
					',
					
			 array('p.name','p.name_en','f.orig_name', 'f.text_contents'),
			 array('Наименование','Наименование англ.', 'Имя прикрепленного файла', 'Содержание прикрепленного файла'),
			 $pL_decorator,
			  'ed_pl_position.php', 'action=1', 'id',
			  'pricelist.php', 'price_kind_id_1='.$price_kind_id, 'id_1'
			  ));
		
	}
	
	
	
	
	
	
	
	
	
	//добавим тендер
	if($au->user_rights->CheckAccess('w',929)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Tender_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',934)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableTenderIds($result['id'])));	
		}
		
		 
		
			
		$_search->AddDoc( new Search_Tender(
			'Тендеры',
			'  from tender as p
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
					
			 	left join tender_file as f on f.bill_id =p.id
				left join tender_history as h on h.sched_id=p.id
				left join tender_history_file as hf on hf.history_id=h.id
				
				 ',
			 array('p.code','sup.full_name', 'p.topic', 'f.orig_name', 'f.text_contents', 'hf.orig_name', 'hf.text_contents', 'eq.name', 'cr.name_s', 'u.name_s'),
			 array('Номер','Контрагент', 'Название', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Имя файла комментариев', 'Содержание  файла комментариев', 'Тип оборудования', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_tender.php', 'action=1', 'id',
			  'tenders.php', '', 'code'
			  ));
		
		
	
	} 
	
	
	
	
	//добавим лид
	if($au->user_rights->CheckAccess('w',948)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Lead_Group;
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',953)){
			$kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableLeadIds($result['id'])));	
		}
	 	
		
		 
		
			
		$_search->AddDoc( new Search_Lead(
			'Лиды',
			'   
				from lead as p
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
					
			 	left join lead_file as f on f.bill_id =p.id
				left join lead_history as h on h.sched_id=p.id
				left join lead_history_file as hf on hf.history_id=h.id
				
				 ',
			 array('p.code','sup.full_name', 'p.topic', 'f.orig_name', 'f.text_contents', 'hf.orig_name', 'hf.text_contents', 'eq.name', 'cr.name_s', 'u.name_s'),
			 array('Номер','Контрагент', 'Название', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Имя файла комментариев', 'Содержание  файла комментариев', 'Тип оборудования', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_lead.php', 'action=1', 'id',
			  'leads.php', '', 'code'
			  ));
		
		
	
	} 
	
	
	//справ. информация
	if($au->user_rights->CheckAccess('w',841)){
		$kp_decorator=new DBDecorator;
		
		$_file=new spitem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  35, 'w',  $_file->GetTableName(), 2);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_SpravFile(
			'Справочная информация', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
				left join user as u on u.id=p.user_id
				
				 ', //1
			 array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_pl.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-5', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	
	//ф и д
	if($au->user_rights->CheckAccess('w',840)){
		$kp_decorator=new DBDecorator;
		
		$_file=new FilePoItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  37, 'w',  $_file->GetTableName(), 1);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_File(
			'Файлы и документы', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
				left join user as u on u.id=p.user_id
				
				 ', //1
			 array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-1', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	//письма
	if($au->user_rights->CheckAccess('w',840)){
		$kp_decorator=new DBDecorator;
		
		$_file=new FileLetItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  38, 'w',  $_file->GetTableName(), 4);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_FileL(
			'Письма', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
				left join user as u on u.id=p.user_id
				
				 ', //1
			  array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_l.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-3', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	//спец
	if($au->user_rights->CheckAccess('w',476)){
		$kp_decorator=new DBDecorator;
		
		$_file=new SpSItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  36, 'w',  $_file->GetTableName(), 3);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_Sps(
			'Спецдокументы', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
				left join user as u on u.id=p.user_id
				
				 ', //1
			 array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_spl.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-6', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	 
	//добавить: СЗ, З, ИСХ, ВХ, ВН
	//сз
	if($au->user_rights->CheckAccess('w',1140)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new MemoAllGroup; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',733)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		 
		
			
		$_search->AddDoc( new Search_Memo(
			'Служебные записки',
			'   
		from memo as p
			left join memo_kind as k on p.kind_id=k.id
			left join document_status as st on st.id=p.status_id
			left join user as u on u.id=p.manager_id
			left join user as crea on crea.id=p.user_id
			left join user as u1 on u1.id=p.user_confirm_id
			left join user as u2 on u2.id=p.user_ruk_id
			left join user as u3 on u3.id=p.user_dir_id
				
				 ',
			 array('p.code',  'p.topic',  'crea.name_s', 'u.name_s'),
			 array('Номер',  'Тема', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'memo_my_history.php', 'action=1', 'id',
			  'memos.php', '', 'code_3'
			  ));
		
		
	
	} 
	
	
	//З
	if($au->user_rights->CheckAccess('w',1139)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new PetitionAllGroup; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',727)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		 
		
			
		$_search->AddDoc( new Search_Petition(
			'Заявления',
			'   
		from petition as p
			left join petition_kind as k on p.kind_id=k.id
			left join document_status as st on st.id=p.status_id
			left join user as u on u.id=p.manager_id
			left join user as crea on crea.id=p.user_id
			left join user as u1 on u1.id=p.user_confirm_id
			left join user as u2 on u2.id=p.user_ruk_id
			left join user as u3 on u3.id=p.user_dir_id
			
			left join  petition_vyh_date as vd on vd.petition_id=p.id
				
				 ',
			 array('p.code',  'p.txt',  'crea.name_s', 'u.name_s'),
			 array('Номер',  'Комментарий', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'petition_my_history.php', 'action=1', 'id',
			  'petitions.php', '', 'code_3'
			  ));
		
		
	
	}  
	
	 
	//исх
	 if($au->user_rights->CheckAccess('w',1061)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new DocOut_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',1068)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 1, SqlEntry::E));
		 
		
			
		$_search->AddDoc( new Search_DocOut(
			'Исходящие письма',
			'   
		from doc_out as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.send_user_id
				
				 ',
			 array('p.code', 'p.topic', 'sup.full_name',  'p.description',  'cr.name_s', 'u.name_s'),
			 array('Номер', 'Тема', 'Контрагент', 'Текст', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_doc_out.php', 'action=1', 'id',
			  'doc_outs.php', '', 'code_1'
			  ));
		
		
	
	} 
	
 
	
	 if($au->user_rights->CheckAccess('w',1061)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new DocOut_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',1068)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 2, SqlEntry::E));
		 
		
			
		$_search->AddDoc( new Search_DocOut(
			'Исх. информационные письма',
			'   
		from doc_out as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.send_user_id
				
				 ',
			 array('p.code', 'p.topic', 'sup.full_name',  'p.description',  'cr.name_s', 'u.name_s'),
			 array('Номер', 'Тема', 'Контрагент', 'Текст', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_doc_out.php', 'action=1', 'id',
			  'doc_outs.php', '', 'code_2'
			  ));
		
		
	
	} 
	
	 if($au->user_rights->CheckAccess('w',1061)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new DocOut_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',1068)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 3, SqlEntry::E));
		 
		
			
		$_search->AddDoc( new Search_DocOut(
			'Сопроводительные письма',
			'   
		from doc_out as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_out_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_out_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.send_user_id
				
				 ',
			 array('p.code', 'p.topic', 'sup.full_name',  'p.description',  'cr.name_s', 'u.name_s'),
			 array('Номер', 'Тема', 'Контрагент', 'Текст', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_doc_out.php', 'action=1', 'id',
			  'doc_outs.php', '', 'code_3'
			  ));
		
		
	
	} 
	 
	//вх
	if($au->user_rights->CheckAccess('w',1142)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new DocIn_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',1083)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 1, SqlEntry::E));
		 
		
			
		$_search->AddDoc( new Search_DocIn(
			'Входящие письма',
			'   
		from doc_in as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_in_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_in_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_view_id
				left join doc_in_kind as kind on p.kind_id=kind.id
				
				 ',
			 array('p.code', 'p.topic', 'sup.full_name',  'p.description',  'cr.name_s', 'u.name_s'),
			 array('Номер', 'Тема', 'Контрагент', 'Текст', 'Создатель', 'Адресат'),
			 $kp_decorator,
			  'ed_doc_in.php', 'action=1', 'id',
			  'doc_ins.php', '', 'code1'
			  ));
		
		
	
	} 
	
	if($au->user_rights->CheckAccess('w',1142)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new DocIn_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',1083)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 2, SqlEntry::E));
		 
		
			
		$_search->AddDoc( new Search_DocIn(
			'Входящие КП',
			'   
		from doc_in as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
				left join doc_in_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_in_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_view_id
				left join doc_in_kind as kind on p.kind_id=kind.id
				
				 ',
			 array('p.code', 'p.topic', 'sup.full_name',  'p.description',  'cr.name_s', 'u.name_s'),
			 array('Номер', 'Тема', 'Контрагент', 'Текст', 'Создатель', 'Адресат'),
			 $kp_decorator,
			  'ed_doc_in.php', 'action=1', 'id',
			  'doc_ins.php', '', 'code2'
			  ));
		
		
	
	} 
	
	
	//внутр док
	if($au->user_rights->CheckAccess('w',1063)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new DocVn_Group; 
		$_plans->SetAuthResult($result);
		
		if(!$au->user_rights->CheckAccess('w',1095)){
			 $kp_decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_plans->GetAvailableDocIds($result['id'])));	
		}
		
		 
			
		$_search->AddDoc( new Search_DocVn(
			'Внутренние документы',
			'   
		from doc_vn as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 	left join sched as sc on p.sched_id=sc.id and sc.kind_id=2
				left join sched_suppliers as ss on ss.sched_id=sc.id
				left join sched_cities as cit on cit.sched_id=sc.id
				left join sprav_city as city on city.id=cit.city_id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_vn_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_ruk_id
				left join doc_vn_kind as kind on p.kind_id=kind.id
			 
				
				 ',
			 array('p.code', 'sup.full_name',    'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Создатель', 'Адресат'),
			 $kp_decorator,
			  'ed_doc_vn.php', 'action=1', 'id',
			  'doc_inners.php', '', 'code1'
			  ));
		
		
	
	}  
	
	 
	
	
	
	
	
	
/******************************************************************************************************/	
	
	//план-к
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 3);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 3, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Встречи',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'c.name', 'ss.note', 'cont.name', 'cont1.name', 'ss.result',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент', 'Город',   'Цель', 'Контакт','Контакт', 'Результат', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter3=1', 'code3'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 1);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 1, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Задачи',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join sched as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
				
				 ',
			 array('p.code', 'sup.full_name',     'cont.name',  'p.description',   'u1.name_s', 'u2.name_s'),
			 array('Номер',   'Контрагент',     'Контакт',  'Описание задачи', 'Постановщик', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched_task.php', 'action=1', 'id',
			  'shedule.php',  'doFilter1=1', 'code1'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 4);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 4, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Звонки',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'c.name', 'ss.note', 'cont.name', 'cont1.name', 'ss.result',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент', 'Город',   'Цель', 'Контакт','Контакт', 'Результат', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter4=1', 'code4'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 5);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 5, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Заметки',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'p.topic', 'p.description',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент',  'Тема', 'Текст', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter5=1', 'code5'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 2);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 2, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Командировки',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'c.name', 'ss.note', 'cont.name', 'cont1.name', 'ss.result',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент', 'Город',   'Цель', 'Контакт','Контакт', 'Результат', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter2=1', 'code2'
			  ));
			
	} 
	  
	  
	$do_it=(bool)(strlen(trim($_GET['data']))>=3);
	
	 $search=$_search->GetData(SecStr($_GET['data']), $do_it, $total);
	// print_r($search);
	
	 if($do_it){
			 //журнал событий 

		$log->PutEntry($result['id'],'выполнил Поиск по сайту',NULL,NULL,NULL,'поисковый запрос '.SecStr($_GET['data'])); 
	 }
		 
	 $sm1=new SmartyAdm;
	 $sm1->assign('data', $_GET['data']);
	 $sm1->assign('do_it', $do_it);
	 $sm1->assign('total', $total);
	 $sm1->assign('search', $search);
	 
	 
	 $content=$sm1->fetch('search.html');
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>