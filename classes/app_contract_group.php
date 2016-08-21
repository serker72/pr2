<?
require_once('abstractgroup.php');
require_once('app_contract_view.class.php');
require_once('discr_man.php');
require_once('user_s_item.php');
require_once('user_pos_item.php');

// абстрактная группа
class AppContractGroup extends AbstractGroup {
    //установка всех имен
    protected function init(){
        $this->tablename = 'app_contract';
        $this->pagename = 'app_contract.php';		
        $this->subkeyname = 'user_id';	
        $this->vis_name = 'is_shown';
        $this->_view = NULL;
    }

    // Показать договора
    public function ShowPos($template, DBDecorator $dec, $from=0, $to_page=ITEMS_PER_PAGE, $user_id=0, $can_create_contract, $can_edit_contract, $can_delete_contract, $can_confirm_contract, $can_unconfirm_contract, $can_annul_contract, $can_restore_contract,$can_view_all=false){
        $sm = new SmartyAdm;

        $sql = 'select p.*, 
                    u1.login as user_login, u1.name_s as user_name,
                    u2.login as user_confirm_login, u2.name_s as user_confirm_name,
                    sup.full_name as supplier_name,
                    spo.name as opf_name,
                    supc.name as supplier_contact_name, supc.position as supplier_contact_position,
                    st.name as last_status,
                    (select count(id) from app_contract_history where app_contract_id=p.id) as history_message_count
                from ' . $this->tablename . ' as p
                    left join user as u1 on p.' . $this->subkeyname . ' = u1.id	
                    left join user as u2 on p.user_confirm_id = u2.id
                    left join supplier as sup on p.supplier_id = sup.id
                    left join opf as spo on spo.id = sup.opf_id 
                    left join supplier_contact as supc on p.supplier_contact_id = supc.id
                    left join app_contract_status as st on p.status_id = st.id
                ';

        $sql_count = 'select count(*) 
                from ' . $this->tablename . ' as p
                    left join user as u1 on p.' . $this->subkeyname . ' = u1.id	
                    left join user as u2 on p.user_confirm_id = u2.id
                    left join supplier as sup on p.supplier_id = sup.id
                    left join opf as spo on spo.id = sup.opf_id 
                    left join supplier_contact as supc on p.supplier_contact_id = supc.id
                    left join app_contract_status as st on p.status_id = st.id';

        $this->_view = new AppContract_ViewGroup;

        if (!$can_view_all) {
            $sql .= ' where p.' . $this->subkeyname . ' = "'.$user_id.'" ';
            $sql_count .= ' where p.' . $this->subkeyname . ' = "'.$user_id.'" ';
        }



        $db_flt=$dec->GenFltSql(' and ');
        if(strlen($db_flt)>0){
            if($can_view_all && !$user_id) {
                $sql.=' where '.$db_flt;
                $sql_count.=' where '.$db_flt;
            }else{
                $sql.=' and '.$db_flt;
                $sql_count.=' and '.$db_flt;	
            }
        }

        $ord_flt=$dec->GenFltOrd();
        if(strlen($ord_flt)>0){
                $sql.=' order by '.$ord_flt;
        }
        
        // Debug SQL
        //echo '<p>SQL = ' . $sql . '</p>';

        $set=new mysqlSet($sql,$to_page, $from,$sql_count);
        $rs=$set->GetResult();
        $rc=$set->GetResultNumRows();
        $total=$set->GetResultNumRowsUnf();


        //page
        $navig = new PageNavigator($this->pagename, $total, $to_page, $from, 10, '&'.$dec->GenFltUri());
        $navig->SetFirstParamName('from');
        $navig->setDivWrapperName('alblinks');
        $navig->setPageDisplayDivName('alblinks1');			
        $pages= $navig->GetNavigator();

        $alls=array();
        for($i=0; $i<$rc; $i++){
                $f=mysqli_fetch_array($rs);
                foreach($f as $k=>$v) $f[$k]=stripslashes($v);
                $f['pdate']=date("d.m.Y H:i:s",$f['pdate']);

                //print_r($f);	
                $alls[]=$f;
        }

        // Заполним шаблон полями
        $current_status = '';

        $fields = $dec->GetUris();
        foreach($fields as $k => $v){
            if($v->GetName() == 'status_id'){
                $current_status = $v->GetValue();
            }

            $sm->assign($v->GetName(), $v->GetValue());	
        }
        
        // Статусы
        $as=new mysqlSet('select * from app_contract_status order by id asc');
        $rs=$as->GetResult();
        $rc=$as->GetResultNumRows();
        $ids=array(); $names=array(); 
        $ids[]=''; $names[]='';
        for($i=0; $i<$rc; $i++){
                $f=mysqli_fetch_array($rs);
                foreach($f as $k=>$v) $f[$k]=stripslashes($v);

                $ids[]=$f['id'];
                $names[]=$f['name'];
        }
        $sm->assign('status_ids',$ids);
        $sm->assign('status_names',$names);
        $sm->assign('status_id',$current_status);

        $sm->assign('from',$from);
        $sm->assign('to_page',$to_page);
        $sm->assign('pages',$pages);
        $sm->assign('items',$alls);

        //ссылка для кнопок сортировки
        $link=$dec->GenFltUri();
        $link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
        $sm->assign('link',$link);
        
        //показ конфигурации
        $sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
        $sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
        
        $sm->assign('can_create_contract', $can_create_contract);
        $sm->assign('can_edit_contract', $can_edit_contract);
        $sm->assign('can_delete_contract', $can_delete_contract);
        $sm->assign('can_confirm_contract', $can_confirm_contract);
        $sm->assign('can_unconfirm_contract', $can_unconfirm_contract);
        $sm->assign('can_annul_contract', $can_annul_contract);
        $sm->assign('can_restore_contract', $can_restore_contract);
        

        $sss = $sm->fetch($template);
        return $sss;
        //return $sm->fetch($template);
    }
    
