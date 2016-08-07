<?

//сортировка массива по числовому значению
class ArraySorter{
	
	
	//сортировка массива
	static public function SortArr($arr, $fieldname, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min
				$a=self::FindMin($arr, $fieldname, $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}else{
				//max
				$a=self::FindMax($arr, $fieldname, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	static protected function FindMin($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$minval=999999999999999999999999999999999999999;
		foreach($arr as $k=>$v){
			if($v[$fieldname]<$minval){
				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	static protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$maxval=-999999999999999999999;
		foreach($arr as $k=>$v){
			
			if($v[$fieldname]>$maxval){
				
				$maxval=$v[$fieldname];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}

}



//сортировка массива по первому строковому значению  
class ArrayStringSorter{
	
	
	
	
	//сортировка массива
	static public function SortArr($arr, $fieldname, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min
				$a=self::FindMin($arr, $fieldname,  $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else{
					 $result[]=array_pop($arr);
				}
			}else{
				//max
				$a=self::FindMax($arr, $fieldname,  $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else{
					 $result[]=array_pop($arr);
				}
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	static protected function FindMin($arr, $fieldname,&$index){
		$index=-1;
		$res=array();
		$minval='яяяяяяяяяяяяяяяяяяяяяяяяяяяяяяя';
		foreach($arr as $k=>$v){
		
			if($v[$fieldname]<$minval){
				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	static protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$maxval='00000000000000000000000000000';
		foreach($arr as $k=>$v){
			
			
			
			if($v[$fieldname]>$maxval){
				
				$maxval=$v[$fieldname];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}

}


//сортировка массива по первому строковому значению в св€занном с записью массиве значений
class ArrayStringArrSorter{
	
	
	
	
	//сортировка массива
	static public function SortArr($arr, $fieldname, $inner_field_name, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min
				$a=self::FindMin($arr, $fieldname,  $inner_field_name, $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else{
					 $result[]=array_pop($arr);
				}
			}else{
				//max
				$a=self::FindMax($arr, $fieldname,   $inner_field_name, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else{
					 $result[]=array_pop($arr);
				}
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	static protected function FindMin($arr, $fieldname, $inner_field_name, &$index){
		$index=-1;
		$res=array();
		$minval='яяяяяяяяяяяяяяяяяяяяяяяяяяяяяяя';
		foreach($arr as $k=>$v){
			if(!isset($v[$fieldname][0][$inner_field_name])) continue;
			
			if($v[$fieldname][0][$inner_field_name]<$minval){
				$minval=$v[$fieldname][0][$inner_field_name];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	static protected function FindMax($arr, $fieldname, $inner_field_name, &$index){
		$index=-1;
		$res=array();
		$maxval='00000000000000000000000000000';
		foreach($arr as $k=>$v){
			
			if(!isset($v[$fieldname][0][$inner_field_name])) continue;
			
			if($v[$fieldname][0][$inner_field_name]>$maxval){
				
				$maxval=$v[$fieldname][0][$inner_field_name];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}

}

?>