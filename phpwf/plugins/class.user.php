<?php


class User 
{

  function User(&$app)
  {
    $this->app = &$app;
  }

  function GetID()
  { 
    return $this->app->DB->Select("SELECT user_id FROM useronline WHERE sessionid='".session_id()."'
      AND ip='".$_SERVER[REMOTE_ADDR]."' AND login='1'");
  }

  function GetType()
  { 
    if($this->app->Conf->WFdbname!="")
      $type = $this->app->DB->Select("SELECT type FROM user WHERE id='".$this->GetID()."'");
    if($type=="")
      $type = $this->app->Conf->WFconf[defaultgroup];
    
    return $type;
  }

  function GetName()
  { 
    return $this->app->DB->Select("SELECT username FROM user WHERE id='".$this->GetID()."'");
  }

  function GetDescription()
  { 
    return $this->app->DB->Select("SELECT description FROM user WHERE id='".$this->GetID()."'");
  }

  function GetAdresse()
  { 
    return $this->app->DB->Select("SELECT adresse FROM user WHERE id='".$this->GetID()."'");
  }

  function GetFirma()
  { 
    return $this->app->DB->Select("SELECT firma FROM adresse WHERE id='".$this->GetAdresse()."'");
  }



}
?>
