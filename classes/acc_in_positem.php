<?
require_once('abstractitem.php');
require_once('acc_pospmitem.php');
require_once('acc_item.php');
require_once('acc_in_item.php');
//require_once('billitem.php');
require_once('billpositem.php');
require_once('sh_i_positem.php');

require_once('authuser.php');
require_once('actionlog.php');
require_once('acc_notesitem.php');
require_once('sh_i_notesitem.php');
require_once('billnotesitem.php');

//����������� �������
class AccInPosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='acceptance_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='acceptance_id';	
	}
	
	
	
	//�������� 
	public function Add($params, $pms=NULL,$change_high_mode=0,$change_low_mode=0){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//������� +/- ��� �������
			$bpm=new AccPosPMItem;
			
			if($code>0){
				$pms['acceptance_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		$_acc=new AccItem; $_bpi=new BillPosItem;
		
		if(isset($params['quantity'])&&isset($params['acceptance_id'])){
			$acc=$_acc->getitembyid($params['acceptance_id']);
			if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 
				'position_id'=>$params['position_id'], 
				'pl_position_id'=>$params['pl_position_id'], 
				'pl_discount_id'=>$params['pl_discount_id'],
				'pl_discount_value'=>$params['pl_discount_value'],
				'pl_discount_rub_or_percent'=>$params['pl_discount_rub_or_percent']
				));	
				if(($bpi!==false)&&($bpi['quantity']==$params['quantity'])){
					$params['total']=$bpi['total'];	
				}elseif(($bpi!==false)){
					//����� �������� ������. ������.
					$price=round($bpi['total']/$bpi['quantity'],10);
					$params['total']=round($params['quantity']*$price,2);
				}
				
			}
			
		}
		
		
		
		
		return $code;
	}
	
	
	//�������������
	public function Edit($id,$params,$pms=NULL,$change_high_mode=0,$change_low_mode=0){
		//���� ������ ���-�� - �������� ���� total
		//
		$_acc=new AccInItem; $_bpi=new BillPosItem;
		$item=$this->GetItemById($id);
		
		if(isset($params['quantity'])&&($params['quantity']!=$item['quantity'])){
			 if(isset($params['price_pm'])&&($params['price_pm']!=$item['price_pm'])) $price=$params['price_pm'];
			 else $price=$item['price_pm'];
			 
			 //����� ���������� �� ����� ��� �����/���-��, ���. �� 10 ������
			 //������ �������� � ������������� ��������� ��� ��������� �����������
			 $acc=$_acc->getitembyid($params['acceptance_id']);
			 if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 
				'position_id'=>$item['position_id'], 
				
				'pl_position_id'=>$item['pl_position_id'], 
				'pl_discount_id'=>$item['pl_discount_id'],
				'pl_discount_value'=>$item['pl_discount_value'],
				'pl_discount_rub_or_percent'=>$item['pl_discount_rub_or_percent'],
				'out_bill_id'=>$item['out_bill_id']
				));	
				if(($bpi!==false)){
					$price=round($bpi['total']/$bpi['quantity'],10);
					$params['price_pm']=$price;
					//echo $price; die();
				}
			 }
			
			
			 
			 $params['total']=$params['quantity']*$price;
		}
		
		//���� ���-��=���-�� �� ����� - ������ �����=����� �� �����...
			  
		if(isset($params['quantity'])&&isset($params['acceptance_id'])){
			$acc=$_acc->getitembyid($params['acceptance_id']);
			if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 
				'position_id'=>$item['position_id'], 
					'pl_position_id'=>$item['pl_position_id'], 
				'pl_discount_id'=>$item['pl_discount_id'],
				'pl_discount_value'=>$item['pl_discount_value'],
				'pl_discount_rub_or_percent'=>$item['pl_discount_rub_or_percent'],
				'out_bill_id'=>$item['out_bill_id']
				));	
				if(($bpi!==false)&&($bpi['quantity']==$params['quantity'])){
					$params['total']=$bpi['total'];	
				}
				
			}
			
		}
		
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//���� ��� ���� ��, �� ����� ����������� ���
			//���� ��� - �� �������
			$_bpm=new AccPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('acceptance_position_id'=>$id));
			if($bpm===false){
				$pms['acceptance_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['acceptance_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}
		
		
		
	}
	
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from acceptance_position_pm where acceptance_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	public function SetChainQuantity($acceptance_id,$position_id, $pl_position_id, $pl_discount_id, $pl_discount_value, $pl_discount_rub_or_percent, $quantity,$change_high_mode=0,$change_low_mode=0, $_result=NULL, $item=NULL, $out_bill_id=0){
		//if(($change_high_mode==0)&&($change_low_mode==0)) return;
		
		
		$log=new ActionLog;
		$auth=new AuthUser;
		
		if($_result===NULL) $_result=$auth->Auth();
		
		
		$_ai=new AccInItem;
		if($item===NULL) $ai=$_ai->getitembyid($acceptance_id);
		else $ai=$item;
		
		if($ai!==false){
			$_bpi=new ShIPosItem;
			$_bpmi=new ShIPosPmItem;
		
			
			$bpi=$_bpi->GetItemByfields(array('sh_i_id'=>$ai['sh_i_id'],
			 'position_id'=>$position_id, 
			 'pl_position_id'=>$pl_position_id, 
			 'pl_discount_id'=>$pl_discount_id,
			 'pl_discount_value'=>$pl_discount_value,
			 'pl_discount_rub_or_percent'=>$pl_discount_rub_or_percent,
			 'out_bill_id'=>$out_bill_id
			 ));
			if($bpi!==false){
				
				//������ ���������� � ������������
				//$bpi['quantity']
				
				
				$delta_rasp=0;
				//������ ���������� � ������ ������������ ������������
				$set=new MysqlSet('select sum(quantity) from acceptance_position
				 where 
				 	acceptance_id<>"'.$acceptance_id.'" 
					and position_id="'.$position_id.'" 
					and pl_position_id="'.$pl_position_id.'" 
					and pl_discount_id="'.$pl_discount_id.'" 
					and pl_discount_value="'.$pl_discount_value.'" 
					and pl_discount_rub_or_percent="'.$pl_discount_rub_or_percent.'" 
					and out_bill_id="'.$out_bill_id.'" 
					and acceptance_id in(select id from acceptance where sh_i_id="'.$bpi['sh_i_id'].'" and is_confirmed=1)');
				$rs=$set->getResult();
				
				$f=mysqli_fetch_array($rs);
				
				$delta_rasp=round(($quantity-($bpi['quantity']-(float)$f[0])),3);
				
				//���������� - ��� ������ >0
				//���������� - ��� ������ <0
				//echo $delta_rasp; 
				
				if(($delta_rasp!=0)
				/*(($delta_rasp>0)&&($change_high_mode==1))||
				
				(($delta_rasp<0)&&($change_low_mode==1))*/
				){
					
					//������ ������������
					$new_quantity=round(($bpi['quantity']+$delta_rasp),3);
					
					$bpmi=$_bpmi->GetItemByFields(array('sh_i_position_id'=>$bpi['id']));
					if($bpmi!==false){
						$pms=array(
							'plus_or_minus'=>$bpmi['plus_or_minus'],
							'rub_or_percent'=>$bpmi['rub_or_percent'],
							'value'=>$bpmi['value']
						);	
					}else $pms=NULL;
					
					
					
					
					$_bpi->Edit($bpi['id'],array('quantity'=>$new_quantity), $pms);
					
					//������� �������������� � ������������ �� ��������
					$_ni=new ShINotesItem;
					
					$note='���������� ������� '.SecStr($bpi['name']).' ������������� ��������� ��� ����������� ���������� ����������� �'.$acceptance_id.' ����������� '.$_result['name_s'].' ('.$_result['login'].')'.'. ��������� ���������� '.$bpi['quantity'].', �������� ���������� '.$new_quantity;
					
					$_ni->Add(array(
						'user_id'=>$ai['sh_i_id'],
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$_result['id'],
						'note'=>$note
					
					));
					
					//������ � ������ ������������ �� ��������
					$log->PutEntry($_result['id'],'�������������� ������������ ������� ������������ �� ������� ��� ����������� ���������� �����������',  NULL, 642,NULL, $note, $ai['sh_i_id']);
					
					
					//�������������� � �����������
					$_ani=new AccNotesItem;
					$note='���������� ������� '.SecStr($bpi['name']).' � ������������ �� ������� �'.$ai['sh_i_id'].' ������������� ��������� ��� ����������� ����������� ����������� '.$_result['name_s'].' ('.$_result['login'].')'.'. ��������� ���������� '.$bpi['quantity'].', �������� ���������� '.$new_quantity;
					
					$_ani->Add(array(
						'user_id'=>$ai['id'],
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$_result['id'],
						'note'=>$note
					
					));
					
					
					
					
					
					//������ �����....
					//�� ���� �������, ����� ��������� ���-�� � ������� ��������
					if(!(($delta_rasp<0)&&($change_low_mode==0))){
					
					  $_bill_positem=new BillPosItem;
					  $_bill_pospmitem=new BillPosPMItem;
					  
					 					  
					  $bill_positem=$_bill_positem->Getitembyfields(array(
					  	'bill_id'=>$ai['bill_id'], 
						'position_id'=>$position_id, 
						'pl_position_id'=>$pl_position_id, 
						'pl_discount_id'=>$pl_discount_id, 
						'pl_discount_value'=>$pl_discount_value,
						'pl_discount_rub_or_percent'=>$pl_discount_rub_or_percent,
						'out_bill_id'=>$out_bill_id
						)); 
					  if($bill_positem!==false){
						  
						  
						  $bill_pospmitem=$_bill_pospmitem->GetItemByFields(array('bill_position_id'=>$bill_positem['id']));
						  if($bill_pospmitem!==false){
							  $pms=array(
								  'plus_or_minus'=>$bill_pospmitem['plus_or_minus'],
								  'rub_or_percent'=>$bill_pospmitem['rub_or_percent'],
								  'value'=>$bill_pospmitem['value'],
								  //'discount_plus_or_minus'=>$bill_pospmitem['discount_plus_or_minus'],
								  'discount_rub_or_percent'=>$bill_pospmitem['discount_rub_or_percent'],
								  'discount_value'=>$bill_pospmitem['discount_value']
							  );	
						  }else $pms=NULL;
						  
						  
						  
						  $new_bill_quantity=round(($bill_positem['quantity']+$delta_rasp),3);
						  
						  $_bill_positem->Edit($bill_positem['id'], array('quantity'=>$new_bill_quantity), $pms);
						  
						  //������� �������������� � �����
						  $_ni=new BillNotesItem;
					  
						  $note='���������� ������� '.SecStr($bill_positem['name']).' ������������� ��������� ��� ����������� ���������� ����������� �'.$acceptance_id.' ����������� '.$_result['name_s'].' ('.$_result['login'].')'.'. ��������� ���������� '.$bill_positem['quantity'].', �������� ���������� '.$new_bill_quantity;
						  
						  $_ni->Add(array(
							  'user_id'=>$ai['bill_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
						  ));
						  
						  //������ � ������ ����� - ,
						  $log->PutEntry($_result['id'],'�������������� ������������ ������� ��������� ����� ��� ����������� ���������� �����������',  NULL, 610,NULL, $note, $ai['bill_id']);
					  
					  	  
						  //�������������� � �����������
						  $_ani=new AccNotesItem;
						  $_bi=new BillInItem;
						  $bill=$_bi->GetItemById($ai['bill_id']);
						  $note='���������� ������� '.SecStr($bpi['name']).' �� �������� ����� �'.$bill['code'].' ������������� ��������� ��� ����������� ����������� ����������� '.$_result['name_s'].' ('.$_result['login'].')'.'. ��������� ���������� '.$bill_positem['quantity'].', �������� ���������� '.$new_bill_quantity;
						  
						  $_ani->Add(array(
							  'user_id'=>$ai['id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
						  ));
					  }
					}
					
					
					
				}
				
				
				
			}
		}
		//die();
	}
	
	
	
}
?>