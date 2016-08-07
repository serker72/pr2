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

require_once('../classes/user_s_group.php');
require_once('../classes/cachereports.php');
require_once('../classes/an_index.php');
 

$au=new AuthUser();
$result=$au->Auth(false,false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 
//кварталы!
$year=date('Y');
		$quarts=array(
			array('number'=>'1', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,1,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,3,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,1,1,$year), 'pdate_end_unf'=>mktime(23,59,59,3,31,$year)),
			array('number'=>'2', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,4,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,6,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,4,1,$year), 'pdate_end_unf'=>mktime(23,59,59,6,30,$year)),
			
			array('number'=>'3', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,7,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,9,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,7,1,$year), 'pdate_end_unf'=>mktime(23,59,59,9,30,$year)),
			
			array('number'=>'4', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,10,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,12,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,10,1,$year), 'pdate_end_unf'=>mktime(23,59,59,12,31,$year)),
			
		
		); 
 

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="load_users_activity")){
	 $_cache=new CacheReportsItem;
	
	 $pdate=$_POST['pdate'];
	 
	  $_ua=new UsersActivity;
	  
	  $very_total=0;
	  
	  $_pdate=DateFromdmY(DateFromYmd($pdate));
	  
	   $ret="Categories,Дни\n";
	  
	  $stru=array();
	  for($i=0; $i<4; $i++){
		 //echo  $_pdate;
		 $current_date=$_pdate-24*60*60*$i;
		 $pdate11=$current_date; $pdate12=$current_date+24*60*60;
		 
		 
		 $marker=$current_date;//mktime(0, 0,0,date('m'),date('d'), date('Y'));
		 $m11=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>4, 'user_id'=>$result['id']));
		 
		// var_dump(array('pdate'=>$marker, 'kind'=>4, 'user_id'=>$result['id']));
		 
		 
		$total=(float)$m11['value'];
		 
			$ret.=date('d.m.Y', $current_date).','.$total;
			if($i<3) $ret.="\n";	
		 
		  
	  }
		
	 
} 
elseif(isset($_POST['action'])&&($_POST['action']=="load_users_activity_compare")){
//время работы по сравнению с другими сотрудниками	
	$pdate=$_POST['pdate'];
	$_ua=new UsersActivity;
	  
		  
	$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	
	$_cache=new CacheReportsItem;
	
	//найдем данные по сотруднику за день (текущий)
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m11=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>4, 'user_id'=>$result['id']));
	
	  $one_day=(float)$m11['value'];
	
	//месяц (текущий)
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m12=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>5, 'user_id'=>$result['id']));
	
	  $one_month=(float)$m12['value'];
		
	//год (текущий)
	
	
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m13=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>3, 'user_id'=>$result['id']));
	
	  $one_year=(float)$m13['value'];
	
	
	//найдем данные по ВСЕМ сотрудникам....
	//цикл по всем сотрудникам и суммируем?
	$_ug=new UsersSGroup;
	$users=$_ug->GetItemsArr(0,1);

	$all_by_day=0;
	$all_by_month=0;
	$all_by_year=0;
	
	
	
	
	
	 
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m1=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>0));
	
	if($m1===false){
		foreach($users as $user){
				//найдем данные по всем
				 
				 $_m_temp=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>4, 'user_id'=>$result['id']));
				  $all_by_day+=(float)$_m_temp['value'];
				 
		}
		$_cache->Add(array('pdate'=>$marker, 'kind'=>0, 'value'=>$all_by_day));
	}else $all_by_day=$m1['value'];
	
	$marker=mktime(0,0,0,date('m'),date('d'), date('Y'));
	
	
	///$m1=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>0));
	$m2=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>1));
	$m3=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>2));
	
 
		$all_by_month=(float)$m2['value'];
		$all_by_year=(float)$m3['value'];
	 
	 
	
 	$ret="Categories,Периоды работы в программе, %\n";
	
	if($all_by_day>0) $ret.="За день ".DateFromYmd($pdate)." $one_day час.,".round(100*$one_day/$all_by_day,1)."\n";
	else  $ret.="За день ".DateFromYmd($pdate)." 0 час.,0\n";
	
	if($all_by_month>0) $ret.="За месяц ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." $one_month час.,".round(100*$one_month/$all_by_month,1)."\n";
	else $ret.="За месяц ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." 0 час.,0\n";
	
	if($all_by_year>0) $ret.="За год ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $one_year час.,".round(100*$one_year/$all_by_year,1)."";
	else  $ret.="За год ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 час.,0";
	
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_users_kp")){
//коммерческие предложения сотрудника
	$pdate=$_POST['pdate'];
	 	  
	$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//за год!
	$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;

	$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$result['org_id'].'" and user_manager_id="'.$result['id'].'" and (pdate between "'.$pdate11.'" and "'.$pdate12.'")';
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$year_by_user=(int)$f[0];
	
	$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$result['org_id'].'" and (pdate between "'.$pdate11.'" and "'.$pdate12.'")';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$year_by_all=(int)$f[0];
	
	//за месяц
	$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;

	$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$result['org_id'].'" and user_manager_id="'.$result['id'].'" and (pdate between "'.$pdate11.'" and "'.$pdate12.'")';
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$month_by_user=(int)$f[0];
	
	$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$result['org_id'].'" and (pdate between "'.$pdate11.'" and "'.$pdate12.'")';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$month_by_all=(int)$f[0];
	
	
	//за квартал
		
	//найдем наш квартал
	$quart=array();
	foreach($quarts as $quart){
		
		if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
	}
	
	
	$pdate11=$quart['pdate_beg_unf'];
	$pdate12=$_pdate+24*60*60;

	$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$result['org_id'].'" and user_manager_id="'.$result['id'].'" and (pdate between "'.$pdate11.'" and "'.$pdate12.'")';
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$quart_by_user=(int)$f[0];
	
	$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$result['org_id'].'" and (pdate between "'.$pdate11.'" and "'.$pdate12.'")';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$quart_by_all=(int)$f[0];
	
	
	 
	
	
	$ret="Categories,Ваши КП, %\n";
	
	if($month_by_all>0) $ret.="За месяц ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." $month_by_user КП,".round(100*$month_by_user/$month_by_all,1)."\n";
	else $ret.="За месяц ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." 0 КП,0\n";
	
	if($quart_by_all>0) $ret.="За ".$quart['number']." квартал ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $quart_by_user КП,".round(100*$quart_by_user/$quart_by_all,1)."\n";
	else $ret.="За ".$quart['number']." квартал ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 КП,0\n";
	
	if($year_by_all>0) $ret.="За год ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $year_by_user КП,".round(100*$year_by_user/$year_by_all,1)."";
	else $ret.="За год ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 КП,0";
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_users_pf")){
//договора сотрудника
	$pdate=$_POST['pdate'];
	 	  
	$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//за год!
	$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;

	$sql='select count(*) from  plan_fact_fact where is_confirmed=1 and org_id="'.$result['org_id'].'" and user_id="'.$result['id'].'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"';
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$year_by_user=(int)$f[0];
	
	$sql='select count(*) from plan_fact_fact where is_confirmed=1 and org_id="'.$result['org_id'].'" and  (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"';
	
	
	 
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$year_by_all=(int)$f[0];
	
	
	
	
	
	//за месяц
	$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;

	$sql='select count(*) from  plan_fact_fact where is_confirmed=1 and org_id="'.$result['org_id'].'" and user_id="'.$result['id'].'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"';
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$month_by_user=(int)$f[0];
	
	$sql='select count(*) from plan_fact_fact where is_confirmed=1 and org_id="'.$result['org_id'].'" and  (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"';
	
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$month_by_all=(int)$f[0];
	
	
	//за квартал
		
	//найдем наш квартал
	$quart=array();
	foreach($quarts as $quart){
		
		if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
	}
	
	
	$pdate11=$quart['pdate_beg_unf'];
	$pdate12=$_pdate+24*60*60;

	$sql='select count(*) from  plan_fact_fact where is_confirmed=1 and user_id="'.$result['id'].'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"';
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$quart_by_user=(int)$f[0];
	
	$sql='select count(*) from plan_fact_fact where is_confirmed=1 and  (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"';
	
	
	//echo $sql;
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	$quart_by_all=(int)$f[0];
	
	
	 
	
	$ret="Categories,Ваши договора, %\n";
	
	if($month_by_all>0) $ret.="За месяц ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." $month_by_user дог-ров,".round(100*$month_by_user/$month_by_all,1)."\n";
	else $ret.="За месяц ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." 0 дог-ров,0\n";
	
	if($quart_by_all>0) $ret.="За ".$quart['number']." квартал ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $quart_by_user дог-ров,".round(100*$quart_by_user/$quart_by_all,1)."\n";
	else $ret.="За ".$quart['number']." квартал ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 дог-ров,0\n";
	
	if($year_by_all>0) $ret.="За год ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $year_by_user дог-ров,".round(100*$year_by_user/$year_by_all,1)."";
	else  $ret.="За год ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 дог-ров,0";
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="load_extended_kp")||($_POST['action']=="load_extended_kp_graf"))){
//расширенные данные КП
	$pdate=$_POST['pdate'];
	$divide=$_POST['divide'];
	$period=$_POST['period'];
	
	
	
	$_pdate=mktime(0,0,0,date('m'),date('d'),date('Y'));
	 	  
	//$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//
	switch($period){
		case 0:
			//сегодня
			$pdate11=mktime(0,0,0,date('m'),date('d'),date('Y'));
			$pdate12=$pdate11+24*60*60-1;	
		break;
		case 1:
			//неделя
			$pdate11= strtotime("last Monday");
			
			$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'))+24*60*60-1;;
		break;
		case 2:
			//месяц
			$pdate11=mktime(0,0,0,date('m'),1,date('Y'));
			$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'))+24*60*60-1;;	
		break;
		case 3:
			//квартал
			//найдем наш квартал
			$quart=array();
			foreach($quarts as $quart){
				
				if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
			}
			
			$pdate11=$quart['pdate_beg_unf'];
			 
		
		break;
		case 4:
			//год
			$pdate11=mktime(0,0,0,1,1,date('Y'));
			$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'))+24*60*60-1;;	
		break;
	}
	
	
	
	$_an_index=new AnIndex;
	$txt= $_an_index->ShowDataKP($pdate11, $pdate12, $divide, $result['org_id'],  'index_reports.html',   true, $alls);
	
	if($_POST['action']=="load_extended_kp") $ret=$txt;
	
	else{
		foreach($alls['items'] as $k=>$v){
			$ret.=str_replace(',','',$v['name']).','.$v['percentage'].','.($v['value']).' шт.';
			if($k<count($alls['items'])-1) $ret.="\n";	
		}
	}
	
	//print_r($alls);
	
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="load_extended_dog")||($_POST['action']=="load_extended_dog_graf"))){
//расширенные данные КП
	$pdate=$_POST['pdate'];
	$divide=$_POST['divide'];
	$period=$_POST['period'];
	$_pdate=mktime(0,0,0,date('m'),date('d'),date('Y'));
	 	  
	//$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//
	switch($period){
		case 0:
			//сегодня - нет такого
			$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
			$pdate12=$_pdate+24*60*60;
		break;
		case 1:
			//неделя - нет такого
			$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
			$pdate12=$_pdate+24*60*60;
		break;
		case 2:
			//месяц
			$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
			 $pdate12=$_pdate+24*60*60;
		break;
		case 3:
			//квартал
			//найдем наш квартал
			$quart=array();
			foreach($quarts as $quart){
				
				if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
			}
			
			$pdate11=$quart['pdate_beg_unf'];
			$pdate12=$_pdate+24*60*60-1;
		
		break;
		case 4:
			//год
			$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
			$pdate12=$_pdate+24*60*60;
			 
		break;
	}
	
	
	
	$_an_index=new AnIndex;
	
	$txt= $_an_index->ShowDataDog($pdate11, $pdate12, $period,   $divide, $result['org_id'],  'index_reports.html',   true, $alls);
	
	if($_POST['action']=="load_extended_dog") $ret=$txt;
	
	else{
		 
		foreach($alls['items'] as $k=>$v){
			$ret.=str_replace(',','',$v['name']).','.$v['percentage'].','.($v['value']).' шт.';	
			if($k<count($alls['items'])-1) $ret.="\n";
		}
	}
	
	
	//print_r($alls);
	
	
}

