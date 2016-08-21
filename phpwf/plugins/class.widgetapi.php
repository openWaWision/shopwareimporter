<?php

class WidgetAPI 
{
  private $app;

  function WidgetAPI($app)
  {
    $this->app = &$app;
  }

  function Get($name, $parsetarget)
  {
    if(file_exists("widgets/widget.$name.php")) {
      include_once("widgets/widget.$name.php");
      //echo "es gibt ein modifiziertes objecy";
      $classname = "Widget".ucfirst($name);
      return new $classname($this->app,$parsetarget);	
    } else {
      //echo "es gibt nur das generiewrte";
      include_once("widgets/_gen/widget.gen.$name.php");
      //echo "es gibt ein modifiziertes objecy";
      $classname = "WidgetGen".ucfirst($name);
      return new $classname($this->app,$parsetarget);	
    }


  }
}
?>
