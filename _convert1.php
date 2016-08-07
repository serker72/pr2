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


require_once('classes/user_s_item.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Конвертация КП');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	//include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$sql='select * from kp where price_kind_id=0 and status_id<>3';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		
		$sql1='select * from kp_position where kp_id='.$f['id'].' and price_kind_id<>3 and parent_id=0 limit 1';
		$set1=new mysqlset($sql1);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		if($rc1>0){
			$g=mysqli_fetch_array($rs1);
			
			new Nonset('update kp set  price_kind_id='.$g['price_kind_id'].' where id='.$f['id']);
			
			echo $f['code'].' - '.$g['price_kind_id'].'<br>';		
		}
		
			
	}
	

$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>