//время работы по всем сотрудникам

elseif(isset($_POST['action'])&&(($_POST['action']=="load_extended_work")||($_POST['action']=="load_extended_work_graf"))){
//расширенные данные КП
	$pdate=$_POST['pdate'];
	$divide=$_POST['divide'];
	$period=$_POST['period'];
	$_pdate=mktime(0,0,0,date('m'),date('d'),date('Y'));
	 $marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$_cache=new CacheReportsItem;	  
	$_ua=new UsersActivity; 
	
	$_ug=new UsersSGroup;
	$users=$_ug->GetItemsArr(0,1);
			
	
	////за сегодня - собираем из активности пол-лей, за прочие периоды - из БД
	$alls=array();
	$alls['items']=array(); $total_hrs=0;
	 switch($period){
		case 0:
			//сегодня 
			$pdate11=$_pdate;
			$pdate12=$_pdate+24*60*60;
			
		 	
			
			
			
			
			
			foreach($users as $user){
		
			
				$marker1=mktime(date('H'), 0,0,date('m'),date('d'), date('Y'));
				$m11=$_cache->GetItemByFields(array('pdate'=>$marker1, 'kind'=>4, 'user_id'=>$user['id']));
				
				if($m11===false){
				
					$pdate11=$_pdate; $pdate12=$_pdate+24*60*60;
					$decorator=new DBDecorator;
						  
					 $decorator->AddEntry(new SqlEntry('pdate',$pdate11, SqlEntry::BETWEEN,$pdate12));
					 $decorator->AddEntry(new SqlEntry('login',SecStr($user['login']), SqlEntry::E));
					 
					 $ua=$_ua->ShowLog('',$decorator,new DbDecorator(),0,100, $total);
					
					 
					
					$_cache->Add(array('pdate'=>$marker1, 'kind'=>4, 'value'=>$total, 'user_id'=>$user['id']));
				}else $total=$m11['value'];
					
				 
				
				if((float)$total==0) continue; 
				
				$total_hrs+=$total;
				//$ret.='за год '.$total."\n";
				$alls['items'][]=array(
					'name'=>$user['name_s'],
					'percentage'=>0,
					'value'=>$total);
				 
			}
			
			
		break;
		case 1:
			//неделя 
			
			
			 
			
			foreach($users as $user){
				$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>7, 'user_id'=>$user['id']));	
				
				if((float)$m['value']==0) continue;
				$total_hrs+=(float)$m['value'];
				 
				$alls['items'][]=array(
					'name'=>$user['name_s'],
					'percentage'=>0,
					'value'=>$m['value']);
				
			}
		break;
		case 2:
			//месяц
			foreach($users as $user){
				$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>5, 'user_id'=>$user['id']));	
				if((float)$m['value']==0) continue;
				
				$total_hrs+=(float)$m['value'];
				 
				$alls['items'][]=array(
					'name'=>$user['name_s'],
					'percentage'=>0,
					'value'=>$m['value']);
				
			}
		break;
		case 3:
			//квартал
			foreach($users as $user){
				$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>6, 'user_id'=>$user['id']));	
				if((float)$m['value']==0) continue;
				
				$total_hrs+=(float)$m['value'];
				 
				$alls['items'][]=array(
					'name'=>$user['name_s'],
					'percentage'=>0,
					'value'=>$m['value']);
				
			}
		
		break;
		case 4:
			//год
			foreach($users as $user){
				$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>3, 'user_id'=>$user['id']));	
				
				if((float)$m['value']==0) continue;
				$total_hrs+=(float)$m['value'];
				 
				$alls['items'][]=array(
					'name'=>$user['name_s'],
					'percentage'=>0,
					'value'=>$m['value']);
				
			}
			 
		break;
		 
	}
	
	
	//посчитаем процент
	foreach($alls['items'] as $k=>$v){
		
		if($total_hrs>0) $v['percentage']=round(100*$v['value']/$total_hrs, 2);
		else $v['percentage']=0;
		
		$alls['items'][$k]['percentage']=$v['percentage'];
		
		 
	}
	
	$alls['total']=$total_hrs;
	
	//выполним сортировку
	$alls['items']=ArraySorter::SortArr($alls['items'], 'value',1);		 
	
	if($_POST['action']=="load_extended_work"){
		 $sm=new SmartyAj;
		 $sm->assign('alls', $alls);
		 $sm->assign('title','Время работы, час.');
		 $txt=$sm->fetch('index_reports.html');
		 $ret=$txt;
	
	}else{
		 
		 
		foreach($alls['items'] as $k=>$v){
			$ret.=$v['name'].','.$v['percentage'].','.($v['value']).' час.';	
			if($k<count($alls['items'])-1) $ret.="\n";
		}
	}
		 
	 
	
	//print_r($alls);
	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>