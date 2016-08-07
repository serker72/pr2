<?
require_once('abstractfileitem.php');

//����������� ����
class AppContractFileItem extends AbstractFileItem{
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='app_contract_file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='history_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/app_contract/';	
	}
	
	
	public function GetStoragePath(){
		return $this->storage_path;	
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