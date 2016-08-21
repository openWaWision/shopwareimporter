<?php


class EasyTable {
  
  var $app;

  var $rows;
  var $dataset;
  var $headings;

  function EasyTable($app) 
  {
    $this->app = $app;
    $this->sql ="";
    $this->limit ="";
  }

  function Query($sql,$limit=0,$newevent="")
  {
    $this->sql = $sql; 
    $this->limit= $limit; 
    $this->headings="";
    
    if($limit!=0){
      $page = $this->app->Secure->GetGET("page");
      if(!is_numeric($page)) $page = 1;

      $this->page = $page;
      $this->start= ($page-1) * $this->limit; 

      $sql.= " LIMIT {$this->start},{$this->limit}";
    }
    $this->datasets = $this->app->DB->SelectArr($sql);
    if(count($this->datasets)>0){
      foreach($this->datasets[0] as $colkey=>$value)
	$this->headings[]=ucfirst($colkey);
    }
    if($newevent!="noAction")
      $this->headings[count($this->headings)-1] = 'Aktion';
  }

  function DisplayWithSort($parsetarget)
  {
    
    $htmltable = new HTMLTable(0,"100%","",3,1);
    $htmltable->AddRowAsHeading($this->headings);

    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0){
      foreach($this->datasets as $row){
	$htmltable->NewRow();
	foreach($row as $field)
	  $htmltable->AddCol($field);
      }
    } 
    $module = $this->app->Secure->GetGET("module");
    $htmltable->ReplaceCol(count($this->headings),
      "<a href=\"index.php?module=$module&action=edit&id=%value%\">l&ouml;schen</a>
      &nbsp;<a href=\"index.php?module=$module&action=delete&id=%value%\">new</a>
      &nbsp;<a href=\"index.php?module=$module&action=delete&id=%value%\">nauf</a>
      &nbsp;<a href=\"index.php?module=$module&action=delete&id=%value%\">nunda</a>
      ");
    
    $this->app->Tpl->Set($parsetarget,$htmltable->Get());
  }
  
  function DisplayWithDelivery($parsetarget)
  {
    
    $htmltable = new HTMLTable(0,"100%","",3,1);
    $htmltable->AddRowAsHeading(array('Suchen','','',''));

    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0){
      foreach($this->datasets as $row){
	$htmltable->NewRow();
	$link="";
	$cols=0;
	foreach($row as $key=>$field){
	  if($cols<3){
	    $htmltable->AddCol($field);
	    $cols++;
	  }
	  if($key!="id")
	    $link = $link."window.opener.document.getElementsByName('$key')[0].value='$field';";
	}
	$htmltable->AddCol("<input type=\"button\" onclick=\"
	      $link
	            window.close();
		          \" value=\"OK\">
			        ");

      }
    } 
    $module = $this->app->Secure->GetGET("module");
    /*
    $htmltable->ReplaceCol(4,
      "<input type=\"button\" onclick=\"
      $link
      window.close();
      \" value=\"OK\">
      ");
   */ 
    $this->app->Tpl->Set($parsetarget,$htmltable->Get());
  }


