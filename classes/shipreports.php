<?
require_once('abstractgroup.php');

class ShipReports extends AbstractGroup{

	//ÐÀÁÎÒÀÅÒ ÀÁÛ ÊÀÊ!!! ÂÂÅÄÅÍÀ ÄËß ÎÒ×ÅÒÀ "ÒÎÂÀÐÛ Â ÏÓÒÈ"
	public function InBills($position_id,$bill_id,$template,$org_id,$is_ajax=true, $sh_i_id=NULL, $pl_position_id=NULL, $pl_discount_id=NULL, $pl_discount_value=NULL, $pl_discount_rub_or_percent=NULL, $can_view_supplier=false, $out_bill_id=NULL, $kp_id=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		//$sql='select distinct b.id, b.code, b.pdate, bp.quantity from bill as b inner join bill_position as bp on b.id=bp.bill_id where  bp.komplekt_ved_pos_id="'.$kvid.'"  and b.org_id="'.$org_id.'"';
		
		$flt='';
		if($sh_i_id!==NULL) $flt.=' and s.id="'.$sh_i_id.'" ';
		
		if($pl_position_id!==NULL) $flt.=' and bp.pl_position_id="'.$pl_position_id.'"';
		if($pl_discount_id!==NULL) $flt.=' and bp.pl_discount_id="'.$pl_discount_id.'"';
		if($pl_discount_value!==NULL) $flt.=' and bp.pl_discount_value="'.$pl_discount_value.'"';
		if($pl_discount_rub_or_percent!==NULL) $flt.=' and bp.pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'"';
		if($out_bill_id!==NULL) $flt.=' and bp.out_bill_id="'.$out_bill_id.'" ';
		if($kp_id!==NULL) $flt.=' and bp.kp_id="'.$kp_id.'" ';
		
				
		$sql='select distinct b.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name    
		from bill as b
		inner join bill_position as bp on b.id=bp.bill_id 
		inner join  sh_i as s  on s.bill_id=b.id 
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		where bp.position_id="'.$position_id.'" and b.id="'.$bill_id.'" and s.is_confirmed=1 '.$flt.' and s.org_id="'.$org_id.'"';
	
		//echo $sql;
	
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		return $sm->fetch($template);
	}
	
	//ÐÀÁÎÒÀÅÒ ÀÁÛ ÊÀÊ!!! ÂÂÅÄÅÍÀ ÄËß ÎÒ×ÅÒÀ "ÒÎÂÀÐÛ Â ÏÓÒÈ"
	public function InSh($position_id,$bill_id,$template,$org_id,$is_ajax=true, $sh_i_id=NULL, $pl_position_id=NULL, $pl_discount_id=NULL, $pl_discount_value=NULL, $pl_discount_rub_or_percent=NULL, $can_view_supplier=false, $out_bill_id=NULL, $kp_id=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$flt='';
		if($sh_i_id!==NULL) $flt.=' and s.id="'.$sh_i_id.'" ';
		
		
		
		if($pl_position_id!==NULL) $flt.=' and bp.pl_position_id="'.$pl_position_id.'"';
		if($pl_discount_id!==NULL) $flt.=' and bp.pl_discount_id="'.$pl_discount_id.'"';
		if($pl_discount_value!==NULL) $flt.=' and bp.pl_discount_value="'.$pl_discount_value.'"';
		if($pl_discount_rub_or_percent!==NULL) $flt.=' and bp.pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'"';
		if($out_bill_id!==NULL) $flt.=' and bp.out_bill_id="'.$out_bill_id.'" ';
		if($kp_id!==NULL) $flt.=' and bp.kp_id="'.$kp_id.'" ';
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name 
		  from sh_i as s  inner join sh_i_position as bp on s.id=bp.sh_i_id inner join bill as b on s.bill_id=b.id 
		
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		
		    where bp.position_id="'.$position_id.'" '.$flt.' and s.is_confirmed=1  and s.org_id="'.$org_id.'"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		return $sm->fetch($template);
	}
	
	
	public function InAcc($position_id,$bill_id,$template,$org_id,$is_ajax=true, $sh_i_id=NULL, 
	$pl_position_id=NULL, $pl_discount_id=NULL, $pl_discount_value=NULL, $pl_discount_rub_or_percent=NULL,$can_view_supplier=false, $out_bill_id=NULL, $kp_id=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$flt='';
		if($sh_i_id!==NULL) $flt.=' and s.sh_i_id="'.$sh_i_id.'" ';
		
		
		
		if($pl_position_id!==NULL) $flt.=' and bp.pl_position_id="'.$pl_position_id.'"';
		if($pl_discount_id!==NULL) $flt.=' and bp.pl_discount_id="'.$pl_discount_id.'"';
		if($pl_discount_value!==NULL) $flt.=' and bp.pl_discount_value="'.$pl_discount_value.'"';
		if($pl_discount_rub_or_percent!==NULL) $flt.=' and bp.pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'"';
		if($out_bill_id!==NULL) $flt.=' and bp.out_bill_id="'.$out_bill_id.'" ';
		if($kp_id!==NULL) $flt.=' and bp.kp_id="'.$kp_id.'" ';
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name    
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		where bp.position_id="'.$position_id.'" and b.id="'.$bill_id.'" and s.is_confirmed=1 '.$flt.' and s.org_id="'.$org_id.'"';
	
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		return $sm->fetch($template);
	}
}
?>