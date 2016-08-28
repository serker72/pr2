<?
require_once('abstractfileitem.php');

//����������� ����
class AppContractFileItem extends AbstractFileItem{
	//protected $storage_id;
	//protected $storage_name;
	//protected $storage_path;
	
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='app_contract_file';
		$this->item=NULL;
		$this->pagename='app_contract_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/app_contract/';	
	}
	
	
	public function GetStoragePath(){
		return $this->storage_path;	
	}
	
	
	//�������� 
	public function Add($params){
		$params[$this->storage_name]=$this->storage_id;
		
		
		return parent::Add($params);
	}
	
	//�������
	public function Edit($id,$params){
		$params[$this->storage_name]=$this->storage_id;
		
		return parent::Edit($id,$params);
	}
	
	
	//��������� ������� ����� �� ������ �����
	public function GetItemByFields($params){
		$params[$this->storage_name]=$this->storage_id;
		return parent::GetItemByFields($params);
	}
	
	//�������
	public function Del($id){
		$item=$this->GetItemById($id);
		if($item!==false){
		
		  @unlink($this->storage_path.$item['filename']);
		  parent::Del($id);
		}
	}	
	
}
?>