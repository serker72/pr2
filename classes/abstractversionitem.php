<?
require_once('abstractitem.php');

//элемент версированный
class AbstractVersionItem extends AbstractItem{
	protected $version_tablename;//='menu_lang';
	protected $mid_name;
	protected $version_id_name;

	const SET_NULL=NULL;
	const ACTIVE_VERSION=NULL;
	
	//установка всех имен
	protected function init(){
		$this->tablename='bdr';
		$this->version_tablename='bdr_version';
		$this->item=NULL;
		$this->pagename='page.php';		
		
		$this->mid_name='bdr_id';
		$this->version_id_name='vid';
		
		$this->vis_name='is_shown';
	}
	
	//добавить 
	public function Add($params, $version_params){
		
		
		$it1=new AbstractItem(); $it2=new AbstractItem();
		
		//параметры общие
		$it1->SetTableName($this->tablename);
		$mid=$it1->Add($params);
		
		//параметры версированные
		 
		/*echo '<pre>';
		print_r($params);
		echo '</pre>';
			*/
		$this->AddVersionByParams($mid,$version_params);
		
		
		
		return $mid;
	}
	
	
	//добавить версию на основе параметров
	public function AddVersionByParams($id, $version_params){
		$it1=new AbstractItem();
		$it1->SetTableName($this->version_tablename);
		
		$version_params[$this->mid_name]=$id;
		//если не задан номер версии, то сгенерировать его на основе свободных...
		$version_params['version_no']=$this->GenerateVersionNo($id);
		
		$version_id=$it1->Add($version_params);
		
		//print_r($version_params);
		
		return $version_id;
		
	}
	
	//добавить версию, копиру€ предыдущую
	public function AddVersion($id, &$version_no){
		$version_id=NULL;
		//получить пќ—Ћ≈ƒЌёё версию данного документа
		$sql='select * from '.$this->version_tablename.' where '.$this->mid_name.'="'.$id.'" order by '.$this->version_id_name.' desc limit 1';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs, MYSQL_ASSOC);		
		
		
			//получить новый номер
			$params=$f;
			unset($params[$this->version_id_name]);
			$params['version_no']=(int)$params['version_no'] + 1;
			$params['given_pdate']=time();
			$version_no=$params['version_no'];
			
			foreach($params as $k=>$v) $params[$k]=SecStr($v);
			
			
			if($params['gain_val']=="") $params['gain_val']=NULL;
			if($params['gain_percent']=="") $params['gain_percent']=NULL;
			if($params['gain_ebitda']=="") $params['gain_ebitda']=NULL;
			if($params['gain_chp']=="") $params['gain_chp']=NULL;
			
			
			
			//сохранить ее как новую версию
			$it1=new AbstractItem();
			$it1->SetTableName($this->version_tablename);
			
