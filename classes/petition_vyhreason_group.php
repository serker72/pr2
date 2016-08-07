<?

require_once('abstractgroup.php');

//������ ������ ������ � ��������
class PetitionVyhReasonGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_vyh_reason';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������ �������
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by name asc, id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by name asc, id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=array();
		$f['id']=0;
		$f['name']='-��������-';
		$f['is_current']=(bool)($current_id==0);
		
		$arr[]=$f;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
}
?>