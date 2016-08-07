<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
 
 
require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');
 
require_once('../classes/acc_notesitem.php');
 
require_once('../classes/user_s_item.php');
 
 
require_once('../classes/period_checker.php');

require_once('../classes/messageitem.php');

require_once('../classes/lead.class.php');

/* письма от лидов по менеджерам

*/

 

//$sql='select distinct user_id from ';


$sql='select * from user where is_active=1 and id in( select distinct manager_id from lead where status_id=2)';

//echo $sql;

$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();


$users_to_send=array();
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	if(($f['is_in_vacation']==1)&&(($f['vacation_till_pdate']+24*60*60)>time())){
		//continue;	
	}
	$users_to_send[]=$f;
}

//print_r($users_to_send); die();


$topic='Новые лиды';
$_mi=new MessageItem; $_task=new Lead_Item;
foreach($users_to_send as $k1=>$user){
	
	$txt='<div>';
	$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='Уважаемый(ая) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div>&nbsp;</div>';
	
	
	$txt.='<div>';
	$txt.='<strong>У Вас появились следующие лиды:</strong>';
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div><ul>';
	
	$sql='select * from lead where status_id=2 and manager_id="'.$user['id'].'" ';
	
	$set1=new mysqlset($sql);
	$rs1=$set1->GetResult();
	$rc1=$set1->GetResultNumRows();
	
	if($rc1==0) continue;

	for($j=0; $j<$rc1; $j++){
		$g=mysqli_fetch_array($rs1);
		
		$txt.='<li>';
		$txt.='<a href="ed_lead.php?action=1&id='.$g['id'].'" target="_blank">'.$_task->ConstructFullName($g['id'], $g).'</a>';
		
		if($g['pdate_finish']!="") $txt.=',<strong> прогнозируемая дата заключения контракта:</strong><em> '.DateFromYmd($g['pdate_finish']).' '.$g['ptime_finish'].'</em>';
		
		 
		
		$txt.='</em></li>';
		
		 
		
		
		
	//	print_r($g);	
	}
	
	
	
	$txt.='</ul><div>&nbsp;</div>';
	
	$txt.='<div><strong>Просим своевременно взять в работу перечисленные лиды!</strong></div>';
	
	
	$txt.='<div>&nbsp;</div>';

	$txt.='<div>';
	$txt.='C уважением, программа "'.SITETITLE.'".';
	$txt.='</div>';
	
	
	
	//echo $txt;
	
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}

 

exit();
	
?>