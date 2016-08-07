<?
require_once('abstractfileitem.php');

// ���� �������
class TenderFileItem2 extends AbstractFileItem{
	
	
	public function __construct($id=2){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='tender_file';
		$this->item=NULL;
		$this->pagename='tender_files2.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/tender/';	
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
	
	
}

 
?>