    //список ID лидов, которых может видеть текущий сотрудник
    public function GetAvailableAppContractIds($user_id){
        $arr=array();

        $_man=new DiscrMan;

        //проверить супердоступ
        //если он есть - то это все лиды
        if($_man->CheckAccess($user_id,'w',1160)){
                $sql='select id from app_contract';
                $set=new mysqlSet($sql);
                $rs=$set->GetResult();
                $rc=$set->GetResultNumRows();

                for($i=0; $i<$rc; $i++){
                        $f=mysqli_fetch_array($rs);		
                        $arr[]=$f['id'];	
                }
        }else{
                //свои
                $sql='select id from app_contract where user_id="'.$user_id.'" '; // or created_id="'.$user_id.'" ';	

                //руководитель отдела - давать доступ к документам подчиненных
                $_ui=new UserSItem;
                $user=$_ui->GetItemById($user_id);
                $_upos=new UserPosItem;
                $upos=$_upos->GetItemById($user['position_id']);
                if($upos['is_ruk_otd']==1){
                        $sql.=' or user_id in(select id from user where department_id="'.$user['department_id'].'")';

                } 


                //echo $sql;
                $set=new mysqlSet($sql);
                $rs=$set->GetResult();
                $rc=$set->GetResultNumRows();

                for($i=0; $i<$rc; $i++){
                        $f=mysqli_fetch_array($rs);		
                        $arr[]=$f['id'];	
                }

        }


        //вставка -1 для корректности
        //if(!$except_me) $arr[]=$user_id;
        //else 
        if(count($arr)==0) $arr[]=-1;

        return $arr;	
    }  

    //проверка, будет ли доступен лид при указанном менеджере указанному сотруднику
    public function ScanAvailableByUserId($manager_id, $user_id){
            $arr=array();

            $_man=new DiscrMan;

            //проверить супердоступ
            //если он есть - то это все лиды
            if($_man->CheckAccess($user_id,'w',1160)){


                    return true;
            }else{
                    //свои
                    if($manager_id==$user_id){
                            return true;	
                    }

                    //echo $sql;

                    //руководитель отдела - давать доступ к документам подчиненных
                    $_ui=new UserSItem;
                    $user=$_ui->GetItemById($user_id);
                    $_upos=new UserPosItem;
                    $upos=$_upos->GetItemById($user['position_id']);
                    if($upos['is_ruk_otd']==1){
                            //$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
                            $user_ids=array();
                            $sql=' select id from user where department_id="'.$user['department_id'].'"';
                            $set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultnumrows();
                            for($i=0; $i<$rc; $i++){
                                    $f=mysqli_fetch_array($rs); $user_ids[]=$f['id'];	
                            }
                            if(in_array($manager_id,$user_ids)) return true;	 
                    } 



            }


            return false;

    }  
    
}
?>