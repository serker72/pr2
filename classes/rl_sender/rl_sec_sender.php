<?
require_once('rl_abstract_sender.php');

class RlSecSender extends RlAbstractSender{

	 //��������� ������ ���-���
	 //��������� ������ ������������
	 //��������� ���������
	 
	 
	 public function LoadAndSend(array $eq_ids, array $user_ids){
		$this->eqs=array();
		$this->eqs=$eq_ids;
		
		$this->receivers=array();
		$this->receivers=$user_ids; 
		 
		$this->SendMessages(); 
	 }
	
	
}
?>