<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// группа файлов вн. док
class DocVnFileGroup extends AbstractFileDocFolderGroup {
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='doc_vn_file';
		$this->file_instance=$file_instance; //экземпл€р класса файла
		$this->folder_instance=$folder_instance; //экземпл€р класса папки
		$this->pagename='ed_doc_vn.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/doc_vn/';	
		
		
		$this->tablename_folder='doc_vn_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	 
}



 
?>