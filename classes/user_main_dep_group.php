<?

require_once('abstractgroup.php');
require_once('upos_direction.php');

// группа департаментов
class UserMainDepGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='user_main_department';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		
		$_up=new UposDirection;
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select * from '.$this->tablename.' order by name asc';
		
		 
		 $set=new MysqlSet($sql);
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//привязка дол-ти к направлению
			//$f['dirs']=$_up->GetAllDirsByPostionArr($f['id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//список пол-лей по декоратору в тегах option
	public function GetItemsByDecOpt($current_id=0,$fieldname='name', DBDecorator $dec, $do_no=false, $no_caption='-выберите-'){
		$txt='';
			
		/*$sql='select u.*, pos.name as position_name, dep.name as department_name from '.$this->tablename.'  as u
		left join user_position as pos on pos.id=u.position_id
		left join user_department as dep on dep.id=u.department_id ';*/
		
		$sql='select distinct u.id, u.name from '.$this->tablename.'  as u
		inner join user as us on us.department_id=u.id 
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
		
		
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
}
?>