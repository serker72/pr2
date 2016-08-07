<?
require_once('NonSet.php');
require_once('MysqlSet.php');
require_once('authuser.php');
require_once('db_decorator.php');
require_once('abstractgroup.php');

// группа элементов версированная
class AbstractVersionGroup extends AbstractGroup {
	 
	protected $vis_name;
	protected $_auth_result;
	
	protected $version_tablename;//='menu_lang';
	protected $mid_name;
	protected $version_id_name;
	
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='bdr';
		$this->version_tablename='bdr_version';
		
		$this->pagename='view.php';		
		$this->mid_name='bdr_id';
		
			$this->subkeyname='mid';	
		
		$this->version_id_name='vid';
		
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	
	//список позиций
	public function GetItems($mode=0,$from=0,$to_page=10){
		
		$txt='';

		
		return $txt;
	}
	
	//список позиций
	public function GetItemsById($id, $mode=0,$from=0,$to_page=10){
		
		$txt='';

		
		return $txt;
	}
	
	
	//список позиций - последняя версия
	public function GetItemsByIdTemplate($id, $template, $current_id=0){
		
		$txt='';
		$arr=$this->GetItemsByIdArr($id, $current_id);
		$sm=new SmartyAdm;
		$sm->assign('items', $arr);
		$sm->assign('id', $id);
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	//список позиций - последняя версия
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		
		 
		
		
		$sql='select t.*, l.* from '.$this->tablename.' as t
		
		left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id )
	 
		  where t.'.$this->subkeyname.'="'.$id.'" order by  t.id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		 
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
	
	
	//список позиций
	public function GetItemsTemplate($template, $current_id=0){
		
		$txt='';
		$arr=$this->GetItemsArr($current_id);
		$sm=new SmartyAdm;
		$sm->assign('items', $arr);
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	//список позиций
	public function GetItemsArr($current_id=0){
		$arr=array();
		 
		$sql='select t.*, l.* from '.$this->tablename.' as t
		
		left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id ) 
		order by  t.id asc';
		
		//echo $sql; 
		 
		$set=new MysqlSet($sql);
		 
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
	public function GetItemsOpt($current_id=0,$fieldname='t.name', $do_no=false, $no_caption='-выберите-'){
		$txt='';
		
		$sql='select t.*, l.* from '.$this->tablename.' as t
		
		left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id ) 
		order by  '.$fieldname.' asc';
		
		 
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
	
	
	//получение итемov по набору полей
	public function GetItemsByFieldsArr($params, $version_params){
		$res=array(); $_params=array();
		
		$qq='';
		foreach($params as $key=>$val){
			 
			$_params[]='t.'.$key.'="'.$val.'" ';
		}
		foreach($version_params as $key=>$val){
			 
			$_params[]='l.'.$key.'="'.$val.'" ';
		}
		
		$sql='select t.*, l.* from '.$this->tablename.' as t
		
		left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id ) 
		';
		
		if(count($_params)>0) $sql.=' where '.implode(' and ',$_params);
		
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		//unset($item);
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v){
				$f[$k]=stripslashes($v);	
			}
			$res[]=$f;
		}
		
		
		return $res;
	}
	
	
	 
	
	//получение набора итемов по декоратору
	public function GetItemsByDecArr(DBDecorator $dec){
		$res=array();
		
		
		//$sql='select p.* from '.$this->tablename.' as p ';
		
		$sql='select t.*, l.* from '.$this->tablename.' as t
		
		left join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id and l.'.$this->version_id_name.' in (select max(ll.'.$this->version_id_name.') as `s_q` from '.$this->version_tablename.' as ll where ll.'.$this->mid_name.'=t.id ) 
		';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		 
			
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		 
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v) $f[$k]=stripslashes($v);	
			 
			$res[]=$f;
		}
		
		return $res;
	}
	
	
	 
}
?>