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
 
//��������!
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
	  
	   $ret="Categories,���\n";
	  
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
//����� ������ �� ��������� � ������� ������������	
	$pdate=$_POST['pdate'];
	$_ua=new UsersActivity;
	  
		  
	$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	
	$_cache=new CacheReportsItem;
	
	//������ ������ �� ���������� �� ���� (�������)
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m11=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>4, 'user_id'=>$result['id']));
	
	  $one_day=(float)$m11['value'];
	
	//����� (�������)
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m12=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>5, 'user_id'=>$result['id']));
	
	  $one_month=(float)$m12['value'];
		
	//��� (�������)
	
	
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m13=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>3, 'user_id'=>$result['id']));
	
	  $one_year=(float)$m13['value'];
	
	
	//������ ������ �� ���� �����������....
	//���� �� ���� ����������� � ���������?
	$_ug=new UsersSGroup;
	$users=$_ug->GetItemsArr(0,1);

	$all_by_day=0;
	$all_by_month=0;
	$all_by_year=0;
	
	
	
	
	
	 
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$m1=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>0));
	
	if($m1===false){
		foreach($users as $user){
				//������ ������ �� ����
				 
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
	 
	 
	
 	$ret="Categories,������� ������ � ���������, %\n";
	
	if($all_by_day>0) $ret.="�� ���� ".DateFromYmd($pdate)." $one_day ���.,".round(100*$one_day/$all_by_day,1)."\n";
	else  $ret.="�� ���� ".DateFromYmd($pdate)." 0 ���.,0\n";
	
	if($all_by_month>0) $ret.="�� ����� ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." $one_month ���.,".round(100*$one_month/$all_by_month,1)."\n";
	else $ret.="�� ����� ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." 0 ���.,0\n";
	
	if($all_by_year>0) $ret.="�� ��� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $one_year ���.,".round(100*$one_year/$all_by_year,1)."";
	else  $ret.="�� ��� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 ���.,0";
	
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_users_kp")){
//������������ ����������� ����������
	$pdate=$_POST['pdate'];
	 	  
	$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//�� ���!
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
	
	//�� �����
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
	
	
	//�� �������
		
	//������ ��� �������
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
	
	
	 
	
	
	$ret="Categories,���� ��, %\n";
	
	if($month_by_all>0) $ret.="�� ����� ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." $month_by_user ��,".round(100*$month_by_user/$month_by_all,1)."\n";
	else $ret.="�� ����� ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." 0 ��,0\n";
	
	if($quart_by_all>0) $ret.="�� ".$quart['number']." ������� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $quart_by_user ��,".round(100*$quart_by_user/$quart_by_all,1)."\n";
	else $ret.="�� ".$quart['number']." ������� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 ��,0\n";
	
	if($year_by_all>0) $ret.="�� ��� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $year_by_user ��,".round(100*$year_by_user/$year_by_all,1)."";
	else $ret.="�� ��� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 ��,0";
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_users_pf")){
//�������� ����������
	$pdate=$_POST['pdate'];
	 	  
	$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//�� ���!
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
	
	
	
	
	
	//�� �����
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
	
	
	//�� �������
		
	//������ ��� �������
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
	
	
	 
	
	$ret="Categories,���� ��������, %\n";
	
	if($month_by_all>0) $ret.="�� ����� ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." $month_by_user ���-���,".round(100*$month_by_user/$month_by_all,1)."\n";
	else $ret.="�� ����� ".date('m.Y', DateFromDmy(DateFromYmd($pdate)))." 0 ���-���,0\n";
	
	if($quart_by_all>0) $ret.="�� ".$quart['number']." ������� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $quart_by_user ���-���,".round(100*$quart_by_user/$quart_by_all,1)."\n";
	else $ret.="�� ".$quart['number']." ������� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 ���-���,0\n";
	
	if($year_by_all>0) $ret.="�� ��� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." $year_by_user ���-���,".round(100*$year_by_user/$year_by_all,1)."";
	else  $ret.="�� ��� ".date('Y', DateFromDmy(DateFromYmd($pdate)))." 0 ���-���,0";
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="load_extended_kp")||($_POST['action']=="load_extended_kp_graf"))){
//����������� ������ ��
	$pdate=$_POST['pdate'];
	$divide=$_POST['divide'];
	$period=$_POST['period'];
	
	
	
	$_pdate=mktime(0,0,0,date('m'),date('d'),date('Y'));
	 	  
	//$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//
	switch($period){
		case 0:
			//�������
			$pdate11=mktime(0,0,0,date('m'),date('d'),date('Y'));
			$pdate12=$pdate11+24*60*60-1;	
		break;
		case 1:
			//������
			$pdate11= strtotime("last Monday");
			
			$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'))+24*60*60-1;;
		break;
		case 2:
			//�����
			$pdate11=mktime(0,0,0,date('m'),1,date('Y'));
			$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'))+24*60*60-1;;	
		break;
		case 3:
			//�������
			//������ ��� �������
			$quart=array();
			foreach($quarts as $quart){
				
				if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
			}
			
			$pdate11=$quart['pdate_beg_unf'];
			 
		
		break;
		case 4:
			//���
			$pdate11=mktime(0,0,0,1,1,date('Y'));
			$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'))+24*60*60-1;;	
		break;
	}
	
	
	
	$_an_index=new AnIndex;
	$txt= $_an_index->ShowDataKP($pdate11, $pdate12, $divide, $result['org_id'],  'index_reports.html',   true, $alls);
	
	if($_POST['action']=="load_extended_kp") $ret=$txt;
	
	else{
		foreach($alls['items'] as $k=>$v){
			$ret.=str_replace(',','',$v['name']).','.$v['percentage'].','.($v['value']).' ��.';
			if($k<count($alls['items'])-1) $ret.="\n";	
		}
	}
	
	//print_r($alls);
	
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="load_extended_dog")||($_POST['action']=="load_extended_dog_graf"))){
//����������� ������ ��
	$pdate=$_POST['pdate'];
	$divide=$_POST['divide'];
	$period=$_POST['period'];
	$_pdate=mktime(0,0,0,date('m'),date('d'),date('Y'));
	 	  
	//$_pdate=DateFromdmY(DateFromYmd($pdate));
	
	//
	switch($period){
		case 0:
			//������� - ��� ������
			$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
			$pdate12=$_pdate+24*60*60;
		break;
		case 1:
			//������ - ��� ������
			$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
			$pdate12=$_pdate+24*60*60;
		break;
		case 2:
			//�����
			$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
			 $pdate12=$_pdate+24*60*60;
		break;
		case 3:
			//�������
			//������ ��� �������
			$quart=array();
			foreach($quarts as $quart){
				
				if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
			}
			
			$pdate11=$quart['pdate_beg_unf'];
			$pdate12=$_pdate+24*60*60-1;
		
		break;
		case 4:
			//���
			$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
			$pdate12=$_pdate+24*60*60;
			 
		break;
	}
	
	
	
	$_an_index=new AnIndex;
	
	$txt= $_an_index->ShowDataDog($pdate11, $pdate12, $period,   $divide, $result['org_id'],  'index_reports.html',   true, $alls);
	
	if($_POST['action']=="load_extended_dog") $ret=$txt;
	
	else{
		 
		foreach($alls['items'] as $k=>$v){
			$ret.=str_replace(',','',$v['name']).','.$v['percentage'].','.($v['value']).' ��.';	
			if($k<count($alls['items'])-1) $ret.="\n";
		}
	}
	
	
	//print_r($alls);
	
	
}

