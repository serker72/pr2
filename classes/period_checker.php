<?
require_once('abstractitem.php');
require_once('pergroup.php');

//����� ��� �������� �������� ��� (�� ����� ��������� �������)
class PeriodChecker{
	
	
	public function GetDate(){
		//����� - ����� ���������� �� ������� ��������
		
		return '01.03.2015';	
	}
	
	
	//�������� ����, �� ������ �������� � �������� ������
	public function CheckDateByPeriod($pdate, $org_id, &$rss, $periods=NULL){
		$rss='';
		$res=true;
		
		if($periods===NULL){
			$_pg=new PerGroup;
			$periods=$_pg->GetItemsByIdArr($org_id,0,1);	
		}
		
		foreach($periods as $k=>$v){
			if(($pdate>=$v['pdate_beg_unf'])&&($pdate<=$v['pdate_end_unf'])){
				$rss.=' �������� � �������� ������ � '.$v['pdate_beg'].' �� '.$v['pdate_end'];
				$res=$res&&false;
				break;	
			}
		}
		
		return $res;
	}
	
	
}
?>