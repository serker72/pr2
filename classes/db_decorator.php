<?
require_once('db_uri_entry.php');
require_once('db_uri_arr_entry.php');
require_once('db_sql_entry.php');
require_once('db_sql_having_entry.php');
require_once('db_sqlord_entry.php');

//��������� sql � uri ��� ������� ��������
class DBDecorator{
	protected $entries=array();
	
	
	public function AddEntry(AbstractEntry $entry){
		$this->entries[]=$entry;	
	}
	
	
	//�������� ����� ������� �� ����������
        // KSK - 03.07.2016 - ������� ������� ��������� $skip_names �� ������������ ������ ��������� SMU
        // ������� �������� ��� �������� ������� �������� ������
	public function GenFltSql($mode=' and ', $skip_names=NULL){
		$txt='';
		$arr=array(); $to_skip=0;
                // KSK - 28.06.2016 - ��������� �������� ��� ��������� ������
                $skobka_l = 0;
                $skobka_r = 0;
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlEntry) {
                                // KSK - 03.07.2016 - ������� ������� �� ������������ ������ ��������� SMU
                                // ������� �������� ��� �������� ���s���� �������� ������
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
	
	
	//�������� ����� ������� �� ���������� ��� HAVING
        // KSK - 03.07.2016 - ���������� ������� ��������� $skip_names �� �������� � ������� GenFltSql
	public function GenFltHavingSql($mode=' and ', $skip_names=NULL){
		$txt='';
		$arr=array(); $to_skip=0;
                // KSK - 28.06.2016 - ��������� �������� ��� ��������� ������
                $skobka_l = 0;
                $skobka_r = 0;
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlHavingEntry) {
                                // KSK - 03.07.2016 - ����������  ������� �� �������� � ������� GenFltSql
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
	
	
	//�������� ����� uri �� ����������
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
	
	//�������� ������ ����������
	public function GenFltOrd($mode=', '){
		$txt='';
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlOrdEntry) $arr[]=$v->Deploy();
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
        // KSK - 30.06.2016 - ������� ������ �� ������������ ������ ��������� SYA
        // ������� �������� ��� �������� ������� �������� ������ DBDecorator
        // � ����������� ������������� ����� �����������
	public function GenFltOrdArr(){
		 
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlOrdEntry) $arr[]=$v;
		}
	 
		return $arr;
	}
        
	//�������� ������ ��������� SQL
	public function GetSqls(){
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlEntry) $arr[]=$v; //$arr[]='('.$v->Deploy().')';
		}
		return $arr;
	}

}
?>