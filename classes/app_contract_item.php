<?
require_once('abstractitem.php');
require_once('app_contract_fileitem.php');

//абстрактный элемент
class AppContractItem extends AbstractItem{
	protected $subkeyvalue;
	
	protected $order_name;
	protected $order_value;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename = 'app_contract';
		$this->item = NULL;
		$this->pagename = 'page.php';	
		$this->vis_name = 'is_shown';	
		$this->subkeyname = 'id';
                $this->subkeyname1 = 's_user_id';	
                $this->subkeyname2 = 'd_user_id';	
		$this->order_name = 'order_id';
		
		$this->subkeyvalue = '';	
	}
	
	//удалить
	public function Del($id){
		//удалить все файлы
		$fset=new MysqlSet('select * from app_contract_file where history_id in (select id from app_contract_history where app_contract_id="'.$id.'")');
		$fc=$fset->GetResultNumRows();
		$rfs=$fset->GetResult();
		
		$fi=new AppContractFileItem;
		for($i=0; $i<$fc; $i++){
			$f=mysqli_fetch_array($rfs);
			//GetStoragePath()
			@unlink($fi->GetStoragePath().$f['filename']);
		}
		
		
		
		//удалить файлы из бд
		$query = 'delete from app_contract_file where history_id in (select id from app_contract_history where app_contract_id="'.$id.'")';
		$it=new nonSet($query);
		
		//удалить историю
		$query = 'delete from app_contract_history where app_contract_id="'.$id.'";';
		$it=new nonSet($query);
		
		parent::Del($id);
	}	

	//получить по айди и коду видимости
	public function GetItemById($id,$mode=0){
		if($id === NULL) return false;
                $sql = 'select o.*, 
                            u1.login as s_user_login, u1.name_s as s_user_name,
                            u2.login as d_user_login, u2.name_d as d_user_name,
                            st.name as last_status,
                            (select count(id) from app_contract_history where app_contract_id=o.id) as history_message_count
                        from ' . $this->tablename . ' as o
                            left join user as u1 on o.' . $this->subkeyname1 . ' = u1.id	
                            left join user as u2 on o.' . $this->subkeyname2 . ' = u2.id	
                            left join app_contract_status as st on o.status_id = st.id
                        where o.' . $this->subkeyname . ' = ' . $id;
                
		if($mode == 0) $item = new mysqlSet($sql . ';');
		else $item = new mysqlSet($sql . ' and ' . $this->vis_name . ' = 1;');
		

		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= Array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
			$this->item=NULL;
			return false;
		}
	}
        
}
?>