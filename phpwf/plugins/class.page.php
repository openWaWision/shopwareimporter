<?php

/// central config board for the engine
class Page 
{
  var $engine;
  function Page(&$app)
  {
    $this->app = &$app;
    //$this->engine = &$engine;
  }

  /// load a themeset set
  function LoadTheme($theme)
  {
    //$this->app->Tpl->ReadTemplatesFromPath("themes/$theme/templates/");
    $this->app->Tpl->ReadTemplatesFromPath("themes/$theme/templates/");
  }
  
  /// show complete page
  function Show()
  {
    return $this->app->Tpl->FinalParse('page.tpl');
  }

  /// build navigation tree
  function CreateNavigation($menu)
  {
    $i=0;
    if(count($menu)>0){
      foreach($menu as $key=>$value){
	$i++;
        if($value[first][2]!="")
          $this->app->Tpl->Set(FIRSTNAV,' href="index.php?module='.$value[first][1].'&action='.$value[first][2].'"
          >'.$value[first][0].'</a>');
        else
          $this->app->Tpl->Set(FIRSTNAV,' href="index.php?module='.$value[first][1].'" 
	  >'.$value[first][0].'</a>');
	
	$this->app->Tpl->Parse(NAV,'firstnav.tpl');
	if(count($value[sec])>0){
	  $this->app->Tpl->Add(NAV,'<div id="firstnav'.$i.'" class="yuimenu">
                                            <div class="bd">
                                                <ul>');
          foreach($value[sec] as $secnav){
            if($secnav[2]!="")
              $this->app->Tpl->Set(SECNAV,' href="index.php?module='.$secnav[1].'&action='.$secnav[2].'"
              >'.$secnav[0].'</a>');
            else
              $this->app->Tpl->Set(SECNAV,' href="index.php?module='.$secnav[1].'">'.$secnav[0].'</a>');

            $this->app->Tpl->Parse(NAV,'secnav.tpl');
          }
	  $this->app->Tpl->Add(NAV,"</ul></div></div></li>");
        }
      }
    }
  }

}
?>
