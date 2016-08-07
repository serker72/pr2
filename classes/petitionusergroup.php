<?

require_once('abstractgroup.php');

// абстрактная группа
class PetitionUserGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_user';
		$this->pagename='view.php';		
		$this->subkeyname='petition_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArrById($id){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.*, up.name as position_name
		from '.$this->tablename.' as t
		inner join user as u on u.id=t.user_id
		left join user_position as up on u.position_id=up.id
		
		 where '.$this->subkeyname.'="'.$id.'" order by ord asc, name_s asc, id asc';
		
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
	
	
	//список утверждений с проверкой всех прав
	public function GetConfirmsbyId($id, $user_id, $result=NULL, $can_confirm=false, $can_super_confirm=false, $can_unconfirm=false, $can_super_unconfirm=false, $can_not_to_next=false){
		
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select 
		
		 t.petition_id, 	t.user_id,  	t.ord, 	t.is_confirmed, 	t.confirm_pdate, 	t.confirm_id, 	t.is_not_to_next, 	t.not_to_next_pdate, 	t.not_to_next_id,
		
		u.name_s as name_s, u.login as login, u.position_s as position_s, u.id as id, 
		
		c.name_s as c_name_s, c.login as c_login, c.position_s as c_position_s,
		n.name_s as n_name_s, n.login as n_login, n.position_s as n_position_s
		
		from '.$this->tablename.' as t
		inner join user as u on u.id=t.user_id
		
		left join user as c on c.id=t.confirm_id
		left join user as n on n.id=t.not_to_next_id
		
		 where '.$this->subkeyname.'="'.$id.'" order by ord asc, name_s asc, id asc';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$was_confirmed=false; $last_utv_index=-1; $first_not_utv_index=0;
		$all_were_confirmed=true;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['confirm_pdate_unf']=$f['confirm_pdate'];
			$f['confirm_pdate']=date('d.m.Y H:i:s',$f['confirm_pdate']);
			
			$f['not_to_next_pdate_unf']=$f['not_to_next_pdate'];
			$f['not_to_next_pdate']=date('d.m.Y H:i:s',$f['not_to_next_pdate']);
			
			if(($f['is_confirmed']==0)&&$was_confirmed){
				$first_not_utv_index=$i;
				$last_utv_index=$i-1;	
			}
			
			
			if($f['is_confirmed']==1){
				$f['confirm_confirmer']=$f['c_position_s'].' '.$f['c_name_s'].' '.$f['c_login'].' '.$f['confirm_pdate'];
				$was_confirmed=true;
			}else $was_confirmed=false;
			
			if($f['is_not_to_next']==1){
				$f['not_to_next_confirmer']=$f['n_position_s'].' '.$f['n_name_s'].' '.$f['n_login'].' '.$f['confirm_pdate'];
			}
			
			$all_were_confirmed=($all_were_confirmed&&($f['is_confirmed']==1));
			
			$arr[]=$f;
		}
		//рассмотреть условие, если все утверждены...
		if($all_were_confirmed){
			$first_not_utv_index=-1;
			$last_utv_index=$rc-1;
		}
		
		//echo '<br>первая неутв. запись:' .$first_not_utv_index;
		//echo '<br>последняя утв. запись:' .$last_utv_index;
		
		
		//применим массив прав
		foreach($arr as $k=>$f){
			//can_confirm
			//can_not_to_next
			
			$arr[$k]['can_confirm']=false;
			$arr[$k]['can_not_to_next']=false;
			
			
			//если это последняя утв. запись - то права на снятие утв-я брать из аргументов
			//если это последняя утв. запись - can_confirm=can_unconfirm 
			if($k==$last_utv_index){
				if($can_super_unconfirm) $arr[$k]['can_confirm']=$can_super_unconfirm;
				elseif($f['user_id']==$user_id){
				
					$arr[$k]['can_confirm']=$can_unconfirm;	
				}
				//echo 'zzz';
			}
			//								 - 	
			//если это первая не утв. запись - то права на установку утв:
			//									can_confirm=can_confirm если это я
			//									can_confirm=can_super_confirm если это не я
			//Иначе - ничего нельзя!
			if($k==$first_not_utv_index){
				if($can_super_confirm) $arr[$k]['can_confirm']=$can_super_confirm;
				elseif($f['user_id']==$user_id){
					 $arr[$k]['can_confirm']=$can_confirm; 	
					 //echo 'zzz';
				}
			}
			
			//как быть с can_not_to_next???
			//доступно только для первой не утв. записи...
			//если это первая не утв. запись - can_not_to_next=can_not_to_next
			//при снятии утв-ия снимается и is_not_to_next
			if($k==$first_not_utv_index) $arr[$k]['can_not_to_next']=$can_not_to_next&&$arr[$k]['can_confirm'];
			
		}
		
		
		
		return $arr;
			
		
	}
	
	
}
?>