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


require_once('classes/an_plan_fact_sales.class.php');


require_once('classes/user_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет План/факт продаж');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',789)){
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
	if(!$au->user_rights->CheckAccess('w',790)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчет План/факт продаж',NULL,789,NULL,NULL);	

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));


if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);


$_menu_id=66;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
//******************************************************************************************************************
//вкладка отчет ПФ
	
	
	$an=new AnPlanFactSales;
	$prefix='_1';
	$decorator=new DBDecorator; //для формирования ссылок и заполнения полей
	
	$dec1=new DBDecorator; //отдел, сотр-к
	$dec2=new DBDecorator; //год, месяц, валюта
	$dec3=new DBDecorator; //факт продаж - все фильтры
			
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	
	
	if(isset($_GET['department_id'.$prefix])&&(is_array($_GET['department_id'.$prefix]))){
		//$decorator->AddEntry(new UriEntry('department_id',$_GET['department_id'.$prefix]));
		//$dec1->AddEntry(new SqlEntry('p.department_id',SecStr($_GET['department_id'.$prefix]), SqlEntry::E));		 
		
		$deps=array();
		foreach($_GET['department_id'.$prefix] as $k=>$v){
			 $decorator->AddEntry(new UriArrEntry('department_id',$v));
			 if($v!=0) $deps[]=$v;
			 //$dec1->AddEntry(new UriArrEntry('department_id',$v));
			 //$dec1->AddEntry(new SqlEntry('p.department_id',$v, SqlEntry::E));
		}
		
		if(count($deps)>0){
			$dec1->AddEntry(new SqlEntry('p.department_id',NULL, SqlEntry::IN_VALUES,NULL,$deps));
			
		}
	} 
	
	//валюта
	if(isset($_GET['currency_id'.$prefix])&&(abs((int)$_GET['currency_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('currency_id',$_GET['currency_id'.$prefix]));
		$dec2->AddEntry(new UriEntry('currency_id',$_GET['currency_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	}else{
		$decorator->AddEntry(new UriEntry('currency_id',CURRENCY_DEFAULT_ID));
		$dec2->AddEntry(new UriEntry('currency_id',CURRENCY_DEFAULT_ID));
	}
	
	//год
	if(isset($_GET['year'.$prefix])&&(abs((int)$_GET['year'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('year',$_GET['year'.$prefix]));
		$dec2->AddEntry(new UriEntry('year',$_GET['year'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	}else{
		$decorator->AddEntry(new UriEntry('year',date('Y')));
		$dec2->AddEntry(new UriEntry('year',date('Y')));
	}
	
	
	
	
	
	//сотрудник
	
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $dec1->AddEntry(new SqlEntry('p.name_s',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['user_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('user_name',iconv("utf-8","windows-1251",$_GET['user_name'.$prefix])));
		}else{
		  $dec1->AddEntry(new SqlEntry('p.name_s',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['user_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
			
		}
	}
			//ограничения по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		
		$dec1->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	//print_r($limited_user);
	
	
	//месяцы
	if(isset($_GET['month'.$prefix])&&(is_array($_GET['month'.$prefix]))){
		foreach($_GET['month'.$prefix] as $k=>$v){
			 $decorator->AddEntry(new UriArrEntry('month',$v));
			 $dec2->AddEntry(new UriArrEntry('month',$v));
		}
	}else{
		for($i=1; $i<=12; $i++){
			 $decorator->AddEntry(new UriArrEntry('month',$i));
			 $dec2->AddEntry(new UriArrEntry('month',$i));
		}
	}
	
	//станок
	if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $dec3->AddEntry(new SqlEntry('eq_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('eq_name',iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
		 
		}else{
		  $dec3->AddEntry(new SqlEntry('eq_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['eq_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));
	 
			
		}
	}
	
	
	if(isset($_GET['eq_is_new'.$prefix])){
		 $decorator->AddEntry(new UriEntry('eq_is_new',$_GET['eq_is_new'.$prefix]));
		 if($_GET['eq_is_new'.$prefix]==1) $dec3->AddEntry(new SqlEntry('eq_is_new',1, SqlEntry::E));	
		 elseif($_GET['eq_is_new'.$prefix]==0) $dec3->AddEntry(new SqlEntry('eq_is_new',0, SqlEntry::E));	
	}else{
		 $decorator->AddEntry(new UriEntry('eq_is_new',2));
	}
	
	
	if(isset($_GET['price_kind_id'.$prefix])&&(abs((int)$_GET['price_kind_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id'.$prefix]));
		$dec3->AddEntry(new SqlEntry('price_kind_id',abs((int)$_GET['price_kind_id'.$prefix]), SqlEntry::E));
		//$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	//клиент
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		
		  $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		  $dec3->AddEntry(new SqlEntry('supplier_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]))))); 
		}else{
		
		  $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		  $dec3->AddEntry(new SqlEntry('supplier_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['supplier_name'.$prefix]))));
			
		}
	}
	
	if(isset($_GET['supplier_is_new'.$prefix])){
		 $decorator->AddEntry(new UriEntry('supplier_is_new',$_GET['supplier_is_new'.$prefix]));
		  if($_GET['supplier_is_new'.$prefix]==1) $dec3->AddEntry(new SqlEntry('supplier_is_new',1, SqlEntry::E));	
	}else{
		 $decorator->AddEntry(new UriEntry('supplier_is_new',0));
	}
	
	//города...
	$cities=array();
	foreach($_GET as $k=>$v){
		if(eregi('^city_selected_', $k)){
				$decorator->AddEntry(new UriEntry('city_selected_'.$v,$v));
				$cities[]=$v;
		}
	}
	
	if(count($cities)>0){
		$dec3->AddEntry(new SqlEntry('city_id',NULL, SqlEntry::IN_VALUES,NULL,$cities));
		
	}
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$dec1->AddEntry(new SqlOrdEntry('dep.name',SqlOrdEntry::ASC));
	
	$dec1->AddEntry(new SqlOrdEntry('p.name_s',SqlOrdEntry::ASC));
	
	
	//echo 'zzzzzzzzzzzzzzzzzzzzzzzzz';
	
	
	$filetext=$an->ShowData($result['org_id'], 
	 
	  $prefix,
	  'an_plan_fact_sales/an_plan_fact_sales'.$print_add.'.html',
	  $decorator,
	   $dec1,
	   $dec2,
	   $dec3,
	  'an_plan_fact_sales.php',
	  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)),
	  $au->user_rights->CheckAccess('w',790), 
	  $result,
	  $au->user_rights->CheckAccess('w',784)
	  );
	
	//$filetext='<em>Извините, У Вас недостаточно прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log',$filetext);
	
	
	$sm->assign('has_kp',$au->user_rights->CheckAccess('w',789));
	
	//фиксировать открытие отчета
	if(  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)) ){
		$log->PutEntry($result['id'],'открыл Отчет План/факт продаж (сводный)',NULL,789,NULL, NULL);	
	}
	
	
	
	
//************************************* вкладка Дополнительные опции *****************************************/

	
	
	
	$an=new AnPlanFactSales;
	$prefix='_2';
	$decorator=new DBDecorator; //для формирования ссылок и заполнения полей
	
	$dec1=new DBDecorator; //отдел, сотр-к
	$dec2=new DBDecorator; //год, месяц, валюта
	$dec3=new DBDecorator; //факт продаж - все фильтры
			
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	
	
	if(isset($_GET['department_id'.$prefix])&&(is_array($_GET['department_id'.$prefix]))){
		//$decorator->AddEntry(new UriEntry('department_id',$_GET['department_id'.$prefix]));
		//$dec1->AddEntry(new SqlEntry('p.department_id',SecStr($_GET['department_id'.$prefix]), SqlEntry::E));		 
		
		$deps=array();
		foreach($_GET['department_id'.$prefix] as $k=>$v){
			 $decorator->AddEntry(new UriArrEntry('department_id',$v));
			 if($v!=0) $deps[]=$v;
			 //$dec1->AddEntry(new UriArrEntry('department_id',$v));
			 //$dec1->AddEntry(new SqlEntry('p.department_id',$v, SqlEntry::E));
		}
		
		if(count($deps)>0){
			$dec1->AddEntry(new SqlEntry('p.department_id',NULL, SqlEntry::IN_VALUES,NULL,$deps));
			
		}
	} 
	
	//валюта
	if(isset($_GET['currency_id'.$prefix])&&(abs((int)$_GET['currency_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('currency_id',$_GET['currency_id'.$prefix]));
		$dec2->AddEntry(new UriEntry('currency_id',$_GET['currency_id'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	}else{
		$decorator->AddEntry(new UriEntry('currency_id',CURRENCY_DEFAULT_ID));
		$dec2->AddEntry(new UriEntry('currency_id',CURRENCY_DEFAULT_ID));
	}
	
	//год
	if(isset($_GET['year'.$prefix])&&(abs((int)$_GET['year'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('year',$_GET['year'.$prefix]));
		$dec2->AddEntry(new UriEntry('year',$_GET['year'.$prefix]));
		//$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	}else{
		$decorator->AddEntry(new UriEntry('year',date('Y')));
		$dec2->AddEntry(new UriEntry('year',date('Y')));
	}
	
	
	
	
	
	//сотрудник
	
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $dec1->AddEntry(new SqlEntry('p.name_s',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['user_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('user_name',iconv("utf-8","windows-1251",$_GET['user_name'.$prefix])));
		}else{
		  $dec1->AddEntry(new SqlEntry('p.name_s',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['user_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
			
		}
	}
	
	
	//месяцы
	if(isset($_GET['month'.$prefix])&&(is_array($_GET['month'.$prefix]))){
		foreach($_GET['month'.$prefix] as $k=>$v){
			 $decorator->AddEntry(new UriArrEntry('month',$v));
			 $dec2->AddEntry(new UriArrEntry('month',$v));
		}
	}else{
		for($i=1; $i<=12; $i++){
			 $decorator->AddEntry(new UriArrEntry('month',$i));
			 $dec2->AddEntry(new UriArrEntry('month',$i));
		}
	}
	
	//станок
	if(isset($_GET['eq_name'.$prefix])&&(strlen($_GET['eq_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		  $dec3->AddEntry(new SqlEntry('eq_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix]))))); 
		  $decorator->AddEntry(new UriEntry('eq_name',iconv("utf-8","windows-1251",$_GET['eq_name'.$prefix])));
		 
		}else{
		  $dec3->AddEntry(new SqlEntry('eq_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['eq_name'.$prefix])))); 
		  $decorator->AddEntry(new UriEntry('eq_name',$_GET['eq_name'.$prefix]));
		//  echo 'zzz';
		 
			
		}
	}
	
	
	if(isset($_GET['eq_is_new'.$prefix])){
		 $decorator->AddEntry(new UriEntry('eq_is_new',$_GET['eq_is_new'.$prefix]));
		 if($_GET['eq_is_new'.$prefix]==1) $dec3->AddEntry(new SqlEntry('eq_is_new',1, SqlEntry::E));	
		 elseif($_GET['eq_is_new'.$prefix]==0) $dec3->AddEntry(new SqlEntry('eq_is_new',0, SqlEntry::E));	
	}else{
		 $decorator->AddEntry(new UriEntry('eq_is_new',2));
	}
	
	
	if(isset($_GET['price_kind_id'.$prefix])&&(abs((int)$_GET['price_kind_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('price_kind_id',$_GET['price_kind_id'.$prefix]));
		$dec3->AddEntry(new SqlEntry('price_kind_id',abs((int)$_GET['price_kind_id'.$prefix]), SqlEntry::E));
		//$decorator->AddEntry(new SqlEntry('p.price_kind_id',SecStr($_GET['price_kind_id'.$prefix]), SqlEntry::E));		 
	} 
	
	//клиент
	/*if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
		
		  $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		  $dec3->AddEntry(new SqlEntry('supplier_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]))))); 
		}else{
		
		  $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		  $dec3->AddEntry(new SqlEntry('supplier_name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['supplier_name'.$prefix]))));
			
		}
	}*/
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier_name'.$prefix]);
		 
		$dec3->AddEntry(new SqlEntry('supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));	
		$decorator->AddEntry(new UriEntry('supplier_name',  $_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['supplier_is_new'.$prefix])){
		 $decorator->AddEntry(new UriEntry('supplier_is_new',$_GET['supplier_is_new'.$prefix]));
		  if($_GET['supplier_is_new'.$prefix]==1) $dec3->AddEntry(new SqlEntry('supplier_is_new',1, SqlEntry::E));	
	}else{
		 $decorator->AddEntry(new UriEntry('supplier_is_new',0));
	}
	
	//города...
	$cities=array();
	foreach($_GET as $k=>$v){
		if(eregi('^city_selected_', $k)){
				$decorator->AddEntry(new UriEntry('city_selected_'.$v,$v));
				$cities[]=$v;
		}
	}
	
	if(count($cities)>0){
		$dec3->AddEntry(new SqlEntry('city_id',NULL, SqlEntry::IN_VALUES,NULL,$cities));
		
	}
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$dec1->AddEntry(new SqlOrdEntry('dep.name',SqlOrdEntry::ASC));
	
	$dec1->AddEntry(new SqlOrdEntry('p.name_s',SqlOrdEntry::ASC));
	
	
	//echo 'zzzzzzzzzzzzzzzzzzzzzzzzz';
	
	
	$filetext=$an->ShowData($result['org_id'], 
	 
	  $prefix,
	  'an_plan_fact_sales/an_plan_fact_sales_optional'.$print_add.'.html',
	  $decorator,
	   $dec1,
	   $dec2,
	   $dec3,
	  'an_plan_fact_sales.php',
	  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)),
	  $au->user_rights->CheckAccess('w',790), 
	  $result,
	  $au->user_rights->CheckAccess('w',784)
	  );
	
	//$filetext='<em>Извините, У Вас недостаточно прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log2',$filetext);
	
	
	$sm->assign('has_kp',$au->user_rights->CheckAccess('w',789));
	
	
	
	
	
	//фиксировать открытие отчета
	if(  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)) ){
		$log->PutEntry($result['id'],'открыл Отчет План/факт продаж (дополнительные опции)',NULL,789,NULL, NULL);	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_plan_fact_sales/an_plan_fact_sales_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>