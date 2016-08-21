<?php
/* Author: Benedikt Sauter <sauter@sistecs.de> 2007
 *
 * Dies ist sozusagen der Zuendschluessel fuer die Anwendung
 * Man definiert hier mit welcher Konfigurationsdatei
 * was fuer ein Bereich der Anwendung gestartet werden soll
 */
//ob_start('ob_gzhandler');

//if($_SERVER['HTTP_HOST']!="shop.embedded-projects.net")
//  header("Location: http://shop.embedded-projects.net".$_SERVER['REQUEST_URI']);
//ini_set('display_errors', '1');

// layer 1 -> mechnik steht bereit
// Melde alle Fehler außer E_NOTICE
// Dies ist der Vorgabewert in php.ini
error_reporting(E_ERROR | E_PARSE);
include("eproosystem.php");
include("conf/main.conf.php");
$config = new Config();

$app = new erpooSystem($config);



// layer 2 -> darfst du ueberhaupt?
include("phpwf/class.session.php");
$session = new Session();
$session->Check($app);
// layer 3 -> nur noch abspielen
include("phpwf/class.player.php");
$player = new Player();
$player->Run($session);


?>
