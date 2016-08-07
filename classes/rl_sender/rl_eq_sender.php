<?
require_once('rl_abstract_sender.php');

class RlEqSender extends RlAbstractSender{

	 //загрузить список пол-лей
	 //загрузить список оборудования
	 //разослать сообщения
	 
	 
	 public function LoadAndSend($eq_id, array $user_ids){
		$this->eqs=array($eq_id);
		
		$this->receivers=$user_ids; 
		 
		$this->SendMessages(); 
	 }
	
	
}
?>