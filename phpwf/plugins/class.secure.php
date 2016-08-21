<?php

/// Secure Layer, SQL Inject. Check, Syntax Check
class Secure 
{
  var $GET;
  var $POST;

  function Secure()
  {
    // clear global variables, that everybody have to go over secure layer
    
    $this->GET = $_GET;
    $_GET="";
    $this->POST = $_POST;
    $_POST="";

    $this->AddRule('notempty','reg','.'); // at least one sign
    $this->AddRule('alpha','reg','[a-zA-Z]');
    $this->AddRule('digit','reg','[0-9]');
    $this->AddRule('space','reg','[ ]');
    $this->AddRule('specialchars','reg','[_-]');
    $this->AddRule('email','reg','^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$');
    $this->AddRule('datum','reg','([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})');
    
    $this->AddRule('username','glue','alpha+digit');
    $this->AddRule('password','glue','alpha+digit+specialchars');
  }
 

  function GetGET($name,$rule="",$maxlength="",$sqlcheckoff="")
  {
    return $this->Syntax(isset($this->GET[$name])?$this->GET[$name]:'',$rule,$maxlength,$sqlcheckoff);
  }

  function GetPOST($name,$rule="",$maxlength="",$sqlcheckoff="")
  {
    return $this->Syntax(isset($this->POST[$name])?$this->POST[$name]:'',$rule,$maxlength,$sqlcheckoff);
  }

  function GetPOSTArray()
  {
    if(count($this->POST)>0)
    {
      foreach($this->POST as $key=>$value)
      {
	$key = $this->GetPOST($key,"alpha+digit+specialchars",20);
	$ret[$key]=$this->GetPOST($value);
      }	
    }
    return $ret;
  }

  function GetGETArray()
  {
    if(count($this->GET)>0)
    {
      foreach($this->GET as $key=>$value)
      {
	$key = $this->GetGET($key,"alpha+digit+specialchars",20);
	$ret[$key]=$this->GetGET($value);
      }	
    }
    return $ret;
  }

  // check actual value with given rule
  function Syntax($value,$rule,$maxlength="",$sqlcheckoff="")
  {
    if(is_array($value))
      return $value;
    
    $value = strip_tags($value);

    if($maxlength!=""){
      if(strlen($value)>$maxlength)
        return "";
    }

    if($rule=="")
      return $value; //mysql_real_escape_string

    // build complete regexp

    // check if rule exists
   
    if($this->GetRegexp($rule)!=""){
      //$v = '/^['.$this->GetRegexp($rule).']+$/';
      $v = $this->GetRegexp($rule);
      if (preg_match_all('/'.$v.'/i', $value, $teffer) )
      {
	if($sqlcheckoff=="")
	  return $value; //mysql_real_escape_string
	else
	  return $value;
      }
      else
	return "";
    }
    else
    {
      echo "<table border=\"1\" width=\"100%\" bgcolor=\"#FFB6C1\">
	<tr><td>Rule <b>$rule</b> doesn't exists!</td></tr></table>";
      return "";
    }
  }


  function RuleCheck($value,$rule)
  {
    $v = $this->GetRegexp($rule);
    if (preg_match_all('/'.$v.'/i', $value, $teffer) )
      return true;
    else
      return false;
  }

  function AddRule($name,$type,$rule)
  {
    // type: reg = regular expression
    // type: glue ( already exists rules copy to new e.g. number+digit)
    $this->rules[$name]=array('type'=>$type,'rule'=>$rule);
  }

  // get complete regexp by rule name
  function GetRegexp($rule)
  {
    $rules = explode("+",$rule);
    $ret = '';
    foreach($rules as $key)
    {
        // check if rule is last in glue string
        if($this->rules[$key]['type']=="glue")
        {
          $subrules = explode("+",$this->rules[$key]['rule']);
          if(count($subrules)>0)
          {
            foreach($subrules as $subkey)
            {
              $ret .= $this->GetRegexp($subkey);
            }
          }
        }
        elseif($this->rules[$key]['type']=="reg")
        {
          $ret .= $this->rules[$key]['rule'];
        }
        else
        {
          //error
        }
    }
    if($ret=="")
      $ret = "none";
    return $ret;
  }

}


?>