  function DisplayOwn($parsetarget,$menu,$limit=30,$idlabel="id")
  {
    $pages = count($this->app->DB->SelectArr($this->sql)) / $this->limit;

    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    $id = $this->app->Secure->GetGET("id");

    $colmenu = "<table width=\"100%\"><tr><td><a href=\"index.php?module=$module&action={$action}&$idlabel=$id&page=before\"><</a></td>";

    for($i=0;$i<$pages;$i++)
    {
      if($this->page==($i+1))
      {
	$colmenu .= "<td><a href=\"index.php?module=$module&action={$action}&$idlabel=$id&page=".($i+1)."\"><b>".
	  ($i+1)."</b></a></td>";
      } else {
	$colmenu .= "<td><a href=\"index.php?module=$module&action={$action}&$idlabel=$id&page=".($i+1)."\">".
	  ($i+1)."</a></td>";
      }
    }
    $colmenu .= "<td><a href=\"index.php?module=$module&action={$action}&$idlabel=$id&page=next\">></a></td></tr></table>";

    $this->app->Tpl->Set($parsetarget,$colmenu);

    $htmltable = new HTMLTable(0,"100%","",3,1);
    $htmltable->AddRowAsHeading($this->headings);

    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0){
      foreach($this->datasets as $row){
	$htmltable->NewRow();
	foreach($row as $field)
	  $htmltable->AddCol($field);
      }

      for($i=0;$i<count($menu);$i++)
      {
	$menustring .= "<a href=\"index.php?module=$module&action={$menu[$i]}&$idlabel=%value%&id=$id\">{$menu[$i]}</a>&nbsp;";
      }

      $htmltable->ReplaceCol(count($this->headings),$menustring);

      $this->app->Tpl->Add($parsetarget,$htmltable->Get());
    }
    else {
      $this->app->Tpl->Add($parsetarget,"Keine Daten vorhanden!");
    }
    $this->app->Tpl->Add($parsetarget,$colmenu);
  }


  /*
    displays table with an extra row to add a new set to the DB
    by DukeDrake
  */
  function DisplayNewRow($parsetarget,$click="",$newevent="",$rowdata=array(),$formname="")
  {
    $htmltable = new HTMLTable(0,"100%","",3,1);
    $htmltable->AddRowAsHeading($this->headings);
    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0) {
      foreach($this->datasets as $row){
        $htmltable->NewRow();
        foreach($row as $field)
          $htmltable->AddCol($field);
      }
      if($newevent!="noAction"){
        $htmltable->ReplaceCol(count($this->headings),$click);
      }

    }
		
		if($formname) {
			$out = '<form name="'.$formname.'" action="" method="post">';
      $htmltable->NewRow();
      foreach($rowdata as $field) {
				if(preg_match("/type=\"hidden\"/i", $field))
					$out .= $field;
				else 
	        $htmltable->AddCol($field);
			}
			$out .= $htmltable->Get().'</form>';
		} else {
    	 $htmltable->AddRow($rowdata);
			 $out = $htmltable->Get();
		}
    $this->app->Tpl->Add($parsetarget,$out);
  }




  function DisplayWidthInlineEdit($parsetarget,$click="",$newevent="",$nomenu="false")
  {
    $htmltable = new HTMLTable(0,"100%","",3,1);
    $htmltable->AddRowAsHeading($this->headings);

    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0){
      foreach($this->datasets as $row){
	$htmltable->NewRow();
	foreach($row as $field)
	  $htmltable->AddCol($field);

	$htmltable->NewRow();

	$start = "<form>";	
	foreach($row as $key=>$field){
	  if($key!="id")
	    $htmltable->AddCol($start."<input type=\"text\" size=\"10\" value=\"$field\">");
	  else
	    $htmltable->AddCol($field."</form>");

	  $start="";
	}
      }
      $module = $this->app->Secure->GetGET("module");
      if($newevent!="noAction"){
	$htmltable->ReplaceCol(count($this->headings),$click);
      }
      $this->app->Tpl->Add($parsetarget,$htmltable->Get(1));
    }
    else {
      $this->app->Tpl->Add($parsetarget,"Keine Daten vorhanden! $newevent");
    }
  }

  function DisplayNew($parsetarget,$click="",$newevent="",$nomenu="false")
  {
    $htmltable = new HTMLTable(0,"100%","",3,1);

    // Letzte Spalte aendern
    if($newevent == "noAction")
      $this->headings[count($this->headings)-1] = $click;

    $htmltable->AddRowAsHeading($this->headings);
    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0){
      foreach($this->datasets as $row){
	$htmltable->NewRow();
	foreach($row as $field)
	  $htmltable->AddCol($field);
      }
      $module = $this->app->Secure->GetGET("module");
      if($newevent!="noAction"){
	$htmltable->ReplaceCol(count($this->headings),$click);
      }
      $this->app->Tpl->Add($parsetarget,$htmltable->Get());
    }
    else {
      $this->app->Tpl->Add($parsetarget,"Keine Daten vorhanden! $newevent");
    }
  }

  function Display($parsetarget,$clickmodule="",$clickaction="",$clicklabel="",$newevent="")
  {
    
    $htmltable = new HTMLTable(0,"100%","",3,1);
    $htmltable->AddRowAsHeading($this->headings);

    $htmltable->ChangingRowColors('#eaeaea','#feffe3');

    if(count($this->datasets)>0){
      foreach($this->datasets as $row){
	$htmltable->NewRow();
	foreach($row as $field)
	  $htmltable->AddCol($field);
      }
      $module = $this->app->Secure->GetGET("module");
      if($clickaction=="") {
	$htmltable->ReplaceCol(count($this->headings),
	  "<a href=\"index.php?module=$module&action=edit&id=%value%\">edit</a>
	  <!--<a href=\"index.php?module=$module&action=copy&id=%value%\">copy</a>-->
	  &nbsp;<a href=\"index.php?module=$module&action=delete&id=%value%\">del</a>");
      } else {
	$htmltable->ReplaceCol(count($this->headings),
	  "<a href=\"index.php?module=$clickmodule&action=$clickaction&id=%value%\">$clicklabel</a>");

      }
      $this->app->Tpl->Add($parsetarget,$htmltable->Get());
    }
    else {
      $this->app->Tpl->Add($parsetarget,"Keine Daten vorhanden! $newevent");
    }
  }

}


?>
