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



require_once('classes/an_kp.class.php');


require_once('classes/an_kp_rent.class.php');

require_once('classes/user_to_user.php');

require_once('classes/an_tele.class.php');
require_once('classes/an_tele_monitor.class.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Металлообработка 2015');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',967)&&!$au->user_rights->CheckAccess('w',969)&&!$au->user_rights->CheckAccess('w',1002)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);

if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',968)&&!$au->user_rights->CheckAccess('w',970)&&!$au->user_rights->CheckAccess('w',1003)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчет Тендеры/Лиды',NULL,967,NULL,NULL);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));

if($print==0) $smarty->display('top.html');
//else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=76;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	$sm1=new SmartyAdm;
	
	
	$sql_ids='select id
	from supplier 
	where is_customer=1 and id in(
		select distinct sc.supplier_id from sched_suppliers as sc inner join sched as s
		on s.id=sc.sched_id
		where s.kind_id in(2,3)
		and(
		    (
			s.kind_id=2 and(
			sc.note like "%Металлообработка 2015%"
			or sc.note like "%Металлообработка-2015%"
			or sc.note like "%Металлообработка- 2015%"
			or sc.note like "%Металлообработка -  2015%"
			or sc.note like "%Металлообработка-2015%"
			or sc.note like "%Металлообработка2015%"
			 )
			)
			or 
			(
			s.kind_id=3 and s.meet_id=1 and(
				s.meet_value like "%Металлообработка 2015%"
				or s.meet_value like "%Металлообработка-2015%"
				or s.meet_value like "%Металлообработка- 2015%"
				or s.meet_value like "%Металлообработка -  2015%"
				or  s.meet_value like "%Металлообработка-2015%"
				or s.meet_value like "%Металлообработка2015%"
				 
			)
			)
			
		)
	)
	';
	
	//echo $sql_ids;
	
	$sql_kps='select p.*, 

					
					 sp.full_name as supplier_name,  
					spo.name as opf_name, 
					c.name, c.position, 
					
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					pk.name as price_kind_name,
					sd.name as supply_pdate_name,
					st.name as status_name,
					
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					
					umn.id as user_manager_id, umn.name_s as  user_manager_name, umn.login as user_manager_login,
					war.name as war_name,
					pm.name as pay_name, pm.pred, pm.pered_otgr, pm.pnr
					
					 
					
				from kp as p
					
					 left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
					left join user as u on p.user_confirm_price_id=u.id
					left join user as mn on p.manager_id=mn.id
					left join user as umn on p.user_manager_id=umn.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					left join kp_supply_pdate_mode as sd on p.supply_pdate_id=sd.id
					left join document_status as st on st.id=p.status_id
					left join supplier_contact as c on p.contact_id=c.id
					
				 	left join  kp_warranty as war on war.id=p.warranty_id
					left join  kp_paymode as pm on pm.id=p.paymode_id
				
				where p.supplier_id in('.$sql_ids.')
				order by p.id
					';
	
	//echo $sql_kps;
	$kps=array();
	$set=new mysqlSet($sql_kps); //,$to_page, $from,$sql_count);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	

require_once('classes/kp_supply_item.php');

