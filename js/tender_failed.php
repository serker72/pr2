<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/tender.class.php');
require_once('../classes/tender_history_fileitem.php');
require_once('../classes/tender_history_item.php');
require_once('../classes/tender_history_group.php');
require_once('../classes/docstatusitem.php');


 

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}


$_timeout=6*60;

$ret='';
if(isset($_GET['action'])&&($_GET['action']=="try_fail")){
	//перевод в статус УПУЩЕН
	
	$kind=abs((int)$_GET['kind']);
	
	
	
	$do_sync=false;
	
	//проверим МАРКЕР
	$sql='select * from tender_marker where kind_id='.$kind.' and user_id=0   and ptime>="'.(time()-$_timeout*60).'"';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	if($rc>0){
		//маркер есть, возвращаем -1, ничего не делаем
		$ret=-1;
		
		//echo ' HAS MARK ';
	}else{
		//маркера нет,  вносим новый маркер	
		 
		$_mark=new Tender_MarkerItem;
		$mark=$_mark->GetItemByFields(array('kind_id'=>$kind,'user_id'=>0 ));
		if($mark===false) $_mark->Add(array('kind_id'=>$kind, 'user_id'=>0, 'ptime'=>time()));
		else $_mark->Edit($mark['id'], array('ptime'=> time()));
		
		//echo ' NO MARK ';
		$do_sync=true;
	}
	
	
	
	
	
	
	//$do_sync=true;
 
	
	
	if($do_sync){
	//echo 'zzzzzzzzzzzz';
	$class=NULL;
	 
	switch($kind){
		/*case 1:
			$class=new BillGroup;
		break;
		case 2:
			$class=new ShIGroup;
		break;
		case 3:
			$class=new AccGroup;
		break;
		case 4:
			$class=new TrustGroup;
		break;
		case 5:
			$class=new PayGroup;
		break;
		case 6:
			$class=new IsGroup;
		break;
		case 7:
			$class=new WfGroup;
		break;
		case 8:
			$class=new KomplGroup;
		break;
		case 9:
			$class=new InvGroup;
		break;
		case 10:
			$class=new InvCalcGroup;
		break;
		case 11:
			$class=new BillInGroup;
		break;
		case 12:
			$class=new ShIInGroup;
		break;
		case 13:
			$class=new PayInGroup;
		break;*/
		case 10:
			$class=new Tender_Group;
		break;
		
		default:
			$class=new Tender_Group;
		break;
	};
	
	$class->AutoFail();
	}
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>