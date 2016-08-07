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


require_once('classes/an_sched.php');
require_once('classes/an_sched_su.php');

require_once('classes/an_sched_newcli.php');

require_once('classes/an_sched_suresp.php');

require_once('classes/sched.class.php');

require_once('classes/an_sched_forgotten.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Планировщик');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=1;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);


 

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',903)&&!$au->user_rights->CheckAccess('w',903)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}
 
	
	 

	 
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	 
	
	
	
	
	$sm=new SmartyAdm;
	 
	 
	 
	$sm1=new SmartyAdm; 
	$alls=array();
	
	$sql='
	SELECT cg.name AS prod_name, g.name AS group_name, c.name,
	plp.price, curr.signature, pnr.name as pnr_name
	
FROM catalog_position AS c
inner join pl_position as p on p.position_id=c.id
LEFT JOIN pl_producer AS cg ON c.producer_id = cg.id
LEFT JOIN catalog_group AS g ON c.group_id = g.id

inner join catalog_position AS pnr on pnr.parent_id=c.id and pnr.is_install=1
inner join pl_position_price as plp on plp.pl_position_id=pnr.id and plp.price_kind_id=3 and plp.currency_id=cg.currency_id
left join pl_currency as curr on curr.id=cg.currency_id


WHERE c.parent_id =0
and c.is_active=1
 

ORDER BY cg.name, c.name
'; 
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		$alls[]=$f;	
	}
	
	$sm1->assign('items', $alls);
	
	$filetext=$sm1->fetch('an_pnr.html');
	
	// echo $filetext;
	 
	$sm->assign('log1'.$prefix,$filetext);
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',1);
	
	$content=$sm->fetch('an_sched/an_sched_form_print.html');
	
	
	
 
		
		//echo $content; die();
		
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
		$foot=$sm->fetch('plan_pdf/pdf_footer_lo.html');
		$ftmp='f'.time();
		
		$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
		fputs($f, $foot);
		fclose($f);
		
		//die();
		
		if( isset($_GET['doSub6'])||isset($_GET['doSub6_x'])){
			$orient='--orientation Landscape ';
		}else $orient='--orientation Portrait';
		
		 
		
		//$comand = "wkhtmltopdf-i386  --load-error-handling skip --encoding windows-1251 --page-size A4 ".$orient." --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf 1>".ABSPATH.'/tmp/'."data 2> ".ABSPATH.'/tmp/'."exit";
		
		$comand = "wkhtmltopdf-i386  --load-error-handling skip --encoding windows-1251 --page-size A4 ".$orient." --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".ABSPATH."/tmp/".$ftmp.".html ".ABSPATH.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf 1>".ABSPATH.'/tmp/'."data 2> ".ABSPATH.'/tmp/'."exit";
		
		
	//echo $comand;
		 
	 
		 
	 	exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Отчет_ПНР.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	 
		
	
	
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/'.$ftmp.'.html');  
		 
		exit;
		
	 
	unset($smarty);

 
?>