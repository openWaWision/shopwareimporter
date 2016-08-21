<?php

/// represent a HTML Form structure
class HTMLForm
{
  var $action;
  var $method;
  var $name;
  var $id;
 
  var $FieldList;
 
  function HTMLForm($action="",$method="post",$name="",$id="")
  {
    $this->action=$action;
    $this->name=$name;
    $this->method=$method;
    $this->id=$id;
  }

  function Set($value)
  {
  }

  function Get()
  {

  }

  function GetClose()
  {
  }
}



class HTMLTextarea
{
  var $name;
  var $rows;
  var $value;
  var $cols;
  var $id="";
  var $readonly="";
  var $disabled="";
  var $class;

  function HTMLTextarea($name,$rows,$cols,$defvalue="",$id="",$readonly="",$disabled="")
  {
    $this->name = $name;
    $this->rows = $rows;
    $this->cols = $cols;
    $this->value = $defvalue;
    $this->id = $id;
    $this->readonly = $readonly;
    $this->disabled = $disabled;
    $this->class="";
  }

  function Get()
  {
    // TEMP ACHTUNG HIER IST MIST!!!
    $value =   preg_replace('/<br\\s*?\/??>/i', "\n", $this->value);
    $value = str_replace("\\r\\n","\n",$value);
    
    $html = "<textarea rows=\"{$this->rows}\" id=\"{$this->id}\" class=\"{$this->class}\"
       name=\"{$this->name}\" cols=\"{$this->cols}\" 
       {$this->readonly} {$this->disabled}>$value</textarea>";
    return $html;
  }
  
  function GetClose()
  {
  }
}


/// fuer Datenfelder die mit in die Datenbank o.ae. kommen sollen, aber nicht durch den 
/// user in irgendeiner art und weise gesehen und manipuliert werden koennen

class BlindField
{
  var $name;
  var $value;

  function BlindField($name,$value)
  {
    $this->name = $name;
    $this->value = $value;
  }
  function Get(){}
  function GetClose(){}
}



class HTMLCheckbox extends HTMLInput
{
  function HTMLCheckbox($name,$value,$defvalue,$checkvalue="")
  {
    
    if($checkvalue!="")
      $this->checkvalue=$checkvalue;
    else
      $this->checkvalue=$value;

    $this->name = $name;
    $this->type = "checkbox";
    $this->checkradiovalue = $okvalue;
    $this->defvalue = $defvalue;
    $this->value = $value;
    $this->orgvalue = $value;
  }


  function Get()
  {
    if(($this->value=="" && $this->defvalue==$this->checkvalue)) {
    }
    if($this->checkvalue==$this->value) {
      $this->checked="checked";
    }
    if($this->value=="" && $this->defvalue!=$this->checkvalue)
      $this->checked="";

    $this->value = $this->checkvalue;
    //$this->value=1;
    return parent::Get();
  }

  function GetClose()
  {
  }

};

class HTMLInput
{
  var $name;
  var $type;
  var $value;
  var $dbvalue;
  var $checkvalue;
  var $defvalue;
  var $size;
  var $maxlength;
  var $id="";
  var $readonly="";
  var $disabled="";
  var $class;
  var $checked;

  function HTMLInput($name,$type,$value,$size="",$maxlength="",$id="",$defvalue="",$checked="",$readonly="",$disabled="")
  {
    $this->name = $name;
    $this->type = $type;
    $this->value = $value;
    $this->size = $size;
    $this->maxlength = $maxlength;
    $this->id = $id;
    $this->readonly = $readonly;
    $this->disabled = $disabled;
    $this->class="";
    $this->checked=$checked;
    $this->defvalue=$defvalue; // if value is empty use this

  }

  function Get()
  {
    if($this->id=="") $this->id = $this->name;

    switch($this->type)
    {
      case "text":
	$html = "<input type=\"{$this->type}\" id=\"{$this->id}\"  class=\"{$this->class}\"
	  name=\"{$this->name}\"  value=\"{$this->value}\"  size=\"{$this->size}\"
	  maxlength=\"{$this->maxlength}\" {$this->readonly} {$this->disabled}>";
      break;
      case "password":
	$html = "<input type=\"{$this->type}\" id=\"{$this->id}\"  class=\"{$this->class}\"
	  name=\"{$this->name}\"  value=\"{$this->value}\"  size=\"{$this->size}\"
	  maxlength=\"{$this->maxlength}\" {$this->readonly} {$this->disabled}>";
      break;
      case "checkbox":
	  $html = "<input type=\"{$this->type}\" id=\"{$this->id}\"  class=\"{$this->class}\"
	  name=\"{$this->name}\"  value=\"{$this->value}\" {$this->checked}
	  {$this->readonly} {$this->disabled}>";
      break;
      case "radio":
	$html = "<input type=\"{$this->type}\" id=\"{$this->id}\"  class=\"{$this->class}\"
	name=\"{$this->name}\"  value=\"{$this->value}\" {$this->checked}
	  {$this->readonly} {$this->disabled}>";
      break;
      case "submit":
	$html = "<input type=\"{$this->type}\" id=\"{$this->id}\"  class=\"{$this->class}\"
	  name=\"{$this->name}\"  value=\"{$this->value}\" 
	  {$this->readonly} {$this->disabled}>";
      break;
      case "hidden":
	$html = "<input type=\"{$this->type}\" id=\"{$this->id}\"  class=\"{$this->class}\"
	  name=\"{$this->name}\"  value=\"{$this->value}\"  size=\"{$this->size}\"
	  maxlength=\"{$this->maxlength}\" {$this->readonly} {$this->disabled}>";
      break;
    }
	
    return $html;
  }
  
  function GetClose()
  {
  }
}


class HTMLSelect
{
  var $name;
  var $size;
  var $id;
  var $readonly;
  var $disabled;

  var $options;
  var $selected;

  var $class;

  function HTMLSelect($name,$size,$id="",$readonly=false,$disabled=false)
  {
    $this->name=$name;
    $this->size=$size;
    $this->id=$id;
    $this->readonly=$readonly;
    $this->disabled=$disabled;
    $this->class="";
  }

  function AddOption($option,$value)
  {
    $this->options[] = array($option,$value);
  }
 
  function AddOptionsSimpleArray($values)
  {
    foreach($values as $key=>$value)
      $this->options[] = array($value,$value);
  }
 
  function AddOptions($values)
  {
    $number=0;
    if(count($values)>0)
    {
      foreach($values as $key=>$row)
	foreach($row as $value)
	{
	  if($number==0){
	    $option=$value;
	    $number=1;
	  }
	  else {
	    $this->options[] = array($option,$value);
	    $number=0;
	    $option="";
	  }
	}
    }

  }
  
  function Get()
  {
    $html = "<select name=\"{$this->name}\" size=\"{$this->size}\" 
      id=\"{$this->id}\"  class=\"{$this->class}\">";

    if(count($this->options)>0)
    {
      foreach($this->options as $key=>$value)
      {
	if($this->value==$value[1])
	  $html .="<option value=\"{$value[1]}\" selected>{$value[0]}</option>";
	else
	  $html .="<option value=\"{$value[1]}\">{$value[0]}</option>";
      }

    }
    $html .="</select>";
    return $html;
  }

  function GetClose()
  {
  }

}

?>
