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

require_once('classes/petitionallgroup.php');
require_once('classes/memoallgroup.php');
require_once('classes/an_petitions.php');
require_once('classes/an_memos.class.php');


require_once('classes/user_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Документооборот');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 

if(!$au->user_rights->CheckAccess('w',846)
&&!$au->user_rights->CheckAccess('w',848)
&&!$au->user_rights->CheckAccess('w',1137)

){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



if($print!=0){
	if(!$au->user_rights->CheckAccess('w',847)
	&&!$au->user_rights->CheckAccess('w',849)
	&&!$au->user_rights->CheckAccess('w',1138)
	){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчет Документооборот',NULL,846,NULL,NULL);	

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));


if($print==0) $smarty->display('top.html');
 
unset($smarty);


$_menu_id=68;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	//echo $tab_page; die();
	
//******************************************************************************************************************
//вкладка отчет Заявления - отпуска
	
	
	$an=new AnPetitions;
	$_pg=new PetitionAllGroup;
	$prefix='_3';
	$an->prefix=$prefix; 
	
	 
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('t.status_id',3, SqlEntry::NE));
	
	
	 
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['given_pdate1'.$prefix];
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['given_pdate2'.$prefix];
	
	
 
	
	$decorator->AddEntry(new UriEntry('given_pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('given_pdate2',$pdate2));
	
	 
	 
	
	 
	//фильтр по статусам
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	   
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('t.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	 
	
	 
	
	
	if(isset($_GET['kind_id'.$prefix])) {
		 
			
		  $selected_kind_ids=$_GET['kind_id'.$prefix];
		  
		 
		  
		  
	}else $selected_kind_ids=NULL;
	
	
	//постановщик
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new UriEntry('user_name',iconv('utf-8', 'windows-1251',$_GET['user_name'.$prefix])));
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr(iconv('utf-8', 'windows-1251',$_GET['user_name'.$prefix])), SqlEntry::LIKE));

		}else{
		
			$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
		}
	}
	
	 
	//отв сотр
	if(isset($_GET['otv_user_name'.$prefix])&&(strlen($_GET['otv_user_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new UriEntry('otv_user_name',iconv('utf-8', 'windows-1251',$_GET['otv_user_name'.$prefix])));
			$decorator->AddEntry(new SqlEntry('t.id','select petition_id from petition_user where user_id in(select id from user where name_s like "%'.SecStr(iconv('utf-8', 'windows-1251',$_GET['otv_user_name'.$prefix])).'%" or login like "%'.SecStr(iconv('utf-8', 'windows-1251',$_GET['otv_user_name'.$prefix])).'%")', SqlEntry::IN_SQL));

		}else{
			$decorator->AddEntry(new UriEntry('otv_user_name',$_GET['otv_user_name'.$prefix]));
			$decorator->AddEntry(new SqlEntry('t.id','select petition_id from petition_user where user_id in(select id from user where name_s like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%" or login like "%'.SecStr($_GET['otv_user_name'.$prefix]).'%")', SqlEntry::IN_SQL));
		}
	}
	
	
	 if(!$au->user_rights->CheckAccess('w',727)){
		//ограничения доступа, если нет прав полного доступа
		$decorator->AddEntry(new SqlEntry('t.id', NULL, SqlEntry::IN_VALUES, NULL,$_pg->GetAvailableDocIds($result['id'])));	
	}
	
	
	
	//сортировка
		if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('t.given_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.begin_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('vd.pdate',SqlOrdEntry::DESC));
			
			
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('t.given_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.begin_pdate',SqlOrdEntry::ASC));
			
			$decorator->AddEntry(new SqlOrdEntry('vd.pdate',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	
	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	
	
	$ships=$an->ShowPos( 
	 $selected_kind_ids,
	 $pdate1,
	 $pdate2,
	 array(1, 2, 3, 8), //0
	 "an_petitions/an_petitions".$print_add.".html", //1
	 $decorator, //2
	 false, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls,  //5
	 isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)), //6
	 $result, //7
	 $au->user_rights->CheckAccess('w',827), //8
	 $au->user_rights->CheckAccess('w',725), //9
	 $au->user_rights->CheckAccess('w',847), //10
	 true,
	 $au->user_rights->CheckAccess('w',829), //11
	 $au->user_rights->CheckAccess('w',830),  //12
	 $au->user_rights->CheckAccess('w',831),  //13
	 $au->user_rights->CheckAccess('w',832),  //14
	 
	 $au->user_rights->CheckAccess('w',828) //15
	 
	 );
	//$ships='Раздел находится в разработке.';
	
	$sm->assign('log',$ships);
	
	 
	
	
	$sm->assign('has_otp',$au->user_rights->CheckAccess('w',846));
	
	//фиксировать открытие отчета
	if(  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)) ){
		//$log->PutEntry($result['id'],'открыл Отчет Заявления - отпуска',NULL,846,NULL, NULL);	
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Отчет Заявления - отпуска',NULL,847,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Отчет Заявления - отпуска',NULL,846,NULL, NULL);	
	}
	
	
	
	
