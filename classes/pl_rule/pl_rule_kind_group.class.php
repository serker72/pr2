<?

 

//  группа  
class PlRuleKindGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_rule_kind';
		$this->pagename='pricelist.php';		
		$this->subkeyname='group_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//итемы в тегах option
	public function GetItemsOpt($current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-', $result=NULL){
		$au=new AuthUser;
		if($result===NULL){
			$result=$au->Auth();	
		}
		
		$txt='';
		$sql='select * from '.$this->tablename.' order by '.$fieldname.' asc';
		
		//echo $sql;
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		 
	 if($tc>0){
			
			
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				 
				
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		 }
		return ($txt);
	}
	
	
	//итемы в тегах option
	public function GetItemsOptById($id, $current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-', $result=NULL){
		$au=new AuthUser;
		if($result===NULL){
			$result=$au->Auth();	
		}
		
		$txt='';
		//$sql='select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by '.$fieldname.' asc';
		
		$sql='select p.* from '.$this->tablename.' as p
		inner join pl_price_kind_group as pc on p.id=pc.price_kind_id
		
		 where pc.group_id="'.$id.'" order by p.'.$fieldname.' asc';
		
		
		
		
		//echo $sql;
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				 
				
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
	
}
?>