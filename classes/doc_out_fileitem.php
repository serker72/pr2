<?
require_once('abstractfileitem.php');

// ���� ���. ���
class DocOutFileItem extends AbstractFileItem{
	
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='doc_out_file';
		$this->item=NULL;
		$this->pagename='ed_doc_out.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/doc_out/';	
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