<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/users_activity.php');
require_once('../classes/users_activity_light.php');

require_once('../classes/user_s_group.php');
require_once('../classes/cachereports.php');
require_once('../classes/an_index.php');
 

 
 
//кварталы!
$year=date('Y');
		$quarts=array(
			array('number'=>'1', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,1,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,3,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,1,1,$year), 'pdate_end_unf'=>mktime(23,59,59,3,31,$year)),
			array('number'=>'2', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,4,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,6,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,4,1,$year), 'pdate_end_unf'=>mktime(23,59,59,6,30,$year)),
			
			array('number'=>'3', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,7,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,9,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,7,1,$year), 'pdate_end_unf'=>mktime(23,59,59,9,30,$year)),
			
			array('number'=>'4', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,10,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,12,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,10,1,$year), 'pdate_end_unf'=>mktime(23,59,59,12,31,$year)),
			
		
		); 

if(isset($_GET['mode'])){
	$mode=abs((int)$_GET['mode']);	
}else $mode=3;



$_pdate=mktime(0,0,0,date('m'),date('d'), date('Y'));

//обобщенна€ функци€ сбора и кешировани€ данных
function LogData($mode, $pdate11, $pdate12){
	echo 'период с '.date('d.m.Y H:i:s', $pdate11). ' do '.date('d.m.Y H:i:s', $pdate12);
	
	$_ua=new UsersActivityLight;
	$_cache=new CacheReportsItem;
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	
	$_ug=new UsersSGroup;
	$users=$_ug->GetItemsArr(0,1);
	
	//print_r($users);
	
	foreach($users as $user){
		
		//echo ' сотрудник '. $user['name_s'];
		
		$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>$mode, 'user_id'=>$user['id']));	
		
		$decorator=new DBDecorator;
			  
		$decorator->AddEntry(new SqlEntry('pdate',$pdate11, SqlEntry::BETWEEN,$pdate12));
		$decorator->AddEntry(new SqlEntry('login',SecStr($user['login']), SqlEntry::E));
		 
		$ua=$_ua->ShowLog('',$decorator,new DbDecorator(),0,100, $total);
		
		//$ret.='величина '.$total."\n";
		 
		
		if($m===false) $_cache->Add(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$total, 'user_id'=>$user['id'])); 
		else $_cache->Edit($m['id'], array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$total, 'user_id'=>$user['id']));
	}
	
}


//сбор данных по каждому за год
if($mode==3){
	$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//сбор данных по кажому за день
elseif($mode==4){
	$pdate11=$_pdate;
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//сбор данных по каждому за квартал
elseif($mode==6){
	 
	$pdate11=$_pdate; $pdate12=$_pdate+24*60*60; 
	
	$quart=array();
	foreach($quarts as $quart){
		
		if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
	}
	
	
	$pdate11=$quart['pdate_beg_unf'];
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//сбор данных по каждому за мес€ц
elseif($mode==5){
	$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//сбор данных по каждому за неделю
elseif($mode==7){
	$pdate11= strtotime("last Monday");
			
	$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	LogData($mode, $pdate11, $pdate12);
	 
}

 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>