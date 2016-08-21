<?php
    
//database connection
class Config {
    
  function Config() 
  {
    include("user.inc.php");
    
    // define defaults
    $this->WFconf['defaultpage'] = 'welcome';
    $this->WFconf['defaultpageaction'] = 'list';
    $this->WFconf['defaulttheme'] = 'new';
    $this->WFconf['defaultgroup'] = 'web';
    
    // allow that cols where dynamically added so structure
    $this->WFconf['autoDBupgrade']=true;
    
    // time how long a user can be connected in seconds
    $this->WFconf['logintimeout'] = 28800;
    
    // alle vorhanden Gruppen in diesem System
    $this->WFconf['groups'] = array('web','admin');
    
    // gruppen die sich anmelden muessen
    $this->WFconf['havetoauth'] = array('admin');
    
    //menu structure
    
    // permissions welcome
    $this->WFconf['permissions']['web']['exportlink'] = array('list');
    $this->WFconf['permissions']['web']['import'] = array('auth','getlist','getfilelist','sendlist','deletearticle','addfilesubjekt','sendfile','navigation','artikelgruppen','artikelartikelgruppen','gast','deletefile','exportlink','inhalt','getfilelistarticle','sendlistlager','updateauftrag');
  }
}
?>
