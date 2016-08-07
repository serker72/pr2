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
require_once('../classes/tender.class.php');

/* сообщения руководителям от отказах  по обязательным действиям по тендерам
*/

 

//$sql='select distinct user_id from ';


$check_pdate1=datefromdmy(date('d.m.Y', time()))-24*60*60;
$check_pdate2=datefromdmy(date('d.m.Y', time()))-24*60*60+24*60*60-1;

//echo date('d.m.Y H:i:s', $check_pdate1);
//echo date('d.m.Y H:i:s', $check_pdate2);


$sql='select * from user where is_active=1 and id in( select distinct user_id from user_rights where object_id=936 and right_id=2)';

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

//найти тендеры, по которым есть отказы
$sql='select distinct l.id from tender as l
inner join action_log as al on al.affected_object_id=l.id
 where 
 
 (al.pdate between "'.$check_pdate1.'" and "'.$check_pdate2.'" )
 and (al.description="отказ сотрудника взять в работу тендер 3 раз подряд" or al.description="отказ сотрудника принять в работу тендер 3 раз подряд")
 and al.object_id=931
 ';
 
 //echo $sql;
 
 $leads=array();
 $set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();
 for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	$leads[]=$f['id'];
 }


//print_r($leads);

if(count($leads)>0){
//	echo 'zzzzzzzzzzz';

	$_ui=new UserSItem;
	$_lead=new Tender_Item;
	
	$topic='Внимание! Недобросоветсная работа сотрудников в разделе Тендеры';
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
		$txt.='<strong>Зафиксирована недобросоветсная работа сотрудников в разделе Тендеры:</strong>';
		$txt.='</div>';
		
		
		//найти 2 группы отказов:
		
		$sql='select  distinct l.id as id, al.user_subj_id as user_id, al.pdate,
		
		 u.name_s 
		from tender as l
		inner join action_log as al on al.affected_object_id=l.id
		left join user as u on u.id=al.user_subj_id
		 where 
		 
		 (al.pdate between "'.$check_pdate1.'" and "'.$check_pdate2.'" )
		 and (al.description="отказ сотрудника взять в работу тендер 3 раз подряд" )
		 and al.object_id=931
		  order by l.id, al.pdate
		 ';
		 
	//	 echo $sql;
		 
	 
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$txt.='<div>&nbsp;</div>';
		if($rc>0) $txt.='<div><strong>Отказ сотрудника взять в работу тендер 3 раз подряд:</strong></div><ul>';
		for($i=0; $i<$rc; $i++){
			$g=mysqli_fetch_array($rs);
			
 
			
			$txt.='<li>';
			$txt.='<a href="ed_tender.php?action=1&id='.$g['id'].'" target="_blank">'.$_lead->ConstructFullName($g['id']).'</a> - сотрудник '.$g['name_s'].', дата отказа '.date('d.m.Y H:i:s', $g['pdate']);
			 
			
			$txt.='</em></li>';
			
		}

		
		if($rc>0) $txt.='</ul><div>&nbsp;</div>';
		
		
		
		 
		$sql='select distinct l.id as id, al.user_subj_id as user_id, al.pdate,
		
		 u.name_s 
		from tender as l
		inner join action_log as al on al.affected_object_id=l.id
		left join user as u on u.id=al.user_subj_id
		 where 
		 
		 (al.pdate between "'.$check_pdate1.'" and "'.$check_pdate2.'" )
		 and (al.description="отказ сотрудника принять в работу тендер 3 раз подряд" )
		 and al.object_id=931
		 order by l.id, al.pdate
		 ';
		 
	//	 echo $sql;
		 
	 
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$txt.='<div>&nbsp;</div>';
		if($rc>0) $txt.='<div><strong>Отказ сотрудника принять в работу тендер 3 раз подряд:</strong></div><ul>';
		for($i=0; $i<$rc; $i++){
			$g=mysqli_fetch_array($rs);
			
 
			
			$txt.='<li>';
			$txt.='<a href="ed_tender.php?action=1&id='.$g['id'].'" target="_blank">'.$_lead->ConstructFullName($g['id']).'</a> - сотрудник '.$g['name_s'].', дата отказа '.date('d.m.Y H:i:s', $g['pdate']);;
			 
			
			$txt.='</em></li>';
			
		}

		
		if($rc>0) $txt.='</ul><div>&nbsp;</div>';
		
		
		
		
		 
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C уважением, программа "'.SITETITLE.'".';
		$txt.='</div>';
		
		
		
		//echo $txt;
		
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}

}

exit();
	
?>