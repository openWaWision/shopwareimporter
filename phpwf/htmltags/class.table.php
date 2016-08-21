<?php

class HTMLTable
{
  var $border;
  var $cellpadding;
  var $cellspacing;
  var $width;
  var $height;
  
  var $color1;
  var $color2;

  var $nextcolor=0;

  var $xpointer; // start at 0
  var $ypointer; // start at 0

  var $Table;

  var $hidecols;
  var $headings;

	var $CompleteRow;

  /// Representiert eine HTML Tabelle
  function HTMLTable($border="1",$width="",$height="",$cellpadding="3",$cellspacing="3")
  {
    $this->border=$border;
    $this->cellpadding=$cellpadding;
    $this->cellspacing=$cellspacing;
    $this->width=$width;
    $this->height=$height;
	}

  /// komplette spalte einer tabelle mit neuem inhalt ersetzten, der alte ist in %value%
  function ReplaceCol($colnumber,$newtext)
  {
    reset($this->Table);
    $first =1;
    $rowcounter=0;
    while (list($coln, $row) = @each($this->Table))
    { 
      if($first==1)
      {
	$first=0;
	continue 1;
      }
      while (list($col, $val) = @each($row))
      {
	if( ($col+1)==$colnumber )
	{
	  $old =  $this->Table[$coln][$col]; 
	  $new=str_replace('%value%',$old,$newtext); 
	  
	  for($i=1; $i <= count($this->Table[$coln]); $i++)
	    $new=str_replace("%$i%",$this->Table[$coln][$i-1],$new); 
	  
	  $new=str_replace('%pos%',$coln,$new); 
	  $new=str_replace('%togl%',$coln%2,$new); 
	  $this->Table[$coln][$col]=$new; 
	}
      }
    }

  }

  function HideCol($number)
  {
    $this->hidecols[$number]=$number;
  }

  /// erzwingt den Cursor in eine neue Zeile
  function NewRow()
  {
    if(count($this->Table)==0)
    {
      $this->xpointer=0;
      $this->ypointer=0;
    }
    else 
    {
      $this->xpointer=0;
      $this->ypointer++;
    }
  }
  /// fuegt eine komplette Zeile an der aktuellen Zeigerpostion ein
  function AddRowAsHeading($cols)
  {
    $this->NewRow();
    foreach($cols as $value)
      $this->AddCol("<h3>".ucfirst($value)."</h3>");
  }


  /// fuegt eine komplette Zeile an der aktuellen Zeigerpostion ein
  function AddRow($cols)
  {
    $this->NewRow();
    foreach($cols as $value)
      $this->AddCol($value);
  }

  
  function AddField($field)
  {
    $rows = count($field);
    for($i=0;$i<$rows;$i++)
    {
      $this->NewRow();
      if(count($field[$i])>0)
      {
	foreach($field[$i] as $key=>$value)
	{
	  $this->AddCol(nl2br($value));
	}
      }
    }

  }

  /// fuegt neue eine neue Tabellenzelle an aktuellen Zeiger ein
  function AddCol($value)
  {
      $this->Table[$this->ypointer][$this->xpointer]=$value;
      $this->xpointer++;
  }

  // fuegt eine komplette spalte am schluss dazu
  function AddCompleteCol($colnumber,$string,$value="")
  {
    $this->CompleteCol[]=array($colnumber,$string,$value);
  }

  function ActualCompleteRow($actualrow)
  {

    if($actualrow==0)
      return "";

    $cols = count($this->CompleteCol);

    for($i=0;$i < $cols;$i++)
    {
      $ret .="<td>";
      $newvalue = $this->Table[$actualrow][$this->CompleteCol[$i][0]];
      $value = $this->Table[$actualrow][$this->CompleteCol[$i][2]];
      $text = str_replace('%col%',$newvalue,$this->CompleteCol[$i][1]);
      $ret .= str_replace('%value%',$value,$text);
      $ret .="</td>";
    }
    return $ret; 
  }

  function GetMaxCols()
  {
    
    $max = 0;
    $count = count($this->Table);
    
    for($i=0;$i<$count;$i++)
    {
      if(count($this->Table[$i]) > $max )
	$max = count($this->Table[$i]);
    }
    return $max;
  }


  function ChangingRowColors($color1,$color2)
  {
    $this->color1=$color1; 
    $this->color2=$color2; 
  } 

  function GetNextColor()
  {
    $this->nextcolor++;
    if($this->nextcolor % 2==0)
      return $this->color2;
    else return $this->color1;
  }

  function Get($hidden="")
  {
    //hidden jede zweite zeile ist versteckt
    $rows = count($this->Table);
    if($rows>0)
    {
      $html = "<table border=\"{$this->border}\" cellpadding=\"{$this->cellpadding}\"
	cellspacing=\"{$this->cellspacing}\" width=\"{$this->width}\" height=\"{$this->height}\">";

      $cols = $this->GetMaxCols();

      for($i=0;$i<$rows;$i++)
      {
	if(($i % 2)==0 && $i > 1 && $hidden==1) $none="none"; else $none="";

	// zwei zeilen kriegen die gleiche nummer nur eins edit hinten dran
	//echo $i." $none<br>";
	$html .="<tr valign=\"top\" style=\"background-color:".$this->GetNextColor().";display:$none;\" id=\"{$i}\">\n";
	for($j=0;$j<$cols;$j++)
	{
	  if($this->hidecols[$j+1]=="")
	  {
	    $html .="<td>";
	    $html .= $this->Table[$i][$j]?$this->Table[$i][$j]:"&nbsp;";
	    $html .="</td>";
	  }
	}
	// get complete cols
	$html .=$this->ActualCompleteRow($i);
	$html .="</tr>";
      }
      $html .="</table>";
    }
    return $html; 
  }

  function GetClose(){}
}
?>
