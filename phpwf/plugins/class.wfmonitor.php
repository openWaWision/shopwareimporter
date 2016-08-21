<?php

class WFMonitor
{


  function WFMonitor(&$app)
  {
    $this->app = &$app;
  }


  function Error($msg)
  {
    $this->ErrorBox($msg);
  }



  function ErrorBox($content)
  {
    $box .="
      <table border=\"1\" width=\"100%\" bgcolor=\"#ffB6C1\">
	<tr><td>phpWebFrame Error: $content</td></tr>
      </table>";

    echo $box;
  }
}
?>
