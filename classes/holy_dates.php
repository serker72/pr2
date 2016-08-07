<?

require_once('billdates.php');
class HolyDates extends BillDates{
	
	
	//дата в формате timestamp!
	public function IsHolyday($pdate){
		$res=false;	
		
		
		if(date('d.m.Y', $pdate)=='20.02.2016') return false;
		
		
		if(in_array($pdate,$this->prazdnik)){
				return true;
		}
		 if((date("w",$pdate)==0)||(date("w",$pdate)==6)) return true;
		 
		return $res;
	}
	
	
	
	 
	
};
?>