//************************************* вкладка Заявление - прочие *****************************************/
 
	
	
	$an=new AnPetitions;
	$prefix='_2';
	$an->prefix=$prefix; 
	
	 
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('t.status_id',3, SqlEntry::NE));
	
	
	 
	
	  
	
		if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['given_pdate1'.$prefix];
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['given_pdate2'.$prefix];
	
	 
	
	$decorator->AddEntry(new UriEntry('given_pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('given_pdate2',$pdate2));
	
 	
	 
	 
		//фильтр по статусам
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	   
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('t.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	
	if(isset($_GET['kind_id'.$prefix])) {
		 
			
		  $selected_kind_ids=$_GET['kind_id'.$prefix];
		  
		 
		  
		  
	}else $selected_kind_ids=NULL;
	
	
	
	//постановщик
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new UriEntry('user_name',iconv('utf-8', 'windows-1251',$_GET['user_name'.$prefix])));
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr(iconv('utf-8', 'windows-1251',$_GET['user_name'.$prefix])), SqlEntry::LIKE));

		}else{
		
			$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
		}
	}
	
	 


	
	
	 if(!$au->user_rights->CheckAccess('w',727)){
		//ограничения доступа, если нет прав полного доступа
		$decorator->AddEntry(new SqlEntry('t.id', NULL, SqlEntry::IN_VALUES, NULL,$_pg->GetAvailableDocIds($result['id'])));	
	}
	
	
	
	//сортировка
		if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.task_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.task_ptime',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('t.given_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('t.begin_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('vd.pdate',SqlOrdEntry::DESC));
			
			
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('t.given_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('t.begin_pdate',SqlOrdEntry::ASC));
			
			$decorator->AddEntry(new SqlOrdEntry('vd.pdate',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	
	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',2));
	
	
	
	$ships=$an->ShowPos( 
	 $selected_kind_ids,
	 $pdate1,
	 $pdate2,
	 array(4,5,6), //0
	 "an_petitions/an_petitions".$print_add.".html", //1
	 $decorator, //2
	 false, //3
	 $au->user_rights->CheckAccess('w',724), //4
	 $alls,  //5
	 isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)), //6
	 $result, //7
	 $au->user_rights->CheckAccess('w',827), //8
	 $au->user_rights->CheckAccess('w',725), //9
	 $au->user_rights->CheckAccess('w',849), //10
	 true,
	 $au->user_rights->CheckAccess('w',829), //11
	 $au->user_rights->CheckAccess('w',830),  //12
	 $au->user_rights->CheckAccess('w',831),  //13
	 $au->user_rights->CheckAccess('w',832),  //14
	 
	 $au->user_rights->CheckAccess('w',828) //15
	 
	 );
	//$ships='Раздел находится в разработке.';
	
	$sm->assign('log2',$ships);
	
	 
	
	
	 
	
	 
	
	$sm->assign('has_others',$au->user_rights->CheckAccess('w',848));
	
	
	
	
	
	//фиксировать открытие отчета
	if(  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)) ){
		//$log->PutEntry($result['id'],'открыл Отчет Заявления - прочие',NULL,848,NULL, NULL);	
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Отчет Заявления - прочие',NULL,849,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Отчет Заявления - прочие',NULL,848,NULL, NULL);	
	}
	
	
	
