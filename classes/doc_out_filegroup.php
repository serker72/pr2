<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// ������ ������ ���. ���
class DocOutFileGroup extends AbstractFileDocFolderGroup {
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='doc_out_file';
		$this->file_instance=$file_instance; //��������� ������ �����
		$this->folder_instance=$folder_instance; //��������� ������ �����
		$this->pagename='ed_doc_out.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/doc_out/';	
		
		
		$this->tablename_folder='doc_out_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	 
}



 
?>