//����� ������ �� ���� �����������

elseif(isset($_POST['action'])&&(($_POST['action']=="load_extended_work")||($_POST['action']=="load_extended_work_graf"))){
//����������� ������ ��
	$pdate=$_POST['pdate'];
	$divide=$_POST['divide'];
	$period=$_POST['period'];
	$_pdate=mktime(0,0,0,date('m'),date('d'),date('Y'));
	 $marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	$_cache=new CacheReportsItem;	  
	$_ua=new UsersActivity; 
	
	$_ug=new UsersSGroup;
	$users=$_ug->GetItemsArr(0,1);
			
	
	////�� ������� - �������� �� ���������� ���-���, �� ������ ������� - �� ��
	$alls=array();
	$alls['items']=array(); $total_hrs=0;
	 switch($period){
		case 0:
			//������� 
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
				//$ret.='�� ��� '.$total."\n";
				$alls['items'][]=array(
					'name'=>$user['name_s'],
					'percentage'=>0,
					'value'=>$total);
				 
			}
			
			
		break;
		case 1:
			//������ 
			
			
			 
			
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
			//�����
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
			//�������
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
			//���
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
	
	
	//��������� �������
	foreach($alls['items'] as $k=>$v){
		
		if($total_hrs>0) $v['percentage']=round(100*$v['value']/$total_hrs, 2);
		else $v['percentage']=0;
		
		$alls['items'][$k]['percentage']=$v['percentage'];
		
		 
	}
	
	$alls['total']=$total_hrs;
	
	//�������� ����������
	$alls['items']=ArraySorter::SortArr($alls['items'], 'value',1);		 
	
	if($_POST['action']=="load_extended_work"){
		 $sm=new SmartyAj;
		 $sm->assign('alls', $alls);
		 $sm->assign('title','����� ������, ���.');
		 $txt=$sm->fetch('index_reports.html');
		 $ret=$txt;
	
	}else{
		 
		 
		foreach($alls['items'] as $k=>$v){
			$ret.=$v['name'].','.$v['percentage'].','.($v['value']).' ���.';	
			if($k<count($alls['items'])-1) $ret.="\n";
		}
	}
		 
	 
	
	//print_r($alls);
	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>