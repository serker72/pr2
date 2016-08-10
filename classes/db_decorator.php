<?
require_once('db_uri_entry.php');
require_once('db_uri_arr_entry.php');
require_once('db_sql_entry.php');
require_once('db_sql_having_entry.php');
require_once('db_sqlord_entry.php');

//генерация sql и uri для сложных фильтров
class DBDecorator{
	protected $entries=array();
	
	
	public function AddEntry(AbstractEntry $entry){
		$this->entries[]=$entry;	
	}
	
	
	//получить кусок запроса по фильтрации
        // KSK - 03.07.2016 - перенос второго параметра $skip_names из аналогичного класса программы SMU
        // Перенос выполнен для создания полного варианта класса
	public function GenFltSql($mode=' and ', $skip_names=NULL){
		$txt='';
		$arr=array(); $to_skip=0;
                // KSK - 28.06.2016 - добавлены счетчики для вложенных скобок
                $skobka_l = 0;
                $skobka_r = 0;
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlEntry) {
                                // KSK - 03.07.2016 - перенос условия из аналогичного класса программы SMU
                                // Перенос выполнен для создания полsного варианта класса
                                if(is_array($skip_names) && in_array($v->GetName(), $skip_names)) continue;
				
				if($to_skip>0){
					$to_skip--;
					continue;	
				}
			
				
				if($v->GetAction()==SqlEntry::SKOBKA_L){
					//$_t_arr=array();
                                        $skobka_l++;
					$_t='';
					$_t.=''.$v->Deploy().'';
					$to_skip=0;
					foreach($this->entries as $kk=>$vv){
						if(($vv instanceof SqlEntry)&&($kk>$k)) {
							$to_skip++;
							
							$_t.=''.$vv->Deploy().'';
							
							if($vv->GetAction()==SqlEntry::SKOBKA_L){
								$skobka_l++;
							}
                                                        
							if($vv->GetAction()==SqlEntry::SKOBKA_R){
                                                            $skobka_r++;
                                                            
                                                            if ($skobka_r == $skobka_l) {
                                                                break;
                                                            }
							}
						}
					}
					$arr[]=''.$_t.'';
                                        
                                        $skobka_l = 0;
                                        $skobka_r = 0;
				}else{
					$to_skip=0;
				
					$arr[]='('.$v->Deploy().')';
				}
			}
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
	
	//получить кусок запроса по фильтрации для HAVING
        // KSK - 03.07.2016 - добавление второго параметра $skip_names по аналогии с методом GenFltSql
	public function GenFltHavingSql($mode=' and ', $skip_names=NULL){
		$txt='';
		$arr=array(); $to_skip=0;
                // KSK - 28.06.2016 - добавлены счетчики для вложенных скобок
                $skobka_l = 0;
                $skobka_r = 0;
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlHavingEntry) {
                                // KSK - 03.07.2016 - добавление  условия по аналогии с методом GenFltSql
                                if(is_array($skip_names) && in_array($v->GetName(), $skip_names)) continue;
				
				if($to_skip>0){
					$to_skip--;
					continue;	
				}
			
				
				if($v->GetAction()==SqlHavingEntry::SKOBKA_L){
					//$_t_arr=array();
                                        $skobka_l++;
					$_t='';
					$_t.=''.$v->Deploy().'';
					$to_skip=0;
					foreach($this->entries as $kk=>$vv){
						if(($vv instanceof SqlHavingEntry)&&($kk>$k)) {
							$to_skip++;
							
							$_t.=''.$vv->Deploy().'';
							
							if($vv->GetAction()==SqlHavingEntry::SKOBKA_L){
								$skobka_l++;
							}
                                                        
							if($vv->GetAction()==SqlHavingEntry::SKOBKA_R){
                                                            $skobka_r++;
                                                            
                                                            if ($skobka_r == $skobka_l) {
                                                                break;
                                                            }
							}
						}
					}
					$arr[]=''.$_t.'';
                                        
                                        $skobka_l = 0;
                                        $skobka_r = 0;
				}else{
					$to_skip=0;
				
					$arr[]='('.$v->Deploy().')';
				}
			}
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
	
	//получить кусок uri со значениями
	public function GenFltUri($mode='&', $prefix='', $prefix_exceptions=NULL){
		$txt='';
		$arr=array();
		foreach($this->entries as $k=>$v){
			if(($v instanceof UriEntry)||($v instanceof UriArrEntry)) $arr[]=$v->Deploy($prefix, $prefix_exceptions);
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
	public function GetUris(){
	
		$arr=array();
		foreach($this->entries as $k=>$v){
			if(($v instanceof UriEntry)||($v instanceof UriArrEntry)) $arr[]=$v;

		}
		
		return $arr;
	}
	
	//получить строку сортировки
	public function GenFltOrd($mode=', '){
		$txt='';
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlOrdEntry) $arr[]=$v->Deploy();
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
        // KSK - 30.06.2016 - перенос метода из аналогичного класса программы SYA
        // Перенос выполнен для создания полного варианта класса DBDecorator
        // и дальнейшего тиражирования между программами
	public function GenFltOrdArr(){
		 
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlOrdEntry) $arr[]=$v;
		}
	 
		return $arr;
	}
        
	//получить список элементов SQL
	public function GetSqls(){
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlEntry) $arr[]=$v; //$arr[]='('.$v->Deploy().')';
		}
		return $arr;
	}

}
?>