require_once('classes/pl_proditem.php');
require_once('classes/pl_positem.php');
	
	$item=new KpItem;
	$_basis=new KpSupplyItem;
	$_pos=new PosItem;
		$_prod=new PlProdItem;
		
		$_pdm=new PlDisMaxValGroup;
	
	for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			if($f['supply_pdate']>0) $f['supply_pdate']=date("d.m.Y",$f['supply_pdate']);
			else $f['supply_pdate']='-';
			
			if($f['valid_pdate']>0) $f['valid_pdate']=date("d.m.Y",$f['valid_pdate']);
			else $f['valid_pdate']='-';
			
			
			
			$f['eq_name_sort']='';
			$positions=$item->GetPositionsArr($f['id'], false);
			
			$prods=array();
			foreach($positions as $k=>$v){
				if($v['parent_id']==0){
					$pos=$_pos->GetItemById($v['id']);
					$prod=$_prod->GetItemById($pos['producer_id']);
					if(!in_array($prod['name'], $prods)) $prods[]=$prod['name'];
					$f['eq_name_sort']=$pos['name'];
				}
			}
			
			$f['prods']=$prods;
			$f['sp_name_sort']=$prods[0];
			
			
			//подтянуть позиции без опций, вывести их в форму
			$f['positions']=$positions;
			
			$f['total_cost']=$item->CalcCost($f['id'], $positions,  $result);
			$f['currency']=$item->currency;
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			//есть ли печатная форма...
			$_pl=new PlPosItem;
			$main_id=0; $txt_kp=''; $install_mode=0; $pl_discount_id=0; $pl_discount_value=0;
			foreach($positions as $k=>$v){
				if($v['parent_id']==0){
					$main_id=$v['pl_position_id'];
					$txt_kp=$v['txt_for_kp'];
					$pl_discount_id=$v['pl_discount_id'];
					$pl_discount_value=$v['pl_discount_value'];
					
					break;	
				}
			}
			foreach($positions as $k=>$v){	
				$pl=$_pl->GetItemById($v['pl_position_id']);
				if($pl['is_install']==1) $install_mode=1;
			}	
			
			$pl=$_pl->GetItemById($main_id);
			$_kp_form=new KpFormItem;
			$kp_form=$_kp_form->GetItemById($pl['kp_form_id']);
			$f['has_print_form']=(($kp_form!==false)&&(strlen($txt_kp)>0));
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			
		 
			 	
		 
			
			//до п поля
			//$pl
			$basis=$_basis->GetItemByFields(array('producer_id'=>$pl['producer_id'], 'price_kind_id'=>$f['price_kind_id']));
			$f['basis']=$basis['name'];
			$f['install_mode']=$install_mode;
			
			//$f['dis_in_card']=$_pdm->GetItemsByKindPosArr($main_id, $f['price_kind_id'],0,$this->_auth_result['id']); 
			$f['pl_discount_id']=$pl_discount_id;
			$f['pl_discount_value']=$pl_discount_value;
			
		 
		$kps[]=$f;		
	}
	
	$sql_leads='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		 eq.name as eq_name, kind.name as kind_name,
		 tender.code as tender_code,
		 
		 cur.name as currency_name, cur.signature as currency_signature,
		 prod.name as producer_name
		 
					 
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
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join pl_producer as prod on p.producer_id=prod.id
			where sup.id in('.$sql_ids.')
				order by p.id	 	 
				 ';
				
	//echo $sql_leads;
	$leads=array();
	 
	$set=new mysqlSet($sql_leads); //,$to_page, $from,$sql_count);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	
	$_sg=new Lead_SupplierGroup;
	
	for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//просрочено или нет
			/*
			статус != 10 !=3 !=1
			и крайний срок !=null <now
			*/
			$expired=false;
			$exp_ptime=NULL;
			if($f['pdate_finish']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_finish']))+24*60*60-1 + (int)substr($f['ptime_finish'], 0,2)*60*60 + (int)substr($f['ptime_finish'],3,2)*60;
				
			 
				 
//				echo date('d.m.Y H:i:s', $exp_ptime).'<br>';
			}
			
			if(
			
			($f['status_id']!=10) && ($f['status_id']!=3) && ($f['status_id']!=1) && ($f['status_id']!=30) && ($f['status_id']!=31) && ($f['status_id']!=32) && ($f['status_id']!=36)
			
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			

			 
		//	$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
		
			
			if($f['pdate_finish']!=="") $f['pdate_finish']=DateFromYmd($f['pdate_finish']);
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-';
			 
				$_res=new Lead_Resolver();
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			 
			
			
			 
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			 
			  
			$f['max_price_formatted']=number_format($f['max_price'],2,'.',' ');//sprintf("", $f['max_price']);
			
			  
			$leads[]=$f;
		}
	
	 
	$sm1->assign('items',$kps);
	$sm1->assign('items1',$leads);
//	$sm1->assign('items2',$ыгзздш);
	
	
	
	
	$sm->assign('log', $sm1->fetch('an_met/an_met.html'));
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_met/an_met_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else {
		//echo $content;
		
		$sm2=new SmartyAdm;
		
		$content=$sm2->fetch('plan_pdf/pdf_header_lo.html').$content;
		
		
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		
		//скомпилируем подвал
		$sm=new SmartyAdm;
		$sm->assign('print_pdate', date("d.m.Y H:i:s"));
			//$username=$result['login'];
			$username=stripslashes($result['name_s']); //.' '.$username;	
			$sm->assign('print_username',$username);
		$foot=$sm->fetch('plan_pdf/pdf_footer_unlogo.html');
		$ftmp='f'.time();
		
		$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
		fputs($f, $foot);
		fclose($f);
		
		//die();
		 
		 
		if( isset($_GET['doSub_3'])||isset($_GET['doSub_3_x'])){
			$orient='--orientation Portrait';
			
		}else $orient='--orientation Landscape ';
		
		
		 
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm ".$orient."  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
		
	//echo $comand;
		 
	 
		 
	 	exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Эффективность_Металлообработка_2015.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	 
		
	
	
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/'.$ftmp.'.html');  
		 
		exit;
	}
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
//else $smarty->display('bottom_print.html');
unset($smarty);
?>