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
		$this->vis_name = 'is_active';	
		$this->subkeyname = 'id';
                $this->subkeyname1 = 'user_id';	
                $this->subkeyname2 = 'posted_user_id';	
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
	public function GetItemById($id, $mode=0){
		if($id === NULL) return false;
                
                $sql = 'select p.*, 
                            u1.login as user_login, u1.name_s as user_name,
                            u2.login as posted_user_login, u2.name_s as posted_user_name,
                            u3.login as user_confirm_login, u3.name_s as user_confirm_name,
                            sup.full_name as supplier_name,
                            spo.name as opf_name,
                            supc.name as supplier_contact_name, supc.position as supplier_contact_position,
                            st.name as last_status,
                            (select count(id) from app_contract_history where app_contract_id=p.id) as history_message_count
                        from ' . $this->tablename . ' as p
                            left join user as u1 on p.' . $this->subkeyname1 . ' = u1.id	
                            left join user as u2 on p.' . $this->subkeyname2 . ' = u2.id	
                            left join user as u3 on p.user_confirm_id = u3.id
                            left join supplier as sup on p.supplier_id = sup.id
                            left join opf as spo on spo.id = sup.opf_id 
                            left join supplier_contact as supc on p.supplier_contact_id = supc.id
                            left join app_contract_status as st on p.status_id = st.id
                        where p.' . $this->subkeyname . ' = ' . $id;
                
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

	//возможность утвердить
	public function CanConfirm($id, &$reason, $item=NULL){
            $can = true;	
            $reason = '';
            $reasons = array();
            if($item === NULL) $item = $this->GetItemById($id);

            if($item['is_confirmed'] != 0){
                $can = $can && false;
                $reasons[] = 'заявка уже утверждена';
            }else{
                if($item['supplier_id'] == 0){
                    $can = $can && false;
                    $reasons[] = 'не указан контрагент';
                }

                if($item['supplier_contact_id'] == 0){
                    $can = $can && false;
                    $reasons[] = 'не указано контактное лицо контрагента';
                }
            }

            $reason .= implode(', ', $reasons);

            return $can;	
	}
	
	//запрос о возможности снятия утв-ия и возвращение причины, почему нельзя 
	public function CanUnConfirm($id ,&$reason){
            $can = true;	
            $reason = ''; 
            $reasons = array();
            $item = $this->GetItemById($id);

            if ($item['is_confirmed'] != 1){
                $can = $can && false;
                $reasons[] = 'уже снято утверждение заявки';
                $reason .= implode(', ', $reasons);
            } else {
            }

            $reason = implode(', ', $reasons);
            
            return $can;
	}
}
?>