/******************************************************************************************************/
//вкладка Служебные записки	
	
		
		
	$an=new AnMemos;
	$prefix='3';
	 
	$_mg=new MemoAllGroup;
	 
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('t.status_id',3, SqlEntry::NE));
	
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	
	//№
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new UriEntry('code',iconv('utf-8', 'windows-1251', $_GET['code'.$prefix])));
			$decorator->AddEntry(new SqlEntry('t.code',abs((int)$_GET['code'.$prefix]), SqlEntry::LIKE));
		}else{
			$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
			$decorator->AddEntry(new SqlEntry('t.code',abs((int)$_GET['code'.$prefix]), SqlEntry::LIKE));
		}
	}
	
	 
	
	//срок выполнения
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	$decorator->AddEntry(new SqlEntry('t.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
	
 
	
	
	 
	 
		//фильтр по статусам
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	   
	}else{
		 
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('t.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	 
	//постановщик
	if(isset($_GET['user_name'.$prefix])&&(strlen($_GET['user_name'.$prefix])>0)){
		if(isset($_GET['print'])&&($_GET['print']==1)){
			$decorator->AddEntry(new UriEntry('user_name',iconv('utf-8', 'windows-1251',$_GET['user_name'.$prefix])));
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr(iconv('utf-8', 'windows-1251',$_GET['user_name'.$prefix])), SqlEntry::LIKE));

		}else{
		
			$decorator->AddEntry(new UriEntry('user_name',$_GET['user_name'.$prefix]));
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['user_name'.$prefix]), SqlEntry::LIKE));
		}
	}
	
 
	
	
	 if(!$au->user_rights->CheckAccess('w',733)){
		//ограничения доступа, если нет прав полного доступа
		$decorator->AddEntry(new SqlEntry('t.id', NULL, SqlEntry::IN_VALUES, NULL,$_mg->GetAvailableDocIds($result['id'])));	
	}
	
	
	
	//сортировка
		if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
		break;
		 
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::DESC));
		break;
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('t.pdate',SqlOrdEntry::ASC));
		break;	
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
		break;
		
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
		break;
		
		 
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('t.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	
	
	
	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',3));
	
	
	
	$ships=$an->ShowPos(  "an_petitions/an_memos".$print_add.".html", //0
	 $decorator, //1
	 $pagename='an_petitions.php', //2  
	 isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3)), //3
	 $au->user_rights->CheckAccess('w',1138), //4 
	 $au->user_rights->CheckAccess('w',731),  //5
	 $alls, //6
	 $result //7
	 
	 );
	
	 
	//$ships='Раздел находится в разработке.';
	
	$sm->assign('log3',$ships);
	
	 
	
	
	
	
		$sm->assign('has_memos',$au->user_rights->CheckAccess('w',1137));
	
	
	
	
	
	//фиксировать открытие отчета
	if(  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3)) ){
		//$log->PutEntry($result['id'],'открыл Отчет Служебные записки',NULL,1137,NULL, NULL);
		
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Отчет Служебные записки',NULL,1138,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Отчет Служебные записки',NULL,1137,NULL, NULL);		
	}
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_petitions/an_petitions_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else {
		//echo $content;
		
		$sm2=new SmartyAdm;
		
		//$content=$sm2->fetch('plan_pdf/pdf_header_lo.html').$content;
		
		
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
		 
		 
		/*if( isset($_GET['doSub_3'])||isset($_GET['doSub_3_x'])){
			$orient='--orientation Portrait';
			
		}else */$orient='--orientation Landscape ';
		
		
		 
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm ".$orient."  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
		
	//echo $comand;
		 
	 
		 
	 	exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Отчет_Документооборот.pdf'.'"');
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
 
unset($smarty);
?>