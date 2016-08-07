<?php
require_once('PageNavigatorOld.php');
////////////////////////////////////////////////////////////////////
class PageNavigator extends PageNavigatorOld{
	//data members
	private $pagename;
	private $totalpages;
	private $recordsperpage;
	private $maxpagesshown;
	private $currentstartpage;
	private $currentendpage;
	private $currentpage;
	//next and previous inactive
	private $spannextinactive;
	private $spanpreviousinactive;
	//first and last inactive
	private $firstinactivespan;
	private $lastinactivespan;	
	//must match $_GET['offset'] in calling page
	private $firstparamname="from";
	//use as "&name=value" pair for getting
	private $params;
	//css class names
	private $inactivespanname = "inactive";
	private $inactivespanname1 = "inactive1";
	private $pagedisplaydivname = "alblinks1";
	private $divwrappername = "alblinks";
	
	private $activenext='activenext';
	private $inactivenext='inactivenext';
	private $activeprev='activeprev';
	private $inactiveprev='inactiveprev';
	
	private $activefirst='activefirst';
	private $inactivefirst='inactivefirst';
	private $activelast='activelast';
	private $inactivelast='inactivelast';
	
	
	private $numlink="num";
	
	//text for navigation
	private $strfirst = "&nbsp;";
	private $strnext = "���������";
	private $strprevious = "����������";
	private $strlast = "&nbsp;";
  //for error reporting
	private $errorstring;	
////////////////////////////////////////////////////////////////////
//constructor
////////////////////////////////////////////////////////////////////
  public function __construct($pagename, $totalrecords, $recordsperpage, $recordoffset, $maxpagesshown = 10, $params = ""){
  	$this->pagename=$pagename;
  	$this->recordsperpage=$recordsperpage;	
		$this->maxpagesshown=$maxpagesshown;
		//already urlencoded
		$this->params=$params;
    //check recordoffset a multiple of recordsperpage
		if(!$this->checkRecordoffset($recordoffset, $recordsperpage)){
		  throw new Exception($this->errorstring);
    }
  	$this->setTotalPages($totalrecords, $recordsperpage);
		$this->calculateCurrentPage($recordoffset, $recordsperpage);
		$this->createInactiveSpans();	
		$this->calculateCurrentStartPage();
		$this->calculateCurrentEndPage();
  }
////////////////////////////////////////////////////////////////////
//public methods
////////////////////////////////////////////////////////////////////
//give css class name to inactive span
////////////////////////////////////////////////////////////////////
  public function setInactiveSpanName($name){
  	$this->inactivespanname=$name;
		//call function to rename span
		$this->createInactiveSpans();	
  }
////////////////////////////////////////////////////////////////////
  public function getInactiveSpanName(){
  	return $this->inactivespanname;
  }
////////////////////////////////////////////////////////////////////
  public function setPageDisplayDivName($name){
  	$this->pagedisplaydivname=$name;		
  }
////////////////////////////////////////////////////////////////////
  public function getPageDisplayDivName(){
  	return $this->pagedisplaydivname;
  }
////////////////////////////////////////////////////////////////////
  public function setDivWrapperName($name){
  	$this->divwrappername=$name;		
  }
////////////////////////////////////////////////////////////////////
  public function getDivWrapperName(){
  	return $this->divwrappername;
  }
////////////////////////////////////////////////////////////////////
  public function setFirstParamName($name){
  	$this->firstparamname=$name;		
  }
////////////////////////////////////////////////////////////////////
  public function getFirstParamName(){
  	return $this->firstparamname;
  }
////////////////////////////////////////////////////////////////////
	public function getNavigator(){
		if(($this->totalpages==0)||($this->totalpages==1)) return '';
		//wrap in div tag
		$strnavigator= "<div class=\"$this->divwrappername\">\n";
		//output movefirst button		
		if($this->currentpage==0){
			$strnavigator.=$this->firstinactivespan;			
		}else{
			$strnavigator .= $this->createLink(0, $this->strfirst, $this->activefirst);
		}
		//output moveprevious button
		if($this->currentpage==0){
			$strnavigator.= $this->spanpreviousinactive;			
		}else{
			$strnavigator.= $this->createLink($this->currentpage-1, $this->strprevious, $this->activeprev);
		}
		//loop through displayed pages from $currentstart
		for($x=$this->currentstartpage;$x<$this->currentendpage;$x++){
			//make current page inactive
			if($x==$this->currentpage){
				$strnavigator.= "<span class=\"$this->inactivespanname1 $this->numlink\">";
				$strnavigator.= $x+1;
				$strnavigator.= "</span>\n";
			}else{
				$strnavigator.= $this->createLink($x, $x+1, $this->numlink);
			}
		}
		//next button		
		if($this->currentpage==$this->totalpages-1){
			$strnavigator.=$this->spannextinactive;			
		}else{
			$strnavigator.=$this->createLink($this->currentpage + 1, $this->strnext, $this->activenext);
		}
		//move last button
		if($this->currentpage==$this->totalpages-1){
			$strnavigator.= $this->lastinactivespan;			
		}else{
			$strnavigator.=$this->createLink($this->totalpages -1, $this->strlast, $this->activelast);
		}
		$strnavigator.= "</div>\n";
		$strnavigator.=$this->getPageNumberDisplay();		
		return $strnavigator;
	}
////////////////////////////////////////////////////////////////////
//private methods
////////////////////////////////////////////////////////////////////
	private function createLink($offset, $strdisplay, $class="" ){
		$strtemp= "<a href=\"$this->pagename?$this->firstparamname=";
		$strtemp.= $offset*$this->recordsperpage;
		$strtemp.= "$this->params\" class=\"$class\">$strdisplay</a>\n";
		return $strtemp;
	}
////////////////////////////////////////////////////////////////////	
	private function getPageNumberDisplay(){
		$str= "<div class=\"$this->pagedisplaydivname\">\n�������� ";
		$str.=$this->currentpage+1;
		$str.= " �� $this->totalpages";
		$str.= "</div>\n";
		return $str;
	}
////////////////////////////////////////////////////////////////////
  private function setTotalPages($totalrecords, $recordsperpage){
  	$this->totalpages=ceil($totalrecords/$recordsperpage);
  }
////////////////////////////////////////////////////////////////////
	private function checkRecordoffset($recordoffset, $recordsperpage){
		$bln=true;
		//if recordoffset=0 won't show error
		if($recordoffset%$recordsperpage!=0){
			$this->errorstring="Error - Offset not a multiple of records per page.";
			$bln=false;	
		}
		return $bln;
	}
////////////////////////////////////////////////////////////////////	
	private function calculateCurrentPage($recordoffset, $recordsperpage){
		$this->currentpage=$recordoffset/$recordsperpage;
	}
////////////////////////////////////////////////////////////////////
// not always needed but create anyway
////////////////////////////////////////////////////////////////////
	private function createInactiveSpans(){
		$this->spannextinactive="<span class=\"".
			"$this->inactivespanname $this->inactivenext\">$this->strnext</span>\n";
		$this->lastinactivespan="<span class=\"".
			"$this->inactivespanname $this->inactivelast\">$this->strlast</span>\n";
		$this->spanpreviousinactive="<span class=\"".
			"$this->inactivespanname $this->inactiveprev\">$this->strprevious</span>\n";
		$this->firstinactivespan="<span class=\"".
			"$this->inactivespanname $this->inactivefirst\">$this->strfirst</span>\n";
	}
////////////////////////////////////////////////////////////////////
// find start page based on current page
////////////////////////////////////////////////////////////////////
	private function calculateCurrentStartPage(){
		$temp = floor($this->currentpage/$this->maxpagesshown);
		$this->currentstartpage=$temp * $this->maxpagesshown;
	}
////////////////////////////////////////////////////////////////////
	private function calculateCurrentEndPage(){
		$this->currentendpage = $this->currentstartpage+$this->maxpagesshown;
		if($this->currentendpage > $this->totalpages)
			$this->currentendpage = $this->totalpages;	
	}
}//end class
////////////////////////////////////////////////////////////////////
?>
