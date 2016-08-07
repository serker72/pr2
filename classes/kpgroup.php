<?
require_once('abstractgroup.php');
require_once('kpitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('kpnotesgroup.php');
require_once('kpnotesitem.php');
require_once('payforbillgroup.php');

require_once('period_checker.php');
require_once('period_checker.php');
require_once('price_kind_group.php');

require_once('kp_supply_pdate_group.php');
require_once('kp_supply_pdate_item.php');
require_once('kp_form_item.php');

require_once('pl_proditem.php');
require_once('pl_positem.php');
require_once('positem.php');

require_once('kp_rights_group.php');

require_once('rl/rl_man.php');

require_once('kp_view.class.php');
require_once('kp_supply_item.php');
 

// группа коммерческих  предложений
class KpGroup extends AbstractGroup {
	protected $_auth_result;
	
	public $prefix='';
	
	protected $_item;
	protected $_notes_group;
	public $view_rules; //экземпляр класса группы правил просмотра КП
	protected $_view;
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp';
		$this->pagename='kps.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->_item=new KpItem;
		$this->_notes_group=new KpNotesGroup;
		
		
		$this->_auth_result=NULL;
		$this->view_rules=new KpRightsGroup;
		
		$this->_view=new KP_ViewGroup;
	}
	
	
	
	
	public function GainSql(&$sql, &$sql_count){
		
		$sql='select p.*,
					
					 sp.full_name as supplier_name,  
					spo.name as opf_name, 
					c.name, c.position, 
					
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					pk.name as price_kind_name,
					sd.name as supply_pdate_name,
					st.name as status_name,
					
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					
					umn.id as user_manager_id, umn.name_s as  user_manager_name, umn.login as user_manager_login,
					war.name as war_name,
					pm.name as pay_name, pm.pred, pm.pered_otgr, pm.pnr,
					
					lead.code as lead_code
					
					/*eq.name as eq_name, eq.code as eq_code*/
					
				from '.$this->tablename.' as p
					
					 left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
					left join user as u on p.user_confirm_price_id=u.id
					left join user as mn on p.manager_id=mn.id
					left join user as umn on p.user_manager_id=umn.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					left join kp_supply_pdate_mode as sd on p.supply_pdate_id=sd.id
					left join document_status as st on st.id=p.status_id
					left join supplier_contact as c on p.contact_id=c.id
					
				/*	left join kp_position as kp_pos on kp_pos.kp_id=p.id and kp_pos.parent_id=0
					left join catalog_position as eq on eq.id=kp_pos.position_id*/
					
					left join  kp_warranty as war on war.id=p.warranty_id
					left join  kp_paymode as pm on pm.id=p.paymode_id
					left join lead as lead on lead.id=p.lead_id
				
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id 
					left join user as u on p.user_confirm_price_id=u.id
					left join user as mn on p.manager_id=mn.id
					left join user as umn on p.user_manager_id=umn.id
					left join pl_price_kind as pk on p.price_kind_id=pk.id
					left join kp_supply_pdate_mode as sd on p.supply_pdate_id=sd.id
					left join document_status as st on st.id=p.status_id
					left join supplier_contact as c on p.contact_id=c.id
					
					/*left join kp_position as kp_pos on kp_pos.kp_id=p.id and kp_pos.parent_id=0
					left join catalog_position as eq on eq.id=kp_pos.position_id*/
					left join  kp_warranty as war on war.id=p.warranty_id
					left join  kp_paymode as pm on pm.id=p.paymode_id
					left join lead as lead on lead.id=p.lead_id
				
					';
		
	}
	
	
	
	
	
	
	public function ShowPos($template, //0
		DBDecorator $dec, //1
		$from=0, //2
		$to_page=ITEMS_PER_PAGE,  //3
		$can_add=false, //4
		$can_edit=false, //5
		$can_delete=false, //6
		$add_to_bill='', //7
		$can_confirm=false, //8 
		$can_super_confirm=false,  //9
		$has_header=true, //10
		$is_ajax=false, //11
		$can_restore=false, //12
		$nested_bill_positions=NULL, //13
		$can_unconfirm=false, //14
		$can_send_email=false, //15
		$can_view_re=false, //16
		$can_print=false, //17
		$can_view_re_unconfirmed=false,  //27
		$can_view_all=false, //19
		$can_view_re_extended=false, //20
		$can_view_prices=false, //21,
		$sortmode=0  //22
		
		 
	){
		
		
		$_rl=new RLMan;
		
	 
				
		//фильтровать оборудование из закрытых категорий, пр-лей
		$flt= ''; $flts=array();
		$restricted_prods=$_rl->GetBlockedItemsArr($this->_auth_result['id'], 34, 'w', 'pl_producer', 0);
		if(count($restricted_prods)>0) $flts[]='  eq.producer_id not in('.implode(', ', $restricted_prods).') ';
		
		//запросить из базы список закрытых для пол-ля категорий
		$restricted_cats=$_rl->GetBlockedItemsArr($this->_auth_result['id'], 1, 'w', 'catalog_group', 0);
		if(count($restricted_cats)>0){
			//если закрыта подгруппа
			$flts[]='  eq.group_id not in('.implode(', ', $restricted_cats).') ';
			//если закрыта группа - учесть ее подгруппы
			$flts[]='  eq.group_id not in(select id from catalog_group where parent_group_id in('.implode(', ', $restricted_cats).') )';
		}
		
	
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$this->GainSql($sql, $sql_count);
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
			
		
		}
		
		
		//фильтры по доступному оборудованию
		if(count($flts)>0) {
			if(strlen($db_flt)>0){
				$flt= ' and ';
			}else $flt= ' where ';
			
			$flt.='  p.id in(select kp_id from kp_position where position_id in(select eq.id from catalog_position as eq where '.implode(' and ',$flts).')) ';
			$sql.=$flt;
			
			$sql_count.=$flt;
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$link=$dec->GenFltUri('&', $this->prefix);
		$link=eregi_replace('&sortmode'.$this->prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$this->prefix,'action',$link);
		$link=eregi_replace('&id'.$this->prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$link);
		
		$navig->SetFirstParamName('from'.$this->prefix);
		
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
		$_prod=new PlProdItem;
		$_pos=new PosItem;
		
		//подчиненные менеджеры (если есть)
		$subords=$this->view_rules->GetManagers($this->_auth_result);
		
		//оборудование по спецправам (если есть)
		$eqs=$this->view_rules->GetList($this->_auth_result, 2);
		
		$_basis=new KpSupplyItem;
		
		$_pdm=new PlDisMaxValGroup;
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			if($f['supply_pdate']>0) $f['supply_pdate']=date("d.m.Y",$f['supply_pdate']);
			else $f['supply_pdate']='-';
			
			if($f['valid_pdate']>0) $f['valid_pdate']=date("d.m.Y",$f['valid_pdate']);
			else $f['valid_pdate']='-';
			
			
			
			$f['eq_name_sort']='';
			$positions=$this->_item->GetPositionsArr($f['id'], false);
			
			$prods=array();
			foreach($positions as $k=>$v){
				if($v['parent_id']==0){
					$pos=$_pos->GetItemById($v['id']);
					$prod=$_prod->GetItemById($pos['producer_id']);
					if(!in_array($prod['name'], $prods)) $prods[]=$prod['name'];
					$f['eq_name_sort']=$pos['name'];
				}
			}
			
			$f['prods']=$prods;
			$f['sp_name_sort']=$prods[0];
			
			
			//подтянуть позиции без опций, вывести их в форму
			$f['positions']=$positions;
			
			$f['total_cost']=$this->_item->CalcCost($f['id'], $positions,  $this->_auth_result);
			$f['currency']=$this->_item->currency;
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			//есть ли печатная форма...
			$_pl=new PlPosItem;
			$main_id=0; $txt_kp=''; $install_mode=0; $pl_discount_id=0; $pl_discount_value=0;
			foreach($positions as $k=>$v){
				if($v['parent_id']==0){
					$main_id=$v['pl_position_id'];
					$txt_kp=$v['txt_for_kp'];
					$pl_discount_id=$v['pl_discount_id'];
					$pl_discount_value=$v['pl_discount_value'];
					
					break;	
				}
			}
			foreach($positions as $k=>$v){	
				$pl=$_pl->GetItemById($v['pl_position_id']);
				if($pl['is_install']==1) $install_mode=1;
			}	
			
			$pl=$_pl->GetItemById($main_id);
			$_kp_form=new KpFormItem;
			$kp_form=$_kp_form->GetItemById($pl['kp_form_id']);
			$f['has_print_form']=(($kp_form!==false)&&(strlen($txt_kp)>0));
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			
			$f['can_unconfirm_price']=$can_unconfirm;
			//if(!$can_view_all) $f['can_unconfirm_price']=$f['can_unconfirm_price']&&($this->_auth_result['id']==$f['manager_id']);
			
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id'],0,0,false,false,false,0,false);
			
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$this->_item->GetBindedDocumentsToAnnul($f['id']);
			
			
			
			//до п поля
			//$pl
			$basis=$_basis->GetItemByFields(array('producer_id'=>$pl['producer_id'], 'price_kind_id'=>$f['price_kind_id']));
			$f['basis']=$basis['name'];
			$f['install_mode']=$install_mode;
			
			//$f['dis_in_card']=$_pdm->GetItemsByKindPosArr($main_id, $f['price_kind_id'],0,$this->_auth_result['id']); 
			$f['pl_discount_id']=$pl_discount_id;
			$f['pl_discount_value']=$pl_discount_value;
			
			
			
			//флаг доступности операций
			$has_access=false;
			
			$has_access=$has_access||$can_view_all;
			//свои кп
			if(!$has_access) $has_access=$has_access||($this->_auth_result['id']== $f['user_manager_id']);
			//кп подчиненных менеджеров
			if(!$has_access) $has_access=$has_access||in_array($f['user_manager_id'], $subords);		
			
			//кп по Особым правам по оборудованию
			if(!$has_access) $has_access=$has_access||in_array($main_id, $eqs);
			
			$f['has_access']=$has_access;
			
			//echo $f['binded_payments'];
			 
			
			$alls[]=$f;
		}
		
		
		
		switch($sortmode){
				case 6:
					$alls=$this->SortArr($alls,'eq_name_sort',1);
				break;
				case 7:
					$alls=$this->SortArr($alls,'eq_name_sort',0);
				break;	
				
				case 8:
					$alls=$this->SortArr($alls,'sp_name_sort',1);
				break;
				case 9:
					$alls=$this->SortArr($alls,'sp_name_sort',0);
				break;	
				
			}
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price=''; $current_user_confirm_price_id='';
		$current_sector='';
		$current_price_kind_id='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		//	if($v->GetName()=='sector_id'.$add_to_bill) $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id'.$add_to_bill) $current_supplier=$v->GetValue();
		//	if($v->GetName()=='storage_id'.$add_to_bill) $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			if($v->GetName()=='price_kind_id'.$add_to_bill) $current_price_kind_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
		
		
		$_pkg=new PriceKindGroup;
		$pkg=$_pkg->GetItemsByFieldsArr(array('is_calc_price'=>1));
		//print_r($pkg);
		$price_kind_ids=array(); $price_kind_id_vals=array();
		$price_kind_ids[]=0; 	 $price_kind_id_vals[]='все КП';	
		foreach($pkg as $k=>$v){
			$price_kind_ids[]=$v['id'];
			$price_kind_id_vals[]=$v['name'];	
		}
		$sm->assign('price_kind_ids', $price_kind_ids);
		$sm->assign('price_kind_id_vals', $price_kind_id_vals);
		
		
		$_pkg=new KpSupplyPdateGroup;
		$pkg=$_pkg->GetItemsArr();
		//print_r($pkg);
		$price_kind_ids=array(); $price_kind_id_vals=array();
		$price_kind_ids[]=0; 	 $price_kind_id_vals[]='все КП';	
		foreach($pkg as $k=>$v){
			$price_kind_ids[]=$v['id'];
			$price_kind_id_vals[]=$v['name'];	
		}
		$sm->assign('supply_pdate_ids', $price_kind_ids);
		$sm->assign('supply_pdate_id_vals', $price_kind_id_vals);
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
			$sm->assign('can_confirm_price',$can_confirm);
			$sm->assign('can_unconfirm_price',$can_unconfirm);
		$sm->assign('can_super_confirm_price',$can_unconfirm);
		$sm->assign('can_send_email', $can_send_email);
		$sm->assign('can_view_re', $can_view_re);
		$sm->assign('can_view_re_unconfirmed', $can_view_re_unconfirmed);
		
		$sm->assign('can_view_all', $can_view_all);
	
		$sm->assign('can_print', $can_print);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('prefix',$this->prefix);
		
		$sm->assign('can_view_re_extended', $can_view_re_extended);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('can_view_prices', $can_view_prices);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('action'.$this->prefix,'action',$link);
		$link=eregi_replace('&id'.$this->prefix,'&id',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($result['id']));
		
		//echo $sm->fetch($template);
		
		return $sm->fetch($template);
	}
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and is_incoming="'.$this->is_incoming.'" order by  id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	

	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=7, $days_after_restore=7, $annul_status_id=3){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
	 
		$_ni=new KpNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			
			//проверить связанные счета
			$sql1='select count(id) from bill where id in(select distinct bill_id from bill_position where kp_id="'.$f['id'].') and is_confirmed_price=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			$sql1='select count(id) from sh_i_id where id in(select distinct sh_i_id from sh_i_position where kp_id="'.$f['id'].') and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			$sql1='select count(id) from acceptance where id in(select distinct acceptance_id from acceptance_position where kp_id="'.$f['id'].')  and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed_price']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления коммерческого предложения, нет утвержденных связанных документов,  документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания коммерческого предложения,  нет утвержденных связанных документов,  документ не утвержден';
					}
				}
			}
			/*
			elseif(($f['is_confirmed_price']==1)){
				//галочка есть, счетов нет... проверим дату Действ по.:
				if(($f['valid_pdate']!=0)&&(time()>($f['valid_pdate']+24*60*60-1))){
					$can_annul=true;
					$reason='прошло '.ceil((time()-($f['valid_pdate']+24*60*60-1))/(24*60*60)).' дней с даты создания коммерческого предложения, нет утвержденных связанных документов';
				}else $can_annul=false;	
				
				
			}
			*/
			
			
			
			
			
			
			
			if($can_annul){
				$this->_item->Edit($f['id'], array('is_confirmed_price'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование коммерческого предложения',NULL,713,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: коммерческое предложение было автоматически аннулировано, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	
	//автоматический переход в статус "не актуально"
	public function AutoInactual($annul_status_id=27){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
	 
		$_ni=new KpNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			
			
			
			
			if($f['is_confirmed_price']==1){
				
				
			 
				
				if(($f['valid_pdate'])>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='окончился срок действия коммерческого предложения';
				}
				 
			}
			 
			
			
			
			
			
			
			if($can_annul){
				$this->_item->Edit($f['id'], array('status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическая смена статуса коммерческого предложения',NULL,701,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: коммерческое предложение автоматически перешло в статус '.$stat['name'].', причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	
	
	/*
	
	//автоматическое выравнивание
	public function AutoEq($days=30, $days_no_acc=45){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new BillNotesItem;
		$_itm=new BillItem;
		
		//перебрать все счета в статусе "утв" или "не вып"
		
		 
		
		$now=time();
		
		$sql='select * from bill where status_id in(2,9) and is_confirmed_price=1 and is_confirmed_shipping=1 and cannot_eq=0 order by id desc';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$can_annul=false;
		
			//проверка по завозу
			$checked_time=$now-$days*24*60*60;
			$checked_time_noacc=$now-$days_no_acc*24*60*60;
			
			$sql1='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1  order by given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$can_annul=true;	
				$g=mysqli_fetch_array($rs1);
				//print_r($g);
			}
			
			
			
			//работать со счетом, только если есть завоз (неважно какой даты)
			if($can_annul){
				//найти позиции счета
				
				$posset=new mysqlset('select * from bill_position where bill_id='.$f['id'].'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				
				
				$was_eqed=false;
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					$args=array();
					$can_annul_position=false;
					//по каждой из позиций - получим связанные реализации
					//если дата последнего поступления (и оно есть) раньше 30 дней - выравн. позицию
					//иначе - проверим дату последнего поступления...
					
					$sql3='select * 
					from acceptance as a inner join acceptance_position as ap on a.id=ap.acceptance_id 
					where 
						a.is_confirmed=1 
						and a.bill_id='.$f['id'].' 
						 
						and ap.position_id='.$h['position_id'].' 
						and ap.pl_position_id='.$h['pl_position_id'].' 
						and ap.pl_discount_id='.$h['pl_discount_id'].' 
						and ap.pl_discount_value='.$h['pl_discount_value'].' 
						and ap.pl_discount_rub_or_percent='.$h['pl_discount_rub_or_percent'].' 
						
					order by a.given_pdate desc limit 1';
					
					$acset=new mysqlset($sql3);
					$rs3=$acset->GetResult();
					$rc3=$acset->GetResultNumRows();
					if($rc3>0){
						//завоз есть, проверить дату завоза
						$hh=mysqli_fetch_array($rs3);
						if($hh['given_pdate']<$checked_time){
							$can_annul_position=true;
							//echo 'Позиция '.$h['name'].' подлежит выравниванию, дата последнего поступления '.date('d.m.Y',$hh['given_pdate']).' '.$hh['id'].' по позиции более 30 дней назад<br>';
						}
					}else{
						//нет завоза - проверка по дате последнего завоза
						
						if($g['given_pdate']<$checked_time_noacc){
							$can_annul_position=true;
							//echo 'Позиция '.$h['name'].' подлежит обнулению, дата последнего поступления '.date('d.m.Y',$g['given_pdate']).' '.$g['id'].' по счету более 45 дней назад<br>';
						}
						
					}
					
					//если выравниваем...
					if($can_annul_position){
						
						$args[]=$h['position_id'].';'.$h['pl_position_id'].';'.$h['pl_discount_id'].';'.$h['pl_discount_value'].';'.$h['pl_discount_rub_or_percent'].';'.$h['quantity'];	
					//	echo 'Выравниваю позицию '.$h['name'].' <br>';
						
						$_itm->DoEq($f['id'],$args,$some_output, 1, $f, $_result,true);
						$was_eqed=$was_eqed||true;
					}else{
					//	echo 'НЕ выравниваю позицию '.$h['name'].' <br>';
					}
				}// of bill positions
				if($was_eqed){
					//echo 'Счет '.$f['code'].' '.$f['id'].' подлежит выравниванию <br><br>';
					$_itm->ScanDocStatus($f['id'],array(),array(),$f,$_result);		
				}
				
			}//of bill
		}
		 
		
	}*/
	
	
	
	
	
	
	//сортировка выходного массива
	protected function SortArr($arr, $fieldname, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min	
				$a=$this->FindMin($arr, $fieldname, $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}else{
				//max
				$a=$this->FindMax($arr, $fieldname, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	protected function FindMin($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$minval='aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
		foreach($arr as $k=>$v){
			if($v[$fieldname]<$minval){
				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$maxval='ЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯ';
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
?>