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
require_once('../classes/holy_dates.php');
require_once('../classes/messageitem.php');

require_once('../classes/tender.class.php');

/* письма по тендерам по задачам дл€:
ответственного
исполнител€
наблюдател€

*/

/*
письма о просроченных тендерах.
*/ 

//$sql='select distinct user_id from ';

$check_pdate=date('Y-m-d', time()+5*24*60*60);


$sql='select * from user where is_active=1 and id in( select distinct p.manager_id from tender as p where  p.is_confirmed=1 and p.status_id in(33, 2, 28) and p.pdate_claiming<="'.$check_pdate.'" )';

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


$topic='¬нимание! ” ¬ас есть просроченные тендеры!';
$_mi=new MessageItem; $_task=new Tender_Item; 
foreach($users_to_send as $k1=>$user){
	
	$txt='<div>';
	$txt.='<em>ƒанное сообщение сгенерировано автоматически.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='”важаемый(а€) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div>&nbsp;</div>';
	
	
	$txt.='<div>';
	$txt.='<strong>” ¬ас есть просроченные тендеры:</strong>';
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div><ul>';
	
	$sql=
	'select 
		 distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		eq.name as eq_name, kind.name as kind_name,
		cur.name as currency_name, cur.signature as currency_signature,
		fz.name as fz_name 
					 
				from tender as p
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
				
				left join tender_fz as fz on p.fz_id=fz.id
		
		
		
		where  p.is_confirmed=1 and p.status_id in(33, 2, 28) and p.pdate_claiming<="'.$check_pdate.'" and p.manager_id="'.$user['id'].'" order by p.code desc
	
	';
	
	//echo $sql;
	
	$set1=new mysqlset($sql);
	$rs1=$set1->GetResult();
	$rc1=$set1->GetResultNumRows();
	
	

	for($j=0; $j<$rc1; $j++){
		$g=mysqli_fetch_array($rs1);
		
		$txt.='<li>';
		$txt.='<a href="ed_tender.php?action=1&id='.$g['id'].'" target="_blank">'.$_task->ConstructFullName($g['id'], $g).'</a>';
		
		if($g['pdate_claiming']!="") $txt.=',<strong> срок подачи за€вок:</strong><em> '.DateFromYmd($g['pdate_claiming']).' '.$g['ptime_claiming'].'</em>';
		
	 
		
		$txt.='</em></li>';
		 
		
		
		
	//	print_r($g);	
	}
	
	
	
	$txt.='</ul><div>&nbsp;</div>';
	
	//$txt.='<div><strong>ѕросим своевременно выполн€ть все поставленные задачи!</strong></div>';
	
	
	$txt.='<div>&nbsp;</div>';

	$txt.='<div>';
	$txt.='C уважением, программа "'.SITETITLE.'".';
	$txt.='</div>';
	
	
	
	//echo $txt;
	
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}



/*
письма о просроченных задачах.
найти всех: ответственных, соисполнителей, у кого задача просрочена: есть перва€ галочка, нет второйо галочки.

по каждому сотруднику: найти просрочнные задачи, в которых он: отв, соисп-ль.

по каждой задаче расписать фулл нейм, рол(и) сотрудника



*/ 
/*
$sql='
(
select * from user where is_active=1 and id in( select distinct user_id from sched_task_users where kind_id in(2,3) and sched_id in(select id from sched where kind_id=1 and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'"))
)
UNION
(
select * from user where is_active=1 and id in(select distinct manager_id from sched where kind_id in(2, 3, 4) and plan_or_fact="0" and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'")

)

';

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


$topic='ѕросроченные встречи/задачи/звонки/командировки в GYDEX.ѕланировщик';
$_mi=new MessageItem; $_task=new Sched_TaskItem; 
foreach($users_to_send as $k1=>$user){
	
	$txt='<div>';
	$txt.='<em>ƒанное сообщение сгенерировано автоматически.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='”важаемый(а€) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div>&nbsp;</div>';
	
	
	$txt.='<div>';
	$txt.='<strong>¬ GYDEX.ѕланировщик у ¬ас просрочены следующие встречи/задачи/звонки/командировки:</strong>';
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div><ul>';
	
	$sql='
	
	(
	select * from sched where kind_id=1 and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'"  and id in(select distinct sched_id from  sched_task_users where   kind_id in(2,3 ) and user_id="'.$user['id'].'" )
	)
	UNION(
	 select * from sched where kind_id in(2, 3, 4) and plan_or_fact="0" and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'" and manager_id="'.$user['id'].'"
	)
	
	';
	
	
	
	$set1=new mysqlset($sql);
	$rs1=$set1->GetResult();
	$rc1=$set1->GetResultNumRows();
	
	

	for($j=0; $j<$rc1; $j++){
		$g=mysqli_fetch_array($rs1);
		
		 $_res=new Sched_Resolver($g['kind_id']);
		
		$txt.='<li>';
		$txt.='<a href="/ed_sched.php?action=1&id='.$g['id'].'" target="_blank">'.$_res->instance->ConstructFullName($g['id'], $g).'</a>';
		if($g['kind_id']==1){
		
			if($g['pdate_beg']!="") $txt.=',<strong> крайний срок:</strong> <em>'.DateFromYmd($g['pdate_beg']).' '.$g['ptime_beg'].'</em>';
			$txt.=', <strong>¬аша роль:</strong> <em>';
			
			//найдем роли...
			$sql2=' select distinct k.kind_id, p.name 
			from sched_task_users as k
			inner join sched_task_users_kind as p on p.id=k.kind_id
			where k.sched_id="'.$g['id'].'" and k.user_id="'.$user['id'].'"
			order by k.kind_id';
			
			//echo $sql2;
			
			$set2=new mysqlset($sql2);
			$rs2=$set2->GetResult();
			$rc2=$set2->GetResultNumRows();
			
			
			$roles=array();
			for($k=0; $k<$rc2; $k++){
				$h=mysqli_fetch_array($rs2);
				$roles[]=$h['name'];
				
				
				
			}
			
			$txt.=implode(', ', $roles);
			
			$txt.='</em></li>';
		}else{
			if($g['pdate_beg']!="") $txt.=', <strong>дата/врем€ начала:</strong> <em>'.DateFromYmd($g['pdate_beg']).' '.$g['ptime_beg'].'</em>';
		}
		 
		
		
	//	print_r($g);	
	}
	
		
	$txt.='</ul><div>&nbsp;</div>';
	
	$txt.='<div><strong>ѕросим своевременно выполн€ть все поставленные задачи!<br>
ќ данной проблеме мы автоматически уведомили постановщиков задач!</strong>
</div>';
	
	
	$txt.='<div>&nbsp;</div>';

	$txt.='<div>';
	$txt.='C уважением, программа "'.SITETITLE.'".';
	$txt.='</div>';
	
	
	
//	echo $txt;
	
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}


*/
exit();
	
?>