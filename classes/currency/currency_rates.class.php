<?
 

//абстрактный элемент
class CurrencyRatesItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='currency_rates';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>