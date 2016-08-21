<?php

class PageBuilder
{
  private $app;

  function PageBuilder($app)
  {
    $this->app = &$app;
  }

 
  function CreateGen($tplfile)
  {
    $widgets = $this->app->Tpl->GetVars("pages/content/_gen/".$tplfile); 
    $this->CreatePage($widgets,$tplfile);
  }
  
  function Create($tplfile)
  {
    $widgets = $this->app->Tpl->GetVars("pages/content/".$tplfile); 
    $this->CreatePage($widgets,$tplfile);
  }
  
  
  function CreatePage($widgets,$tplfile)
  {
    if(count($widgets)>0){
      foreach($widgets as $key=>$varname) {
	// pruefen ob es ein widget sein soll
	if(preg_match("/^[\[]WIDGET_/",$varname)) {
	  $classname = "";
	  $varname = str_replace('[','',$varname);
	  $varname = str_replace(']','',$varname);
	  list($type,$classname,$action)=split('_',$varname);
	  
	  // pruefe ob es ein abgeleitetes gibt wenn nicht starte das generierte
	  if(file_exists("widgets/widget.".strtolower($classname).".php")) {
	    $filename = "widget.".strtolower($classname).".php";
	    $classname = "Widget".ucfirst(strtolower($classname));
	    $action = ucfirst(strtolower($action));
	    include_once("widgets/$filename");
	  } else {
	    $filename = "widget.gen.".strtolower($classname).".php";
	    $classname = "WidgetGen".ucfirst(strtolower($classname));
	    $action = ucfirst(strtolower($action));
	    include_once("widgets/_gen/$filename");
	  }
	  
	  $mywidget = new $classname($this->app,$varname);
	  $mywidget->$action();
	}
      }
    }
    $this->app->Tpl->Parse(PAGE,$tplfile);
  }

}
?>
