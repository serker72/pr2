<?
require_once('abstractgroup.php');

require_once('posgroupgroup.php');

//  группа  групп опций
class PlGroupGroup extends PosGroupGroup{
	

	//установка всех имен
	protected function init(){
		$this->tablename='pl_group';
		$this->pagename='view.php';		
		$this->subkeyname='parent_group_id';	
		$this->vis_name='is_shown';		
		$this->keyname='id';	
		
		
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by ord desc, id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by ord desc, id asc');
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
	
	//итемы в тегах option
	public function GetItemsOpt($current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-'){
		$txt='';
		$sql='select * from '.$this->tablename.' order by ord desc, name asc';
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