<?php

class DatabaseForm
{

  var $parsetarget;
  var $rowlimit=0;
  var $table;
  var $sql;
  var $searchform=false; // normally true
  var $sortheading=true;
  var $html;

  function DatabaseForm(&$app)
  {
    $this->app = &$app;
  }

  function CreateTable($sql,$parsetarget=PAGE)
  {
    $this->parsetarget=$parsetarget; 
    $this->sql=$sql;

    // create html table
    $this->table = new HTMLTable("0","600");
    $this->table->ChangingRowColors('#ffffff','#dddddd');

  }

  function DeleteAsk($colnumber,$msg,$module,$action)
  {
     $link = "<a href=\"#\" onclick=\"str = confirm('{$msg}');
	      if(str!='' & str!=null)
	      window.document.location.href='index.php?module=$module&action=$action&id=%value%';\">
	      loeschen</a>";

    $this->table->ReplaceCol($colnumber,$link); 
  }

  function ReplaceCol($colnumber,$link)
  {
    $this->table->ReplaceCol($colnumber,$link); 
  }

  function BuildFormNow()
  {

    $all = $this->app->DB->SelectArr($this->sql);

    while (list($key, $row) = @each($all))
    { 
      $this->table->NewRow();
      while (list($col, $val) = @each($row))
      { 
	if(count($this->cols)==0)
	  $this->table->AddCol($val); 
        else 
	{
	  if(isset($this->cols[$col]))
	    $this->table->AddCol($val); 
	}
      }
    }
  }

  function Generate()
  {
    $this->BuildFormNow();
  }

  function Show()
  {
    if($this->searchform)
      $this->html .= $this->SortAndSearchForm();

    $this->html .= $this->table->Get();
    $this->app->Tpl->Add($this->parsetarget,$this->html);
  }


  function RowLimit($number)
  {
    $this->rowlimit=$number; 
  }



  function Cols($fields)
  {
    $this->cols=array_flip($fields); 
  }

  function HideCol($number)
  {
    $this->table->HideCol($number);
  }

  function Headings($descriptions)
  {
    $this->table->AddRowAsHeading($descriptions);
    $this->descriptions=$descriptions;
  }

  function SetSortAndSearchForm($bool)
  {
    $this->searchform=$bool; 
  }

  function SetSortHeading($bool)
  {
    $this->sortheading=$bool; 
  }


  function SortAndSearchForm()
  {
    $select = new HTMLSelect("sort",1);
    if(count($this->cols)==0)
    {

    }
    else 
    {
      while (list($col, $val) = @each($this->cols))
      {
	if($this->descriptions[$col]!="")
	  $select->AddOption("Nach {$this->descriptions[$col]} Sortieren",$col);
	else
	  $select->AddOption("Nach $col Sortieren",$col);
	
      }
    }
    $html = $select->Get();

    $search = new HTMLInput("search","text","",20);
    $html .= $search->Get();

    $html .="<input type=\"submit\" value=\"Suchen\">";

    $html .="<br>";

    $alphabet = range('A', 'Z');
    $html .="<table width=\"100%\" cellpadding=\"7\"><tr>";
    foreach ($alphabet as $letter) 
      $html .= "<td><a href=\"\">$letter</a></td>";

    $html .="</tr></table>";

    
    return $html;
  }
}
?>
