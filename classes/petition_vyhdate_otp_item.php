<?
require_once('abstractitem.php');

//����������� �������
class PetitionVyhDateOtpItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_vyh_date_otp';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='petition_id';
		
	}
	
	
	
	 
}
?>