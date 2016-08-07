<?
require_once('abstractgroup.php');
require_once('user_to_user.php');

// абстрактная группа
class UsersGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		
		 if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by login asc, id asc');
		 else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'=1 order by login asc, id asc');
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
			
		}
		
		return $arr;
	}
	
	
	
	//список кураторов
	public function GetCuratorsArr($current_id=0, $fieldname='curator_obor_id'){
		$sql='select * from user where is_active=1 or id in (select distinct '.$fieldname.' from supplier) order by name_s asc, login asc';
		
		//echo $sql;
		
		$alls=array();
		$as=new mysqlSet($sql);
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		
		
		$alls[]=array('id'=>0, 'text'=>'-выберите-', 'is_current'=>($current_id==0), 'is_active'=>1);
		
		for($i=0; $i<$rc; $i++){	
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($v as $k=>$v) $f[$k]=stripslashes($v);
			$f['text']=$f['name_s'].'';
			
			$alls[]=$f;
		}
		
		
		return $alls;
	}
	
	
	//список позиций
	public function GetVarUsersArr(){
		$arr=array();
		$au=new AuthUser;
		$result=$au->auth();
		
		
		$limited_user=NULL;
		$flt='';
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$flt=' and id in('.implode(', ', $limited_user).') ';
		}
		//print_r($limited_user);
	



		
		
		 $sql='select * from '.$this->tablename.' where '.$this->vis_name.'=1 '.$flt.' order by name_s asc, login asc, id asc';
		 $set=new MysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
			
		}
		
		return $arr;
	}
	
	
	//список пол-лей по декоратору массив
	public function GetItemsByDecArr( DBDecorator $dec){
		$arr= array();
			
		$sql='select u.*, pos.name as position_name, dep.name as department_name from '.$this->tablename.'  as u
		left join user_position as pos on pos.id=u.position_id
		left join user_department as dep on dep.id=u.department_id 
		
	 
		';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			//$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		 
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$arr[]=$f;
			}
		}
		return $arr;
	}
}
?>