			$version_id=$it1->Add($params);
		
		}
		
		return $version_id;
	}
	
	//получить первый свободный номер версии
	public function GenerateVersionNo($id){
		$no=0;
		
		$sql='select version_no from '.$this->version_tablename.' where '.$this->mid_name.'="'.$id.'" order by '.$this->version_id_name.' desc  limit 1';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$no=(int)$f['version_no'];
		}
		
		$no++;
		return $no;
	}
	
	
	
	
	
	
	//править
	public function Edit($id, $version_id=self::ACTIVE_VERSION, $params=NULL, $version_params=NULL){
		
		$it1=new AbstractItem(); 
		
		if($version_id==self::ACTIVE_VERSION){
			//получить последнюю версию
			$version_id=$this->GetActiveVersionId($id);	
		}
		
		
		//параметры не€зыковые
		if($params!==NULL){
			$it1->SetTableName($this->tablename);
			$it1->Edit($id,$params);
			unset($it1);
		}
		
		
		//параметры €зыковые
		if($version_params!==NULL){
			$qq='';
			foreach($version_params as $key=>$val){
				if(($key!=$this->mid_name)&&($key!=$this->version_id_name)){
					if($qq==''){
						 if($val===self::SET_NULL) $qq.=$key.'=NULL'; 
						 else $qq.=$key.'="'.$val.'"';
					}else{
						 
						if($val===self::SET_NULL) $qq.=','.$key.'=NULL';
						else $qq.=','.$key.'="'.$val.'"';
					}
				}
			}
			
			
			$query='update '.$this->version_tablename.' set '.$qq.' where '.$this->mid_name.'="'.$id.'" and '.$this->version_id_name.'="'.$version_id.'"';
			
			//echo $query;
			
			$it=new nonSet($query);
			
			
			unset($it);
		}
		
	}
	
	//удалить
	public function Del($id){
		//удал€ть ¬—≈!!!!
		
		$it1=new AbstractItem(); 
		
		//параметры не€зыковые
		$it1->SetTableName($this->tablename);
		$it1->Del($id);
		unset($it1);
		
		//параметры €зыковые
		$query = 'delete from '.$this->version_tablename.' where '.$this->mid_name.'='.$id.';';
		$it=new nonSet($query);
		
		
		unset($it);				
		
		
		
		$this->item=NULL;
	}	
	
	 
	
	
	
	
	//получить документ и его версию по айди , айди версии
	public function GetItemById($id,$version_id=self::ACTIVE_VERSION){
		if($id===NULL) return false;
		
		if($version_id==self::ACTIVE_VERSION){
			//получить последнюю версию
			$version_id=$this->GetActiveVersionId($id);	
		}
		
		
		
		 $query='select * from '.$this->tablename.' as t, '.$this->version_tablename.' as l where t.id='.$id.' and t.id=l.'.$this->mid_name.' and l.'.$this->version_id_name.'='.$version_id.'';
		 
		
		$item=new mysqlSet($query);
		
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
//			echo 'ccc'; die();
			$this->item=NULL;
			return false;
		}
	}
	
	
	//получить последний айди версии
	public function GetActiveVersionId($id){
		$no=0;
		
		$sql='select '.$this->version_id_name.' from '.$this->version_tablename.' where '.$this->mid_name.'="'.$id.'" order by '.$this->version_id_name.' desc limit 1';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$no=(int)$f[$this->version_id_name];
		}
		
		//echo $no; die();
		return $no;
	}
	
	
	//получение первого итема по набору полей
	public function GetItemByFields($params, $version_params){
		$qq='';
		$_params=array();
		foreach($params as $key=>$val){
			 
			$_params[]='t.'.$key.'="'.$val.'" ';
		}
		
	
		foreach($version_params as $key=>$val){
			 
			$_params[]='l.'.$key.'="'.$val.'" ';
		}
		
		$qq.=implode(' and ', $_params);
		 
		
		$sql='select * from '.$this->tablename.' as t
		inner join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id
		
		 where '.$qq.'';
		
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
			$this->item=NULL;
			return false;
		}	
	}
		
	//получение первого итема по набору полей с исключени€ми
	public function GetItemByFieldsWithExcept($params, $version_params, $except_params, $except_version_params){	
		$qq='';
		$_params=array();
		foreach($params as $key=>$val){
			$_params[]='t.'.$key.'="'.$val.'" ';
		}
			
		foreach($version_params as $key=>$val){
			$_params[]='l.'.$key.'="'.$val.'" ';
		}
		
			
		$_except_params=array();
		foreach($except_params as $key=>$val){
			$_except_params[]='t.'.$key.'<>"'.$val.'" ';
		}
			
		foreach($except_version_params as $key=>$val){
			$_except_params[]='l.'.$key.'<>"'.$val.'" ';
		}
		
		
		$qq.=implode(' and ', $_params); 
		
		$sql='select * from '.$this->tablename.' as t
		inner join '.$this->version_tablename.' as l on l.'.$this->mid_name.'=t.id
		
		 where '.$qq.'';
		
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
			$this->item=NULL;
			return false;
		}	
	}
	
	 
	//получить список имеющихс€ версий
	public function GetVersions($id){
		$arr=array();
		
		$sql='select '.$this->version_id_name.', version_no, given_pdate from '.$this->version_tablename.' where '.$this->mid_name.'="'.$id.'" order by version_no desc';
		
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		for($i=0; $i<$rc;$i++){
			$f=mysqli_fetch_array($result);
			
			$arr[]=array(
				$this->version_id_name=>$f[$this->version_id_name],
				'version_no'=>$f['version_no'],
				'given_pdate'=>date('d.m.Y H:i:s', $f['given_pdate'])
				);
		}
		
		return $arr;	
	}
	
}
?>