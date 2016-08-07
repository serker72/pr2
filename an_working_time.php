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

 

require_once('classes/user_to_user.php');

require_once('classes/an_working_time.class.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Время обработки документов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',1059)&&!$au->user_rights->CheckAccess('w',1060)){
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
	if(!$au->user_rights->CheckAccess('w',1060)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчет Время обработки документов',NULL,1059,NULL,NULL);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->assign('do_restrict', !in_array($result['id'], array(1,2,3))&&($print==1));

if($print==0) $smarty->display('top.html');
//else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=78;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
//******************************************************************************************************************
//вкладка отчет Тендеры
 
	
	$an1= new AnWorkingTime;
	
	$prefix='_wt';
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	 
	
	
	$decorator->AddEntry(new UriEntry('prefix',$prefix));

	$decorator->AddEntry(new UriEntry('print',$print));
	
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	
	
	if(!isset($_GET['pdate_1'.$prefix])){
	 		$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	if(!isset($_GET['pdate_2'.$prefix])){
			$_pdate2=DateFromdmY(date("d.m.Y"));//+60*60*24*30*3;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];

	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	$decorator->AddEntry(new SqlEntry('p.pdate',  DateFromdmY($pdate1), SqlEntry::BETWEEN, DateFromdmY($pdate2)+24*60*60-1));
	 
	
	
	 
	
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
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	//вид
	$doc_kinds=array();
	
	$cou_stat=0;   
	if(isset($_GET[$prefix.'kinds'])&&is_array($_GET[$prefix.'kinds'])) $cou_stat=count($_GET[$prefix.'kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $doc_kinds=$_GET[$prefix.'kinds'];
	     
	}else{
		 $doc_kinds=array(0,1,2,3,4,5);
		 $decorator->AddEntry(new UriEntry('all_kinds',1));
		
	}
	
	if(count($doc_kinds)>0){
		$of_zero=true; foreach($doc_kinds as $k=>$v) if($v>-1) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_kinds',1));
		}else{
		
			foreach($doc_kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kind_'.$v,1));
			foreach($doc_kinds as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'kinds[]',$v));
			
			 
		}
	}
	
	
	//отдел
	$deps=array();
	
	$cou_stat=0;   
	if(isset($_GET[$prefix.'kinds'])&&is_array($_GET[$prefix.'deps'])) $cou_stat=count($_GET[$prefix.'deps']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $deps=$_GET[$prefix.'deps'];
	   
	}else{
		 $deps=array(0,1,2,3,4,5,6);
		 $decorator->AddEntry(new UriEntry('all_deps',1));
	}
	
	if(count($deps)>0){
		$of_zero=true; foreach($deps as $k=>$v) if($v>-1) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_deps',1));
		}else{
		
			foreach($deps as $k=>$v) $decorator->AddEntry(new UriEntry('dep_'.$v,1));
			foreach($deps as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'deps[]',$v));
			
			 
		}
	}
	 
	
	
	//фильтры по отв сотруднику
	 if(isset($_GET['manager_name'.$prefix])&&(strlen(SecStr($_GET['manager_name'.$prefix]))>0)){
		$_users1=explode(';', $_GET['manager_name'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('manager_name',  $_GET['manager_name'.$prefix]));
	}
	 
	 
	 
	//расширенная форма
	
	  if(isset($_GET['has_content'.$prefix])&&($_GET['has_content'.$prefix]==1)){
	 	$decorator->AddEntry(new UriEntry('has_content', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_content', 0));
	 
	 
	 
	 
	//сортировка данных
	if(!isset($_GET['sortmode'.$prefix])||(strlen($_GET['sortmode'.$prefix])==0)){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	//$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
	
		
		
 	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('3',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('3',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('10',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('10',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('9',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('9',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('4',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('4',SqlOrdEntry::ASC));
		break;
	 
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('8',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('8',SqlOrdEntry::ASC));
		break;
	 
		 
		
		default:
				
			$decorator->AddEntry(new SqlOrdEntry('1',SqlOrdEntry::DESC));
			 
		break;	
		
	}
	
	 
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	 
	 
	
	$filetext=$an1->ShowData( 
		'an_wt/an_wt'.$print_add.'.html', $doc_kinds, $deps,
		$decorator, 
		'an_working_time.php', 
		isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)), 
		$au->user_rights->CheckAccess('w',1060),  
		$data,
		$result);
	
	 
	 
		$sm->assign('log',$filetext);
	 
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		 
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Отчет Время обработки документов',NULL,1060,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Отчет Время обработки документов',NULL,1059,NULL, NULL);		
	}
	
	
	$sm->assign('has_wt',$au->user_rights->CheckAccess('w',1059));	
	
	
	
	
	
	
	 
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_wt/an_wt_form'.$print_add.'.html');
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else {
		//echo $content; die();
		
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
		 
		 
		//if( isset($_GET['doSub_3'])||isset($_GET['doSub_3_x'])){
			$orient='--orientation Portrait';
			
		//}else $orient='--orientation Landscape ';
		
		
		 
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 10mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm ".$orient."  --footer-html ".SITEURL."/tmp/".$ftmp.".html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
		
	//echo $comand;
		 
	 
		 
	 	exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Отчет_Время_обработки_документов.pdf'.'"');
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